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
            'sms' => '重设iBS密码，请输入OTP动态密码 :otp，5分钟后失效。',
            'email' => '
            亲爱的 :name, 
            <br><br>
        
            兹收到您要求重设iBS登入密码的通知。请输入您的OTP动态密码 :otp 。
            <br>
            请在5分钟内输入您的动态密码，否则失效。
            <br><br>
            如果您不想重设密码，请忽略或删除这封邮件。
            <br><br>
            顺祝 商祺!!
            <br><br>
            
            支援团队
            <br><br>
            <hr>
            <br><br>
            网址 : <a href="www.elken.com">www.elken.com</a>
            <br>
            电子邮件 : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><br>
            <div style="text-align: center;">< - - - 这是系统自动讯息，请不用回复。 - - - ></div>
        '
        ],

];
