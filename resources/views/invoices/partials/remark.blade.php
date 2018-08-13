
<table class="remarkTbl" cellpadding="0" cellspacing="0">
    <tr>
        <td class="title" colspan="3">Remark :</td>
    </tr>
    @if ($remarks)
        <tr>
            <td class="summary1 underline space-top">GST Summary</td>
            <td class="summary2 underline space-top right">Amount (MYR)</td>
            <td class="summary3 underline space-top right">Tax (MYR)</td>
        </tr>
        @foreach ($remarks as $remark)
            <tr>
                <td class="summary1">{!! $remark['summary'] !!}</td>
                <td class="summary2 right">{{ number_format($remark['amount'], 2) }}</td>
                <td class="summary3 right">{{ number_format($remark['tax'], 2) }}</td>
            </tr>
        @endforeach
    @endif
</table>
