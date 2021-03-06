@extends('invoices.exchange')

@section('customCSS')
<style type="text/css">
    body{font-family: Khmer;}
    .info1 {width: 12%;}
    .info2 {width: 50%;}
    .info3 {width: 15%;}
    .info4 {width: 23%;}
   
    .data1{width: 4%;}
    .data2{width: 15%;}
    .data3{width: 30%;}
    .data4{width: 4%;}
    .data5{width: 10%;}
    .data6{width: 9%;}
    .data7{width: 8%;}
    .data8{width: 9%;}
    .data9{width: 11%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header center"><h3>CAMBODIA</h3><br/>
                #28 E2 The iCON Professional Building 216, Norodom Blvd, Tonle Bassac, Chamkarmon,<br/> Phnom Penh, Cambodia<br/>
                Tel: +855 (0)23 98 23 23&nbsp;&nbsp;&nbsp;&nbsp;Fax: +855 (0)23 99 38 38
            </td>
        </tr>
        <tr>
            <td class="header excHeader center"><h3>EXC Print Bill</h3></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">Location From</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['location']}}</td>
            <td class="info3">EXC No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no'] }}</td>
        </tr>
        <tr>
            <td class="info1">Member Code</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['memberID'] }}</td>
            <td class="info3">Transaction Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date'] }}</td>
        </tr>
        <tr>
            <td class="info1">Member Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name'] }}</td>
            <td class="info3">Page No</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td class="info1">Address</td>
            <td class="divider">:</td>
            <td class="info2 top wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Issued By</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['issuer'] }}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">No</td>
            <td class="data2 dataHeader">Product Code</td>
            <td class="data3 dataHeader">Description</td>
            <td class="data4 dataHeader center">Qty</td>
            <td class="data5 dataHeader">Price Code</td>
            <td class="data6 dataHeader right">Unit Price</td>
            <td class="data7 dataHeader right">Unit Tax</td>
            <td class="data8 dataHeader right">Total Tax</td>
            <td class="data9 dataHeader right">Total Price</td>
        </tr>

        @foreach ($items['products'] as $product)
        <tr>
            <td class="data1 dataRow">{{ $product['no'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow center">{{ $product['qty'] }}</td>
            <td class="data5 dataRow center">{{ $product['priceCode'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['unitPrice'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($product['unitTax'], 2) }}</td>
            <td class="data8 dataRow right">{{ number_format($product['tax'], 2) }}</td>
            <td class="data9 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach

        <tr>
            <td class="data1 dataHeader"></td>
            <td class="data2 dataHeader"></td>
            <td class="data3 dataHeader"></td>
            <td class="data4 dataHeader"></td>
            <td class="data5 dataHeader"></td>
            <td class="data6 dataHeader"></td>
            <td class="data7 dataHeader right">Total</td>
            <td class="data8 dataHeader right">{{ number_format($items['total']['tax'], 2) }}</td>
            <td class="data9 dataHeader right">{{ number_format($items['total']['total'], 2) }}</td>
        </tr>

        <tr>
            <td colspan="9">
                @if(is_array($remarks))
                    @include('invoices.partials.remark', $remarks)
                @else
                    <tr>
                        <td class="title" colspan="3">Remark : </td>
                    </tr>
                    <tr>
                        <td colspan="3">{{$remarks}}</td>
                    </tr>
                @endif
            </td>
        </tr>
    </table>

@endsection