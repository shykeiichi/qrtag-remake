@extends('layouts.base', 
[
    'header' => [
        ['Event', '/admin/'],
        ['!Användare', '/admin/users']
    ],
    'titleSuffix' => 'Admin',
    'nofooter' => true
])
@section('main')
<button class="btn btn-success position-fixed mt-2 ms-3" style="width: 150px" data-bs-toggle="modal" data-bs-target="#createUser">
    Skapa användare
</button>

{{-- Huvud sidan med bordet  --}}
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
                    <th scope="col">Användarnamn</th>
                    <th scope="col">För- Efternamn</th>
                    <th scope="col">Klass</th>
                    <th scope="col">Roll</th>
                    <th scope="col">Registrerad</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="align-middle overflow-auto">
                        <th scope="row">{{ $user->id }}</th>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->display_name }}</td>
                        <td>{{ $user->class }}</td>
                        @if($user->is_admin == false)
                            <td>Användare</td>
                        @else
                            <td>Administratör</td>
                        @endif
                        <td>{{ $user->created_at }}</td>
                        <td>
                            <a class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#createEvent" onclick="setModalOptions({{ $user->id }}, '{{ $user->username }}', '{{ $user->display_name }}', '{{ $user->class }}', '{{ $user->is_admin }}')">
                                Visa 
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Uppdatera användare pop uppen --}}
<div class="modal fade" id="createEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="Sätts i javascripten" method="POST" id="editUserForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createEventLabel">Redigera användare</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Användarnamn</label>
                        <input type="text" class="form-control" id="usernameInput" name="username" placeholder="12abcd" required>
                    </div>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">För -Efternamn</label>
                        <input type="text" class="form-control" id="displayNameInput" name="display_name" placeholder="Erik Svensson" required>
                    </div>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Klass</label>
                        <div class="dropdown">
                            <select class="btn btn-secondary dropdown-toggle" id="classInput" name="class">
                                <option>TE23D</option>
                                <option>TE23C</option>
                                <option>TE23B</option>
                                <option>TE23A</option>

                                <option>TE22D</option>
                                <option>TE22C</option>
                                <option>TE22B</option>
                                <option>TE22A</option>

                                <option>TE21D</option>
                                <option>TE21C</option>
                                <option>TE21B</option>
                                <option>TE21A</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isAdminInput" name="is_admin">
                        <label class="form-check-label" for="isAdminInput">Admin rättigheter</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stäng</button>
                    <button type="submit" class="btn btn-danger" name="delete">Ta bort</button>
                    <button type="submit" class="btn btn-primary" name="update">Spara förändringar</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Skapa användare pop uppen --}}
<div class="modal fade" id="createUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/api/users" method="POST" id="createUserForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createUserLabel">Skapa användare</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Användarnamn</label>
                        <input type="text" class="form-control" id="createUsernameInput" name="username" placeholder="12abcd" required>
                    </div>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">För -Efternamn</label>
                        <input type="text" class="form-control" id="createDisplayNameInput" name="display_name" placeholder="Erik Svensson" required>
                    </div>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Klass</label>
                        <div class="dropdown">
                            <select class="btn btn-secondary dropdown-toggle" id="classInput" name="class">
                                <option>TE23D</option>
                                <option>TE23C</option>
                                <option>TE23B</option>
                                <option>TE23A</option>

                                <option>TE22D</option>
                                <option>TE22C</option>
                                <option>TE22B</option>
                                <option>TE22A</option>

                                <option>TE21D</option>
                                <option>TE21C</option>
                                <option>TE21B</option>
                                <option>TE21A</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isAdminInput" name="is_admin">
                        <label class="form-check-label" for="isAdminInput">Admin rättigheter</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stäng</button>
                    <button type="submit" class="btn btn-primary" name="create">Skapa användare</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Funktion för att dynamiskt sätta värdena i uppdatera användare pop uppen så att man kan ha den lite dynamisk
function setModalOptions(id, username, display_name, school_class, is_admin) {
    document.getElementById('usernameInput').value = username;
    document.getElementById('displayNameInput').value = display_name;
    document.getElementById('classInput').value = school_class;
    document.getElementById('isAdminInput').checked = is_admin == 1;
    document.getElementById('editUserForm').action = "/api/users/" + id;
}
</script>
@stop