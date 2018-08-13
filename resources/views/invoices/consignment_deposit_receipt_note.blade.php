@extends('invoices.tax_invoice')

@section('customCSS')
<style type="text/css">
    body{font-family: "Arial";}
    .info0 {width: 10%;}
    .info1 {width: 10%;}
    .info2 {width: 40%;}
    .info3 {width: 10%;}
    .info4 {width: 20%;}

    .data0{width:10%}
    .data1{width:5%;}
    .data2{width:55%;}
    .data3{width:20%;}

</style>
@endsection

@section('content')
<div>
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header center"><u>{{ $data['title'] }}</u></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="4">
        <tr>
            <td class="info0"></td>
            <td class="info1">Dist. Code</td>
            <td class="divider">:</td>
            <td class="info2">{{ $data['stockist'] }}</td>
            <td colspan="3" class="info3 right"><b>{{ $data['no'] }}</b></td>
            <td class="info0"></td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">Name</td>
            <td class="divider">:</td>
            <td class="info2">{{ $data['name'] }}</td>
            <td class="info3">Page No.</td>
            <td class="divider">:</td>
            <td class="info4">1 / 1</td>
            <td class="info0"></td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1 top">Address</td>
            <td class="divider">:</td>
            <td class="info2 top wrapCol">{{ $data['address'] }}</td>
            <td class="info3">Date</td>
            <td class="divider">:</td>
            <td class="info4">{{ $data['date'] }}</td>
            <td class="info0"></td>
        </tr>
        <tr>
            <td class="info0"></td>
            <td class="info1">Tel</td>
            <td class="divider">:</td>
            <td class="info2">{{ $data['tel'] }}</td>
            <td class="info3"></td>
            <td class="divider"></td>
            <td class="info4"></td>
            <td class="info0"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <th class="data0"></th>
            <th class="data1 left dataHeader">No</th>
            <th class="data2 left dataHeader">Description</th>
            <th class="data3 right dataHeader">Amount {{ $data['currency']? "(".$data['currency'].")" : "" }}</th>
            <th class="data0"></th>
        </tr>
        <tr>
            <td class="data0"></td>
            <td class="data1 dataRow left">1</td>
            <td class="data2 dataRow left wrapCol">{{ $data['description'] }}</td>
            <td class="data3 dataRow right">{{ number_format($data['total'], 2) }}</td>
            <td class="data0"></td>
        </tr>

        <tr>
            <td class="data0"></td>
            <td colspan="3" class="left">
                <br/><br/>
                Remarks: {{$data['remark']}}
                <br/><br/>
            </td>
            <td class="data0"></td>
        </tr>
        <tr>
            <td class="data0"></td>
            <td class="data1 upperline dataHeader">Total</td>
            <td class="data2 upperline dataHeader"></td>
            <td class="data3 upperline right dataHeader">{{ number_format($data['total'], 2) }}</td>
            <td class="data0"></td>
        </tr>
        @if ($data['payments'])
        <tr>
            <td class="data0"></td>
            <td colspan="2" class="data1" >
                <table class="summaryTbl" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="summary1">Payment</td>
                        <td class="summary2"></td>
                        <td class="summary3"></td>
                    </tr>
                    @foreach($data['payments'] as $payment)
                    <tr>
                        <td class="summary1">{{ $payment['method'] }}</td>
                        <td class="summary2"></td>
                        <td class="summary3 right">{{ number_format($payment['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
            <td class="data3"></td>
            <td class="data0"></td>
        </tr>
        @endif
    </table>
    <div class="signature">
        <table cellpadding="0" cellspacing="5">
            <tr>
                <td style="width:10%"></td>
                <td class="signSpace center bottom">{{ $data['issuer'] }}</td>
                <td style="width: 50%"></td>
                <td class="signSpace center bottom">{{ $data['approver'] }}</td>
                <td style="width:10%"></td>
            </tr>
            <tr>
                <td style="width:10%"></td>
                <td class="footer-line">Issued By</td>
                <td style="width: 50%"></td>
                <td class="footer-line">
                    Received & Approved By</td>
                <td style="width:10%"></td>
            </tr>
        </table>
    </div>
</div>
@endsection