<?php
namespace App\Services\Sales;

use App\Interfaces\{
    Sales\SaleProductKittingInterface,
    General\CwSchedulesInterface,
    Settings\SettingsInterface
};
use App\Models\{
    Kitting\Kitting,
    Products\Product,
    Users\User,
    Products\ProductPrice,
    Kitting\KittingPrice,
    Masters\MasterData,
    Bonus\EnrollmentRank
};
use App\Helpers\ValueObjects\{
    StatusMessage,
    SaleProductKitting,
    ProductKitting,
    CvBreakdown
};
use App\Facades\Master;
use App\Exceptions\Masters\InvalidSaleTypeIdException;
use Illuminate\Support\{
    Collection,
    Facades\Config
};

class CommissionService
{
    private
        $cvAcronymCodes,
        $saleTypeCvSettings,
        $transactionTypeConfigCodes,
        $cvMechanism,
        $cvRequirement,
        $cwScheduleRepository,
        $enrolmentRanks,
        $settingsRepository
    ;

    /**
     * CommissionService constructor.
     *
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param SettingsInterface $settingsInterface
     */
    public function __construct (
        CwSchedulesInterface $cwSchedulesInterface,
        SettingsInterface $settingsInterface
    )
    {
        $this->cwScheduleRepository = $cwSchedulesInterface;

        $this->settingsRepository = $settingsInterface;

        $this->cvAcronymCodes = Config('mappings.cv_acronym');

        $this->transactionTypeConfigCodes = Config('mappings.sale_types');

        $this->saleTypeCvSettings = Config('setting.sale-type-cvs');

        $this->cvMechanism = Config('setting.cv-mechanism');

        $this->cvRequirement = Config('setting.cv-requirement');
    }

    /**
     * Validate User minimum cv requirement
     *
     * @param SaleProductKittingInterface $productKitting
     * @param User $user
     * @return StatusMessage
     * @throws InvalidSaleTypeIdException
     */
    public function userMinimumCvRequirement (SaleProductKittingInterface $productKitting, User $user) : StatusMessage
    {
        // Skip check for guest
        if (!$user->isGuest()) {
            $totals = $this->calculateCvTotals($productKitting);

            // Minimum eligible cv requirement for inactive user
            // TODO : check requirement and remove this validation
            /*
            if ($user->active == false) {
                foreach ($this->cvRequirement['inactive-user'] as $mechanism => $minimum) {
                    if (isset($totals[$mechanism]) && $totals[$mechanism] < $minimum) {
                        return new StatusMessage(
                            false,
                            trans('message.cv.minimum_cv_requirement', [
                                'cv' => $minimum,
                                'eligibleType' => trans('message.cv.' . $mechanism)
                            ])
                        );
                    }
                }
            }
            */

            // Ba Upgrade Min requirement
            if (
                $this->saleHasSaleType($productKitting, $this->transactionTypeConfigCodes['ba-upgrade']) &&
                isset($totals['upgrade'])
            ) {
                $nextEnrolmentRank = $this->getUserNextEnrolmentRank($user);

                $firstBaRank = $this->getFirstBaRank();
                // Check if user has a next rank to go if so validate minimum cv requirement
                if (!is_null($nextEnrolmentRank) && !is_null($firstBaRank)) {
                    if ($totals['upgrade'] < $firstBaRank->CV) {
                        return new StatusMessage(
                            false,
                            trans('message.cv.minimum_cv_requirement', [
                                'cv' => $firstBaRank->CV,
                                'eligibleType' => trans('message.cv.upgrade')
                            ])
                        );
                    }
                }
            }

            // AMP cv minimum requirement
            if (
                $this->saleHasSaleType($productKitting, $this->transactionTypeConfigCodes['auto-maintenance']) &&
                isset($totals['amp'])
            ) {
                if ($ampPerRank = collect($this->settingsRepository ->getSettingDataByKey(['minimum_amp_cv_per_sales']))->pop()->first()) {
                    if ($totals['amp'] < $ampPerRank->value) {
                        return new StatusMessage(
                            false,
                            trans('message.cv.minimum_cv_requirement', [
                                'cv' => $ampPerRank->value,
                                'eligibleType' => trans('message.cv.amp')
                            ])
                        );
                    }
                }
            }
        }

        return new StatusMessage(
            true,
            isset($totals)
                ? collect($totals)
                    ->map(function ($total, $mechanism) {
                        return trans('message.cv.calculation', [
                            'cv' => $total,
                            'eligibleType' => trans('message.cv.' . $mechanism)
                        ]);
                    })
                    ->toArray()
                : []
        );
    }

    /**
     * Calculate cv total for mechanism
     *
     * Calculates the total cvs for a given mechanism
     *
     * @param SaleProductKittingInterface $productKitting
     * @param string $mechanism
     * @return int
     * @throws InvalidSaleTypeIdException
     */
    public function calculateCvTotalForMechanism (SaleProductKittingInterface $productKitting, $mechanism = 'eligible') : int
    {
        $cvs = [];

        foreach (
            array_merge($productKitting->getProducts(), $productKitting->getKitting())
            as $productKit
        ) {
            if ($eligibleSalesType = $this->getEligibleSaleType($productKit)) {
                $cvs[] = $this->calculateCvForMechanism($productKit->getPrice(), $eligibleSalesType, $productKit->getQuantity(), $mechanism);
            }
        }

        return array_sum($cvs);
    }

    /**
     * Calculate cv totals
     *
     * Calculates the total cvs for mechanism
     *
     * @param SaleProductKittingInterface $productKitting
     * @return array
     * @throws InvalidSaleTypeIdException
     */
    public function calculateCvTotals (SaleProductKittingInterface $productKitting) : array
    {
        $cvs = [];

        foreach (
            array_merge($productKitting->getProducts(), $productKitting->getKitting())
            as $productKit
        ) {
            if ($eligibleSalesType = $this->getEligibleSaleType($productKit)) {
                $cvs[] = $this->calculateCv($productKit->getPrice(), $eligibleSalesType, $productKit->getQuantity())
                              ->toArray();
            }
        }
        return $this->calculateTotals($cvs);
    }

    /**
     * Calculates CV for the sales type and returns value for the given mechanism
     *
     * @param ProductPrice|KittingPrice $price
     * @param MasterData $saleType master data for of the sale type
     * @param int $quantity
     * @param string $mechanism /config/setting.php[cv-mechanism] key, by default it calculates for eligible cvs
     *
     * @return int
     * @see /config/setting.php
     */
    public function calculateCvForMechanism($price, MasterData $saleType, int $quantity = 1, string $mechanism = 'eligible') : int
    {
        $calculatedCvs = $this->calculateCv($price, $saleType, $quantity);

        return isset($calculatedCvs[$mechanism])
            ? $calculatedCvs[$mechanism]
            : 0;
    }

    /**
     * Calculates CV for the sales type
     *
     * @param ProductPrice|KittingPrice $price
     * @param MasterData $saleType master data for of the sale type
     * @param int $quantity
     * @return Collection
     */
    public function calculateCv($price, MasterData $saleType, int $quantity = 1) : Collection
    {
        // calculates cv for all given mechanisms
        return ($saleTypeCvs = $this->getSaleTypeMappedCvs($price, $saleType))
            ? $saleTypeCvs->pipe(function ($cvs) use ($quantity) {
                return collect($this->cvMechanism)
                        ->map(function ($mechanism, $key) use ($cvs, $quantity) {
                            return $cvs->filter(function ($value, $cv) use ($mechanism, $quantity) {
                                return in_array($cv, $mechanism);
                            })->values()->sum() * $quantity;
                        });
            })
            : collect([]);
    }

    /**
     * See if sale has sale type in products
     *
     * @param SaleProductKittingInterface $productKitting
     * @param string $saleTypeTitle
     * @return bool
     * @throws InvalidSaleTypeIdException
     */
    public function saleHasSaleType(SaleProductKittingInterface $productKitting, string $saleTypeTitle) : bool
    {
        foreach (
            array_merge($productKitting->getProducts(), $productKitting->getKitting())
            as $productKit
        ) {
            if ($eligibleSalesType = $this->getEligibleSaleType($productKit)) {
                if (strtolower(Master::getMasterDataTitleById('sale_types', $eligibleSalesType->id)) === strtolower($saleTypeTitle)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the cvs to use for a given sale type and price
     *
     * @param ProductPrice|KittingPrice $price
     * @param MasterData $saleType master data for of the sale type
     * @return Collection|null
     */
    public function getSaleTypeMappedCvs ($price, MasterData $saleType) : ?Collection
    {
        $saleTypes = Master::getMasterDataArray('sale_types');

        // Map CVs keys to acronyms because settings uses acronym
        $cvAcronym = collect($price->toArray())
            ->filter(function ($value, $key) {
                return isset($this->cvAcronymCodes[$key]);
            })
            ->keyBy(function ($value, $key) {
                return isset($this->cvAcronymCodes[$key]) ? $this->cvAcronymCodes[$key] : $key;
            });

        return  isset($saleTypes[$saleType->id])
            ? collect($this->saleTypeCvSettings)
                // filter out the sales type settings to use for the $transactionTypeId
                // using the config.mappings.sales_types keys and config.setting.sale-type-cvs setting
                ->filter(function ($item, $salesTypeKey) use ($saleTypes, $saleType) {
                    return $this->transactionTypeConfigCodes[$salesTypeKey] === strtolower($saleTypes[$saleType->id]);
                })
                ->values()
                // Sum up only those cv which is specified in the settings to sum up
                ->pipe(function ($cvsForSalesType) use ($cvAcronym) {
                    $cvsForSalesTypeKeys = $cvsForSalesType->values()->first();

                    return $cvAcronym->filter(function ($item, $key) use ($cvsForSalesTypeKeys) {
                        return in_array($key, $cvsForSalesTypeKeys);
                    });
                })
            : null;
    }

    /**
     * Get eligible sale type for the sale product
     *
     * Checks the product sale type against products general settings of the product and validates if
     * the product can be purchased with that sales type
     *
     * @param SaleProductKitting $productAndKitting
     * @return MasterData|null
     * @throws InvalidSaleTypeIdException
     */
    public function getEligibleSaleType (SaleProductKitting $productAndKitting) : ?MasterData
    {
        if (Master::idBelongsToMaster('sale_types', $productAndKitting->getSaleType()->id)) {
            if ($productKitting = $productAndKitting->getProductKitting()) {
                $eligibleSalesType = null;

                switch (get_class($productKitting)) {
                    case (Product::class) :
                        $generalSettings = $productKitting->productGeneralSetting;

                        break;
                    case (Kitting::class) :
                        $generalSettings = $productKitting->kittingGeneralSetting;

                        break;
                }

                collect($generalSettings)
                    ->each(function ($item) use (&$eligibleSalesType, $productAndKitting) {
                        if ($item->master_data_id === $productAndKitting->getSaleType()->id) {
                            $eligibleSalesType = $productAndKitting->getSaleType();
                        }
                    });
                return $eligibleSalesType;
            }
        } else {
            throw new InvalidSaleTypeIdException;
        }
    }

    /**
     * Returns the users next enrolment rank if user can upgrade
     *
     * @param User $user
     * @return EnrollmentRank|null
     */
    public function getUserNextEnrolmentRank(User $user) : ?EnrollmentRank
    {
        if ($userEnrolmentRank = $user->member()->with('enrollmentRank')->first()) {
            return collect($this->getEnrolmentRanks())
                ->first(function ($value, $key) use ($userEnrolmentRank) {
                    return $value->CV > $userEnrolmentRank->enrollmentRank->CV;
                });
        }
    }

    /**
     * Returns the first rank for Ba member type
     *
     * @return EnrollmentRank|null
     */
    public function getFirstBaRank () : ?EnrollmentRank
    {
        if ($baUpgradeSaleType = collect($this->settingsRepository->getSettingDataByKey(['minimum_ba_upgrade_cv_per_sales']))->pop()->first()) {
            return collect(EnrollmentRank::all())
            ->first(function ($value, $key) use ($baUpgradeSaleType) {
                return $value->CV == $baUpgradeSaleType->value;
            });
        } else {
            return null;
        }
    }

    /**
     * Calculate Breakdown
     *
     * Calculates breakdown based on the product or kitting cvs, sale_types and mechanism,
     * the calculation presents the data ordered from /config/mappings.php[sales_types]
     *
     * @param ProductKitting $productKitting
     * @return CvBreakdown
     * @throws \Exception
     */
    public function calculateBreakdown(ProductKitting $productKitting) : CvBreakdown
    {
        // Sale type breakdown calculation
        $product = $productKitting->getProductKitting();

        switch (get_class($product)) {
            case (Product::class) :
                $generalSettings = $product->productGeneralSetting;

                break;
            case (Kitting::class) :
                $generalSettings = $product->kittingGeneralSetting;

                break;
        }

        $saleTypes = [];

        foreach ($generalSettings as $generalSetting) {
            if (Master::idBelongsToMaster('sale_types', $generalSetting->master_data_id)) {
                if (
                    $key = collect($this->transactionTypeConfigCodes)
                        ->flip()
                        ->first(function ($key, $title) use ($generalSetting) {
                            return strtolower($title) === strtolower(Master::getMasterDataTitleById('sale_types', $generalSetting->master_data_id));
                        })
                ) {
                    $saleTypes[$key] = $this->calculateCv(
                        $productKitting->getPrice(),
                        (new MasterData)->forceFill(['id' => $generalSetting->master_data_id])
                    )->toArray();
                }
            }
        }

        // order based on config /config/mappings.php[sales_types]
        $saleTypes = collect($saleTypes)
            ->sortBy(function ($value, $key) {
                return array_search(
                    strtolower($key),
                    collect($this->transactionTypeConfigCodes)
                        ->flip()
                        ->values()
                        ->toArray()
                );
        })->toArray();

        // CV type breakdown calculation
        $breakdown = collect($productKitting->getPrice()->toArray())
            ->filter(function ($value, $key) {
                return isset($this->cvAcronymCodes[$key]);
            })
            ->keyBy(function ($item, $key) {
                return isset($this->cvAcronymCodes[$key]) ? $this->cvAcronymCodes[$key] : $key;
            })
            ->toArray();

        return new CvBreakdown($breakdown, $saleTypes);
    }

    /**
     * GetDefaultSaleTypeWithKey
     *
     * @param CvBreakdown $breakdown
     * @return array
     * @throws \Exception
     */
    public function getDefaultSaleTypeBrakeDown (CvBreakdown $breakdown, MasterData $masterData = null) : array
    {
        return collect($breakdown->getSaleTypes())
            ->map(function ($item, $key) {
                if (isset($this->transactionTypeConfigCodes[$key])) {
                    $item['master'] = Master::getMasterDataByTitle('sale_types', $this->transactionTypeConfigCodes[$key]);
                } else {
                    $item['master'] = null;
                }
                return $item;
            })
            ->reject(function ($saleType) {
                return is_null($saleType['master']);
            })
            ->filter(function ($saleType) use ($masterData) {
                if ($masterData) {
                    return $saleType['master']->id === $masterData->id;
                } else {
                    return true;
                }
            })
            ->map(function ($item) {
                unset($item['master']);
                return $item;
            })
            ->first() ?? [];
    }

    /**
     * Calculates total cv and updates the referred object
     *
     * @param array $values
     * @return array
     */
    private function calculateTotals (array $values) : array
    {
        $totals = [];
        foreach($values as $name => $value) {
            if (is_array($value)){  // calculate total cv for sales types
                foreach ($value as $valueType => $valueRate) {
                    if (!isset($totals[$valueType])) {
                        $totals[$valueType] = 0;
                    }
                    $totals[$valueType] += $valueRate;
                }
            } else { // calculate for basic, amp and registration
                if (!isset($totals[$name])) {
                    $totals[$name] = 0;
                }

                $totals[$name] += $value;
            }
        }
        return $totals;
    }

    /**
     * returns list of enrolment ranks in CV order
     *
     * @return mixed
     */
    private function getEnrolmentRanks ()
    {
        if (!$this->enrolmentRanks) {
            $this->enrolmentRanks = $this->cwScheduleRepository->getEnrollmentRanksList(
                [
                    'sort' => 'CV',
                    'order' => 'desc',
                    'limit' => 0
                ]
            )['data'] ?? [];
        }

        return $this->enrolmentRanks;
    }
}