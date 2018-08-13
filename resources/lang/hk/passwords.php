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
            Dear :name, 
            <br><br>
        
            We have received notification that you want to reset your iBS login password.  
            <br>
            Please enter the one-time password (OTP) : :otp 
            <br>
            The OTP only valid for 5 minutes.  
            <br><br>
            If you do not want to reset your password, you may simply ignore or delete this email; your login password will not be changed, and you will still be able to access your member portal.  
            <br><br>
            To Your Success! 
            <br><br>
            
            Support Team 
            <br><br>
            <hr>
            <br><br>
            Web : <a href="www.elken.com">www.elken.com</a>
            Email : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><br>
            <div style="text-align: center;">< - - - This message was generated automatically. Please do not reply. - - - ></div>
        '
    ],

];
