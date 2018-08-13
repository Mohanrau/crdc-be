<?php
return [
    /**
     * ----------------------------------------------------------------------------------
     * Email section
     * ----------------------------------------------------------------------------------
     */
    'email' => [
        'subject' => 'Selamat datang ke ELKEN!',
        'content' => '
            Kepada :name, 
            <br><br>
        
            <span style="color: blue">Selamat datang ke ELKEN!</span>
            <br><br>
            
            Tahniah kerana mengambil langkah pertama menyertai kami untuk menjamin masa depan dan kejayaan kewangan peribadi anda. Ia adalah masa yang sesuai dan anda telah membuat pilihan yang terbaik untuk menjadi sebahagian daripada kami. 
            <br><br>
            Pelan Pemasaran iElken direka untuk memberi ganjaran dan kestabilan untuk memastikan anda mempunyai perniagaan yang kukuh dan menguntungkan. 
            <br><br>
            Kami menggalakkan anda untuk mengambil masa untuk membiasakan diri dengan Pelan Pemasaran iElken, alat bantu Perniagaan/Pengurusan Perniagaan serta pengetahuan produk untuk membantu anda membina perniagaan yang berjaya. Sebagai ahli, anda layak untuk membeli produk kami pada harga ahli. 
            <br><br>
            Sekali lagi, terima kasih kerana membuat keputusan yang tepat untuk menyertai kami. 
            <br><br>
            Demi Kejayaan Anda! 
            <br>
            Pihak Pengurusan
            <br><br>
            Laman Web : <a href="www.elken.com">www.elken.com</a>
            Email : <a href="mailto:customerservice@elken.com">customerservice@elken.com</a>
            <br><hr>
            
            Maklumat Log Masuk Anda<br>
            ID Keahlian: :iboMemberId
            
            <br><br>
            Kata Laluan: :password
            
            <br><br>
            *Anda boleh menukar kata laluan anda dengan log masuk ke dalam portal ahli.
            <a href=":url">:url</a>
            <br><br>
            <div style="text-align: center;">< - - - Mesej ini telah dijana secara automatik. Sila jangan balas kembali. - - - ></div>
        ',
    ],

    /**
     * ----------------------------------------------------------------------------------
     * SMS section
     * ----------------------------------------------------------------------------------
     */
    'sms' => [
        'content' => 'Selamat datang & tahniah! Sila log masuk portal iBS: :url Maklumat log masuk anda, IBO ID: :iboMemberId kata laluan: :password',
    ],

];