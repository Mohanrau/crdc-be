<?php
return [
    /**
     * ----------------------------------------------------------------------------------
     * Email section
     * ----------------------------------------------------------------------------------
     */
    'email' => [
        'subject' => 'ស្វាគមន៍មកកាន់អែលខេន (ELKEN)',
        'content' => '
            ជូនចំពោះ :name, 
            <br><br>
        
            <span style="color: blue">ស្វាគមន៍មកកាន់អែលខេន (ELKEN)</span>
            <br><br>
            
            យើងខ្ញុំសូមអបអរសាទរអ្នកក្នុងការទិញទំនិញពីពួកយើង។ 
            <br><br>
            អ្នកបានជ្រើសរើសជម្រើសមួយដ៍ត្រឹមត្រូវក្នុងការក្លាយជាអតិថិជនរបស់យើង។ 
            <br><br>
            សូមយកពេលវេលាដើម្បីយល់ឱ្យបានច្បាស់ ជាមួយនឹងផលិតផលរបស់យើងដោយខ្លួនឯង។ 
            <br><br>
            ជាថ្មីម្តងទៀតយើងខ្ញុំសូមអរគុណសម្រាប់ការគាំទ្រនិងការទិញរបស់អ្នក។ 
            <br><br>
            ដើម្បីភាពជោគជ័យរបស់អ្នក! 
            <br>
            ក្រុមអ្នកគ្រប់គ្រង 
            <br><br>
            គេហទំព័រ : <a href="www.elken.com">www.elken.com</a>
            អ៊ីមែល : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><hr>
            
            Your Login Info<br>
            Member ID: :iboMemberId
            
            <br><br>
            Current Password: :password
            
            <br><br>
            *You can change your password by logging into member portal.
            <a href=":url">:url</a>
            <br><br>
            <div style="text-align: center;">< - - - សារនេះត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ។សូមកុំឆ្លើយតប - - - ></div>
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

];