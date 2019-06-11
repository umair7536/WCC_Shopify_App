@extends('layouts.auth_newforget')

@section('content')

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

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


                    <form class="forget-form"
                          role="form"
                          method="POST"
                          action="{{ url('password/email') }}">
                        <input type="hidden"
                               name="_token"
                               value="{{ csrf_token() }}">
                        <h3>Forget Password ?</h3>
                        <p> Enter your e-mail address below to reset your password. </p>
                        <div class="form-group">
                            <div class="input-icon">
                                <i class="fa fa-envelope"></i>
                                <input class="form-control placeholder-no-fix" type="email" autocomplete="off"
                                       placeholder="Email" name="email" value="{{ old('email') }}"/></div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('auth.login') }}" id="back-btn" class="btn red btn-outline">Back </a>

                            <button type="submit" class="btn green pull-right"> Submit</button>
                        </div>
                    </form>

@endsection