<?php
    $value = str_pad((int)$sales['total']['total'], 8, '0', STR_PAD_LEFT);

    $temp = str_split($value);

    $cnNumber = [];

    foreach ($temp as $value)
    {
        $val = str_replace([0,1,2,3,4,5,6,7,8,9], ["零","壹" ,"貳","叁","肆","伍","陸","柒","捌","玖"], $value);
        array_push($cnNumber, $val);
    }

    $dt = new Carbon\Carbon($basic['created_at']);
    $dt->subYears(1911);
?>

@extends('invoices.tax_invoice')

@section('customCSS')
<style type="text/css">
    body {font-family:Arial, BIG5;font-size:7.5pt;line-height: 12pt}
    .info1 {width: 13%;height:12pt;}
    .info2 {width: 38%;height:12pt;}
    .info3 {width: 18%;height:12pt;}
    .info4 {width: 31%;height:12pt;}
   
    .data1{width:5%;height:12pt;}
    .data2{width:12%;height:12pt;}
    .data3{width:41%;height:12pt;margin-right: 5px}
    .data4{width:12%;height:12pt;}
    .data5{width:14%;height:12pt;}
    .data6{width:16%;height:12pt;}

    .remark1 {width:35%;margin-left:3px;vertical-align: top;}
    .remark2 {width:65%;vertical-align: top;}
    .padding-right {padding-right:25px;}

    .items > td {
        height: 15pt;
    }
    .wrapCol{margin-right:20px;}

    table{vertical-align: top; width: 100%;padding-top: 4px;padding-bottom: 4px;font-size: 10pt;}
</style>
@endsection

@section('content')
<div style="width:710px; padding-left: 40px; padding-right: 50px">
    <div style="width:100%;height:60pt"></div>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:80%;height:12pt"></td>
            <td>1</td>
            <td>{nb}</td>
        </tr>
        <tr>
            <td style="width:80%;height:12pt"></td>
            <td></td>
            <td>1</td>
        </tr>
    </table>
    <div style="width:100%;height:14pt"></div>
    <table border="0" cellspacing="0" cellpadding="0" style="padding-bottom: 0px;padding-top: 10px">
        <tr>
            <td style="width:42%"></td>
            <td style="width:11%">{{ $dt->year }}</td>
            <td style="width:10%">{{ $dt->month }}</td>
            <td style="width:10%">{{ $dt->day }}</td>
            <td style="width:27%"></td>
        </tr>
    </table>
    <table border="0" cellspacing="2" cellpadding="0" style="padding-top: 0px">
        <tr>
            <td class="info1"></td>
            <td class="info2">{{ $basic['memberID']}}</td>
            <td class="info3"></td>
            <td class="info4">{{ $basic['no'] }}</td>
        </tr>
        <tr>
            <td class="info1"></td>
            <td class="info2">{{ $basic['name']}}</td>
            <td class="info3"></td>
            <td class="info4"></td>
        </tr>
        <tr>
            <td class="info1"></td>
            <td class="info2"></td>
            <td class="info3"></td>
            <td class="info4">{{ $basic['saleDocNo'] }}</td>
        </tr>
        <tr>
            <td rowspan="3" class="info1"></td>
            <td rowspan="3" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3"></td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info3"></td>
            <td class="info4">{{ $basic['orderType']}}</td>
        </tr>
        <tr>
            <td class="info3"></td>
            <td class="info4"></td>
        </tr>
        <tr>
            <td class="info1"></td>
            <td class="info2" style="margin-top:4px">{{ $basic['sponsorID']}}</td>
            <td class="info3"></td>
            <td class="info4" style="margin-top:4px">{{ $basic['location'] }}</td>
        </tr>
    </table>
    <br/>
    <table cellspacing="0" cellpadding="0" style="padding-top: 10px; padding-bottom: 2px">
        <tr>
            <td style="width:530px;height:500px;vertical-align: top;">
                <table id="items" border="0" cellspacing="0" cellpadding="2">
                    <?php
                        $remainLine = 20; 
                    ?>
                    @foreach($sales['products'] as $product)
                        <tr>
                            <td class="data1 center">{{ $product['no'] }}</td>
                            <td class="data2 left">{{ $product['code'] }}</td>
                            <td class="data3 left">{!! $product['description'] !!}</td>
                            <td class="data4 right">{{ $product['qty'] }} {{ $product['uom'] }}</td>
                            <td class="data5 right">{{ number_format($product['unitPrice'], 2) }}</td>
                            <td class="data6 right padding-right">{{ number_format($product['total'], 2) }}</td>
                        </tr>
                        <?php $remainLine--; ?>
                    @endforeach

                    <tr><td colspan="6" style="height:{{ $remainLine*16 }}pt"></td></tr>

                    <tr>
                        <td colspan="6" style="height: 12pt">
                            @foreach($summary['items'] as $item => $amount)
                                {{ $item }} = {{ $amount}},
                            @endforeach
                        </td>
                    </tr>

                    <tr><td colspan="6" style="height:56px"></td></tr>

                    <tr>
                        <td colspan="5" class="center">
                            ---- 最後一頁 ----
                        </td>
                        <td class="data6 right padding-right">{{ number_format($sales['total']['total'], 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td class="data6 right padding-right">{{ number_format($sales['total']['total'], 2) }}</td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;height:500px">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="remark1"></td>
                        <td class="remark2">--</td>
                    </tr>
                    @if ($remarks)
                        <tr>
                            <td class="remark1">Remark :</td>
                            <td class="remark2">{!! $remarks !!}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="remark1">&nbsp;</td>
                            <td class="remark2">&nbsp;</td>
                        </tr>
                    @endif
                    @if ($shipping)
                        <tr>
                            <td class="remark1">姓名 : </td>
                            <td class="remark2">{!! $shipping['name'] !!}</td>
                        </tr>
                        <tr>
                            <td class="remark1">電話 : </td>
                            <td class="remark2 wrapCol">{!! $shipping['address'] !!}</td>
                        </tr>
                        <tr>
                            <td class="remark1">寄送地址 : </td>
                            <td class="remark2"> {!! $shipping['contact'] !!}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="remark1">&nbsp;</td>
                            <td class="remark2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="remark1">&nbsp;</td>
                            <td class="remark2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="remark1">&nbsp;</td>
                            <td class="remark2">&nbsp;</td>
                        </tr>
                    @endif

                    <tr><td colspan="2" style="height:125px"></td></tr>

                    <tr>
                        <td class="remark1" colspan="2" style="height:60px">
                            @foreach($summary['payments'] as $payment)
                                {{ $payment['method'] }} {{ number_format($payment['total'], 2) }}
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <td class="remark1">(CV)</td>
                        <td class="right padding-right">{{ $sales['subTotal']['cv'] }}</td>
                    </tr>
                    <tr>
                        <td style="height:8px"></td>
                        <td class="right padding-right"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="right padding-right">-- </td>
                    </tr>
                    <tr>
                        <td style="height:25px"></td>
                        <td class="right padding-right"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="right padding-right">-- </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="2" cellpadding="2" style="width:100%;padding-top: 6px">
        <tr>
            <td style="width:150px"></td>
            <td >{{ $cnNumber[0] }}</td>
            <td >{{ $cnNumber[1] }}</td>
            <td >{{ $cnNumber[2] }}</td>
            <td >{{ $cnNumber[3] }}</td>
            <td >{{ $cnNumber[4] }}</td>
            <td >{{ $cnNumber[5] }}</td>
            <td >{{ $cnNumber[6] }}</td>
            <td >{{ $cnNumber[7] }}</td>
            <td style="width:50px">&nbsp;</td>
        </tr>
    </table>
</div>
@endsection