@extends('invoices.tax_invoice')

@section('customCSS')
<style type="text/css">
    .info1 {width: 13%;}
    .info2 {width: 45%;}
    .info3 {width: 19%;}
    .info4 {width: 23%;}
   
    .data1{width: 4%;}
    .data2{width: 14%;}
    .data3{width: 37%;}
    .data4{width: 10%;}
    .data5{width: 15%;}
    .data6{width: 20%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:35%"></td>
            <td class="header excHeader center dotted-underline">PRE-ORDER NOTE</td>
            <td style="width:35%"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1"></td>
            <td class="divider"></td>
            <td class="info2"></td>
            <td class="info3"></td>
            <td class="divider"></td>
            <td class="info4"><b>{{ $basic['saleDocNo'] }}</b></td>
        </tr>
        <tr>
            <td class="info1">Global ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['memberID'] }}</td>
            <td class="info3">Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['location'] }}</td>
        </tr>
        <tr>
            <td class="info1">Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name'] }}</td>
            <td class="info3">Date</td>
            <td class="divider">:</td>
            <td class="info4">{{  $basic['date'] }}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 top wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Commisionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle'] }}</td>
        </tr>
        <tr>
            <td class="info3">Branch Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['branch'] }}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel'] }}</td>
            <td class="info3">Stockist ID</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['issuer'] }}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">NO</td>
            <td class="data2 dataHeader">CODE NO.</td>
            <td class="data3 dataHeader">DESCRIPTION</td>
            <td class="data4 dataHeader right">QTY</td>
            <td class="data5 dataHeader right">CV</td>
            <td class="data6 dataHeader right">TOTAL DP ({{ $basic['currency'] }})</td>
        </tr>

        @foreach ($items['products'] as $product)
        <tr>
            <td class="data1 dataRow">{{ $product['no'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow right">{{ $product['qty'] }}</td>
            <td class="data5 dataRow right">{{ $product['cv'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach

        @foreach($esacVouchers as $discount)
        <tr>
            <td class="data1 dataRow"></td>
            <td class="data2 dataRow">Discount</td>
            <td class="data3 dataRow wrapCol">SAC No: {{$discount->voucher_number}}</td>
            <td class="data4 dataRow center"></td>
            <td class="data5 dataRow center"></td>
            <td class="data6 dataRow right">({{ number_format($discount->voucher_value, 2) }})</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="6" class="breaker"></td>
        </tr>
        <tr>
            <td class="data1 dataRow"></td>
            <td class="data2 dataRow"></td>
            <td class="data3 dataRow right">TOTAL : &nbsp;&nbsp;</td>
            <td class="data4 dataHeader right">{{ number_format($items['subTotal']['qty'], 0) }}</td>
            <td class="data5 dataHeader right">{{ number_format($items['subTotal']['cv'], 0) }}</td>
            <td class="data6 dataHeader right">{{ number_format($items['total']['total'], 2) }}</td>
        </tr>

        <tr>
            <td colspan="6">
                @if ($summary)
                <table class="summaryTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <th colspan="3" style="font-weight: bold;width: 100%;text-align: left">Summary : </th>
                    </tr>
                    @foreach($summary['items'] as $item => $amount)
                        <tr>
                            <td class="summary1">{{ $item }} =</td>
                            <td class="summary2 right" style="padding-right: 30px">{{ $amount}}</td>
                            <td class="summary3"></td>
                        </tr>
                    @endforeach
                </table>
                @endif
            </td>
        </tr>

        <tr>
            <td colspan="5" class="data5 dataRow right">Amount Received: &nbsp;&nbsp;</td>
            <td class="data6 dataRow right">{{ number_format($summary['paid'], 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="data5 dataRow right">Balance Due: &nbsp;&nbsp;</td>
            <td class="data6 dataRow right">{{ number_format($summary['balanceDue'], 2) }}</td>
        </tr>

        <tr>
            <td colspan="6">
                @if ($summary)
                <table class="summaryTbl" cellpadding="0" cellspacing="0">
                    @if (array_key_exists('payments', $summary))
                        <tr>
                            <th colspan="3" style="font-weight: bold;width: 100%;text-align: left">Payment : </th>
                        </tr>
                        @if (count($summary['payments'])>0)
                            @foreach($summary['payments'] as $payment)
                                <tr>
                                    <td class="summary1">{{ $payment['method'] }}</td>
                                    <td class="summary2"></td>
                                    <td class="summary3 right">{{ number_format($payment['total'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="summary1"> -- </td>
                                <td class="summary2"></td>
                                <td class="summary3"></td>
                            </tr>
                        @endif
                    @endif
                </table>
                @endif
            </td>
        </tr>
    </table>

    <div class="signature">
        <table cellpadding="0" cellspacing="5">
            <tr>
                <td class="signSpace"></td>
                <td style="width: 7%"></td>
                <td class="signSpace"></td>
                <td style="width: 6%"></td>
                <td class="signSpace"></td>
                <td style="width: 7%"></td>
                <td class="signSpace"></td>
            </tr>
            <tr>
                <td class="footer-line">Authorised Signature</td>
                <td style="width: 7%"></td>
                <td class="footer-line">Issued By</td>
                <td style="width: 6%"></td>
                <td class="footer-line">Picked By</td>
                <td style="width: 7%"></td>
                <td class="footer-line">Acknowledged By <br>(Signature / Chop)</td>
            </tr>
        </table>
        <div class="breaker"></div>
        <div class="wrapCol">SALES WILL ONLY BE REGISTERED UPON FULL PAYMENT SETTLEMENT.<br>
        NO AMENDMENT AND CANCELLATION IS ALLOWED<br>
        PLEASE SETTLE PAYMENT BEFORE CV CLOSING AS STATED ON THE [PRE-ORDER NOTE], OR ELSE BOOKING AND FREEBIES WILL BE FORFEITED.
        </div>
    </div>
@endsection