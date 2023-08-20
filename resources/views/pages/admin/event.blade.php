@extends('layouts.base', 
[
    'header' => [
        ['!Event', '/admin/'],
        ['Användare', '/admin/users']
    ],
    'titleSuffix' => 'Admin',
    'nofooter' => true
])
@section('main')
<style>
.hide {
	color: transparent !important;
	text-shadow: 0 0 10px #fff !important;
}
.hide:hover {
	color: white !important;
	text-shadow: 0 0 0px #fff !important;
}
</style>
<div class="d-flex flex-column col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center w-100 d-flex flex-column">
        <div class="d-flex flex-row justify-content-end">
            <button type="submit" class="btn btn-primary me-2" name="create" data-bs-toggle="modal" data-bs-target="#addUser">
                Lägg till spelare
            </button>
            <button type="submit" class="btn btn-primary me-2" name="patch" data-bs-toggle="modal" data-bs-target="#editEvent" onclick="setModalOptions({{ $event->id }}, '{{ $event->name }}', '{{ $event->start_date }}', '{{ $event->end_date }}')">
                Redigera Event
            </button>
            <form action="/api/events/{{ $eventId }}/targets" method="POST" class="me-2">
                <button type="submit" class="btn btn-warning" name="delete">
                    Tilldela mål
                </button>
            </form>
            <form action="/api/events/{{ $eventId }}/revive-all" method="POST">
                @method("PATCH")
                <button type="submit" class="btn btn-danger me-2" name="revive-all">
                    Återuppliva alla
                </button>
            </form>
            <form action="/api/events/{{ $eventId }}" method="POST">
                @method('DELETE')
                <button type="submit" class="btn btn-danger" name="delete">
                    Ta bort
                </button>
            </form>
        </div>
        <hr class="my-4">
        @isset($error)
            <div class="alert alert-danger" role="alert">
                {{ $error }}
            </div>
        @endisset
        @isset($success)
            <div class="alert alert-success" role="alert">
                {{ $success }}
            </div>
        @endisset
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Namn</th>
                    <th scope="col">Klass</th>
                    <th scope="col">Kullade</th>
                    <th scope="col">Hemlis</th>
                    <th scope="col">Mål</th>
                    <th scope="col">Vid liv</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $user)
                    <tr class="align-middle">
                        <th scope="row">{{ $user->id }}</th>
                        <td>{{ $user->display_name }}</td>
                        <td>{{ $user->class }}</td>
                        <td>{{ $user->tag_count }}</td>
                        <td class="hide">{{ $user->secret }}</td>
                        <td class="hide">{{ $user->target_display_name }}</td>
                        @if($user->is_alive)
                            <td>Ja</td>
                        @else
                            <td>
                                Nej
                            </td>
                        @endif
                        @if($user->is_alive)
                            <td>
                                <form action="/api/events/{{ $eventId }}/kill" method="POST">
                                    @method("PATCH")
                                    <input type="hidden" id="custId" name="user_id" value="{{ $user->user_id }}">
                                    <button class="btn btn-link">
                                        Kill
                                    </button>
                                </form>
                            </td>
                        @else
                            <td>
                                <form action="/api/events/{{ $eventId }}/revive" method="POST">
                                    @method("PATCH")
                                    <input type="hidden" id="custId" name="user_id" value="{{ $user->user_id }}">
                                    <button class="btn btn-link">
                                        Revive
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Redigera event --}}
<div class="modal fade" id="editEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="Sätts i javascripten" method="POST" id="editEventForm">
            @method("PATCH")
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createEventLabel">Redigera event</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Namn</label>
                        <input type="text" class="form-control" id="nameInput" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="startDateInput" class="form-label">Start datum</label>
                        <input type="datetime-local" class="form-control" id="startDateInput" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="endDateInput" class="form-label">Slut datum</label>
                        <input type="datetime-local" class="form-control" id="endDateInput" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stäng</button>
                    <button type="submit" class="btn btn-primary">Spara förändringar</button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- Lägg till användare --}}
<div class="modal fade" id="addUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/api/events/{{ $event->id }}/players" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createEventLabel">Lägg till användare</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fs-10">OBS: Att lägga till en användare i mitten av ett event kan skapa problem</p>

                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Användarnamn</label>
                        <input type="text" class="form-control" id="usernameInput" name="username" placeholder="12abcd" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stäng</button>
                    <button type="submit" class="btn btn-primary">Lägg till</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Funktion för att dynamiskt sätta värdena i uppdatera event pop uppen så att man kan ha den lite dynamisk
function setModalOptions(id, name, start_date, end_date) {
    document.getElementById('nameInput').value = name;
    document.getElementById('startDateInput').value = start_date;
    document.getElementById('endDateInput').value = end_date;
    document.getElementById('editEventForm').action = "/api/events/" + id;
}
</script>

@stop