<?php
    $data = [
        [
            'f_company_code' => 'TH',
            'f_code' => '14000066364',
            'f_name' => "กรกฏ ปงลังกา",
            'adjcode' => 'Bonus Adjustment',
            'adjtype' => 'Commission Underpaid',
            'f_adjustment_amount' => 285.71,
            'f_remark' => "BEC CW18'17 6POINTS",
            'username' => 'EKMYsltan'
        ],
        [
            'f_company_code' => 'MY',
            'f_code' => '10000066364',
            'f_name' => "xxxxxxx",
            'adjcode' => 'Bonus Adjustment',
            'adjtype' => 'Commission Underpaid',
            'f_adjustment_amount' => 20.00,
            'f_remark' => "BEC CW18'17 6POINTS",
            'username' => 'EKMYsltan'
        ]
    ];
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <table>
        <tr>
            <td>f_company_code</td>
            <td>f_code</td>
            <td>f_name</td>
            <td>adjcode</td>
            <td>adjtype</td>
            <td>f_adjustment_amount</td>
            <td>f_remark</td>
            <td>username</td>
        </tr>
        @foreach($data as $row)               
            <tr>
                <td>{{ $row['f_company_code'] }}</td>
                <td>{{ $row['f_code'] }}</td>
                <td>{{ $row['f_name'] }}</td>
                <td>{{ $row['adjcode'] }}</td>
                <td>{{ $row['adjtype'] }}</td>
                <td>{{ $row['f_adjustment_amount'] }}</td>
                <td>{{ $row['f_remark'] }}</td>
                <td>{{ $row['username'] }}</td>
            </tr>
        @endforeach
    </table>
    
</body>
</html>