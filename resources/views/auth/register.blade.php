@extends('layouts.auth')

@section('content')


<div class="wrapper-page">

    <div class="card">
        <div class="card-block">

            <!-- <h3 class="text-center m-0">
                <a href="index.html" class="logo logo-admin"><img src="assets/images/logo.png" height="30" alt="logo"></a>
            </h3> -->

            <div class="p-3">
                <h4 class="text-muted font-18 m-b-5 text-center">Register</h4>
                <!-- <p class="text-muted text-center">Get your free Admiria account now.</p> -->

                <form class="form-horizontal m-t-30" method="post" action="{{route('register')}}" autocomplete="off">
                    {{ csrf_field() }}
                    <input type="text" name="" style="display: none;">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="name" placeholder="Enter username" value="{{old('name')}}">

                        @if ($errors->has('name'))
                            <small class="has-error help-block">{{ $errors->first('name') }}</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="useremail">Email</label>
                        <input type="email" class="form-control" id="useremail" name="email" placeholder="Enter email" value="{{old('email')}}">
                        @if ($errors->has('email'))
                            <small class="has-error help-block">{{ $errors->first('email') }}</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="userpassword">Password</label>
                        <input type="password" class="form-control" id="userpassword" name="password" placeholder="Enter password">
                        @if ($errors->has('password'))
                            <small class="has-error help-block">{{ $errors->first('password') }}</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="userpassword">Password</label>
                        <input type="password" class="form-control" id="userpassword" name="password_confirmation" placeholder="Repeat password">
                    </div>
                    

                    <div class="form-group row m-t-20">
                        <div class="col-12 text-right">
                            <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Register</button>
                        </div>
                    </div>

                    <div class="form-group m-t-10 mb-0 row">
                        <div class="col-12 m-t-20">
                            <p class="font-14 text-muted mb-0">By registering you agree to the Admiria <a href="#">Terms of Use</a></p>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <div class="m-t-40 text-center">
        <p class="text-white">Already have an account ? <a href="{{route('login')}}" class="font-500 font-14 text-white font-secondary"> Login </a> </p>
        
    </div>

</div>

@endsection
