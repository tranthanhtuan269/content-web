@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12 px-md-4">
            {{ Form::select('language', $languages, isset($_GET['language']) ? $_GET['language'] : 1, ['class' => 'form-select my-3', 'id' => 'change-language']) }}
        </div>
        <div class="col-md-9 ms-sm-auto col-lg-6 px-md-4">
            <label for="exampleFormControlTextarea1" class="form-label">Input</label>
            <textarea class="form-control" id="input-txt" rows="24"></textarea>
        </div>
        <div class="col-md-9 ms-sm-auto col-lg-6 px-md-4">
            <label for="exampleFormControlTextarea1" class="form-label">Output</label>
            <textarea class="form-control" id="output-txt" rows="24" disabled></textarea>
        </div>

        <div class="col-md-12 ms-sm-auto col-lg-12 px-md-4 mt-2">
            <div class="btn btn-primary" id="remove-btn">Remove stopword</div>
        </div>

        <div class="text-reject d-none">
            @foreach($words as $word)
            <p>{{ $word->word }}</p>
            @endforeach
        </div>
    </div>

    <script>
        $(document).ready(() => {
            $('#change-language').change(function(){
                window.location.href = "/dashboard?language=" + $(this).val();
            })
        })
    </script>
@endsection