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
            ជូនចំពោះ :name, 
            <br><br>
        
            យយើងបានទទួលការជូនដំណឹងមួយដែលអ្នកចង់កំណត់ពាក្យសម្ងាត់ចូលអ៊ីនធីស៍របស់អ្នក។ សូមបញ្ចូលពាក្យសម្ងាត់ម្តង (OTP): :otp 
            <br>
            OTP មានសុពលភាពត្រឹមតែ 5 នាទីប៉ុណ្ណោះ។
            <br><br>
            បើសិនជាអ្នក លែងចង់ធ្វើការផ្លាស់ប្តូរលេខកូដសំងាត់ អ្នកគ្រាន់តែទុកចោលឬលុបចោលអ៊ីមែលនេះ។   
            <br><br>
            ដើម្បីភាពជោគជ័យរបស់អ្នក!
            <br><br>
            
            ក្រុមអ្នកគាំទ្រ
            <br><br>
            <hr>
            <br><br>
            គេហទំព័រ :<a href="www.elken.com">www.elken.com</a>
            <br>
            អ៊ីមែល :<a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><br>
            <div style="text-align: center;">< - - - សារនេះត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ។សូមកុំឆ្លើយតប។- - - ></div>
        '
    ],

];
