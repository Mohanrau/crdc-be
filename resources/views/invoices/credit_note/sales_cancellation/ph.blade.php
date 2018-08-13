@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    .info1 {width: 12%;}
    .info2 {width: 50%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}
   
    .data1{width:4%;}
    .data2{width:12%;}
    .data3{width:34%;}
    .data4{width:15%;}
    .data5{width:4%;}
    .data6{width:15%;}
    .data7{width:4%;}
    .data7{width:12%;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td rowspan="4" class="header1 top"><img src="{{ config('setting.logo_url') }}" /></td>
            <td class="header2 header center top">ELKEN INTERNATIONAL PHILIPPINES CO., LTD.</td>
            <td rowspan="3" class="header3"></td>
        </tr>
        <tr>
            <td class="header2 subHeader center">
                Unit 101, Parc Royale Building, Dona Julia Vargas<br/>
                Avenue, Ortigas 1605, Pasig City, Metro Manila, Philippines<br/><br/>
                VAT Reg Tin: 008-618-569-0000<br/>
                Tel : +6302 535 2266<br/>
                Fax : +6302 535 2282<br/>
            </td>
        </tr>
        <tr>
            <td class="header2 header center dotted-underline">CREDIT NOTE</td>
        </tr>
        <tr>
            <td class="header2"></td>
            <td class="header3 header right">{{ $basic['no']}}<br/><div class="smaller right">- ORIGINAL COPY-</div></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">GID</td>
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
            <td class="info3">Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date']}}</td>
        </tr>
        <tr>
            <td class="info1">TIN</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['taxNo']}}</td>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1 top">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Commisionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info3">Sales Order No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Inv Ref No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['invoiceNo'] }}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor Code</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID'] }} - {{ $basic['sponsorName'] }}</td>
            <td class="info3">Inv Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['salesDate']}}</td>
        </tr>
        <tr>
            <td class="info1 top">Business Style</td>
            <td class="divider top">:</td>
            <td class="info2 top">{{ $basic['businessStyle']}}</td>
            <td class="info3 top">Reason</td>
            <td class="divider top">:</td>
            <td class="info4 top wrapCol">{{ $reason }}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">NO</td>
            <td class="data2 dataHeader">CODE NO</td>
            <td class="data3 dataHeader">DESCRIPTION</td>
            <td class="data4 dataHeader center">QTY</td>
            <td class="data5 dataHeader center">CV</td>
            <td class="data6 center dataHeader">GROSS UNIT PRICE</td>
            <td class="data7 dataHeader"></td>
            <td class="data8 right dataHeader">TOTAL AMT</td>
        </tr>
        @foreach($sales['products'] as $product)
        <tr>
            <td class="data1 dataRow">{{ $product['no'] }}</td>
            <td class="data2 dataRow">{{ $product['code'] }}</td>
            <td class="data3 dataRow wrapCol">{!! $product['description'] !!}</td>
            <td class="data4 dataRow center">{{ $product['qty'] }}</td>
            <td class="data5 dataRow center">{{ $product['cv'] }}</td>
            <td class="data6 dataRow right">{{ number_format($product['unitPrice'], 2) }}</td>
            <td class="data7 dataRow center">PHP</td>
            <td class="data8 dataRow right">{{ number_format($product['total'], 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="8">
                <table class="summaryTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <th class="title" colspan="3">Summary</th>
                    </tr>
                    @foreach($summary['items'] as $item => $amount)
                        <tr>
                            <td class="summary1">{{ $item }} =</td>
                            <td class="summary2 right">{{ $amount}}</td>
                            <td class="summary3"></td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="8">
                <table class="noteTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <th class="note1 title">Remarks:</th>
                        <td class="note2"> {{ $remarks }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="data1 upperline"></td>
            <td class="data2 upperline"></td>
            <td class="data3 upperline dataRow"></td>
            <td class="data4 upperline dataRow right">TOTAL SALES</td>
            <td class="data5 upperline dataRow center">{{ $sales['subTotal']['cv'] }}</td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline dataRow center">PHP</td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['subTotal']['total'], 2) }}</td>
        </tr>

        <tr>
            <td class="data0 dataRow"></td>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3"></td>
            <td class="data4"></td>
            <td class="data5"></td>
            <td class="data6"></td>
            <td class="data7"></td>
            <td class="data8"></td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3 dataRow"></td>
            <td class="data4 upperline dataRow right">Grand Total</td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline"></td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['total']['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3"></td>
            <td class="data4 upperline dataRow right">VATABLE SALE</td>
            <td class="data5 upperline"></td>
            <td class="data6 upperline"></td>
            <td class="data7 upperline"></td>
            <td class="data8 upperline dataRow right">{{ number_format($sales['total']['excTax'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3"></td>
            <td class="data4 dataRow right">VAT</td>
            <td class="data5"></td>
            <td class="data6"></td>
            <td class="data7"></td>
            <td class="data8 dataRow right">{{ number_format($sales['total']['tax'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1"></td>
            <td class="data2"></td>
            <td class="data3"></td>
            <td class="data4 dataRow right">VAT EXEMPT SALE</td>
            <td class="data5"></td>
            <td class="data6"></td>
            <td class="data7"></td>
            <td class="data8 dataRow right">{{ number_format($sales['total']['exempt'], 2) }}</td>
        </tr>
        <tr>
            <td class="data1 underline"></td>
            <td class="data2 underline"></td>
            <td class="data3 underline"></td>
            <td class="data4 dataRow right underline">ZERO RATED SALE</td>
            <td class="data5 underline"></td>
            <td class="data6 underline"></td>
            <td class="data7 underline"></td>
            <td class="data8 dataRow underline right">{{ number_format($sales['total']['zeroRated'], 2) }}</td>
        </tr>
        @if(isset($summary['payments']))
        <tr>
            <td colspan="8">
                <table class="summaryTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="summaryFooter summary1 underline">Payment Mode</td>
                        <td class="summary2 underline"></td>
                        <td class="summaryFooter summary3 underline right">Pay Amount</td>
                    </tr>
                    @foreach($summary['payments'] as $payment)
                        <tr>
                            <td class="summary1">{{ $payment['method'] }}</td>
                            <td class="summary2"></td>
                            <td class="summary3 right">{{ number_format($payment['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif
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
                <td class="footer-line">Received By</td>
            </tr>
        </table>
        <div class="breaker"></div>
        <div class="center" style="width: 100%">
            IT IS NOT AN OFFICIAL CREDIT NOTE
        </div>
    </div>
@endsection