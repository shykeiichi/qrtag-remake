@extends('layouts.home')
@section('content')
<div class="container col-xl-10 col-xxl-8 px-4 py-5">
    <div class="row align-items-center g-lg-5 py-5">
        <div class="col-lg-7 text-center text-lg-start">
            <h1 class="display-4 fw-bold lh-1 text-body-emphasis mb-3">Logga in</h1>
            <p class="col-lg-10 fs-4">Du loggar in på QRTag med ditt skolkonto (samma lösenord som till datorn).</p>
        </div>
        <div class="col-md-10 mx-auto col-lg-5">
            <form class="p-4 p-md-5 border rounded-3 bg-body-tertiary" data-bitwarden-watching="1" action="/api/auth/login" method="POST">
                @csrf 

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="20abcd" required>
                    <label for="username">Användarnamn</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <div class="checkbox mb-3">
                    <label>
                        <input type="checkbox" value="remember-me"> Kom ihåg mig
                    </label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit">Logga in</button>
                @isset($error)
                    <div class="alert alert-danger mt-3" role="alert">
                        {{ $error }}
                    </div>
                @endisset
            </form>
        </div>
    </div>
</div>
@stop