@extends('backend.layouts.app')
@section('content')
@inject('trainer', 'App\Models\Trainer')

    <div class="content-wrapper">
        <!-- Page Title  -->
        <!-- <div class="pageTitle">
            <h2>Assignment</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Assignment</li>
            </ol>
        </div> -->

        <!-- Main content -->
        <section class="pr-0 border-bottom mb-4 pb-3 pt-1">
            @if (session()->has('success'))
                <div class="alert alert-success" style="text-align: center;">
                    {{ session()->get('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger" style="text-align: center;">
                    {{ session()->get('error') }}
                </div>
            @endif
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="title"><h3>Assignment</h3></div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-xl-4 col-md-8 mb-3">
                    <select class="form-select w-100" aria-label="Default select example" name="primaryLevel" id="primaryLevel">
                        <!-- <option value="">-- Select --</option> -->
                        @if(isset($filteredLevels['primary']))
                            <optgroup label="Levels">
                            @foreach ($filteredLevels['primary'] as $k => $level)
                                <option value="{{ $level['id'] }}" @if((int) request('gradeId', $selectedGradeId) == $level['id']) selected @endif   @if(empty($level['has_access']) || !$level['has_access']) disabled @endif>{{ $level['grade'] }} </option>
                            @endforeach
                            </optgroup>
                        @endif
                        @if(isset($filteredLevels['add-ons']))
                            <optgroup label="Content Add-Ons">
                            @foreach ($filteredLevels['add-ons'] as $k => $level)
                                <option value="{{ $level['id'] }}" @if((int) request('gradeId', $selectedGradeId) == $level['id']) selected @endif  @if(empty($level['has_access']) || !$level['has_access']) disabled @endif>{{ $level['grade'] }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="col-xl-4 col-md-8 mb-3">
                    <select class="form-select w-100" id="assignmentCategory">
                         <option value="facilitated" {{ request('category', 'self_learning') === 'facilitated' ? 'selected' : '' }}>Facilitated</option>
                         <option value="self_learning" {{ request('category', 'self_learning') === 'self_learning' ? 'selected' : '' }}>Self Learning</option>
                    </select>
                </div>
                <div class="col-xl-4 col-md-8 mb-3">
                    <select class="form-select w-100" name="stream" id="stream">
                        <option value="">-- Select Session --</option>
                        @if($streams && $streams->count())
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}" @if((int) request('streamId') === $stream->id) selected @endif>{{ $stream->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="card">
                <div class="card-body"> 
                    <div class="col-12">
                        <div class="nav nav-tabs BeginnerTab scroll-tab pb-0" id="trainer" role="tablist"
                            aria-orientation="vertical">
                            <button @class(['nav-item nav-link btn text-left active'])
                                id="new-worksheets"
                                data-toggle="pill" data-target="#new-worksheets-tab" type="button"
                                role="tab" aria-controls="new-worksheets-tab"
                                aria-selected="true">New Worksheets</button>
                            <button @class(['nav-item nav-link btn text-left'])
                                id="submitted-worksheets"
                                data-toggle="pill" data-target="#submitted-worksheets-tab" type="button"
                                role="tab" aria-controls="submitted-worksheets-tab"
                                aria-selected="true">Submitted Worksheets</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="tab-content" id="trainer" role="tablist">
                            <div @class(['tab-pane fade', 'show active']) id="new-worksheets-tab"  role="tabpanel" aria-labelledby="new-worksheets-tab">
                                <div class="row for-loop-color" id="new-worksheets-data"> 
                                    @if($worksheets->count())
                                        @foreach ($worksheets as $worksheet)
                                        <div class="col-lg-4 col-md-6 bg-r">
                                            <div class="InnerContentCard add-ons-box border-radius-20 @if(!$worksheet->is_active) assignment-worksheet-lock @endif">
                                                <div class="ContentBody p-0">
                                                    <div class="IConHere text-center ml-auto mr-auto mb-3"> <img class="level_icon" @if(!empty($worksheet->icon_name)) src="{{asset('/image/assignment/'.$worksheet->icon_name)}}" @else src="{{asset('/asset/dist/img/contect/THINKpreneur.png')}}" @endif> </div>
                                                    <div class="content mb-auto">
                                                        <h2 class="card-title text-center w-100 d-block text-black">{{$worksheet->title}}</h2>
                                                    </div>
                                                    <div class="view-button d-flex align-items-center justify-content-center mt-2">
                                                        <a href="{{ route('student.assigment.show', array_merge(['student_communications' => $worksheet->id], request()->query())) }}" class="btn ThemeBtnContent btn-orange">View <i class="fa fa-arrow-right"></i> </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="col-lg-4 col-md-6 bg-r align-items-center">No worksheets found!</div>
                                    @endif
                                </div>
                            </div>
                            <div @class(['tab-pane fade']) id="submitted-worksheets-tab"  role="tabpanel" aria-labelledby="submitted-worksheets-tab">
                                <div class="row for-loop-color" id="submitted-worksheets-data"> 
                                    @if($pastWorksheets->count())
                                        @foreach ($pastWorksheets as $pastWorksheet)
                                        <div class="col-lg-4 col-md-6 bg-r">
                                            <div class="InnerContentCard add-ons-box border-radius-20 @if(!$pastWorksheet->assignment->is_active) assignment-worksheet-lock @endif">
                                                <div class="ContentBody p-0">
                                                    <div class="IConHere text-center ml-auto mr-auto mb-3"> <img class="level_icon" @if(!empty($pastWorksheet->assignment->icon_name)) src="{{asset('/image/assignment/'.$pastWorksheet->assignment->icon_name)}}" @else src="{{asset('/asset/dist/img/contect/THINKpreneur.png')}}" @endif> </div>
                                                    <div class="content mb-auto">
                                                        <h2 class="card-title text-center w-100 d-block text-black">{{$pastWorksheet->assignment->title}}</h2>
                                                    </div>
                                                    <div class="view-button d-flex align-items-center justify-content-center mt-2">
                                                        <a href="{{ route('student.assigment.show', array_merge(['student_communications' => $pastWorksheet->assignment_id], request()->query())) }}" class="btn ThemeBtnContent btn-orange">View <i class="fa fa-arrow-right"></i> </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="col-lg-4 col-md-6 bg-r align-items-center">No worksheets found!</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    @if (session()->has('confetti_visible'))
    <script>
        var confetti_sound_path = "{{asset('asset/dist/audio/great-job-speech.mp3')}}";
    </script>
    <script src="{{asset('asset/dist/js/confetti.js')}}"></script>
    <script>
        // Call Confetti Animation 
        poof();
    </script>
    @endif

    <script>
        function updateWorksheets() {
            var gradeId = parseInt($('#primaryLevel').val());
            var category = $('#assignmentCategory').val();
            var streamId = $('#stream').val() ? parseInt($('#stream').val()) : null;
            var params = new URLSearchParams();
            
            if(!isNaN(gradeId)) {
                params.set('gradeId', gradeId);
            }
            if(category) {
                params.set('category', category);
            }
            if(streamId) {
                params.set('streamId', streamId);
            }

            var queryString = params.toString();
            var currentQuery = queryString ? `?${queryString}` : '';
            history.replaceState(null, '', `${window.location.pathname}${currentQuery}`);

            if(!isNaN(gradeId)) {
                $("#primaryLevel").attr('disabled','disabled');
                $("#assignmentCategory").attr('disabled','disabled');
                $("#stream").attr('disabled','disabled');
                
                $.ajax({
                    url: "{{ route('student.getStudentWorksheets') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        gradeId: gradeId,
                        category: category,
                        streamId: streamId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res.success) {
                            // Update streams dropdown
                            if(!$.isEmptyObject(res.success.streams)) {
                                var streamOptions = '<option value="">-- Select Session --</option>';
                                $.each(res.success.streams, function (key, val) {
                                    var selected = (streamId && streamId == val.id) ? 'selected' : '';
                                    streamOptions += `<option value="${val.id}" ${selected}>${val.title}</option>`;
                                });
                                $('#stream').html(streamOptions);
                            } else {
                                $('#stream').html('<option value="">-- No Session Available --</option>');
                            }

                            // Update new worksheets
                            if(!$.isEmptyObject(res.success.worksheets)) {
                                var worksheets = '';
                                $.each(res.success.worksheets, function (key, val) {
                                    var assignment_title = val.title;
                                    var assignment_id = val.id;
                                    var assigment_link = "{{ route('student.assigment.show', 'assignment_id') }}";
                                    assigment_link = assigment_link.replace('assignment_id', assignment_id) + currentQuery;
                                    var assignment_icon = "{{ asset('/asset/dist/img/contect/THINKpreneur.png') }}";
                                    if(!$.isEmptyObject(val.icon_name)) {
                                        assignment_icon = "{{ asset('/image/assignment/icon_name') }}";
                                        assignment_icon = assignment_icon.replace('icon_name', val.icon_name);
                                    }
                                    var lock_class = '';
                                    if(!val.is_active) 
                                        lock_class = 'assignment-worksheet-lock';
                                    worksheets += `<div class="col-lg-4 col-md-6 bg-r">
                                                    <div class="InnerContentCard add-ons-box border-radius-20 ${lock_class}">
                                                        <div class="ContentBody p-0">
                                                            <div class="IConHere text-center ml-auto mr-auto mb-3"> 
                                                                <img class="level_icon" src="${assignment_icon}"> 
                                                            </div>
                                                            <div class="content mb-auto">
                                                                <h2 class="card-title text-center w-100 d-block text-black">${assignment_title}</h2>
                                                            </div>
                                                            <div class="view-button d-flex align-items-center justify-content-center mt-2">
                                                                <a href="${assigment_link}" class="btn ThemeBtnContent btn-orange">View <i class="fa fa-arrow-right"></i> </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>`;
                                });
                                $('#new-worksheets-data').html(worksheets);
                            } else {
                                $('#new-worksheets-data').html(`<div class="col-lg-4 col-md-6 bg-r align-items-center">No worksheets found!</div>`);
                            } 
                            if(!$.isEmptyObject(res.success.pastWorksheets)) {
                                var past_worksheets = '';
                                $.each(res.success.pastWorksheets, function (key, val) {
                                    var assignment_title = val.assignment.title;
                                    var assignment_id = val.assignment_id;
                                    var assigment_link = "{{ route('student.assigment.show', 'assignment_id') }}";
                                    assigment_link = assigment_link.replace('assignment_id', assignment_id) + currentQuery;
                                    var assignment_icon = "{{ asset('/asset/dist/img/contect/THINKpreneur.png') }}";
                                    if(!$.isEmptyObject(val.assignment.icon_name)) {
                                        assignment_icon = "{{ asset('/image/assignment/icon_name') }}";
                                        assignment_icon = assignment_icon.replace('icon_name', val.assignment.icon_name);
                                    }
                                    var lock_class = '';
                                    if(!val.assignment.is_active) 
                                        lock_class = 'assignment-worksheet-lock';
                                    past_worksheets += `<div class="col-lg-4 col-md-6 bg-r">
                                                            <div class="InnerContentCard add-ons-box border-radius-20 ${lock_class}">
                                                                <div class="ContentBody p-0">
                                                                    <div class="IConHere text-center ml-auto mr-auto mb-3"> 
                                                                        <img class="level_icon" src="${assignment_icon}"> 
                                                                    </div>
                                                                    <div class="content mb-auto">
                                                                        <h2 class="card-title text-center w-100 d-block text-black">${assignment_title}</h2>
                                                                    </div>
                                                                    <div class="view-button d-flex align-items-center justify-content-center mt-2">
                                                                        <a href="${assigment_link}" class="btn ThemeBtnContent btn-orange">View <i class="fa fa-arrow-right"></i> </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>`;
                                });
                                $('#submitted-worksheets-data').html(past_worksheets);
                            } else {
                                $('#submitted-worksheets-data').html(`<div class="col-lg-4 col-md-6 bg-r align-items-center">No worksheets found!</div>`);
                            }      
                            $("#primaryLevel").removeAttr('disabled');
                            $("#assignmentCategory").removeAttr('disabled');
                            $("#stream").removeAttr('disabled');
                        } 
                    }
                });
            } else {
                $("#primaryLevel").removeAttr('disabled');
                $("#assignmentCategory").removeAttr('disabled');
                $("#stream").removeAttr('disabled');
            }
        }

        $("#primaryLevel").change(function () {
            updateWorksheets();
        });
        $('#assignmentCategory').change(function() {
            updateWorksheets();
        });

        $('#stream').change(function() {
            updateWorksheets();
        });

        if (window.location.search.length > 0) {
            const params = new URLSearchParams(window.location.search);
            if (params.has('gradeId')) {
                $('#primaryLevel').val(params.get('gradeId'));
            }
            if (params.has('category')) {
                $('#assignmentCategory').val(params.get('category'));
            }
            if (params.has('streamId')) {
                $('#stream').val(params.get('streamId'));
            }
            updateWorksheets();
        }
    </script>
@endsection
