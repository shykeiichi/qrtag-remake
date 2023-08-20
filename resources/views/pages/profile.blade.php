@extends('layouts.base', ['header' => [
    ['Hem', '/'],
    ['Poängtavla', '/scoreboard']
]])
@section('main')
<div class="row col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center col-md-6 mx-auto">
        Du har kullat {{ $tags }} personer totalt. 
    </div>
</div>
@stop