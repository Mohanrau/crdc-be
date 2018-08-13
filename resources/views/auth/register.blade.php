@extends('layouts.auth')

@section('title') Sign Up | Elken @endsection

@section('css')
    <link rel="stylesheet" href="{{ mix('css/auth/auth-pages.min.css') }}">
@endsection

@section('body_class', 'animsition page-login-v3 layout-full')

@section('content')
    <!-- Page -->
    <div class="page vertical-align text-xs-center" data-animsition-in="fade-in" data-animsition-out="fade-out">

        <div class="page-content vertical-align-middle">

            <div class="panel">

                <div class="panel-body">

                    <div class="brand">
                        <img class="brand-img" src="{{ asset('assets/images/logo_med.png')}}" alt="Elken">
                        <h2 class="brand-text font-size-18">Builds You To Build Others</h2>
                    </div>

                    <form class="form-horizontal" role="form" method="POST" action="{{ route('register') }}">

                        {{ csrf_field() }}

                        <div class="form-group form-material floating {{ $errors->has('name') ? ' has-error' : '' }}" data-plugin="formMaterial">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus />

                            <label class="floating-label">Full Name</label>

                            @if ($errors->has('name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group form-material floating {{ $errors->has('email') ? ' has-error' : '' }}" data-plugin="formMaterial">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus />

                            <label class="floating-label">E-Mail Address</label>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group form-material floating {{ $errors->has('password') ? ' has-error' : '' }}" data-plugin="formMaterial">
                            <input type="password" class="form-control" name="password" value="{{ old('password') }}" required autofocus />

                            <label class="floating-label">Password</label>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group form-material floating {{ $errors->has('password') ? ' has-error' : '' }}" data-plugin="formMaterial">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required />

                            <label class="floating-label">Confirm Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg m-t-40">Sign up</button>

                    </form>

                </div>

            </div>

            <footer class="page-copyright page-copyright-inverse">
                <p>Elken Global SDN BHD</p>
                <p>© 2017. All RIGHT RESERVED.</p>
            </footer>

        </div>

    </div>
    <!-- End Page -->

@endsection

@section('js')
    <script src="{{ mix('js/auth/auth-pages.min.js') }}"></script>
@endsection

