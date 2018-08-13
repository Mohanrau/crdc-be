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
        
            <span style="color: blue">Welcome to Elken </span>
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
            <br>
            Email: customerservice@elken.com 
            <br><hr>
            
            Your Login Info<br>
            Member ID: :iboMemberId
            
            <br><br>
            Current Password: :password
            
            <br><br>
            *You can change your password by logging into member portal.
        ',
    ],

    /**
     * ----------------------------------------------------------------------------------
     * SMS section
     * ----------------------------------------------------------------------------------
     */
    'sms' => [
        'content' => '
Dear :name
Welcome to Elken
Thank you for Enrolling!
Your Login Info

Member ID: :iboMemberId 
Password: :password
',
    ],

];