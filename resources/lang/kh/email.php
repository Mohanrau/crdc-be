<?php
return [

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Custom Email
    |-------------------------------------------------------------------------------------------------------------------
    */

    //Welcome Email Content---------------------------------------------------------------------------------------------
    'welcome' => [
        'subject' => 'Welcome to Elken IBS',
        'dear' => 'Dear :Salutation :Name ',
        'body' => 'Thank you for registering on our IBS system and we welcome you onboard',
        'visit' => 'Visit IBS',
        'footer' => 'Thank you for using our Elken IBS!'
    ],

    //staff welcome message --------------------------------------------------------------------------------------------
    'staff_welcome' => [
        'subject' => 'Welcome to Elken',
        'dear' => 'Dear :Salutation :Name ',
        'body' => 'Thank you for being part of Elken. and welcome on board.',
        'credentials' => 'please use the following credentials to login in to our IBS.',
        'email' => 'Email: :email',
        'password' => 'Password: :password',
        'visit' => 'Visit IBS',
        'footer' => 'Elken IBS!'
    ],
];
