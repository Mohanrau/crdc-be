@extends('layouts.email')

@section('content')
    {!! __(
        'passwords.reset-password.email',
        [
            'name' => $name,
            'url' => $url,
            'otp' => $otp
        ], $language
    )
    !!}
@endsection
