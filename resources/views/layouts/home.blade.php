@extends('layouts.base', ['header' => [
    ['!Hem', '/'],
    ['Poängtavla', '/scoreboard']
]])
@section('main')
<div class="d-flex flex-column col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center w-100">
        <h1 class="display-5 fw-bold text-body-emphasis">Om leken</h1>
        <div class="col-lg-6 mx-auto">
            <div>
                <p class="lead mb-4">
                    QRTag är en digital version av leken "Killer Game" och ett bra sätt att möta andra elever på skolan.
                </p>
                <p class="lead mb-4">
                    Leken går ut på att alla har har ett mål som man ska "tagga". Du taggar ditt mål genom att lägga handen på dens axel och säga "tag". Sedan ska du scanna personens QR-kod för att få poäng. När du taggar någon så ärver du den personens mål medans ditt mål är ute ur spelet.
                </p>
                <h3 class="display-5 fw-bold text-body-emphasis">Regler</h3>
                <ul class="text-start">
                    <li class="lead text-start">Du får inte använda våld</li>
                    <li class="lead text-start">Du får endast tagga någon som inte är på lunch eller lektion</li>
                    <li class="lead text-start">Du får använda vilken metod som helst för att hitta ditt mål</li>
                </ul>
                <h3 class="display-5 fw-bold text-body-emphasis">Priset</h3>
                <p class="lead mb-4">Personen som klarar sig längst och personen som taggar flest mål vinner. Priset består av heder och ett SSIS-starterpack som kommer att hjälpa dig genom terminen.</p>
            </div>
        </div>
    </div>

    <hr>
    
    @yield('content')
</div>
@stop