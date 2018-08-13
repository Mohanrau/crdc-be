<?php
use App\Helpers\Classes\Utilities;
?>
@extends('bonus.bonus_template')

@section('customCSS')
<style type="text/css">
    .w1 {width: 5%;}
    .w2 {width: 12%;}
    .w3 {width: 20%;}
    .w4 {width: 10%;}
    .w5 {width: 9%;}
    .w6 {width: 15%;}
    .w7 {width: 12%;}
    .w8 {width: 12%;}

</style>
@endsection

@section('content')

<table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td rowspan="3" class="header1 top"><img src="{{ config('setting.logo_url') }}" /></td>
        <td class="header2 header center top">ELKEN GLOBAL SDN BHD</td>
        <td rowspan="3" class="header3"></td>
    </tr>
    <tr>
        <td class="header2 subHeader center bottom">
            20, Bangunan Elken,Jalan 1/137C, Batu 5 Jalan Kelang Lama Kuala Lumpur
        </td>
    </tr>
    <tr>
        <td class="header2 subHeader center top">
            Bonus Statement {{ $bonus['summary']->cw->cw_name }}
        </td>
    </tr>
</table>

<table border=0 cellpadding=3 cellspacing=0 style='width:650px'>
    <tr>
        <td class="header1">Statement Date</td>
        <td>{{ $bonus['summary']->statement_date }}</td>
    </tr>
    <tr>
        <td>Member Code</td>
        <td>{{ $bonus['summary']->user->old_member_id }}</td>
    </tr>
    <tr>
        <td>Name</td>
        <td>{{ $bonus['summary']->name }}</td>
    </tr>
    <tr>
        <td>Tax No</td>
        <td>{{ $bonus['summary']->tax_no }}</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>Highest Rank</td>
        <td>{{ ($bonus['summary']->highestRank)?$bonus['summary']->highestRank->rank_code: '' }}</td>
    </tr>
    <tr>
        <td>Effective Rank</td>
        <td>{{ $bonus['summary']->effectiveRank->rank_code }}</td>
    </tr>

    <tr>
        <td class="top" >Address</td>
        <td class="wrapCol">{!! $bonus['summary']->address_data !!}</td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:325px;'>
    <tr>
        <td width=225 class="left-summary light-blue-bg" >Gross Bonus</td>
        <td width=100 class="right-summary light-blue-bg">US$</td>
    </tr>
    <tr>
        <td class="left-summary">Welcome Bonus</td>
        <td class="right-summary">{{ number_format($bonus['summary']->welcome_bonus, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary">Retail Profit</td>
        <td class="right-summary">0.00</td>
    </tr>
    <tr>
        <td class="left-summary">Team Bonus</td>
        <td class="right-summary">{{ number_format($bonus['summary']->team_bonus_diluted, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary">Mentor Bonus</td>
        <td class="right-summary">{{ number_format($bonus['summary']->mentor_bonus_diluted, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary">Quarterly Dividend</td>
        <td class="right-summary">{{ number_format($bonus['summary']->quarterly_dividend, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary">Incentive</td>
        <td class="right-summary">{{ number_format($bonus['summary']->incentive, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary summary-bottom-line">Total Gross Bonus</td>
        <td class="right-summary summary-bottom-line">{{ number_format($bonus['summary']->total_gross_bonus, 2) }}</td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:100%'>
    <tr>
        <td width=325 class="left-summary">Gross Bonus (US$ 1 = {{ $bonus['summary']->currency->code.' '.$bonus['usdConversionRate'] }})</td>
        <td width=325 class="right-summary">(US$ {{ number_format($bonus['summary']->total_gross_bonus, 2) }})&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {{ $bonus['summary']->currency->code.' '.number_format($bonus['summary']->total_gross_bonus_local_amount, 2) }}</td>
    </tr>
    <tr>
        <td width=325 class="left-summary">{{ $bonus['summary']->tax_type }}</td>
        <td width=325 class="right-summary">{{$bonus['summary']->currency->code.' '.number_format($bonus['summary']->total_tax_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="left-summary">&nbsp;</td>
        <td class="right-summary">&nbsp;</td>
    </tr>
    <tr>
        <td width=325 class="left-summary summary-bottom-line">Net Bonus Payable (before bank charges)</td>
        <td width=325 class="right-summary summary-bottom-line">
            {{$bonus['summary']->currency->code.' '.number_format($bonus['summary']->total_net_bonus_payable, 2) }}
        </td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:100%'>
    <tr>
        <td colspan=8 style='border:1px inset white;'>Welcome Bonus Details</td>
    </tr>
    <tr>
        <td class="w1 noright-summary light-blue-bg">No.</td>
        <td class="w2 noright-summary light-blue-bg">Member Code</td>
        <td class="w3 noright-summary light-blue-bg" width="100px">Name</td>
        <td class="w4 noright-summary light-blue-bg">Join Date</td>
        <td class="w5 noright-summary light-blue-bg center">Level</td>
        <td class="w6 noright-summary light-blue-bg right">Local Amount</td>
        <td class="w7 left-summary light-blue-bg right">US$</td>
        <td class="w8 noleft-summary light-blue-bg">Nett (US$)</td>
    </tr>

    @define $counter = 1;
    @foreach($bonus['welcomeBonus'] as $row)
    <tr>
        <td class="noright-summary">{{ $counter++ }}</td>
        <td class="noright-summary">{{ $row->sponsorChild->old_member_id }}</td>
        <td class="noright-summary" lang="{{ Utilities::getLangCode($row->sponsorChild->member->country->code_iso_2) }}">{{ $row->sponsorChild->name }}</td>
        <td class="noright-summary">{{ $row->join_date }}</td>
        <td class="noright-summary center">{{ $row->sponsor_child_depth_level }}</td>
        <td class="noright-summary right">{{ $bonus['summary']->currency->code.' '.number_format($row->total_amount, 2) }}</td>
        <td class="left-summary right">{{ number_format($row->total_usd_amount, 2) }}</td>
        <td class="noleft-summary">{{ number_format($row->nett_usd_amount, 2) }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="5" class="noright-summary summary-bottom-line"></td>
        <td class="noright-summary summary-bottom-line"></td>
        <td class="left-summary summary-bottom-line"></td>
        <td class="noleft-summary summary-bottom-line">{{ number_format($bonus['summary']->welcome_bonus, 2) }}</td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:100%'>
    <tr>
        <td colspan=12 style='border:1px inset white;'>GCV Details</td>
    </tr>
    <tr>
        <td class="noright-summary light-blue-bg">Batch</td>
        <td class="noright-summary light-blue-bg">Member Code</td>
        <td class="noright-summary light-blue-bg">Name</td>
        <td class="noright-summary light-blue-bg right">C/Forward</td>
        <td class="noright-summary light-blue-bg right">This Week CV</td>
        <td class="noright-summary light-blue-bg right">This Week OPS</td>
        <td class="noright-summary light-blue-bg right">Calculation CV</td>
        <td class="noright-summary light-blue-bg">&nbsp;</td>
        <td class="noright-summary light-blue-bg">%</td>
        <td class="noright-summary light-blue-bg right">Point</td>
        <td class="left-summary light-blue-bg right">Flush</td>
        <td class="noleft-summary light-blue-bg">C/Over</td>
    </tr>

    @foreach($bonus['teamBonus'] as $row)
        <tr>
            <td class="noright-summary">{{ $bonus['summary']->cw->cw_name }}</td>
            <td class="noright-summary">{{ $row->placementChild->old_member_id }}</td>
            <td class="noright-summary" lang="{{ Utilities::getLangCode($row->placementChild->member->country->code_iso_2) }}">{{ $row->placementChild->name }}</td>
            <td class="noright-summary right">{{ number_format($row->gcv_bring_forward, 2) }}</td>
            <td class="noright-summary right">{{ number_format($row->gcv, 2) }}</td>
            <td class="noright-summary right">{{ number_format($row->optimising_personal_sales, 2) }}</td>
            <td class="noright-summary right">{{ number_format($row->gcv_calculation, 2) }}</td>
            <td class="noright-summary">{{ $row->gcv_leg_group }}</td>
            <td class="noright-summary">{{ ($row->team_bonus_percentage * 100) }}</td>
            <td class="noright-summary right">{{ number_format($row->team_bonus, 2) }}</td>
            <td class="left-summary right">{{ number_format($row->gcv_flush, 2) }}</td>
            <td class="noleft-summary right">{{ number_format($row->gcv_bring_over, 2) }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="9" class="noright-summary">&nbsp;</td>
        <td class="noright-summary right">{{ number_format($bonus['summary']->team_bonus, 2) }}</td>
        <td class="left-summary">&nbsp;</td>
        <td class="noleft-summary">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="6" class="noright-summary summary-bottom-line">&nbsp;</td>
        <td colspan=3 class="noright-summary summary-bottom-line left middle">Value per Point {{ $bonus['summary']->diluted_percentage }}</td>
        <td class="noright-summary summary-bottom-line right">US$ {{ number_format($bonus['summary']->team_bonus_diluted, 2) }}</td>
        <td class="left-summary summary-bottom-line">&nbsp;</td>
        <td class="noleft-summary summary-bottom-line">&nbsp;</td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:100%'>
    <tr>
        <td colspan=7 style='border:1px inset white;'>Mentor Bonus Details</td>
    </tr>
    <tr>
        <td class="noright-summary light-blue-bg">No.</td>
        <td class="noright-summary light-blue-bg">Member Code</td>
        <td class="noright-summary light-blue-bg">Name</td>
        <td class="noright-summary light-blue-bg center">Level</td>
        <td class="noright-summary light-blue-bg right">TB Point</td>
        <td class="left-summary light-blue-bg right">%</td>
        <td class="noleft-summary light-blue-bg">Point</td>
    </tr>

    @define $counter = 1;
    @foreach($bonus['mentorBonus'] as $row)
        <tr>
            <td class="noright-summary">{{ $counter++ }}</td>
            <td class="noright-summary">{{ $row->sponsorChild->old_member_id }}</td>
            <td class="noright-summary" lang="{{ Utilities::getLangCode($row->sponsorChild->member->country->code_iso_2) }}">{{ $row->sponsorChild->name }}</td>
            <td class="noright-summary center">{{ $row->sponsor_generation_level }}</td>
            <td class="noright-summary right">{{ number_format($row->team_bonus, 2) }}</td>
            <td class="left-summary right">{{ $row->mentor_bonus_percentage*100 }}</td>
            <td class="noleft-summary">{{ number_format($row->mentor_bonus, 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td class="noright-summary">&nbsp;</td>
        <td class="noright-summary">&nbsp;</td>
        <td class="noright-summary">&nbsp;</td>
        <td class="noright-summary">&nbsp;</td>
        <td class="noright-summary">&nbsp;</td>
        <td class="left-summary">&nbsp;</td>
        <td class="noleft-summary right">{{ number_format($bonus['summary']->mentor_bonus, 2) }}</td>
    </tr>
    <tr>
        <td class="noright-summary summary-bottom-line">&nbsp;</td>
        <td class="noright-summary summary-bottom-line">&nbsp;</td>
        <td class="noright-summary summary-bottom-line">&nbsp;</td>
        <td colspan=3 class="left-summary summary-bottom-line left">Value per Point {{ $bonus['summary']->diluted_percentage}}</td>
        <td class="noleft-summary summary-bottom-line">US$ {{ number_format($bonus['summary']->mentor_bonus_diluted, 2) }}</td>
    </tr>
</table>

<table border=0 cellspacing=0 cellpadding=3 style='width:100%'>
    <tr>
        <td colspan=3 style='border:1px inset white;'>Quarterly Dividen Details</td>
    </tr>
    <tr>
        <td class="noright-summary light-blue-bg">No.</td>
        <td class="left-summary light-blue-bg">Batch No</td>
        <td class="noleft-summary light-blue-bg">Point</td>
    </tr>

    @define $counter = 1;
    @foreach($bonus['quarterlyDividendBonus'] as $row)
    <tr>
        <td class="noright-summary">{{ $counter++ }}</td>
        <td class="left-summary">{{ $row->cw->cw_name }}</td>
        <td class="noleft-summary">{{ number_format($row->shares, 2) }}</td>
    </tr>
    @endforeach
    <tr>
        <td class="noright-summary summary-bottom-line"></td>
        <td class="left-summary summary-bottom-line"></td>
        <td class="noleft-summary summary-bottom-line">{{ number_format($bonus['summary']->quarterly_dividend,2 ) }}</td>
    </tr>
</table>
@endsection