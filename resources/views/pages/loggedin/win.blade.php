@extends('layouts.base', ['header' => [
    ['!Hem', '/'],
    ['Poängtavla', '/scoreboard']
]])
@section('main')
<div class="d-flex flex-column col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center w-100">
        <h1 class="display-5 fw-bold text-body-emphasis">{{ $winner }} har vunnit {{ $eventName }}!</h1>
        <div class="col-lg-6 mx-auto">
            <div>
                <p class="lead mb-4">
                    
                </p>
            </div>
        </div>
    </div>
</div>
@stop