<?php
/*
|---------------------------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------------------------
*/
Route::get('/', function (){
    return view('welcome');
});

Route::get('/enrollment', function () {
    $user = \App\Models\Users\User::find(84891);

//    \Illuminate\Support\Facades\Mail::to('email@email.com')
//        ->send( new \App\Mail\EnrollSucceeded($user, 'XXXX'));

    return  new \App\Mail\EnrollSucceeded($user, 'XXXX');
});

//TODO remove bellow code after testing

Route::get('/invoice', function (){

    $html = View::make('invoices.enrollment.taiwan')
        ->render();

    $mpdfObj = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 7,
        'margin_right' => 7,
        'margin_top' => 2,
        'border' => 'solid 3px #f287b7'
    ]);

    $mpdfObj->WriteHTML($html);

    //create the folders
    if (!file_exists('taiwan')) {
        mkdir('taiwan', 0777 );
    }

    if (!file_exists('taiwan/')) {
        mkdir('taiwan/', 0777 );
    }

    $fileName = str_replace(' ','-','testing').Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d_H_i_s').".pdf";

    $path  = 'taiwan/'.$fileName;

    //$mpdfObj->setFooter('{PAGENO}');

    // Saves file on the server as 'filename.pdf'
    $mpdfObj->Output($path, \Mpdf\Output\Destination::FILE);

    return view('invoices.enrollment.taiwan');
});
