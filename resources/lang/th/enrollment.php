<?php
return [
    /**
     * ----------------------------------------------------------------------------------
     * Email section
     * ----------------------------------------------------------------------------------
     */
    'email' => [
        'subject' => 'ยินดีต้อนรับสู่เอลเคน',
        'content' => '
            เรียน คุณ :name, 
            <br><br>
        
            <span style="color: blue">ยินดีต้อนรับสู่เอลเคน</span>
            <br><br>
            
            ขอแสดงความยินดีต่อท่านที่ท่านตัดสินใจเลือกก้าวไปพร้อมกับเราเพื่ออนาคตที่มั่นคงและเ พื่อประสบความสำเร็จทางการเงิน 
            <br><br>
            แผนการตลาดไอเอลเคน ถูกออกแบบมาเพื่อจ่ายผลตอบแทนให้กับนักธุรกิจเพื่อให้ท่านได้ก้าวเดินไปข้างหน้าอย่าง มั่นคง 
            <br><br>
            ขอให้ท่านใช้เวลาในการศึกษาและทำความคุ้นเคยกับแผนการจ่ายผลตอบแทนไอเอลเคน รวมไปถึงเครื่องมือทางธุรกิจ ระบบการจัดการและผลิตภัณฑ์ เพื่อต่อยอดให้ธุรกิจของท่านประสบความสำเร็จในภายภาคหน้า ทั้งนี้ท่านสมาชิกสามารถซื้อผลิตภัณฑ์ได้ในราคาสมาชิก 
            <br><br>
            ขอขอบคุณอีกครั้งที่ท่านได้เลือกเข้าร่วมธุรกิจกับเรา. 
            <br><br>
            ขอแสดงความยินดีกับความสำเร็จของท่านล่วงหน้า! 
            <br>
            คณะผู้บริหาร 
            <br><br>
            เว็บไซต์: <a href="www.elken.com">www.elken.com</a>
            อีเมล์: <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><hr>
            
            ข้อมูลล็อกอินของท่าน<br>
            รหัสสมาชิก: :iboMemberId
            
            <br><br>
            รหัสผ่านปัจจุบัน: :password
            
            <br><br>
            *านสามารถเปลี่ยนแปลงรหัสผ่านของท่าน โดยล็อกอินเข้าไปที่หน้าสมาชิก
            <a href=":url">:url</a>
            <br><br>
            <div style="text-align: center;">< - - - อีเมล์นี้ถูกส่งจากระบบอัตโนมัติ โปรดอย่าตอบกลับอีเมล์นี้ - - - ></div>
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