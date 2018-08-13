@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    .info1 {width: 12%;}
    .info2 {width: 50%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}
   
    .data0{width:3%;}
    .data1{width:4%;}
    .data2{width:11%;}
    .data3{width:17%;}
    .data4{width:3%;}
    .data5{width:6%;}
    .data6{width:8%;}
    .data7{width:8%;}
    .data8{width:7%;}
    .data9{width:12%;}
    .data10{width:9%;}
    .data11{width:12%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header center">CREDIT NOTE</td>
        </tr>
        <tr>
            <td class="center smaller">GST Registration No: 000182603776</td>
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
            <td class="info3">Order Type</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['orderType']}}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1 top">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Location</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['location']}}</td>
        </tr>
        <tr>
            <td class="info3">Transaction Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date']}}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID']}}</td>
            <td class="info3">Commisionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorName']}}</td>
            <td class="info3">Tax Invoice</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['invoiceNo']}}</td>
        </tr>
        <tr>
            <td class="info1"></td>
            <td class="divider"></td>
            <td class="info2"></td>
            <td class="info3">Sales Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['salesDate']}}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data0 dataHeader">No</td>
            <td class="data1 dataHeader">TOS</td>
            <td class="data2 dataHeader">Product Code</td>
            <td class="data3 dataHeader">Description</td>
            <td class="data4 dataHeader">Qty</td>
            <td class="data5 center dataHeader">Total CV</td>
            <td class="data6 right dataHeader">Unit Price (RM)</td>
            <td class="data7 right dataHeader">Sub-Total (RM)</td>
            <td class="data8 right dataHeader">Discount</td>
            <td class="data9 right dataHeader">GMP (Excl GST) (RM)</td>
            <td class="data10 right dataHeader">GST {{$taxRate}}% (RM)</td>
            <td class="data11 right dataHeader">GMP (Incl GST) (RM)</td>
        </tr>
        @foreach($sales['products'] as $product)
        <tr>
            <td class="data0 dataRow">{{ $product['no'] }}</td>
            <td class="data1 dataRow">{{ $product['tos'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow center">{{ $product['qty'] }}</td>
            <td class="data5 dataRow center">{{ $product['cv'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['unitPrice'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($product['subTotal'], 2) }}</td>
            <td class="data8 dataRow right">{{ number_format($product['discount'], 2) }}</td>
            <td class="data9 dataRow right">{{ number_format($product['excTax'], 2) }}</td>
            <td class="data10 dataRow right">{{ number_format($product['tax'], 2) }}</td>
            <td class="data11 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="12">
                @include('invoices.partials.summary', $summary)
            </td>
        </tr>
        <tr>
            <td colspan="12">
                <table class="noteTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="note1">Remarks</td>
                        <td class="divider">: </td>
                        <td class="note2"> {{ $remarks }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="12" class="smallNote center">Note: for any sales cancellation, delivery charges and EPP admin cost is not refundable</td>
        </tr>

        <tr>
            <td class="data0 upperline"></td>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Sub Total </td>
            <td class="data4 upperline dataRow center">{{ $sales['subTotal']['qty'] }}</td>
            <td class="data5 upperline dataRow center">{{ $sales['subTotal']['cv'] }}</td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline"></td>
            <td class="data8 upperline"></td>
            <td class="data9 upperline dataRow right">{{ number_format($sales['subTotal']['excTax'],2) }}</td>
            <td class="data10 upperline dataRow right">{{ number_format($sales['subTotal']['tax'], 2) }}</td>
            <td class="data11 upperline dataRow right">{{ number_format($sales['subTotal']['total'], 2) }}</td>
        </tr>

        <tr>
            <td class="data0"></td>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3 dataRow right">Delivery Charges </td>
            <td class="data4"></td>
            <td class="data5"></td>
            <td class="data6"></td>
            <td class="data7"></td>
            <td class="data8"></td>
            <td class="data9 dataRow right">{{ number_format($sales['delivery']['excTax'],2) }}</td>
            <td class="data10 dataRow right">{{ number_format($sales['delivery']['tax'], 2) }}</td>
            <td class="data11 dataRow right">{{ number_format($sales['delivery']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data0 upperline"></td>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Admin Cost </td>
            <td class="data4 upperline"></td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline"></td>
            <td class="data8 upperline"></td>
            <td class="data9 upperline dataRow right">{{ number_format($sales['admin']['excTax'],2) }}</td>
            <td class="data10 upperline dataRow right">{{ number_format($sales['admin']['tax'], 2) }}</td>
            <td class="data11 upperline dataRow right">{{ number_format($sales['admin']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data0"></td>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3 dataRow right">Other Cost </td>
            <td class="data4"></td>
            <td class="data5"></td>
            <td class="data6"></td>
            <td class="data7"></td>
            <td class="data8"></td>
            <td class="data9 dataRow right">{{ number_format($sales['other']['excTax'],2) }}</td>
            <td class="data10 dataRow right">{{ number_format($sales['other']['tax'], 2) }}</td>
            <td class="data11 dataRow right">{{ number_format($sales['other']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data0 upperline"></td>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow right">Total </td>
            <td class="data4 upperline"></td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline"></td>
            <td class="data8 upperline"></td>
            <td class="data9 upperline dataRow right">{{ number_format($sales['total']['excTax'],2) }}</td>
            <td class="data10 upperline dataRow right">{{ number_format($sales['total']['tax'], 2) }}</td>
            <td class="data11 upperline dataRow right">{{ number_format($sales['total']['total'], 2) }}</td>
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
        <div class="breaker"></div>
        <div class="wrapCol">Distributors' & Customer's data & information will be kept confidential and used solely for the purposes as stated in our Personal Data Protection Notice shown in our website at http://www.elken.com/my</div>
        <div class="breaker"></div>
        ELKEN GLOBAL SDN BHD
    </div>
@endsection