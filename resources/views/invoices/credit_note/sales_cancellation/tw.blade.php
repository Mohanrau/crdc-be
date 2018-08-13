<?php
    $dt = new Carbon\Carbon($basic['date']);
    $dt = $dt->subYears(1911);
?>

@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    body {font-family:Arial, BIG5;font-size:8pt;}
    .info0 {width: 60%;}
    .info1 {width: 15%;}
    .info2 {width: 25%;}
   
    .data1{width:3%;height:32px;}
    .data21{width:5%;height:32px;}
    .data22{width:3%;height:32px;}
    .data23{width:4%;height:32px;}
    .data31{width:6%;height:32px;}
    .data32{width:10%;height:32px;padding-right: 5px}
    .data4{width:16%;height:32px;font-size:9px;overflow: hidden}
    .data5{width:6%;height:32px;}
    .data6{width:8%;height:32px;}
    .data7{width:15%;height:32px;}
    .data8{width:9%;height:32px;}
    .data9{width:5%;height:32px;}
    .data10{width:5%;height:32px;}
    .data11{width:5%;height:32px;}

    .padding-right {padding-right:20px;}
</style>
@endsection

@section('content')
<div style="width:800px;">
    <div style="height:40px"></div>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:18%"></td>
            <td style="width:60px">{{ $dt->year }}</td>
            <td style="width:60px">{{ $dt->month }}</td>
            <td style="width:60px">{{ $dt->day }}</td>
            <td ></td>
        </tr>
    </table>

    <table border="0" cellspacing="2" cellpadding="0">
        <tr>
            <td class="info0"></td>
            <td class="info1">系統編號</td>
            <td class="info2">{{ $basic['no'] }}</td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">憑單編號</td>
            <td class="info2">{{ $basic['invoiceNo']}}</td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">頁面</td>
            <td class="info2">1 / {nb}</td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">SV月份</td>
            <td class="info2">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">經手人</td>
            <td class="info2">{{ $basic['createdBy']}}</td>
        </tr>
    </table>
    <div style="height:116px"></div>
    <table border="0" cellspacing="0" cellpadding="0" style="margin-left:-20px">
        <tr>
            <td style="width:760px;vertical-align: top;">
                <table border="0" cellspacing="0" cellpadding="2" style="height:600px;width:100%">
                    <?php
                        $count = 0;
                    ?>
                    @foreach($sales['products'] as $product)
                        <tr>
                            <td class="data1 right">4</td>
                            <td class="data21 right">{{ $dt->year }}</td>
                            <td class="data22 right">{{ $dt->month }}</td>
                            <td class="data23 right">{{ $dt->day }}</td>
                            <td class="data31 right">CN</td>
                            <td class="data32 right">91015479</td>
                            <td class="data4 left wrapCol">{{ $product['code'] }} - {!! $product['description'] !!}</td>
                            <td class="data5 right">{{ $product['qty'] }}</td>
                            <td class="data6 right">{{ number_format($product['unitPrice'], 2) }}</td>
                            <td class="data7 right">{{ number_format($product['excTax'], 2) }}</td>
                            <td class="data8 right">{{ number_format($product['tax'], 2) }}</td>
                            <td class="data9 right">&#10004;</td>
                            <td class="data10 right"></td>
                            <td class="data11 right"></td>
                        </tr>
                        <?php $count++; ?>
                    @endforeach

                    @for ($i = $count; $i < 10; $i++)
                        <tr><td colspan="14" style="height:31px"></td></tr> 
                    @endfor
                        <tr>
                            <td class="data1 center"></td>
                            <td class="data21 right"></td>
                            <td class="data22 right"></td>
                            <td class="data23 right"></td>
                            <td class="data31 right"></td>
                            <td class="data32 right"></td>
                            <td class="data4 left"></td>
                            <td class="data5 right"></td>
                            <td class="data6 right"></td>
                            <td class="data7 right">{{ number_format($sales['total']['excTax'], 2) }}</td>
                            <td class="data8 right">{{ number_format($sales['total']['tax'], 2) }}</td>
                            <td class="data9 center"></td>
                            <td class="data10 center"></td>
                            <td class="data11 center"></td>
                        </tr>
                </table>
            </td>
            <td style="width:40px"></td>
        </tr>
    </table>
    <div style="height:315px"></div>

    <table border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse">
        <tr>
            <td style="width:20%"></td>
            <td style="width:33%; text-align: left;font-size: 9pt">
                <br>
                {{ $basic['memberID'] }} <br>
                {{ $basic['name'] }}
            </td>
            <td style="width:47%">
                @if ($addressArray)
                <table style="width:100%;font-size: 10px;text-align: right">
                    <tr>
                        <td style="height:40px;width:14%">{{ $addressArray[0] }}</td>
                        <td style="height:40px;width:14%">{{ $addressArray[1] }}</td>
                        <td style="height:40px;width:35%">{{ $addressArray[2] }}</td>
                        <td style="height:40px;width:24%">{{ $addressArray[3] }}</td>
                        <td style="height:40px;width:13%">&nbsp;</td>
                    </tr>
                </table>
                <table style="width:100%;font-size: 10px;text-align: right">
                    <tr>
                        <td style="width:13%">{{ $addressArray[4] }}</td>
                        <td style="width:21%">{{ $addressArray[5] }}</td>
                        <td style="width:13%">{{ $addressArray[6] }}</td>
                        <td style="width:22%">{{ $addressArray[7] }}</td>
                        <td style="width:18%">{{ $addressArray[8] }}<br>{{ $addressArray[9] }}</td>
                        <td style="width:13%">&nbsp;</td>
                    </tr>
                </table>
                @endif
            </td>
        </tr>
    </table>
</div>
@endsection