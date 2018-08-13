@if ($shipping)
<table class="shippingTbl" style="width:80%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="shipping1 dataRow" style="width:43%">ឈ្មោះអ្នកទទួល / Recipient Name</td>
        <td style="width: 2%">: </td>
        <td class="shipping2 dataRow" style="width:55%"> {!! $shipping['name'] !!}</td>
    </tr>
    <tr>
        <td class="shipping1 dataRow" style="width:43%">លេខទំនាំក់ទំនង / Contact</td>
        <td style="width: 2%">: </td>
        <td class="shipping2 dataRow" style="width:55%"> {!! $shipping['contact'] !!}</td>
    </tr>
    <tr>
        <td class="shipping1 dataRow top" style="width:43%">អាសយដ្ឋានដឹកជញ្ជូន / Shipping Address Detail</td>
        <td style="width: 2%">: </td>
        <td class="shipping2 dataRow top wrapCol" style="width:55%"> {!! $shipping['address'] !!}</td>
    </tr>
</table>
@endif