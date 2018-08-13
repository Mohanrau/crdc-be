@if ($summary)
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
    @if (array_key_exists('payments', $summary))
    <tr>
        <td class="summaryFooter summary1 ">Payment:</td>
        <td class="summary2"></td>
        <td class="summary3"></td>
    </tr>
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
    @endif
</table>
@endif