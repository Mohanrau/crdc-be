@if ($summary)
<table style="width: 47%;margin-left: 10px;margin-top: 20px;margin-bottom: 20px" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="3">សង្ខេប/ Summary :</td>
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
        <td colspan="2" class="summaryFooter summary1 underline">ប្រភេទនៃការទូទាត់ទំនិញ Payment Mode</td>
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