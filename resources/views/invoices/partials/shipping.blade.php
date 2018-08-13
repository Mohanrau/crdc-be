@if ($shipping)
<table class="shippingTbl" cellpadding="0" cellspacing="0">
    <tr>
        <th class="title" colspan="3">Shipping address detail</th>
    </tr>
    <tr>
        <td class="shipping1 dataRow">Recipient Name</td>
        <td style="width: 5%">: </td>
        <td class="shipping2 dataRow"> {!! $shipping['name'] !!}</td>
    </tr>
    <tr>
        <td class="shipping1 dataRow top">Address</td>
        <td style="width: 5%">: </td>
        <td class="shipping2 dataRow top wrapCol"> {!! $shipping['address'] !!}</td>
    </tr>
    <tr>
        <td class="shipping1 dataRow">Contact</td>
        <td style="width: 5%">: </td>
        <td class="shipping2 dataRow"> {!! $shipping['contact'] !!}</td>
    </tr>
</table>
@endif