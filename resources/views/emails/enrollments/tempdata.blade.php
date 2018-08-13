@extends('layouts.email')

@section('content')
    {!! __(
        'enrollment.email_temp.content',
        [
            'name' => $name,
            'unique_id' => $uniqueCode
        ]
    )
    !!}
@endsection
