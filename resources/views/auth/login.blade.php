@extends('layouts.auth')

@section('content')
<form class="form-signin" autocomplete="off" method="post" action="{{route('login')}}">
      <input type="text" style="display:none">
      <input type="password" style="display:none">
    {{ csrf_field() }}
    <input type="text" name="email" style="display: none;">
    <div class="form-group">
        <label for="email">Username</label>
        <input type="text" class="form-control field" id="email" name="email" placeholder="Enter email">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control field" id="password" name="password" placeholder="Enter password">
    </div>

    <div class="checkbox mb-3">
        <label>
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember me
        </label>
      </div>

      <div class="form-group">
          <button class="btn btn-primary btn-block" type="submit">Sign in</button>
      </div>

      <div class="form-group">
          <a href="{{ url('/password/reset') }}">Forgot your password?</a>
      </div>
</form>


@endsection
