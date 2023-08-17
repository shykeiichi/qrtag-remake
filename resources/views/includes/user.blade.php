<div class="dropdown nav-item pt-2">
    <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      {{-- <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2"> --}}
      <strong>{{ $_SESSION['qrtag']['name'] }}</strong>
    </a>
    <ul class="dropdown-menu text-small shadow">
        {{-- <li><a class="dropdown-item" href="#">Profile</a></li> --}}
        {{-- <li><hr class="dropdown-divider"></li> --}}
        <form action="/api/auth/logout" method="POST">
          <li><button class="dropdown-item" href="">Logga ut</button></li>
        </form>
    </ul>
</div>