@extends('layouts.app')

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            {{ Form::select('language', $languages, isset($_GET['language']) ? $_GET['language'] : 1, ['class' => 'form-select my-3', 'id' => 'change-language']) }}
        </div>
        <div class="col-12">
            <div class="btn btn-primary float-end mb-3" id="import-btn">Import</div>
            <input type="file" id="myfile" name="myfile" class="d-none">
            <a href="/words/create?language={{ isset($_GET['language']) ? $_GET['language'] : 1 }}" class="btn btn-primary float-end mb-3 mx-2">Create</a>
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
            @php
                if(isset($_GET['page'])) {
                    $page = (int) $_GET['page'];
                }else {
                    $page = 1;
                }
            @endphp
            @foreach ($words as $key => $word)
                <tr>
                    <td>{{($page - 1) * 20 + $key + 1 }}</td>
                    <td>{{ $word->word }}</td>
                    <td>{{ $word->language->name }}</td>
                    <td>
                        <form action="{{ route('words.destroy',$word->id) }}" method="Post">
                            <a class="btn btn-primary" href="/words/{{ $word->id }}/edit?language={{ isset($_GET['language']) ? $_GET['language'] : 1 }}">Edit</a>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
        </tbody>
    </table>
    {!! $words->appends(request()->input()) !!}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(() => {
            $('#import-btn').click(() => {
                $("#myfile").click();
            })

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
                        Swal.fire({
                            title: 'Success!',
                            text: 'Words have been created!',
                            icon: 'success',
                            confirmButtonText: 'Great'
                        }).then(function(isConfirm) {
                            if (isConfirm) {
                                location.reload();
                            }
                        })
                    },
                    error: function(e)
                    {
                    }
                });
            }));

            $('#change-language').change(function(){
                window.location.href = "/words?language=" + $(this).val();
            })
        })
    </script>
@endsection
