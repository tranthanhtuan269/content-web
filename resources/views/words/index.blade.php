@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
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

    <table class="table table-bordered" id="word-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Word</th>
                <th>Language</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
    </table>

    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(() => {

            $('#word-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('datatables.data') !!}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'word', name: 'word' },
                    { data: 'language', name: 'language' },
                    { 
                        data: 'action', 
                        name: 'action', 
                        render: function(data, type, row){
                            var html = '';
                            html += '<form action="/words/'+row.id+'" id="form-'+row.id+'" method="Post">'
                            html += '<a class="btn btn-primary mx-2" href="/words/'+row.id+'/edit?language={{ isset($_GET['language']) ? $_GET['language'] : 1 }}">Edit</a>'
                            html += '@csrf';
                            html += '@method('DELETE')';
                                html += '<div class="btn btn-danger delete-btn" data-id="'+row.id+'">Delete</div>'
                            html += '</form>'
                            return html;
                        },
                        orderable: false
                    },
                ],
                fnDrawCallback: function( oSettings ) {
                    addEventListener();
                },
            });
            
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

            function addEventListener(){
                $('.delete-btn').off('click');
                $('.delete-btn').click(function(){
                    var id = $(this).data('id');

                    Swal.fire({
                        @if(isset($_GET['language']))
                            @if($_GET['language'] == 2)
                                title: 'Bạn có chắc chắn?',
                                text: "Bạn có chắc chắn muốn xóa?",
                            @else
                                title: 'Are you sure?',
                                text: "Are you sure to delete it?",
                            @endif
                        @else
                            title: 'Are you sure?',
                            text: "Are you sure to delete !",
                        @endif
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#form-' + id).submit();
                        }
                    })
                })
            }

            $('#change-language').change(function(){
                window.location.href = "/words?language=" + $(this).val();
            })
        })
    </script>
@endsection
