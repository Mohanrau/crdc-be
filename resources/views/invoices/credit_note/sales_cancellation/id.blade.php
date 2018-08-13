@extends('invoices.credit_note')

@section('customCSS')
<style type="text/css">
    body {font-family:Arial;}

    .info1 {width: 15%;}
    .info2 {width: 45%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}

    .data1{width:5%;}
    .data2{width:15%;}
    .data3{width:40%;}
    .data4{width:12%;}
    .data5{width:13%;}
    .data6{width:15%;}

</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td rowspan="2" class="col-3"></td>
            <td class="col-3 header center middle dotted-underline">SALES CANCELLATION</td>
            <td rowspan="2" class="col-3 header bottom right">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="col-3"></td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="4">
        <tr>
            <td class="info1">Code</td>
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
            <td rowspan="3" class="info1">Address</td>
            <td rowspan="3" class="divider">:</td>
            <td rowspan="3" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td class="info3">Sales Trn No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['transactionNo'] }}</td>
        </tr>
        <tr>
            <td class="info3">Commissionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Sales Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['date'] }}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">NO</td>
            <td class="data2 dataHeader">CODE NO</td>
            <td class="data3 dataHeader">DESCRIPTION</td>
            <td class="data4 right dataHeader">QTY</td>
            <td class="data5 right dataHeader">TOTAL CV</td>
            <td class="data6 right dataHeader">Total DP</td>
        </tr>
        @foreach($sales['products'] as $product)
            <tr>
                <td class="dataRow data1">{{ $product['no'] }}</td>
                <td class="dataRow data2">{{ $product['code'] }}</td>
                <td class="dataRow data3 wrapCol">{{ $product['description'] }}</td>
                <td class="dataRow data4 right">{{ $product['qty'] }}</td>
                <td class="dataRow data5 right">{{ $product['cv'] }}</td>
                <td class="dataRow data6 right">{{ number_format($product['total'], 2) }}</td>
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
                        <td class="note1">Remarks</td>
                        <td class="divider">: </td>
                        <td class="note2"> {{ $remarks }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="dataRow data1 upperline underline"></td>
            <td class="dataRow data2 upperline underline"></td>
            <td class="dataRow data3 right upperline underline">Total :</td>
            <td class="dataRow data4 right upperline underline">{{ $sales['subTotal']['qty'] }}</td>
            <td class="dataRow data5 right upperline underline">{{ $sales['subTotal']['cv'] }}</td>
            <td class="dataRow data6 right upperline underline">{{ number_format($sales['subTotal']['total'],2) }}</td>
        </tr>
        <tr>
            <td colspan="8">
                <table class="noteTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="note1">Payment</td>
                        <td class="divider">:</td>
                        <td class="note2">
                            @if ($summary['payments'])
                                {{ $summary['payments']['method'] }} : 
                                {{ number_format($summary['payments']['total'], 2) }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="signature-bottom">
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
        <div class="left" style="width: 90%">
            PT.ELKEN GLOBAL INDONESIA, NPWP: 76.732.954.3-044.000<br/>
            Rukan Mangga Dua Square Blok C1-2,<br/>
            JL. Gunung Sahari Raya No.1,<br/>
            Jakarta Utara 14420.<br/>
            Tel: (021)6128288&nbsp;&nbsp;&nbsp;Fax: (021)6128916&nbsp;&nbsp;&nbsp;WebSite: www.elken.com&nbsp;&nbsp;&nbsp;Email: INDONESIA@Elken.com<br/>
        </div>
        </div>
@endsection