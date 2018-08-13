<?php
return [
    /**
     * ----------------------------------------------------------------------------------
     * Email section
     * ----------------------------------------------------------------------------------
     */
    'email' => [
        'subject' => 'Welcome to Elken',
        'content' => '
            Dear :name, 
            <br><br>
        
            <span style="color: blue">Welcome to Elken</span>
            <br><br>
            
            Congratulations on taking your first step with us to secure your future and personal financial success. It is the right time and you have made a fantastic choice to be part of us. 
            <br><br>
            The iElken Compensation Plan is designed to be rewarding and sustainable to ensure you have a strong and profitable business. 
            <br><br>
             We encourage you to take the time to familiarize yourself with the iElken Compensation Plan, business tool/management and products to help you build a successful business. As a member, you are entitled to purchase our products at member price.
            <br><br>
            Once again, thank you for making the right decision to <span style="color: red">join us</span> . 
            <br><br>
            To Your Success! 
            <br>
            The Management Team 
            <br><br>
            Web : www.elken.com
            Email : customerservice@elken.com
            <br><hr>
            
            Your Login Info<br>
            Member ID: :iboMemberId
            
            <br><br>
            Current Password: :password
            
            <br><br>
            *You can change your password by logging into member portal.
            <a href=":url">:url</a>
            <br><br>
            <div style="text-align: center;">< - - - This message was generated automatically. Please do not reply. - - - ></div>
        ',
    ],

    /**
     * ----------------------------------------------------------------------------------
     * SMS section
     * ----------------------------------------------------------------------------------
     */
    'sms' => [
        'content' => 'Welcome to iElken and congratulations on your enrollment! Please log into your iBS: :url with IBO ID: :iboMemberId Password: :password',
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * temp email for enrollment
     * -----------------------------------------------------------------------------------------------------------------
     */
    'email_temp' => [
        'subject' => 'Enrollment Started',
        'content' => '
        Dear : :name 
        <br>
        Welcome to iElken and thank your for start the Enrollment process!
        <br><br>
        Elken Enrollment Unique id : :unique_id.
        <br><br>  
        please keep this unique id to resume enrollment later if you did not complete the process.',
    ],
];