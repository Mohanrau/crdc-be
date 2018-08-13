@extends('layouts.auth')

@section('title') User Login @endsection

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

                      <form method="post" action="{{ route('login') }}" autocomplete="off">

                          {{ csrf_field() }}

                          <div class="form-group form-material floating" data-plugin="formMaterial">

                              <input type="email" required class="form-control" name="email" value="{{ old('email') }}" />

                              <label class="floating-label">Email</label>
                                  @if ($errors->has('email'))
                                      <span class="help-block">
                                          <strong>{{ $errors->first('email') }}</strong>
                                      </span>
                                  @endif

                          </div>

                          <div class="form-group form-material floating" data-plugin="formMaterial">

                              <input type="password" required class="form-control" name="password" />

                              <label class="floating-label">Password</label>
                                  @if ($errors->has('password'))
                                      <span class="help-block">
                                          <strong>{{ $errors->first('password') }}</strong>
                                      </span>
                                  @endif

                          </div>

                          <div class="form-group clearfix">

                              <div class="checkbox-custom checkbox-inline checkbox-primary checkbox-lg pull-xs-left">
                                  <input type="checkbox" id="inputCheckbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                  <label for="inputCheckbox">Remember me</label>
                              </div>

                              <a class="pull-xs-right" href="{{ route('password.request') }}">Forgot password?</a>

                          </div>

                          <button type="submit" class="btn btn-primary btn-block btn-lg m-t-40">Sign in</button>

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

