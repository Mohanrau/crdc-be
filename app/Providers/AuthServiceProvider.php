<?php
namespace App\Providers;

use App\Models\{Authorizations\Permission,
    Authorizations\Role,
    Authorizations\RoleGroup,
    Campaigns\Campaign,
    Campaigns\EsacPromotion,
    Campaigns\EsacVoucher,
    Campaigns\EsacVoucherSubType,
    Campaigns\EsacVoucherType,
    Currency\Currency,
    Dummy\Dummy,
    EWallets\EWallet,
    EWallets\EWalletAdjustment,
    EWallets\EWalletGIROBankPayment,
    EWallets\EWalletGIRORejectedPayment,
    EWallets\EWalletTransaction,
    Invoices\Invoice,
    Kitting\Kitting,
    Languages\Language,
    Locations\City,
    Locations\Country,
    Locations\Entity,
    Locations\Location,
    Locations\State,
    Masters\Master,
    Masters\MasterData,
    Members\Member,
    Members\MemberTree,
    Modules\Module,
    Products\Product,
    Products\ProductCategory,
    Promotions\PromotionFreeItem,
    Sales\Sale,
    Sales\SaleCancellation,
    Sales\SaleExchange,
    Settings\Tax,
    Stockists\ConsignmentDepositRefund,
    Stockists\ConsignmentOrderReturn,
    Stockists\Stockist,
    Stockists\StockistSalePayment,
    Users\User};
use App\Policies\{Authorization\RoleGroupPolicy,
    Authorization\RolePolicy,
    Campaigns\CampaignPolicy,
    Campaigns\EsacPromotionPolicy,
    Campaigns\EsacVoucherPolicy,
    Campaigns\EsacVoucherTypePolicy,
    Campaigns\EsacVoucherTypeSubPolicy,
    Currency\CurrencyPolicy,
    Dummy\DummyPolicy,
    EWallets\EWalletAdjustmentPolicy,
    EWallets\EWalletGIROBankPaymentPolicy,
    EWallets\EWalletGIRORejectedPaymentPolicy,
    EWallets\EWalletPolicy,
    EWallets\EWalletTransactionPolicy,
    Invoices\InvoicePolicy,
    Kitting\KittingPolicy,
    Languages\LanguagePolicy,
    Locations\CityPolicy,
    Locations\CountryPolicy,
    Locations\EntityPolicy,
    Locations\LocationPolicy,
    Locations\StatePolicy,
    Masters\MasterDataPolicy,
    Masters\MasterPolicy,
    Members\MemberPolicy,
    Members\MemberTreePolicy,
    Modules\ModulesPolicy,
    Products\ProductCategoryPolicy,
    Products\ProductPolicy,
    Products\PromotionFreeItemsPolicy,
    Sales\SaleCancellationPolicy,
    Sales\SaleExchangePolicy,
    Sales\SalePolicy,
    Settings\TaxPolicy,
    Stockists\ConsignmentOrderAndReturnPolicy,
    Stockists\ConsignmentOperationPolicy,
    Stockists\StockistPaymentVerificationPolicy,
    Stockists\StockistPolicy,
    Users\UserPolicy};
use Illuminate\{
    Support\Facades\Gate,
    Foundation\Support\Providers\AuthServiceProvider as ServiceProvider
};
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //allowed policies-------------------------------------------------------
        Country::class => CountryPolicy::class,
        City::class => CityPolicy::class,
        State::class => StatePolicy::class,
        Entity::class => EntityPolicy::class,
        Location::class => LocationPolicy::class,
        Tax::class => TaxPolicy::class,
        Language::class => LanguagePolicy::class,
        Master::class => MasterPolicy::class,
        MasterData::class => MasterDataPolicy::class,
        Currency::class => CurrencyPolicy::class,

        //users, role groups, roles, modules ------------------------------------
        User::class => UserPolicy::class,
        RoleGroup::class => RoleGroupPolicy::class,
        Role::class => RolePolicy::class,
        Module::class => ModulesPolicy::class,

        //products, pwp and kitting modules--------------------------------------
        Product::class => ProductPolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        PromotionFreeItem::class => PromotionFreeItemsPolicy::class,
        Kitting::class => KittingPolicy::class,
        Dummy::class =>  DummyPolicy::class,

        //members, member migrate, member statuses modules-----------------------
        Member::class => MemberPolicy::class,
        MemberTree::class => MemberTreePolicy::class,

        //sales modules----------------------------------------------------------
        Sale::class => SalePolicy::class,
        SaleExchange::class => SaleExchangePolicy::class,
        SaleCancellation::class => SaleCancellationPolicy::class,
        Invoice::class => InvoicePolicy::class,

        //stockist module-------------------------------------------------------
        Stockist::class => StockistPolicy::class,
        StockistSalePayment::class => StockistPaymentVerificationPolicy::class,
        ConsignmentDepositRefund::class => ConsignmentOperationPolicy::class,
        ConsignmentOrderReturn::class => ConsignmentOrderAndReturnPolicy::class,

        //Campaign module-------------------------------------------------------
        Campaign::class => CampaignPolicy::class,
        EsacVoucherType::class => EsacVoucherTypePolicy::class,
        EsacVoucherSubType::class => EsacVoucherTypeSubPolicy::class,
        EsacVoucher::class => EsacVoucherPolicy::class,
        EsacPromotion::class => EsacPromotionPolicy::class,

        //EWallet module-------------------------------------------------------
        EWallet::class => EWalletPolicy::class,
        EWalletTransaction::class => EWalletTransactionPolicy::class,
        EWalletAdjustment::class => EWalletAdjustmentPolicy::class,
        EWalletGIROBankPayment::class => EWalletGIROBankPaymentPolicy::class,
        EWalletGIRORejectedPayment::class => EWalletGIRORejectedPaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(now()->addDays(30));

        Passport::refreshTokensExpireIn(now()->addDays(35));

        // $this->loadAppPermissions();
    }

    /**
     * get All Permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getPermissions()
    {
        return Permission::with('roles')->get();
    }

    /**
     * load app permissions and define these permission using Gate Facade
     */
    private function loadAppPermissions()
    {
        foreach($this->getPermissions() as $permission)
        {
            Gate::define($permission->name, function ($user, int $countryId = 0) use ($permission)
            {
                if ($countryId > 0){
                    $roles = $permission
                        ->roles()
                        ->whereHas('countries', function ($query) use ($countryId){
                            $query->where('country_id', $countryId);
                        })->get();

                    return $user->hasRole($roles, $countryId);
                }

                return $user->hasRole($permission->roles);
            });
        }
    }
}
