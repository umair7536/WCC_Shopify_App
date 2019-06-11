<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head_newforget')
</head>

<body class=" login">

<div class="logo">
    <a href="javascript:;">
        <img src="../metronic/assets/layouts/layout/img/logo.png" alt=""/> </a>
</div>

<div class="content">
    @yield('content')
</div>

<div class="scroll-to-top"
     style="display: none;">
    <i class="fa fa-arrow-up"></i>
</div>

@include('partials.javascripts_newforget')

</body>
</html>