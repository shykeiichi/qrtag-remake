@extends('layouts.base', ['header' => [
    ['Hem', '/'],
    ['!Poängtavla', '/scoreboard']
]])
@section('main')
<div class="row col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center col-md-6 mx-auto">
        @if($ongoing)
            <h1 class="display-5 fw-bold text-body-emphasis">Poängtavlan för {{ $eventName }}</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Namn</th>
                        <th scope="col">Kullat</th>
                        <th scope="col">Vid liv</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                        <tr class="align-middle">
                            <td>{{ $player->display_name }}</td>
                            <td>{{ $player->tag_count }}</td>
                            <td>
                                @if($player->is_alive)
                                    Ja
                                @else
                                    Nej 
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <h1 class="display-5 fw-bold text-body-emphasis mt-5">Senaste kullade</h1>
            <table class="table">
                <thead>
                    <tr class="align-middle">
                        @if(count($tags) > 0)
                            <th>
                                {{ $tags[0]->user }} kullade {{ $tags[0]->target }} {{ $tags[0]->timestamp }}
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($tags as $tag)
                        @if ($loop->first) @continue @endif
                        <tr class="align-middle">
                            <td>
                                {{ $tag->user }} kullade {{ $tag->target }} {{ $tag->timestamp }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <h1 class="display-5 fw-bold text-body-emphasis">Inget qrtag pågår för tillfället</h1>
        @endif
    </div>
</div>
@stop