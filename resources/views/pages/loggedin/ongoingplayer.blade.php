@extends('layouts.home')
@section('content')
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
#qrtag img {
    /* Your CSS styles for the <img> tag */
    marign-left: auto !important;
    marign-right: auto !important;
    /* Add more styles as needed */
}

.blurred
{
	color: transparent;
	text-shadow: 0 0 10px #FFF;
 -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome and Opera */
}
</style>
<div class="px-4 py-5 my-5 text-center w-100">
    @isset($error)
        <div class="alert alert-danger" role="alert">
            {{ $error }}
        </div>
    @endisset
    <h1 class="display-5 fw-bold text-body-emphasis">Ditt mål</h1>
    <div class="mx-auto">
        <p class="fs-4">Namn: {{ $target->display_name }}</p>
        <p class="fs-4">Klass: <span class="blurred">TE00XX</span></p>
    </div>
    
    <div class="d-flex flex-row justify-content-center" id="playfield">
        <div class="d-flex flex-column">
            <div class="mx-auto" style="width: max-content">
                <div id="qrcode" class="w-0"></div>
            </div>
            <div>
                <p class="fs-4">Din hemlis: {{ $user->secret }}</p>
            </div>
        </div>
        <div class="ms-5" id="playfield-container">
            <p class="fs-4">Om du blir kullad be kullaren att skanna qr koden eller eventuellt att de skriver in din hemlis nedan</p>
            <div>
                <form class="p-4 p-md-5 mx-auto" data-bitwarden-watching="1" action="/api/users/tag" method="POST">
                    @csrf 
    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="secret" name="secret" placeholder="20abcd" required>
                        <label for="secret">Hemlis</label>
                    </div>
                    <button class="w-100 btn btn-lg btn-primary" type="submit">Kulla</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
new QRCode(document.getElementById("qrcode"), "{{ $_ENV['APP_URL'] }}/api/users/tag?secret={{ $user->secret }}")

function checkAlive() {
    fetch('{{ $_ENV['APP_URL'] }}/api/users/{{ $user->user_id }}/alive', {
        credentials: "same-origin"
    })
    .then(resp => resp.json())
    .then(json => {
        if(json.alive != '1') location.reload()
    })
}

setInterval(checkAlive, 1000 * 10)


// Function to check window size and toggle content
function toggleContentBasedOnWindowSize() {
    if (window.innerWidth < 768) {
        document.getElementById('playfield').classList.remove('flex-row');
        document.getElementById('playfield').classList.add('flex-column');

        document.getElementById('playfield-container').classList.remove('ms-5');
    } else {
        document.getElementById('playfield').classList.remove('flex-column');
        document.getElementById('playfield').classList.add('flex-row');

        document.getElementById('playfield-container').classList.add('ms-5');
    }
}

// Initial check on page load
toggleContentBasedOnWindowSize();

// Event listener for window resize
window.addEventListener('resize', toggleContentBasedOnWindowSize);
</script>
@stop