@extends('invoices.tax_invoice')

@section('customCSS')
<style type="text/css">
    body {font-family:Arial;}

    .info1 {width: 15%;}
    .info2 {width: 45%;}
    .info3 {width: 18%;}
    .info4 {width: 20%;}

    .data1{width:3%;}
    .data2{width:10%;}
    .data3{width:24%;}
    .data4{width:5%;}
    .data5{width:8%;}
    .data6{width:8%;}
    .data7{width:8%;}
    .data8{width:12%;}
    .data9{width:9%;}
    .data10{width:12%;}

    .dataRow{font-size:8pt;}
</style>
@endsection

@section('content')
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td rowspan="2" class="col-3"><img src="{{ config('setting.logo_url') }}" /></td>
            <td class="col-3 header center middle dotted-underline">CASH SALES (ORDERS)</td>
            <td rowspan="2" class="col-3 header bottom right">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="col-3"></td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="4">
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
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / {nb}</td>
        </tr>
        <tr>
            <td rowspan="2" class="info1">Address</td>
            <td rowspan="2" class="divider">:</td>
            <td rowspan="2" class="info2 wrapCol">{!! $basic['address'] !!}</td>
            <td class="info3">Commissionable Cycle</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['cycle']}}</td>
        </tr>
        <tr>
            <td class="info3">Sales No</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['no']}}</td>
        </tr>
        <tr>
            <td class="info1">NPWP/KTP/Passport</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['taxNo'] }}</td>
            <td class="info3">Transaction Type</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['orderType'] }}</td>
        </tr>
        <tr>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['tel']}}</td>
            <td class="info3">Sales Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['created_at'] }}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor ID</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorID'] }}</td>
            <td class="info3">Self Collection Code</td>
            <td class="divider">:</td>
            <td class="info4">{{ $basic['collection'] }}</td>
        </tr>
        <tr>
            <td class="info1">Sponsor</td>
            <td class="divider">:</td>
            <td class="info2">{{ $basic['sponsorName']}}</td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="data1 dataHeader">NO</td>
            <td class="data2 dataHeader">CODE NO</td>
            <td class="data3 dataHeader">DESCRIPTION</td>
            <td class="data4 right dataHeader">QTY</td>
            <td class="data5 right dataHeader">Total CV</td>
            <td class="data6 right dataHeader">Unit Price (IDR)</td>
            <td class="data7 right dataHeader">Sub Total (IDR)</td>
            <td class="data8 right dataHeader">GMP (Excl PPN) (IDR)</td>
            <td class="data9 right dataHeader">PPN {{ $taxRate }}% (IDR)</td>
            <td class="data10 right dataHeader">GMP (Incl PPN) (IDR)</td>

        </tr>
        @foreach($sales['products'] as $product)
            <tr>
                <td class="dataRow data1">{{ $product['no'] }}</td>
                <td class="dataRow data2">{{ $product['code'] }}</td>
                <td class="dataRow data3 wrapCol">{{ $product['description'] }}</td>
                <td class="dataRow data4 right">{{ $product['qty'] }}</td>
                <td class="dataRow data5 right">{{ $product['cv'] }}</td>
                <td class="dataRow data6 right">{{ number_format($product['unitPrice'], 2) }}</td>
                <td class="dataRow data7 right">{{ number_format($product['subTotal'], 2) }}</td>
                <td class="dataRow data8 right">{{ number_format($product['excTax'], 2) }}</td>
                <td class="dataRow data9 right">{{ number_format($product['tax'], 2) }}</td>
                <td class="dataRow data10 right">{{ number_format($product['total'], 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="10">
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
            <td colspan="10">
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
            <td class="dataRow data6 upperline underline"></td>
            <td class="dataRow data7 upperline underline"></td>
            <td class="dataRow data8 right upperline underline">{{ number_format($sales['subTotal']['excTax'],2) }}</td>
            <td class="dataRow data9 right upperline underline">{{ number_format($sales['subTotal']['tax'],2) }}</td>
            <td class="dataRow data10 right upperline underline">{{ number_format($sales['subTotal']['total'],2) }}</td>
        </tr>
        <tr>
            <td colspan="10">
                <table class="noteTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="note1">Payment</td>
                        <td class="divider">:</td>
                        <td class="note2">
                            @if ($summary['payments'])
                                @foreach($summary['payments'] as $payment)
                                {{ $payment['method'] }} :
                                {{ number_format($payment['total'], 2) }}
                                    <br/>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="10">
                @include('invoices.partials.shipping', $shipping)
            </td>
        </tr>
    </table>

    <div class="signature">
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
                <td class="footer-line">Received By<br/>(Signature / Chop)</td>
            </tr>
        </table>
        <div class="breaker"></div>
        <div class="left" style="width: 90%">
            PT.ELKEN GLOBAL INDONESIA, NPWP: 76.732.954.3-044.000<br/>
            Rukan Mangga Dua Square Blok C1-2,<br/>
            JL. Gunung Sahari Raya No.1,<br/>
            Jakarta Utara 14420.<br/>
            Tel: (021)6128288&nbsp;&nbsp;&nbsp;Fax: (021)6128916&nbsp;&nbsp;&nbsp;WebSite: www.elken.com&nbsp;&nbsp;&nbsp;Email: INDONESIA@Elken.com<br/><br/>
            Perhatian: Barang Yang Dibeli Harus Diambil Selambat-lambatnya 7 Hari Dari Tanggal Invoice.<br/>
            Perusahaan Tidak Bertanggungjawab Atas Barang Yang Tidak Diambil Setelah Waktu Yang Ditentukan<br/>
        </div>
        </div>
@endsection