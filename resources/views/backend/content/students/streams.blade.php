@extends('backend.layouts.app')
@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/content.content_list_students') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/content.content_list_students') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="card">
                <div class="card-header">
                    @if (session()->has('success'))
                        <div class="alert alert-success" style="text-align: center;">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex w-100 ">
                                <div>{{ $grade->grade . ' ' . $grade->description }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-tool">
                                <a href="{{ route('backend.addcontent.contentListStudents') }}" class="btn btn-warning float-right ml-2"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                <a href="{{ route('backend.addcontent.addContentStudents') }}" class="btn btn-primary float-right">{{ __('admin/content.list_content') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive">
                    <div class="row">
                        <div class="col-4 border-right border-1">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <ul class="sortable" id="studentStreamDetails">
                                @forelse ($streams as $stream)
                                    <li id="{{$stream->id}}" class="ui-state-default" style="list-style-type: none;"><span class="ui-icon ui-icon-arrowthick-2-n-s"><button @class(['nav-link justify-content-between btn btn-block text-left', 'active' => $loop->first]) id="v-pills-{{ $stream->id }}-tab"
                                        data-toggle="pill" data-target="#v-pills-{{ $stream->id }}" type="button"
                                        role="tab" aria-controls="v-pills-{{ $stream->id }}" aria-selected="true"
                                        data-id="{{ $stream->id }}" style="font-size: 1.5rem;">{{ $stream->title }}
                                        <!-- <span class="d-flex align-item-center">
                                            <span class="mr-2">
                                                <a href="javascript:void(0)"
                                                                    class="btn btn-info btnStremEdit px-3" data-id="{{$stream->id}}" data-value="{{ $stream->title }}">
                                                    <i class="fas fa-edit iconStream"></i>
                                                </a>
                                            </span>
                                            <span>
                                                <a role="button" onclick="deleteStream({{$stream->id}})"
                                                                class="btn btn-danger px-3">
                                                    <i class="fas fa-trash-alt iconStream"></i>
                                                </a>
                                            </span>
                                        </span> -->
                                    </button></span></li>
                                @empty
                                @endforelse
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="tab-content" id="v-pills-tabContent">
                                @forelse ($streams as $stream)
                                    <div @class(['tab-pane fade', 'show active' => $loop->first]) id="v-pills-{{ $stream->id }}" role="tabpanel"
                                        aria-labelledby="v-pills-{{ $stream->id }}-tab">
                                        @php
                                            $maincontents = $contents->where('stream_id', $stream->id)->all();
                                        @endphp
                                        <ul class="list-group sortable" id="studentContentDetails{{ $stream->id }}">
                                            @forelse ($maincontents as $content)
                                                <li id="{{$content->id}}" class="list-group-item ui-state-default @if(!$content->is_publish) draft-content @endif">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div class="w-100 h4">{{ $content->title }}</div>
                                                        <div class="btn-group" role="group" aria-label="Basic example">
                                                            {{-- @isset($content['worksheet']) --}}
                                                                <a class="btn btn-warning @if(!isset($content['worksheet'])) disabled @endif" href="{{ url('/files/content/' . $content['worksheet']) }}" target="_black">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                            {{-- @endisset --}}
                                                            <a href="{{ route('backend.addcontent.viewContentStudents', $content['id']) }}"
                                                                class="btn btn-primary">
                                                                <i class="fas fa-eye"></i></a>
                                                            <a href="{{ route('backend.addcontent.getEditContentStudents', $content['id']) }}"
                                                                class="btn btn-info">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button onclick="deleteContent(this)"
                                                                data-href="{{ route('backend.addcontent.deleteContentStudents', $content['id']) }}"
                                                                class="btn btn-danger">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                            @endforelse
                                        </ul>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade" id="editStream">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title">Edit</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <form>
                <div class="form-group">
                <label>Stream Name</label>
                <input type="hidden" name="txtstreamEditId" id="txtstreamEditId">
                <input type="text" class="form-control" name="txtstreamEdit" id="txtstreamEdit">
                </div>
            </form>
            </div>
            <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary btnStremSave">Save</button>
            </div>
        </div>
        <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <script>
        $(document).ready(function() {
            $( "#studentStreamDetails" ).sortable({
                cancel: '',
                start: function (event, ui) {
                    if ($('#studentStreamDetails').find('button.active')) {
                        $('#studentStreamDetails').find('button.active').removeClass('active');
                    }
                    $(ui.item).find('button').trigger('click');
                }, stop: function (event, ui) {
                    var sortable = JSON.stringify($("#studentStreamDetails").sortable("toArray"));
                    $.ajax({
                        type: "POST",
                        url: "{{ route('backend.updateStudentstream.updateOrderStudentstream') }}",
                        data: { studentstreamids : sortable},
                        
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // window.location.reload();
                        }
                    });
                }
            });
            $("#studentStreamDetails").click('li', function() {
                if ($(this).find('button.active')) {
                    $(this).find('button.active').removeClass('active');
                }
            });
            $(".sortable[id^=studentContentDetails]").sortable({
                cancel: '',
                stop: function (event, ui) {
                    var sortable = JSON.stringify($(this).sortable("toArray"));
                    $.ajax({
                        type: "POST",
                        url: "{{ route('backend.updateStudentcontent.updateOrderStudentcontent') }}",
                        data: { studentcontentids : sortable},
                        
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // window.location.reload();
                        }
                    });
                }
            });
        });
        $(".btnStremEdit").on("click", function(){
            $("#txtstreamEditId").val($(this).attr("data-id"));
            $("#txtstreamEdit").val($(this).attr("data-value"));
            $('#editStream').modal('show'); 
        });

        $(".btnStremSave").on("click", function(){
            if(!$("#txtstreamEdit").val().length) return;
            $.ajax({
                type: "POST",
                url: "{{ route('backend.editstream.editstream') }}",
                data: { id : $("#txtstreamEditId").val(), title : $("#txtstreamEdit").val()},
                
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.reload();
                },
            });
        });

        function deleteStream(id) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.deleteStream.deleteStream') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        stream: id
                    },
                    method: "POST",
                    success: function(data) {
                        window.location.reload();
                    }
                });
            }
        }

        function deleteContent(e) {
            var Id = $(e).data('href');
            swal({
                    title: "Are you sure?",
                    text: "You want to delete this file!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Your file has been deleted!", {
                            icon: "success",
                        });
                        window.location.href = Id;
                    } else {
                        swal("Great! School records are safe.");
                    }
                });
        }
    </script>
@endsection
