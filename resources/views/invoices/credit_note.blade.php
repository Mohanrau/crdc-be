<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
	body {font-family:sans-serif;font-size: 9pt;line-height: 12pt;}
	table{vertical-align: top; width: 100%;padding-top: 8px;padding-bottom: 8px;}
    .header{font-size:12pt;margin:0px 5px;}
    .subHeader{font-size:9pt; padding: 5px 0px;}
    .smaller{font-size: 9pt}
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

    .summaryTbl{width: 50%;margin-left: 10px;margin-top: 10px;margin-bottom: 10px}
    .summaryFooter{padding-top:10px;padding-bottom:2px;}
    .summary1{width:35%;padding-left: 20px}
    .summary2{width:25%;}
    .summary3{width:40%;}

    .noteTbl{width: 60%;margin-left: 10px;margin-top: 10px;margin-bottom: 10px}
    .note1{width:20%;height:40px;vertical-align: top}
    .note2{width:80%;height:40px;vertical-align: top}

    .shippingTbl{width: 70%;margin-left: 10px;margin-top: 20px;}
    .shipping1{width:30%;padding-left: 10px}
    .shipping2{width:65%}

    .smallNote{font-size: 8pt;padding-bottom: 20px}

    .header1{width:20%;}
    .header2{width:60%;}
    .header3{width:20%;}
</style>
@yield('customCSS')
</head>
<body>
@yield('content')
</body>
</html>