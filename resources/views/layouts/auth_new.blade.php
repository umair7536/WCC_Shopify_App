<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head_new')
</head>

<body class=" login">

<div class="logo"></div>

<div class="content">
    @yield('content')
</div>

<div class="scroll-to-top"
     style="display: none;">
    <i class="fa fa-arrow-up"></i>
</div>

@include('partials.javascripts_new')

</body>
</html>