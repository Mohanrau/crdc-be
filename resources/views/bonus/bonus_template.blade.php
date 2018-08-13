<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		body {font-family:sans-serif;font-size: 7.5pt;line-height: 9pt;}
	table{vertical-align: top; width: 100%;padding-top: 8px;padding-bottom: 8px;}
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

    .header1{width:20%;}
    .header2{width:60%;}
    .header3{width:20%;}

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
        border: 1px solid black; padding: 8px 5px;
    }
	</style>

    @yield('customCSS')
</head>
<body>
    @yield('content')
</body>
</html>