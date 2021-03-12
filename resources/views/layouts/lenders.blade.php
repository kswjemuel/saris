
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{ config('app.name', 'Saris') }}</title>
    <meta content="Admin Dashboard" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App Icons -->
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}">

    <!--Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <!-- Basic Css files -->
    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">

    <link href="{{asset('css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/daterangepicker.css')}}" rel="stylesheet">

    <link href="{{asset('plugins/c3.css')}}" rel="stylesheet" type="text/css">
    
    <link href="{{asset('css/icons.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('css/style.css')}}" rel="stylesheet" type="text/css">
    

</head>


<body>
<!-- Top Bar Start -->
@include('includes.topmenu')
<!-- Top Bar End -->

<!-- ========== Left Sidebar Start ========== -->
@include('includes.lenders-sidebar')
<!-- Left Sidebar End -->

@yield('content')


<!-- Javascript -->

<script type="text/javascript" src="{{asset('js/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/bootstrap-datepicker.min.js')}}"></script>

<script type="text/javascript" src="{{asset('js/moment.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/daterangepicker.js')}}"></script>

<script type="text/javascript" src="{{asset('js/alertify.js')}}"></script>


<!-- plugins -->
<script type="text/javascript" src="{{asset('plugins/Chart.min.js')}}"></script>

<script type="text/javascript" src="{{asset('plugins/d3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('plugins/c3.min.js')}}"></script>


<!-- vue js -->
<script type="text/javascript" src="{{asset('js/vue.js')}}"></script>
<script type="text/javascript" src="{{asset('js/axios.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/app.js')}}"></script>
@yield('page-scripts')
</body>
</html>