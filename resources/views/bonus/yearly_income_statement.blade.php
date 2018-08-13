<?php
	$incomes = [];
	for($i = 0; $i< 12; $i++) {
		$income = new \stdClass();
		$income->month = "JANUARY";
		$income->amount = 123456.78;
		$income->nonMonetory = 123.00;
		array_push($incomes, $income);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		body {font-family:sans-serif;font-size: 9pt;line-height: 11pt;}
		table{vertical-align: top; width: 100%;padding-top: 8px;padding-bottom: 8px;}
	    .header{font-size:10pt;margin:0px 5px;font-weight: bold}
	    .subHeader{font-size:8pt; padding: 5px 0px;}
	    .smaller{font-size: 6pt;line-height: 7pt;}
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
	    .col-1{width: 100%}
	    .col-2{width: 50%}
	    .col-3{width: 40%}
	    .col-4{width: 25%}
	    .col-5{width: 10%}

	    .footer-line{width: 20%;vertical-align: top; text-align: center;}
	    .signature{padding-top: 50px;}
	    .signature-bottom{bottom:0;position: fixed}
	    .signSpace{height:50px;border-bottom: solid 1px #000;}

	    .header1{width:20%;}
	    .header2{width:60%;}
	    .header3{width:20%;}

	    .data-1 {width:45%}
	    .data-2 {width:20%}
	    .data-3 {width:35%}

	    .light-blue-bg {background:#EAEAEA;font-weight: bold}
	    .left-summary{border: 1px solid #000;border-bottom: none;height:8.5pt;padding-left: 5px}
	    .right-summary{border-top: 1px solid #000;border-right: 1px solid #000;text-align: right;padding-right: 5px}

	    .left-line {border-left:1px solid #000;}
	    .left-right-line {border-left:1px solid #000;border-right:1px solid #000;}
	    .dataFooter-1 {padding:6px 2px;vertical-align: middle}
	    .dataFooter-2 {padding:6px 2px;vertical-align: middle; border-top: 1px solid #000; border-left: 1px solid #000; border-bottom:1px solid #000;}
	    .dataFooter-3 {padding:6px 2px;vertical-align: middle; border: 1px solid #000}

	    .summary-bottom-line{border-bottom: 1px solid #000;}
	</style>
</head>
<body>
	<table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header1 top" style="padding-left:30px"><img src="{{ config('setting.logo_url') }}" /></td>
            <td class="header2 header center top"><u>INCOME STATEMENT</u></td>
            <td class="header3"></td>
        </tr>
    </table>

    <table border=0 cellpadding=3 cellspacing=0 style='width:650px;padding:10px 30px'>
		<tr>
			<td class="col-2">xxxxxxxxxxxx</td>
			<td class="col-3 right">FOR YEAR ENDED :</td>
			<td class="col-5">31/12/2017</td>
		</tr>
		<tr>
			<td class="col-2">address line 1</td>
			<td class="col-3 right">DATE :</td>
			<td class="col-5">16/01/2018</td>
		</tr>
		<tr>
			<td class="col-2">address line 2</td>
			<td class="col-3 right">STAT. NO :</td>
			<td class="col-5"></td>
		</tr>
		<tr>
			<td class="col-2">address line 3</td>
			<td class="col-3"></td>
			<td class="col-5"></td>
		</tr>
	</table>

	<table border=0 cellpadding=3 cellspacing=0 style='width:650px;padding:10px 30px'>
		<tr>
			<td class="col-4 right">MEMBERSHIP CODE : </td>
			<td>xxxxxx</td>
		</tr>
		<tr>
			<td class="col-4 right">IC. NO : </td>
			<td>xxxxxxxxxxxx</td>
		</tr>
		<tr>
			<td class="col-4 right">DISTRIBUTOR's STATUS : </td>
			<td>abc</td>
		</tr>
		<tr>
			<td class="col-4 right">SPONSOR CODE : </td>
			<td>xxxxxxxxxxx</td>
		</tr>
	</table>

	<table border=0 cellspacing=0 cellpadding=3 style='width:750px;padding:10px 30px'>
		<tr>
			<td class="underline data-1"></td>
			<td class="underline left-line data-2 right">Amount(RM)</td>
			<td class="underline left-right-line data-3 right">Non-Monetory Incentive<br/>Product Redemption Voucher(RM)</td>
		</tr>

		@foreach ($incomes as $income)
			<tr>
				<td>{{ $income->month }}</td>
				<td class="left-line right">{{ $income->amount }}</td>
				<td class="left-right-line right">{{ $income->nonMonetory }}</td>
			</tr>
		@endforeach
		
		<tr>
			<td class="dataFooter-1"><b>Total Income</b></td>
			<td class="dataFooter-2 right">99,995.25 </td>
			<td class="dataFooter-3 right">3,846.00 </td>
		</tr>
	</table>

	<table border=0 cellspacing=0 cellpadding=3 style='width:450px;padding:20px 30px'>
		<tr>
			<td colspan=2 class="left-summary">Campaign Trip</td>
		</tr>
		<tr>
			<td class="left-summary">Destination</td>
			<td class="right-summary">Amount(RM)</td>
		</tr>
		<tr>
			<td class="left-summary">Korea</td>
			<td class="right-summary">5,000.00</td>
		</tr>
		<tr>
			<td class="left-summary">japan</td>
			<td class="right-summary">8,000.00</td>
		</tr>
		<tr>
			<td class="left-summary summary-bottom-line">Total</td>
			<td class="right-summary summary-bottom-line">13,000.00</td>
		</tr>
	</table>

<div class="smaller signature-bottom" style="padding:0px 30px; width: 660px">
	Risk &amp; Confidentiality advice: Kindly keep in strict confidence your statement, whether in print, while retaining it subsequently filing it as such information are confidential to you personally. You shall always maintain close guard of your statement to ensure that it is not misplaced or lost through careless means or sheer failure to maintain its confidentiality.<br/><br/>
	Distributors are solely responsible for compliance with the applicable tax requirements.<br/><br/>
	Responsibilities of Distributors in conducting the Elken Business overseas: Distributors are to adhere to any requirements and restrictions on conducting business, taxation, work and business permit needs and to conduct their operations within the legal framework of law at the Host Country(ies) at all times. As a foreigner in Host Country, Distributors will be subjected to local taxes and they must pay income tax. Distributors operating in most countries are subjected to withholding taxes.<br/><br/>
	As part of this responsibility, you must notify the country if, for any reason, you are deemed as a tax resident by a foreign tax jurisdiction (e.g. spending long periods in a foreign country, commencing additional business or employment in a foreign country etc.). In the event of uncertainty, kindly seek professional tax consultants for advice. The Company will not be responsible for any consequences whatsoever arising from the Distributor's neglect and/or failure to so notify the company.<br/><br/>
	In accordance with Malaysia Income Tax Act, this income is subject to Malaysia income tax. Please consult your tax agent or accountant immediately if you are in any doubt as to the course of action you should take with Regard to Form CP-58.<br/><br/>
	NOTE | THIS IS A COMPUTER GENERATED STATEMENT. NO SIGNATURE REQUIRED.
	<table>
		<tr><td><img src="{{ config('setting.footer_url') }}" /></td></tr>
	</table>
</div>
</body>
</html>