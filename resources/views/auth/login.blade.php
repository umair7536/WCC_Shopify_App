@extends('layouts.auth_new')

@section('content')   
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were problems with input:
                            <br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="login-form"
                          role="form"
                          method="POST"
                          action="{{ url('login') }}">
                        <input type="hidden"
                               name="_token"
                               value="{{ csrf_token() }}">
                        <h3 class="form-title">Login to your account</h3>
                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span> Enter any username and password. </span>
                        </div>

                        <div class="form-group">
                            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                            <label class="control-label visible-ie8 visible-ie9">Email</label>
                            <div class="input-icon">
                                <i class="fa fa-user"></i>
                                <input class="form-control placeholder-no-fix" type="email" autocomplete="off"
                                       placeholder="Username/Email" name="email" value="{{ old('email') }}"/></div>
                        </div>
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9">Password</label>
                            <div class="input-icon">
                                <i class="fa fa-lock"></i>
                                <input class="form-control placeholder-no-fix" type="password" autocomplete="off"
                                       placeholder="Password" name="password"/></div>
                        </div>
                        <div class="form-actions">
                            <label class="rememberme mt-checkbox mt-checkbox-outline">
                                <input type="checkbox" name="remember"/> Remember me
                                <span></span>
                            </label>
                            <button type="submit" class="btn green pull-right"> Login</button>
                        </div>

                        <div class="create-account">
                            <p>
                                <a href="{{ route('auth.register') }}" id="register-btn" class="uppercase">Create an account</a>
                            </p>
                        </div>

                        <div class="forget-password">
                            <h4>Forgot your password ?</h4>
                            <p> no worries, click
                                <a href="{{ route('auth.password.reset') }}" id="forget-password"> here </a> to reset
                                your password. </p>
                        </div>
                    </form>
                </div>



@endsection
