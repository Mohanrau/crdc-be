@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    body{font-family: Khmeros;font-size:9pt;}
    .info1 {width: 15%;}
    .info1-1 {width: 13%;}
    .info2 {width: 27%;}
    .info3 {width: 16%;}
    .info3-1 {width: 16%;}
    .info4 {width: 13%;}

    .data1{width:4%;}
    .data2{width:14%;}
    .data3{width:35%;}
    .data4{width:4%;}
    .data5{width:6%;}
    .data6{width:12%;}
    .data7{width:12%;}
    .data8{width:13%;}

    .noteTbl{width: 80%}
    .note1{width:22%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:10%;vertical-align: top"><img src="{{ config('setting.logo_url') }}" /></td>
            <td style="width: 80%;">
                <table border="0" cellspacing="0" cellpadding="4">
                    <tr>
                        <td colspan="4" class="header center">
                            អែលខេន អ៊ិនធើណេសិនណល (ខេមបូឌា)<br/>Elken International (Cambodia) Co., Ltd.
                            <div class="smaller"> េលខអតស ណកមអតប (VATTIN) L001-107007371</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-4 center smaller">អាសយដ្ឋាន៖<br/>Address</td>
                        <td class="col-4 center smaller">ផ្ទះលេខ៖ ២៨ អឺ២<br/>No 28-E2</td>
                        <td class="col-4 center smaller">មហាវិថីនរោត្តម<br/>Norodom Blvd</td>
                        <td class="col-4 center smaller">សង្កាត់ទន្លេបាសាក់<br/>Sangkat Tonle Bassac</td>
                    </tr>
                    <tr>
                        <td class="col-4 center"></td>
                        <td class="col-4 center smaller">ខណ្ឌចំការមន<br/>Khan Chamkarmon</td>
                        <td class="col-4 center smaller">រាជធានីភ្នំពេញ<br/>City Phnom Penh</td>
                        <td class="col-4 center smaller">ទូរស័ព្ទលេខ 023 982 323<br/> Telephone No 023 982 323</td>
                    </tr>
                    <tr>
                        <td  class="col-4"></td>
                        <td colspan="2" class="header col-2 center dotted-underline">
                            វិក្កយបត្រ<br/>CREDIT NOTE
                        </td>
                        <td class="col-4"></td>
                    </tr>
                </table>
            </td>
            <td style="width: 10%"></td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info1 infoRow">លេខកូដសមាជិក</td>
            <td class="info1-1">/ Member ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['memberID']}}</td>
            <td class="info3">លេខរៀងវិក្កយបត្រ</td>
            <td class="info3">/ Credit Note</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="info1 infoRow">ឈ្មោះ</td>
            <td class="info1-1">/ Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['name']}}</td>
            <td class="info3">ទំព័រលេខ</td>
            <td class="info3-1">/ Page No</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1 top">អាសយដ្ឋាន៖</td>
            <td rowspan="2" class="info1-1 top">/ Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3 infoRow">កម្រៃជើងសារ</td>
            <td class="info3-1">/ Commisionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info3 infoRow">កាលបរិច្ឆេទប្រតិបត្តិការ</td>
            <td class="info3-1">/ Transaction Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date']}}</td>
        </tr>
        <tr>
            <td class="info1 infoRow">ទូរស័ព្ទលេខ</td>
            <td class="info1-1">/ Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">លេខរៀងវិក្កយបត្រ</td>
            <td class="info3-1">/ Invoice No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['invoiceNo']}}</td>
        </tr>
        <tr>
            <td class="info1 infoRow">លេខកូដអ្នកណែនាំ</td>
            <td class="info1-1">/ Sponsor ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID']}}</td>
            <td class="info3">កាលបរិច្ឆេទ វិក្កយបត្រ</td>
            <td class="info3-1">/ Invoice Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['salesDate']}}</td>
        </tr>
        <tr>
            <td class="info1 infoRow">ឈ្មោះអ្នកណែនាំ</td>
            <td class="info1-1">/ Sponsor Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorName']}}</td>
            <td class="info3"></td>
            <td class="info3-1"></td>
            <td class="divider"></td>
            <td class="info4"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader top">ល.រ<br/>NO</td>
            <td class="data2 dataHeader top">លេខកូដមុខទំនិញ<br/>PRODUCT CODE</td>
            <td class="data3 dataHeader top">បរិយាយមុខទំនិញ<br/>DESCRIPTION</td>
            <td class="data4 center dataHeader top">បរិមាណ<br/>QUANTITY</td>
            <td class="data5 center dataHeader top">ពិន្ទ<br/>TOTAL CV</td>
            <td class="data6 right dataHeader top">ថ្លៃឯកត្តា<br/>UNIT PRICE(USD)</td>
            <td class="data7 right dataHeader top">ថ្លៃទំនិញ<br/>GMP(INCL VAT) USD</td>
        </tr>
        @foreach($sales['products'] as $product)
        <tr>
            <td class="data1 dataRow">{{ $product['no'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow center">{{ $product['qty'] }}</td>
            <td class="data5 dataRow center">{{ $product['cv'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['excTax'], 2) }}</td>
            <td class="data7 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7">
                @if ($summary)
                <table style="width: 500px;margin-top: 10px;margin-bottom: 10px" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width:20%">សង្ខេប</td>
                        <td style="width:20%">/Summary :</td>
                        <td style="width:60%"></td>
                    </tr>
                    @foreach($summary['items'] as $item => $amount)
                        <tr>
                            <td style="width:20%;padding-left: 10px">{{ $item }} =</td>
                            <td class="right" style="width:20%">{{ $amount}}</td>
                            <td style="width:60%"></td>
                        </tr>
                    @endforeach
                </table>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <table style="width: 500px;margin-top: 10px;margin-bottom: 10px" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width:20%;height:50px">កំណត់សម្គាល់</td>
                        <td style="width:20%;height:50px">/ Remarks :</td>
                        <td style="width:60%;height:50px"> {{ $remarks }}</td>
                    </tr>
                    <tr>
                        <td style="width:20%;height:50px">លេខកូដមូលហេតុ</td>
                        <td style="width:20%;height:50px">/ Reason Code :</td>
                        <td style="width:60%;height:50px"> {{$reason}}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td colspan="2" class="data5 upperline dataRow right">សរុប</td>
            <td colspan="2" class="data6 upperline dataRow right">Sub Total</td>
            <td class="data7 upperline dataRow right">{{ number_format($sales['subTotal']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td colspan="2" class="data5 dataRow right">ថ្លៃដឹកជញ្ជូន</td>
            <td colspan="2" class="data6 dataRow right">Delivery Charges</td>
            <td class="data7 dataRow right">{{ number_format($sales['delivery']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td colspan="2"  class="data5 dataRow right">ថ្លៃរដ្ឋបាល</td>
            <td colspan="2" class="data6 dataRow right">Admin Cost</td>
            <td class="data7 dataRow right">{{ number_format($sales['admin']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td colspan="2"  class="data5 dataRow right">សរុប (បូកបញ្ចូលទាំងអាករ)</td>
            <td colspan="2" class="data6 underline dataRow right">Total GMP (VAT Included)</td>
            <td class="data7 underline dataRow right">{{ number_format($sales['total']['total'], 2) }}</td>
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
                <td class="footer-line">អនុញ្ញាតដោយ<br/>Authorised Signature</td>
                <td style="width: 7%"></td>
                <td class="footer-line">ចេញដោយ<br/>Issued By</td>
                <td style="width: 6%"></td>
                <td class="footer-line">ផ្តល់ទំនិញដោយ<br/>Picked By</td>
                <td style="width: 7%"></td>
                <td class="footer-line">ទទួលដោយ<br/>Received By</td>
            </tr>
        </table>
    </div>
@endsection