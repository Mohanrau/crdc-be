<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		body {font-family:sans-serif;font-size: 9pt;line-height: 10pt;}
		00px}table{vertical-align: top; width: 100%;padding-top: 8px;padding-bottom: 8px;}
	    .header{font-size:11pt;margin:0px 5px;font-weight: bold}
	    .subHeader{font-size:8pt; padding: 5px 0px;}
	    .smaller{font-size: 7pt}
	    .wrap{word-wrap:break-word}
	    .left{text-align: left;}
	    .right{text-align: right;}
	    .center{text-align: center;}
	    .top {vertical-align: top}
	    .bottom{vertical-align: bottom}
	    .middle{vertical-align: middle}
	    .breaker{height: 15px;}
	    .spacer{height: 5px;}
	    .wrapCol{margin-left:3px;word-wrap:break-word;}
	    .divider{width: 1%}

	    .taller{height:15pt;}
	    .total{padding: 5px 0px; font-weight: 500; font-size: 11pt;vertical-align: middle}
	    .doubleLine{ border-bottom: double 2px #000; border-top: solid 1px #000; }
	    .dotted-underline{border-bottom: dotted 1px #000;}
	    .upperline {border-top: solid 1px #000;font-weight: 100;}
	    .underline {border-bottom: solid 1px #000;font-weight: 100;}
	    .dataHeader {border-bottom: solid 1px #000;border-top: solid 1px #000;font-weight: 100; vertical-align: bottom;padding: 5px 0px}
	    .dataRow{padding-bottom: 6px;padding-top:2px;}
	    .infoRow{padding-bottom: 2px;padding-top:2px;}

	    .title{font-weight: bold;width: 100%;font-size: 11pt;text-align: left}
	    .col-3{width: 33%}
	    .col-4{width: 25%}
	    .col-1{width: 100%}

	    .footer-line{width: 20%;vertical-align: top; text-align: center;}
	    .signature{padding-top: 50px;}
	    .signature-bottom{bottom:0;position: fixed}
	    .signSpace{height:50px;border-bottom: solid 1px #000;}

	    .header1{width:25%;}
	    .header2{width:60%;}
	    .header3{width:15%;}

	    .light-blue-bg {background:#EAEAEA;font-weight: bold}
	    .left-summary{border-top: 1px inset white;border-left: 1px inset white;border-right: 1px inset white;height:8.5pt;padding-left: 5px}
	    .right-summary{border-top: 1px inset white;border-right: 1px inset white;text-align: right;padding-right: 5px}

	    .noright-summary{border-top: 1px inset white;border-left: 1px inset white;height:8.5pt;padding-left: 5px}
	    .noleft-summary{border-top: 1px inset white;border-right: 1px inset white;text-align: right;padding-right: 5px}
	    .summary-bottom-line{border-bottom: 1px inset white;}

	    #border-tbl {
		    border-collapse: collapse;
		    border-style: hidden;
		}

		#border-tbl td, #border-tbl th {
		    padding: 4px;
		}

		.data1 {width:50%;}
		.data2 {width:15%;text-align: right}
		.data3 {width:15%;text-align: right}
		.data4 {width:20%;text-align: right}

		.info1{width: 100px}
		.info2{width: 3
	</style>
</head>
<body>
	<table border="0" cellspacing="0" cellpadding="0" style='width:650px'>
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
                STOCKIST COMMISSION STATEMENT {{ $data->cw->cw_name }}
            </td>
        </tr>
    </table>

	<div class="breaker"></div>

    <table border=0 cellpadding=3 cellspacing=0 style='width:400px'>
		<tr>
			<td class="info1">Statement Date</td>
			<td class="info2">{{ date("d/m/Y", strtotime($data->created_at))  }}</td>
		</tr>
		<tr>
			<td class="info1">Stockist Code</td>
			<td class="info2">{{ $data->stockist->stockist_number }}</td>
		</tr>
		<tr>
			<td class="info1">Name</td>
			<td class="info2">{{ empty($data->tax_company_name)? $data->stockist->memberUser->name : $data->tax_company_name }}</td>
		</tr>
		<tr>
			<td class="info1">Tax No</td>
			<td class="info2">{{$data->tax_no}}</td>
		</tr>
	</table>

	<div class="breaker"></div>

	<table id="border-tbl" border=0 cellspacing=1 cellpadding=1 style='width:650px'>
		<tr>
			<td class="data1">Description</td>
			<td class="data2 right">Total CV</td>
			<td class="data3 right">% on CV</td>
			<td class="data4 right">Amount</td>
		</tr>

		<tr>
			<td class="data1">OTC Sales (USD)</td>
			<td class="data2">{{ number_format($data->otc_sales_cv, 0) }}</td>
			<td class="data3">{{ $data->otc_sales_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->otc_sales_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1">WP (USD)</td>
			<td class="data2">{{ number_format($data->otc_wp_cv, 0) }}</td>
			<td class="data3">{{ $data->otc_wp_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->otc_wp_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1">Others (USD)</td>
			<td class="data2">{{ number_format($data->otc_others_cv, 0) }}</td>
			<td class="data3">{{ $data->otc_others_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->otc_others_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1"></td>
			<td class="data2">{{ number_format($data->total_otc_cv, 0) }}</td>
			<td class="data3"></td>
			<td class="data4">{{ number_format($data->total_otc_amount, 2) }}</td>
		</tr>
		<tr>
			<td colspan="4" class="breaker"></td>
		</tr>
		<tr>
			<td class="data1">IBS Sales (USD)</td>
			<td class="data2">{{ number_format($data->online_sales_cv, 0) }}</td>
			<td class="data3">{{ $data->online_sales_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->online_sales_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1">WP (USD)</td>
			<td class="data2">{{ number_format($data->online_wp_cv, 0) }}</td>
			<td class="data3">{{ $data->online_wp_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->online_wp_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1">Others (USD)</td>
			<td class="data2">{{ number_format($data->online_others_cv, 0) }}</td>
			<td class="data3">{{ $data->online_others_commission_percentage }}%</td>
			<td class="data4">{{ number_format($data->online_others_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1"></td>
			<td class="data2">{{ number_format($data->total_online_cv, 0) }}</td>
			<td class="data3"></td>
			<td class="data4">{{ number_format($data->total_online_amount, 2) }}</td>
		</tr>
		<tr>
			<td colspan="4" class="breaker"></td>
		</tr>

		<tr>
			<td class="data1">Gross Stockist Commission ({{ $data->currency->code}})</td>
			<td class="data2"></td>
			<td class="data3"></td>
			<td class="data4">{{ number_format($data->total_gross_amount, 2) }}</td>
		</tr>
		<tr>
			<td class="data1">{{ $data->tax_type}} {{$data->tax_rate}}% ({{ $data->currency->code}})</td>
			<td class="data2"></td>
			<td class="data3"></td>
			<td class="data4">{{ number_format($data->total_tax_amount, 2) }}</td>
		</tr>
		<tr>
			<td colspan="4" class="breaker"></td>
		</tr>

		<tr>
			<td class="data1">Net Stockist Commission Payable (before bank charges)</td>
			<td class="data2"></td>
			<td class="data3"></td>
			<td class="data4"><b>{{ number_format($data->total_nett_amount, 2) }}</b></td>
		</tr>
	</table>

</body>
</html>