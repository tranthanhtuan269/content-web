@extends('layouts.app')

@section('content')
    @if(session('status'))
    <div class="alert alert-success mb-1 mt-1">
        {{ session('status') }}
    </div>
    @endif
    <form action="{{ route('words.update',$word->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Word:</strong>
                    <input type="text" name="word" value="{{ $word->word }}" class="form-control"
                        placeholder="Word">
                    {{ Form::select('language', $languages, $word->language, ['class' => 'form-select my-3']) }}
                    @error('word')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary ml-3">Submit</button>
        </div>
    </form>
@endsection