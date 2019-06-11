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

    <form class="login-form" role="form" method="POST" action="{{ url('install') }}">

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <h3 class="form-title">Shopify Store URL</h3>
        <div class="alert alert-danger display-hide">
            <button class="close" data-close="alert"></button>
            <span> Enter your shop URL. </span>
        </div>

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-shopping-cart"></i>
                <input value="omniblend.myshopify.com" class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Enter your store url" name="shop" value="{{ old('email') }}"/>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn green"> Install App</button>
        </div>
    </form>

@endsection
