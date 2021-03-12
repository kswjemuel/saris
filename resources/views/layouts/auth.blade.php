
<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta content="Admin Dashboard" name="description" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App Icons -->
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}">

    <!-- Basic Css files -->
    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('css/icons.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('css/auth.css')}}" rel="stylesheet" type="text/css">

    </head>


    <body>
    @yield('content')

    </body>
</html>