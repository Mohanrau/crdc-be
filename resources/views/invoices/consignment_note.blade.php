@extends('invoices.tax_invoice')

<?php

    $font = "Arial";

    $isTax = $basic['tax'];
    
    if ($isTax)
    {
        $header1 = "Unit Price (".$basic['currency'].")";
        $header2 = "Net MP (".$basic['currency'].")";
        $header3 = "VAT 7% (".$basic['currency'].")";
        $header4 = "Gross MP (".$basic['currency'].")";
    }
    else
    {
        $header1 = "Unit Price (".$basic['currency'].")";
        $header2 = "";
        $header3 = "";
        $header4 = "Total (".$basic['currency'].")";
    }

    if ($basic['country'] == 'TH')
    {
        $font = "Garuda";
        $taxNo = "Tax No: 0105542094677";       
    }
    else {
        $taxNo = "";
    }

?>

@section('customCSS')
<style type="text/css">
    body{font-family: "{{ $font }}";}
    .info1 {width: 16%;}
    .info2 {width: 46%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}

    .data1{width:4%;}
    .data2{width:14%;}
    .data3{width:32%;}
    .data4{width:4%;}
    .data5{width:10%;}
    .data6{width:12%;}
    .data7{width:11%;}
    .data8{width:13%;}

</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header center">CONSIGNMENT NOTE<br/>{{ $taxNo }}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">Stockist ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['stockistID'] }}</td>
            <td class="info3">Doc No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no'] }}</td>
        </tr>
        <tr>
            <td class="info1">Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name'] }}</td>
            <td class="info3">Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['location'] }}</td>
        </tr>
        <tr>
            <td rowspan="3" class="info1 top">Address</td>
            <td rowspan="3" class="divider">:</td>
            <td rowspan="3" class="info2 top wrapCol">{{ $basic['address'] }}</td>
            <td class="info3">Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date'] }}</td>
        </tr>
        <tr>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
         <tr>
            <td class="info3">Transaction Type</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['type'] }}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel'] }}</td>
            <td class="info3"></td>
            <td class="divider"></td>
            <td class="info4"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <th class="data1 left dataHeader">No</th>
            <th class="data2 left dataHeader">Product Code</th>
            <th class="data3 left dataHeader">Description</th>
            <th class="data4 center dataHeader">Qty</th>
            <th class="data5 center dataHeader">{{ $header1 }}</th>
            <th class="data6 right dataHeader">{{ $header2 }}</th>
            <th class="data7 right dataHeader">{{ $header3 }}</th>
            <th class="data8 right dataHeader">{{ $header4 }}</th>
        </tr>

        @foreach($sales['products'] as $product)
            <tr>
                <td class="data1 dataRow left">{{ $product['no'] }}</td>
                <td class="data2 dataRow left">{{ $product['code'] }}</td>
                <td class="data3 dataRow left wrapCol">{!! $product['description'] !!}</td>
                <td class="data4 dataRow center">{{ $product['qty'] }}</td>
                <td class="data5 dataRow center">{{ $product['unitPrice'] }}</td>
                <td class="data6 dataRow right">{{ $isTax?number_format($product['excTax'], 2):' ' }}</td>
                <td class="data7 dataRow right">{{ $isTax?number_format($product['tax'], 2):' ' }}</td>
                <td class="data8 dataRow right">{{ number_format($product['total'], 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="8">
                @include('invoices.partials.summary', $summary)
            </td>
        </tr>
        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline center">Total</td>
            <td class="data4 dataRow upperline center">{{ $sales['subTotal']['qty'] }}</td>
            <td class="data5 upperline center"></td>
            <td class="data6 dataRow upperline right">{{ $isTax?number_format($sales['total']['excTax'], 2): ' '}}</td>
            <td class="data7 dataRow upperline right">{{ $isTax?number_format($product['tax'], 2): ' ' }}</td>
            <td class="data8 dataRow upperline right">{{ number_format($sales['total']['total'], 2) }}</td>
        </tr>
    </table>

    <div class="signature">
        <table cellpadding="0" cellspacing="5">
            <tr>
                <td class="signSpace bottom center">{{ $basic['issuer'] }}</td>
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
    </div>
@endsection