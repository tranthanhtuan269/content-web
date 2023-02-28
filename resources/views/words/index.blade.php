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
            <div class="btn btn-primary float-end mb-3 mx-2 btn-open-create">Create</div>
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

    <div class="modal" tabindex="-1" id="create-word">
        <div class="modal-dialog">
            <div class="modal-content">
            <form action="{{ route('words.store') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Create a stopword</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        @csrf
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group my-2">
                                    <strong>Word:</strong>
                                    <input type="text" name="word" class="form-control" placeholder="Word">
                                    @error('word')
                                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group my-2">
                                    <strong>Language:</strong>
                                    {{ Form::select('language_id', $languages, isset($_GET['language']) ? $_GET['language'] : 1, ['class' => 'form-select mb-3', 'id' => 'change-language']) }}
                                    @error('language_id')
                                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                
            </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="update-word">
        <div class="modal-dialog">
            <div class="modal-content">
            <form action="#" id="id-update-form" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit a stopword</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group my-2">
                                <strong>Word:</strong>
                                <input type="text" name="word" id="word-update-form" value="" class="form-control" placeholder="Word">
                                @error('word')
                                <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group my-2">
                                <strong>Language:</strong>
                                {{ Form::select('language_id', $languages, null, ['class' => 'form-select mb-3', 'id' => 'language-update-form']) }}
                                @error('language_id')
                                <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(() => {

            $('#word-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('datatables.data', ['language'=> isset($_GET['language']) ? $_GET['language'] : 1]) !!}',
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
                            html += '<div class="btn btn-primary mx-2 edit-btn" data-id="'+row.id+'" data-word="'+row.word+'" data-language="'+row.language_id+'">Edit</div>'
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
                }
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

            $('.btn-open-create').click(function() {
                $('#create-word').modal('toggle')
            });

            function addEventListener(){
                $('.delete-btn').off('click');
                $('.delete-btn').click(function(){
                    var id = $(this).data('id');

                    Swal.fire({
                        @if(isset($_GET['language']))
                            @if($_GET['language'] == 2)
                                title: 'Bạn có chắc chắn?',
                                text: "Bạn có chắc chắn muốn xóa?",
                                confirmButtonText: 'Vâng, xóa nó!',
                                cancelButtonText: 'Không, dừng xóa!',
                            @else
                                title: 'Are you sure?',
                                text: "Are you sure to delete it?",
                                confirmButtonText: 'Yes, delete it!',
                                cancelButtonText: 'No, cancel!',
                            @endif
                        @else
                            title: 'Are you sure?',
                            text: "Are you sure to delete !",
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'No, cancel!',
                        @endif
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#form-' + id).submit();
                        }
                    })
                })

                
                $('.edit-btn').off('click');
                $('.edit-btn').click(function(){
                    id    = $(this).data('id');
                    word    = $(this).data('word');
                    language    = $(this).data('language');

                    $('#update-word').modal('show');

                    $('#id-update-form').attr('action', '/words/'+id);
                    $('#word-update-form').val(word);
                    $('#language-update-form').val(language);
                })
            }

            function getParam()
            {
                return window.location.href.slice(window.location.href.indexOf('?') + 1).split('=')[1];
            }

            $('#change-language').change(function(){
                window.location.href = "/words?language=" + $(this).val();
            })
        })
    </script>
@endsection
