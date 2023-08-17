<div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary h-100 position-fixed w-sidebar" id="desktop-navbar">
    @if(isset($titleSuffix))
        <a href="/admin" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4">QRTag {{ $titleSuffix }}</span>
        </a>
    @else
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4">QRTag</span>
        </a>
    @endif
    
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        @foreach ($items as $item)
            @if($item[0][0] === '!')
                <li class="nav-item">
                    <a href="{{ $item[1] }}" class="nav-link active" aria-current="page">
                        {{ ltrim($item[0], $item[0][0]) }}
                    </a>
                </li>
            @else
                <li>
                    <a href="{{ $item[1] }}" class="nav-link link-body-emphasis">
                        {{ $item[0] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
    @if(!str_contains($_SERVER['REQUEST_URI'], '/admin'))
        @if(isset($_SESSION['qrtag']['is_admin']) && $_SESSION['qrtag']['is_admin'] === 1)
            <ul class="nav nav-pills">
                <li>
                    <a href="/admin" class="nav-link link-body-emphasis w-100">
                        Admin
                    </a>
                </li>
            </ul>
        @endif
    @else
        <ul class="nav nav-pills">
            <li>
                <a href="/" class="nav-link link-body-emphasis w-100">
                    Gå tillbaka
                </a>
            </li>
        </ul>
    @endif
    
    @if(isset($_SESSION['qrtag']))
        <hr>
        @include('includes.user')
    @endif
  </div>

<div class="w-100 d-flex flex-row justify-content-center bg-body-tertiary">
    <div class="container mx-0 p-0 w-100" id="mobile-navbar">
        <header class="d-flex justify-content-center py-3 w-100 px-2">
            <ul class="nav nav-pills flex-grow-1">
                @foreach ($items as $item)
                    @if($item[0][0] === '!')
                        <li class="nav-item"><a href="{{ $item[1] }}" class="nav-link active" aria-current="page">{{ ltrim($item[0], $item[0][0]) }}</a></li>
                    @else
                        <li class="nav-item"><a href="{{ $item[1] }}" class="nav-link">{{ $item[0] }}</a></li>
                    @endif
                @endforeach
            </ul>
            @if(!str_contains($_SERVER['REQUEST_URI'], '/admin'))
                @if(isset($_SESSION['qrtag']['is_admin']) && $_SESSION['qrtag']['is_admin'] === 1)
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a href="/admin" class="nav-link">Admin</a></li>
                    </ul>
                @endif
            @else
                <ul class="nav nav-pills">
                    <li class="nav-item"><a href="/" class="nav-link">Gå tillbaka</a></li>
                </ul>
            @endif
            <ul class="nav nav-pills">
                @if(isset($_SESSION['qrtag']))
                    @include('includes.user')
                @endif
            </ul>
        </header>
    </div>
</div>