@extends('layouts.base', 
[
    'header' => [
        ['!Event', '/admin'],
        ['Användare', '/admin/users']
    ],
    'titleSuffix' => 'Admin',
    'nofooter' => true
])
@section('main')
<button class="btn btn-success position-fixed mt-2 ms-3" style="width: 150px" data-bs-toggle="modal" data-bs-target="#createEvent">
    Skapa event
</button>
<div class="d-flex flex-column col-md-9 col-sm-12 mx-auto">
    <div class="px-4 py-5 my-5 text-center w-100">
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
                    <th scope="col">Deltagare</th>
                    <th scope="col">Start datum</th>
                    <th scope="col">Slut Datum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="align-middle">
                        <th scope="row">{{ $event->id }}</th>
                        <td>{{ $event->name }}</td>
                        <td>{{ $event->user_count }}</td>
                        <td>{{ $event->start_date }}</td>
                        <td>{{ $event->end_date }}</td>
                        <td>
                            <a class="btn btn-primary" href="/admin/events/{{ $event->id }}">
                                Visa 
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="createEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/api/events" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createEventLabel">Skapa event</h1>
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
                    <button type="submit" class="btn btn-primary">Skapa</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop