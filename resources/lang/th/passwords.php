<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'password' => 'Passwords must be at least six characters and match the confirmation.',
    'reset' => 'Your password has been reset!',
    'sent' => 'We have e-mailed your password reset link!',
    'token' => 'This password reset token is invalid.',
    'user' => "We can't find a user with that e-mail address.",

    'reset-password' => [
        'sms' => 'To reset the password for your iBS account, please enter the OTP :otp, expires 5 min.',
        'email' => '
            เรียน คุณ :name, 
            <br><br>
        
            เราได้รับการแจ้งเตือนว่าคุณต้องการตั้งรหัสผ่านสำหรับเข้าสู่ระบบ iBS ของคุณใหม่ โปรดป้อนรหัสผ่านแบบครั้งเดียว (OTP): :otp 
            <br>
            OTP ใช้ได้เฉพาะเวลา 5 นาที
            <br><br>
            หากท่านไม่ต้องการตั้งรหัสผ่านใหม่ 
            <br>
            กรุณาลบอีเมล์ฉบับนี้และไม่ต้องดำเนินการใดๆ
            <br>
            และท่านจะสามารถเข้าระบบสำนักงานออนไลน์ของสมาชิกได้โดยใช้รหัสผ่านเดิม
            <br><br>
            ขอแสดงความยินดีกับความสำเร็จของท่านล่วงหน้า! 
            <br><br>
            
            ทีมงานสนับสนุน 
            <br><br>
            <hr>
            <br><br>
            เว็บไซต์ : <a href="www.elken.com">www.elken.com</a>
            <br>
            อีเมล์ : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><br>
            <div style="text-align: center;">< - - - อีเมล์นี้ถูกส่งจากระบบอัตโนมัติ โปรดอย่าตอบกลับอีเมล์นี้ - - - ></div>
        '
    ],

];
