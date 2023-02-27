@extends('layouts.app')

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            {{ Form::select('language', $languages, null, ['class' => 'form-select my-3']) }}
        </div>
        <div class="col-12">
            <div class="btn btn-primary float-end mb-3">Import</div>
            <input type="file" id="myfile" name="myfile">
            <a href="/words/create" class="btn btn-primary float-end mb-3 mx-2">Create</a>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Word</th>
                <th>Language</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($words as $word)
                <tr>
                    <td>{{ $word->id }}</td>
                    <td>{{ $word->word }}</td>
                    <td>{{ $word->language }}</td>
                    <td>
                        <form action="{{ route('words.destroy',$word->id) }}" method="Post">
                            <a class="btn btn-primary" href="{{ route('words.edit',$word->id) }}">Edit</a>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
        </tbody>
    </table>
    {!! $words->links() !!}

    <script>
        $(document).ready(() => {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#myfile").on('change',(function(e) {
                e.preventDefault();
                var file_data = $('#myfile').prop('files')[0];   
                var form_data = new FormData();                  
                form_data.append('file', file_data);
                $.ajax({
                    url: "/upload",
                    type: "POST",
                    data:  form_data,
                    contentType: false,
                    cache: false,
                    processData:false,
                    beforeSend : function()
                    {
                    },
                    success: function(data)
                    {
                    },
                    error: function(e) 
                    {
                    }          
                });
            }));
        })
    </script>
@endsection