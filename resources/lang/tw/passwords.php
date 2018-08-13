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
            'sms' => '重設iBS密碼，請輸入OTP動態密碼 :otp，5分鐘後失效。',
            'email' => '
            親愛的 :name, 
            <br><br>
        
            茲收到您要求重設iBS登入密碼的通知。請輸入您的OTP動態密碼 :otp 
            <br>
            請在5分鐘內輸入您的動態密碼，否則失效。
            <br><br>
            如果不能連接上面的連結，請複製並貼上到您的瀏覽器。
            <br><br>
            如果您不想重設密碼，請忽略或刪除這封郵件。
            <br><br>
            順祝 商祺!! 
            <br><br>
            
            支援團隊 
            <br><br>
            <hr>
            <br><br>
            網址 : <a href="www.elken.com">www.elken.com</a>
            <br>
            電子郵件 : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><br>
            <div style="text-align: center;">< - - - 這是系統自動訊息，請不要回復 - - - ></div>
        '
        ],

];
