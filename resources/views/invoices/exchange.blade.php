<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
    body {font-family:sans-serif;font-size: 9pt;line-height: 12pt;}
    table{vertical-align: top; width: 100%;padding-top: 8px;padding-bottom: 8px;}
    .header{font-size:11pt;padding:4px 0px;}
    h3{font-size:13pt;font-weight: bold;}
    .excHeader{border-top:1px solid #000; border-bottom: 2px double #000;padding: 3px 0px}
    .wrap{word-wrap:break-word}
    .left{text-align: left;}
    .right{text-align: right;}
    .center{text-align: center;}
    .top {vertical-align: top}
    .bottom{vertical-align: bottom}
    .middle{vertical-align: middle}
    .breaker{height: 20px;}
    .spacer{height: 5px;}
    .wrapCol{margin-left:3px;word-wrap:break-word;}
    .divider{width: 1%}

    .space-top{padding-top:12px;}
    .total{padding: 6px 0px; font-weight: 500;vertical-align: middle}
    .doubleLine{ border-bottom: double 1px #000; border-top: solid 1px #000; }
    .dotted-underline{border-bottom: dotted 1px #000;}
    .upperline {border-top: solid 1px #000;font-weight: 100;}
    .underline {border-bottom: solid 1px #000;font-weight: 100;}
    .dataHeader {border-bottom: solid 1px #000;border-top: solid 1px #000;vertical-align: top;padding-bottom: 10px;padding-top:2px;}
    .dataRow{padding-bottom: 10px;padding-top:2px;}

    .title{font-weight: bold;width: 100%;font-size: 11pt;text-align: left;}
    .col-3{width: 33%}
    .col-4{width: 25%}
    .col-1{width: 100%}

    .footer-line{width: 25%;border-top: solid 1px #000;}
    .signature{padding-top: 200px;}
    .signature-bottom{bottom:0;position: fixed}
    .signSpace{height:100px;vertical-align: top;}

    .remarkTbl{width: 50%;margin-left: 10px;margin-top: 20px;margin-bottom: 20px}
    .summary1{width:35%;padding-bottom:4px;}
    .summary2{width:35%;text-align:right;}
    .summary3{width:30%;text-align:right;}
</style>
@yield('customCSS')
</head>
<body>
@yield('content')

<div class="signature">
    <table cellpadding="0" cellspacing="5">
        <tr>
            <td class="signSpace">Issued By</td>
            <td style="width: 50%"></td>
            <td class="signSpace">Member Signature</td>
        </tr>
        <tr>
            <td class="footer-line">Date :</td>
            <td style="width: 50%"></td>
            <td class="footer-line">Date :</td>
        </tr>
    </table>
</div>
</body>
</html>