<?php
namespace App\Rules\Sales;

use App\Interfaces\Sales\SaleInterface;
use Illuminate\Contracts\Validation\Rule;

class SalesCancellationProductQuantityCheck implements Rule
{
    private $saleRepositoryObj,
        $invoiceId,
        $userId,
        $saleProducts,
        $saleItemType,
        $productName,
        $maxQty;

    /**
     * CancellationProductQuantity constructor.
     *
     * @param SaleInterface $saleInterface
     * @param int $invoiceId
     * @param int $userId
     * @param array $saleProducts
     * @param string $saleItemType
     */
    public function __construct(
        SaleInterface $saleInterface,
        int $invoiceId,
        int $userId,
        array $saleProducts,
        string $saleItemType
    )
    {
        $this->saleRepositoryObj = $saleInterface;

        $this->invoiceId = $invoiceId;

        $this->userId = $userId;

        $this->saleProducts = $saleProducts;

        $this->saleItemType = $saleItemType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //Get kitting and product number key
        $strAttribute = explode('.', $attribute);

        if($this->saleItemType == 'products' || $this->saleItemType == 'promotions' || $this->saleItemType == 'esac_vouchers'){

            $saleProductId = $this->saleProducts[$strAttribute[2]]['id'];

        } else if($this->saleItemType == 'kitting'){

            $saleKittingId = $this->saleProducts[$strAttribute[2]]['id'];

            $saleProductId = $this->saleProducts[$strAttribute[2]]['kitting_products'][$strAttribute[4]]['id'];
        }

        $saleInvoiceDetail = $this->saleRepositoryObj
            ->getSalesCancellationInvoiceDetails('normal', $this->userId, $this->invoiceId, 0);

        if($this->saleItemType == 'products'){

            $saleProducts = collect($saleInvoiceDetail['sale']['products'])
                ->where('id', $saleProductId)
                ->first();

            $maxQty = $saleProducts['available_quantity_snapshot'];

            $productName = $saleProducts['name'];

        } else if ($this->saleItemType == 'kitting'){

            $saleKittingDetail = collect($saleInvoiceDetail['sale']['kitting'])
                ->where('id', $saleKittingId)
                ->first();

            $saleKittingProduct = collect($saleKittingDetail['kitting_products'])
                ->where('id', $saleProductId)
                ->first();

            $maxQty = $saleKittingProduct['available_quantity_snapshot'];

            $productName = $saleKittingProduct['product']['name'];

        } else if ($this->saleItemType == 'promotions'){

            $salePromotions = collect($saleInvoiceDetail['sale']['promotions'])
                ->where('id', $saleProductId)
                ->first();

            $maxQty = $salePromotions['available_quantity_snapshot'];

            $productName = $salePromotions['name'];

        } else if ($this->saleItemType == 'esac_vouchers'){

            $saleEsacVouchers = collect($saleInvoiceDetail['sale']['esac_vouchers'])
                ->where('id', $saleProductId)
                ->first();

            $maxQty = $saleEsacVouchers['available_quantity_snapshot'];

            $productName = $saleEsacVouchers['name'];

        } else {
            $maxQty = 0;
        }

        $this->productName = $productName;

        $this->maxQty = $maxQty;

        return $value <= $maxQty;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.sales-cancellation.max-product-quantity', [
            'productName' => $this->productName,
            'max' => $this->maxQty
        ]);
    }
}
