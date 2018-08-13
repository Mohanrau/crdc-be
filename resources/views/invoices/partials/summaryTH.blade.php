@if ($summary)
<table class="summaryTbl2" cellpadding="0" cellspacing="0">
    <tr>
        <th class="title" colspan="2">Summary</th>
    </tr>
    <?php $names = $summary['names'] ?>
    @foreach($summary['items'] as $item => $amount)
        <tr>
            <td class="summary12">{{ $item }}&nbsp;&nbsp;&nbsp;{{ $names[$item] }} =</td>
            <td class="summary22 right">{{ $amount}}</td>
        </tr>
    @endforeach
</table>
<table class="summaryTbl" cellpadding="0" cellspacing="0">
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