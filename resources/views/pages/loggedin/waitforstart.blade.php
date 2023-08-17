@extends('layouts.home')
@section('content')
<div class="container col-xl-10 col-xxl-8 px-4 py-5">
    <div class="row align-items-center g-lg-5 py-5">
        <div class="col-lg-7 text-center text-lg-start">
            <h1 class="display-4 fw-bold lh-1 text-body-emphasis mb-3">@if(!$joined)Anmäl dig till {{ $eventName }}!@else Du är med i {{ $eventName }}!@endif</h1>
            <p class="col-lg-10 fs-4">Nästa QRTag börjar om <span id="countdown"></span>!</p>
        </div>
        <div class="col-md-10 mx-auto col-lg-5"> 
            @if(!$joined)
                <form action="/api/events/join" method="POST">
                    <button class="w-100 btn btn-lg btn-primary" type="submit">Gå med</button>
                </form>
            @else
                <form action="/api/events/leave" method="POST">
                    <button class="w-100 btn btn-lg btn-danger" type="submit">Lämna</button>
                </form>
            @endif
        </div>
    </div>
</div>
<script>
    var countDownDate = {{ $startDate * 1000 }};
    const updateCountdown = function() {
    
        var now = new Date().getTime();

        var distance = countDownDate - now;
    
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
        var stringyboi = days == 0 ? '' : days + "d ";
        stringyboi += days == 0 && hours == 0 ? '' : hours + "h ";
        stringyboi += days == 0 && hours == 0 && minutes == 0 ? '' : minutes + "m "
        document.getElementById("countdown").innerHTML = stringyboi + seconds + "s";
        if (distance < 1) {
            window.location = window.location;
        }
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>
@stop