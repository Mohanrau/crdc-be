@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    body{font-family: Garuda;}
    .info1 {width: 17%;}
    .info2 {width: 45%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}

    .data1{width:4%;}
    .data2{width:14%;}
    .data3{width:35%;}
    .data4{width:4%;}
    .data5{width:6%;}
    .data6{width:12%;}
    .data7{width:12%;}
    .data8{width:13%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header center">CREDIT NOTE</td>
        </tr>
        <tr>
            <td class="center smaller">Tax No: 0105542094677</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">Member ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['memberID']}}</td>
            <td class="info3">Credit Note</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="info1">Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name']}}</td>
            <td class="info3">Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['location']}}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1 top">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Transaction Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date']}}</td>
        </tr>
        <tr>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td class="info1">Tax Identification No</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['taxNo']}}</td>
            <td class="info3">Tax Invoice</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['invoiceNo']}}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Tax Invoice Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['salesDate']}}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID']}}</td>
            <td class="info3"></td>
            <td class="divider"></td>
            <td class="info4"></td>
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
            <td class="data4 center dataHeader">Qty</td>
            <td class="data5 center dataHeader">CV</td>
            <td class="data6 right dataHeader">Net MP(THB)</td>
            <td class="data7 right dataHeader">VAT {{$taxRate}}%(THB)</td>
            <td class="data8 right dataHeader">Gross MP(THB)</td>
        </tr>
        @foreach($sales['products'] as $product)
        <tr>
            <td class="data1 dataRow">{{ $product['no'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow center">{{ $product['qty'] }}</td>
            <td class="data5 dataRow center">{{ $product['cv'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['excTax'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($product['tax'], 2) }}</td>
            <td class="data8 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="8">
                @include('invoices.partials.summary', $summary)
            </td>
        </tr>
        <tr>
            <td colspan="8">
                <table class="noteTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="note1">Remarks</td>
                        <td class="divider">: </td>
                        <td class="note2"> {{ $remarks }}</td>
                    </tr>
                    <tr>
                        <td class="note1">Reason Code</td>
                        <td class="divider">: </td>
                        <td class="note2"> {{$reason}}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Sub Total </td>
            <td class="data4 upperline dataRow center">{{ $sales['subTotal']['qty'] }}</td>
            <td class="data5 upperline dataRow center">{{ $sales['subTotal']['cv'] }}</td>
            <td class="data6 upperline dataRow right">{{ number_format($sales['subTotal']['excTax'], 2) }}</td>
            <td class="data7 upperline dataRow right">{{ number_format($sales['subTotal']['tax'], 2) }}</td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['subTotal']['total'], 2) }}</td>
        </tr>

        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3 dataRow right">Delivery Charges </td>
            <td class="data4"></td>
            <td class="data5"></td>
            <td class="data6 dataRow right">{{ number_format($sales['delivery']['excTax'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($sales['delivery']['tax'], 2) }}</td>
            <td class="data8 dataRow right">{{ number_format($sales['delivery']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Admin Fee </td>
            <td class="data4 upperline"></td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline dataRow right">{{ number_format($sales['admin']['excTax'], 2) }}</td>
            <td class="data7 upperline dataRow right">{{ number_format($sales['admin']['tax'], 2) }}</td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['admin']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3 dataRow right">Other Fee </td>
            <td class="data4"></td>
            <td class="data5"></td>
            <td class="data6 dataRow right">{{ number_format($sales['other']['excTax'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($sales['other']['tax'], 2) }}</td>
            <td class="data8 dataRow right">{{ number_format($sales['other']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Total </td>
            <td class="data4 upperline"></td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline dataRow right">{{ number_format($sales['total']['excTax'], 2) }}</td>
            <td class="data7 upperline dataRow right">{{ number_format($sales['total']['tax'], 2) }}</td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['total']['total'], 2) }}</td>
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
                <td class="footer-line">Received By<br/>(Signature / Chop)</td>
            </tr>
        </table>
    </div>
@endsection