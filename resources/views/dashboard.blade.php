@extends('layouts.app')

@section('content')
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">
                    <span data-feather="home"></span>
                    Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/words">
                    <span data-feather="file"></span>
                    Words
                    </a>
                </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h2>Dashboard</h2>
            <div class="row">
                <div class="col-md-9 ms-sm-auto col-lg-6 px-md-4">
                    <label for="exampleFormControlTextarea1" class="form-label">Input</label>
                    <textarea class="form-control" id="input-txt" rows="24"></textarea>
                </div>
                <div class="col-md-9 ms-sm-auto col-lg-6 px-md-4">
                    <label for="exampleFormControlTextarea1" class="form-label">Output</label>
                    <textarea class="form-control" id="output-txt" rows="24" disabled></textarea>
                </div>

                <div class="col-md-12 ms-sm-auto col-lg-12 px-md-4">
                    <div class="btn btn-primary" id="remove-btn">Remove stopword</div>
                </div>

                <div class="text-reject d-none">
                    @foreach($words as $word)
                    <p>{{ $word->word }}</p>
                    @endforeach
                </div>
            </div>
        </main>
    </div>
@endsection