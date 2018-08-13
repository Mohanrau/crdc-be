@extends('invoices.tax_invoice')

@section('customCSS')
<style type="text/css">
    .info1 {width: 15%;}
    .info2 {width: 45%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}

    .data1{width:5%;}
    .data2{width:15%;}
    .data3{width:40%;}
    .data4{width:10%;}
    .data5{width:15%;}
    .data6{width:15%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td rowspan="2" class="col-3"><img src="{{ config('setting.logo_url') }}" /></td>
            <td class="col-3 header center middle dotted-underline">INVOICE</td>
            <td rowspan="2" class="col-3 header bottom right">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="col-3"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">Member ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['memberID']}}</td>
            <td class="info3">Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['location']}}</td>
        </tr>
        <tr>
            <td class="info1">Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name']}}</td>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1 top">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{{ $basic['address']}}</td>
            <td class="info3">Commissionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info3">Invoice No.</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Transaction Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['created_at']}}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID']}}</td>
            <td class="info3">Self Collection Code</td>
            <td class="divider">:</td>
            <td class="info4">{!! $basic['collection'] !!}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorName']}}</td>
            <td class="info3"></td>
            <td class="divider"></td>
            <td class="info4"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">No</td>
            <td class="data2 dataHeader">Product Code</td>
            <td class="data3 dataHeader">Description</td>
            <td class="data4 right dataHeader">Qty</td>
            <td class="data5 right dataHeader">Total CV</td>
            <td class="data6 right dataHeader">Gross DP (BND)</td>
        </tr>
        @foreach($sales['products'] as $product)
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
                <td class="dataRow data1"></td>
                <td class="dataRow data2">Discount</td>
                <td class="dataRow data3 wrapCol">SAC No: {{$discount->voucher_number}}</td>
                <td class="dataRow data4 right"></td>
                <td class="dataRow data5 right"></td>
                <td class="dataRow data6 right">({{ number_format($discount->voucher_value, 2) }})</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="6">
                @include('invoices.partials.summary', $summary)
            </td>
        </tr>

        <tr>
            <td colspan="6">
                @include('invoices.partials.shipping', $shipping)
            </td>
        </tr>

        <tr>
            <td class="data1 dataHeader"></td>
            <td class="data2 dataHeader"></td>
            <td class="data3 dataHeader right">Total :</td>
            <td class="data4 right dataHeader">{{ $sales['subTotal']['qty'] }}</td>
            <td class="data5 right dataHeader">{{ $sales['subTotal']['cv'] }}</td>
            <td class="data6 right dataHeader">{{ number_format($sales['subTotal']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1 dataRow"></td>
            <td class="data2 dataRow"></td>
            <td class="data3 dataRow"></td>
            <td colspan="2" class="right dataRow">Delivery Charges :</td>
            <td class="data6 dataRow right">{{ number_format($sales['delivery']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3"></td>
            <td colspan="2" class="right total">Grand Total :</td>
            <td class="data6 right total doubleLine">{{ number_format($sales['total']['total'], 2) }}</td>
        </tr>
    </table>

    <div class="signature-bottom">
        <table cellpadding="0" cellspacing="5">
            <tr>
                <td class="signSpace center bottom">{{ $basic['issuer'] }}</td>
                <td style="width: 20%"></td>
                <td class="signSpace"></td>
                <td style="width: 20%"></td>
                <td class="signSpace"></td>
            </tr>
            <tr>
                <td class="footer-line">Issued By</td>
                <td style="width: 20%"></td>
                <td class="footer-line">Picked By</td>
                <td style="width: 20%"></td>
                <td class="footer-line">Received By</td>
            </tr>
        </table>
        <div class="breaker"></div>
        <div class="wrapCol">
            Distributors' & Customer's data & information will be kept confidential and used solely for the purposes as stated in our Personal Data Protection Notice shown in our website at www.elken.com/bn
        </div>
        <div class="breaker"></div>
        ELKEN (B) SDN BHD<br/>
        Unit 6,7 and 8, Block B Setia Kenangan 2<br/>
        Spg 150-5-13-18,Jln Jame's Asr, Kg Kiarong<br/>
        Mukim Gadong 'B', BE 1318<br/>
        Brunei Darussalam<br/>
        Tel: 673 2236127&nbsp;&nbsp;&nbsp;Fax: 673 2236130&nbsp;&nbsp;&nbsp;WebSite: www.elken.com/bn&nbsp;&nbsp;&nbsp;Email: customerservice.bn@elken.com 
    </div>
@endsection