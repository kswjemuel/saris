@extends('layouts.auth')

@section('content')

<form class="form-signin" method="post" action="{{route('password.email')}}" autocomplete="off">
    <h4 class="text-muted font-18 m-b-5 text-center">Reset Password here</h4>
    <p class="text-muted text-center">Enter your Email and instructions will be sent to you!</p>
    {{ csrf_field() }}
    <input type="text" name="" style="display: none;">
    <div class="form-group">
        <label for="useremail">Email</label>
        <input type="email" class="form-control field" id="useremail" name="email" value="{{ old('email') }}" placeholder="Enter email">
    </div>

    <div class="form-group row m-t-20">
        <div class="col-12 text-right">
            <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Reset</button>
        </div>
    </div>

</form>



@endsection
