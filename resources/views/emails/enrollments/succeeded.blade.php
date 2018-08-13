@extends('layouts.email')

@section('content')
    {!! __(
        'enrollment.email.content',
        [
            'url' => config('app.member_url'),
            'name' => $user->name,
            'password' => $password,
            'iboMemberId' => $user->old_member_id
        ]
    )
    !!}
@endsection
