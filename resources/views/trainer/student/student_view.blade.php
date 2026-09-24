@extends('backend.layouts.app')

@section('content')
@inject('grade', 'App\Models\Grade')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="pageTitle">
        <h2>Students</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Students</li>
        </ol>
    </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid  p-0">
                @if (session()->has('message'))
                    <div class="alert alert-success" style="text-align: center;">
                        {{ session()->get('message') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <div class="card h-100">
                    <div class="card-header">                        
                        <div class="form-froup form-inline">
                            <label>Select Level</label>
                            <select class="form-control ml-5" name="levelFilter" id="levelFilter">
                                <option value="">---Select Level----</option>
                                @if(isset($filteredLevels['primary']))
                                    <optgroup label="Levels">
                                    @foreach ($filteredLevels['primary'] as $k => $level)
                                        <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                    @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($filteredLevels['add-ons']))
                                    <optgroup label="Content Add-Ons">
                                    @foreach ($filteredLevels['add-ons'] as $k => $level)
                                        <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                    @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <a href="{{ route('trainer.student_list') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card manage-students-div">
                            <div class="card-header">
                                <h3 class="card-title tot-manage-students">You are managing {{ sizeof($students) }} students</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body" style="max-height:820px;overflow-y: scroll;">
                                <table class="table table-striped table-valign-middle">
                                    <tbody class="students-list-table">
                                        @php $stdname = ''; @endphp
                                        @foreach ($students as $student)
                                            <tr class="remove_bg">
                                                <td class="d-flex justify-content-start align-items-start">
                                                    @if($loop->index == 0)
                                                        <input type="hidden" class="firststudent" value="{{$student['id']}}">
                                                        @php $stdname = $student['name'] @endphp
                                                    @endif
                                                    @if ($student['image'] != '' && $student['image'] != 'no_image')
                                                        @php
                                                            $stud_profie_pic = asset($student['image']);
                                                            if ($student['school']['tenant_id'] && !str_contains($student['image'], 'tenants/')) {
                                                                $stud_profie_pic = asset('tenants/'.$student['image']);
                                                            }
                                                        @endphp
                                                        <img class="ml-0 mr-2 profile-user-img img-fluid img-circle"
                                                            style="height: 64px;width: 64px;object-fit: cover;"
                                                            src="{{ $stud_profie_pic }}" />
                                                    @else
                                                        <img src="{{ asset('img/default_image.png') }}"
                                                            class="mr-2 img-circle img-size-64" />
                                                    @endif
                                                    <span>
                                                        <a href="javascript:;" class="student_feedback" data-id="{{ $student['id'] }}">
                                                            {{ $student['name'] }} <br>
                                                        </a>
                                                        {{-- <span class="info-box-text">Level</span> --}}
                                                        @php
                                                            $grade_id = explode(',',$student['grade_id']);
                                                            $grades = $grade->whereIn('id',$grade_id)->get();
                                                            $grade_name = [];
                                                        @endphp
                                                        @foreach($grades as $row)
                                                            {{-- <span class="info-box-number">{{ $student['grade_id'] }}</span> --}}
                                                            @php
                                                                $grade_name[] = '<span class="info-box-number">'.$row->grade.'</span>';
                                                            @endphp
                                                        @endforeach
                                                        @php
                                                            $grade_name = implode(',',$grade_name);
                                                        @endphp
                                                        {!! $grade_name !!}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>

                    <div class="col-md-8 mb-3 student-detail-div">
                        <div class="card InnerForm">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Student : <span class="studentsname">{{$one_student['name']}}</span></h3>
                            </div>
                        </div>
                        <div class="body_add"></div>
                    </div>

                </div>

                <div class="row no-studens-div">
                    <div class="col-md-12">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">No Students Found!</h3>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
    <?php
    $filteredLevels['addons'] = $filteredLevels['add-ons'];
    unset($filteredLevels['add-ons']);
    ?>
    <script>

        var studentid = <?php echo $one_student['id']; ?>;
        var allGrades = [];
        var skill_list = [];
        var mindset_list = [];

        $(document).ready(function() {

            var current_year = <?php echo date('Y'); ?>;
            var privous_year = current_year - 1;
            var privous_2year = privous_year - 1;
            allGrades = <?php echo json_encode($filteredLevels); ?>;
            skill_list = <?php echo json_encode($skills); ?>;
            mindset_list = <?php echo json_encode($mindset); ?>;

            $('.no-studens-div').hide();
            $('.student-detail-div').show();

            // studentFeedback(studentid); // commented out — not required for now

        });

        $(document).on("click",".student_feedback", function () {
            var studentId = $(this).attr('data-id');
            studentid = studentId;
            var stdname = $(this).text().trim();
            $('.studentsname').html(stdname);
            $('.student-detail-div').show();
            // studentFeedback(studentId); // commented out — not required for now
            $('html, body').animate({
                scrollTop: $(".studentsname").offset().top
            }, 500);
        });

        /* feedback submit — commented out, not required for now
        $(document).on("click", ".feedback_submit", function() {
            var custom_data = $('#feedback_submit').serialize();
            $('#todo_success').css('display', 'none');
            $("#feedback_submit .text-danger").hide();
            $.ajax({
                type: "POST",
                url: "{{ route('trainer.student_feedback_submit') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: false,
                data: custom_data,
                dataType: 'json',
                success: function(data) {
                    if (data.feedback_check) {
                        alert('Data Already exist');
                    }
                    if (data.success) {
                        $('#todo_success').addClass('alert alert-success');
                        $('#todo_success').css('display', 'block');
                        $('#todo_success').html(data.success);
                    } else  {
                        $.each(data.errors, function (key, val) {
                            $("#" + key + "_error").text(val[0]).show();
                        });
                    }
                    $('html, body').animate({
                        scrollTop: $(".studentsname").offset().top
                    }, 1000);
                }
            });
        });
        */

        $('#levelFilter').on("change", function() { 
            var gradeId = $(this).val();
            $(this).prop("disabled", true);
            $.ajax({
                type: "POST",
                url: "{{ route('trainer.student_by_level') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    gradeId: gradeId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('.manage-students-div').hide();
                    $('.student-detail-div').hide();
                    $('.no-studens-div').hide();
                },
                success: function(data) {
                    $("#levelFilter").prop("disabled", false);
                    if(data.success) {
                        var students = data.success.students;
                        var firstStudent = data.success.one_student;
                        if(!$.isEmptyObject(students)) {
                            $('.tot-manage-students').text(`You are managing ${students.length} students`);
                            var studList;
                            var stdname = ''; 
                            $.each(students, function( key, value ) {
                                var className = '';
                                if(firstStudent['id'] == value['id']) {
                                    // className = 'bg-secondary';
                                }
                                studList += `
                                        <tr class="remove_bg ${className}">
                                            <td class="d-flex justify-content-start align-items-start">
                                    `;
                                if(key == 0) {
                                    studList += `<input type="hidden" class="firststudent" value="${value['id']}"> `;
                                    stdname = value['name']; 
                                } 
                                var studPic = `<img src="{{ asset('img/default_image.png') }}" class="mr-2 img-circle img-size-64" />`;             
                                if(!$.isEmptyObject(value['image'])  && value['image'] != 'no_image') {
                                    studPic = `<img class="ml-0 mr-2 profile-user-img img-fluid img-circle" style="height: 64px;width: 64px;object-fit: cover;" src="{{ url('') }}"${value['image']} />`;
                                }
                                studList += studPic;
                                studList += `
                                    <span>
                                        <a href="javascript:;" class="student_feedback" data-id="${value['id']}">
                                        ${value['name']}  <br>
                                        </a> 
                                `;
                                var studentGrade = value['grade_id'];
                                var gradeName = '';
                                if(!$.isEmptyObject(studentGrade)){
                                    var gradeArr = studentGrade.split(",");
                                    if(!$.isEmptyObject(allGrades.primary)) {      
                                        $.each(allGrades.primary, function( index, value ) {  
                                            if($.inArray(`${value['id']}`, gradeArr) !== -1) {
                                                gradeName += `<span class="info-box-number">${value['grade']}</span>,`;
                                            }    
                                        });
                                    }
                                    if(!$.isEmptyObject(allGrades.addons)) {
                                        $.each(allGrades.addons, function( index, value ) {  
                                            if($.inArray(`${value['id']}`, gradeArr) !== -1) {
                                                gradeName += `<span class="info-box-number">${value['grade']}</span>,`;
                                            }    
                                        });
                                    }
                                    gradeName = gradeName.replace(/,\s*$/, "");
                                }
                                studList += `${gradeName}`;
                                studList += `</span></td></tr>`;
                            });
                            $('.students-list-table').html(studList);
                            $('.manage-students-div').show();
                            studentid = firstStudent['id'];
                            $('.studentsname').html(firstStudent['name']);
                            $('.student-detail-div').show();
                            // studentFeedback(firstStudent['id'], gradeId); // commented out — not required for now
                        } else {
                            $('.no-studens-div').show();
                        }
                    } else {
                        $('.no-studens-div').show();
                    }
                }
            });
        });
        
        function studentFeedback(studentId, levelId) {
            // $('.remove_bg').removeClass('bg-secondary');
            // $(this).parent().parent().parent().addClass('bg-secondary');
            return;
            // var studentId = $(this).attr('data-id');
            $.ajax({
                type: "POST",
                url: "{{ route('trainer.student_feedback') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    studentId: studentId,
                    levelId: levelId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('.student-detail-div').hide();
                    $('.certi-box').hide();
                },
                success: function(data) {
                    var observation = '';
                    if(!$.isEmptyObject(data.observationData)) {
                        var observationCnt = '';
                        $.each(data.observationData, function( index, observationsData ) {   
                            observationCnt = index;
                            var level = '';
                            if(!$.isEmptyObject(allGrades)) {
                                if(!$.isEmptyObject(allGrades.primary)) {
                                    level += `<optgroup label="Levels">`;
                                    $.each(allGrades.primary, function( index, value ) { 
                                        var selected = '';
                                        if(value.id == observationsData.grade_id) {
                                            selected = 'selected';
                                        }   
                                        level += `<option value="${value.id}" ${selected}>${value.grade}</option>`;
                                    });
                                    level += `</optgroup">`;
                                }
                                if(!$.isEmptyObject(allGrades.addons)) {
                                    level += `<optgroup label="Content Add-Ons">`;
                                    $.each(allGrades.addons, function( index, value ) {  
                                        var selected = '';
                                        if(value.id == observationsData.grade_id) {
                                            selected = 'selected';
                                        }    
                                        level += `<option value="${value.id}" ${selected}>${value.grade}</option>`;
                                    });
                                    level += `</optgroup">`;
                                }
                            }

                            var stream = '';
                            if(!$.isEmptyObject(observationsData.stream_list)) {
                                $.each(observationsData.stream_list, function (index, value) {
                                    var selected = '';
                                    if(value.id == observationsData.stream_id) {
                                        selected = 'selected';
                                    }   
                                    stream += `<option value="${value.id}" ${selected}>${value.title}</option>`;
                                });                                
                            }

                            var session = '';
                            if(!$.isEmptyObject(observationsData.session_list)) {
                                $.each(observationsData.session_list, function (index, value) {
                                    var selected = '';
                                    if(value.id == observationsData.session_id) {
                                        selected = 'selected';
                                    }   
                                    session += `<option value="${value.id}" ${selected}>${value.title}</option>`;
                                });                                
                            }

                            var skill_list_options = '';
                            if(!$.isEmptyObject(skill_list)) {
                                $.each(skill_list, function( index, value ) {
                                    skill_list_options += `<option value="${value.id}">${value.skill_name}</option>`;
                                });
                            }

                            var skills = '';
                            var add_skill = true;
                            if(!$.isEmptyObject(skill_list) && !$.isEmptyObject(observationsData.skill_id)) {
                                var skill_id = observationsData.skill_id.split(",");
                                if(skill_id.length > 0) {
                                    $.each(skill_id, function( ind, sid ) {    
                                        var s_class = '';
                                        if(ind !=0 ) {
                                            s_class = `mt-2`
                                        }
                                        skills += `<select class="form-control skill-select${observationCnt} ${s_class}" name="skills[${observationCnt}][]" id="skills[${observationCnt}][]">`;
                                        skills += `<option value="">---Select Skill----</option>`;
                                        $.each(skill_list, function( index, value ) {
                                            var selected = '';
                                            if(value.id == sid) {
                                                selected = 'selected';
                                            }    
                                            skills += `<option value="${value.id}" ${selected}>${value.skill_name}</option>`;
                                        });
                                        skills += `</select>`;
                                    });
                                    if(skill_id.length == 3) {
                                        add_skill = false;
                                    }
                                } else {
                                    skills += `<select class="form-control skill-select${observationCnt}" name="skills[${observationCnt}][]" id="skills[${observationCnt}][]">
                                                <option value="">---Select Skill----</option>
                                                ${skill_list_options}
                                            </select>`;
                                }
                            } else {
                                if($.isEmptyObject(skill_list)) {
                                    add_skill = false;
                                }
                                skills += `<select class="form-control skill-select${observationCnt}" name="skills[${observationCnt}][]" id="skills[${observationCnt}][]">
                                                <option value="">---Select Skill----</option>
                                                ${skill_list_options}
                                            </select>`;
                            }
                            if(add_skill) {
                                skills += `<a href="javascript:void(0)" class="float-right" id="addMoreSkill${observationCnt}" onclick="add_skill(${observationCnt});">+ Add</a>`;
                            }

                            var mindset = '';
                            if(!$.isEmptyObject(mindset_list)) {
                                var tot_mindset = 0;
                                mindset += `<label>Assess mindset using a slider scale (Min - 1, Max - 10)</label> `;
                                $.each(mindset_list, function( index, value ) {    
                                    tot_mindset++;
                                    if(index%2 == 0) {
                                        mindset += `<div class="row">`;    
                                    }
                                    var mVal = 1;
                                    if(!$.isEmptyObject(observationsData.stud_mindset_data) && index in observationsData.stud_mindset_data) {
                                            mVal = observationsData.stud_mindset_data[index].value;
                                    }
                                    mindset += `<div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>${value.mindset_name}</label> 
                                                        <div class="d-flex flex-column align-items-center">
                                                            <output>${mVal}</output>
                                                            <input type="range" min="1" max="10" class="form-control my-0" name="mindset${observationCnt}[]" value="${mVal}" oninput="this.previousElementSibling.value = this.value">
                                                        </div>
                                                    </div>
                                                </div>                                
                                            `;
                                    if(index%2 == 1) {
                                        mindset += `</div>`;    
                                    }
                                });
                                if(tot_mindset%2 == 1) {
                                    mindset += `</div>`;    
                                }
                            }

                            var ob_session_image = '';
                            if(!$.isEmptyObject(observationsData.session_image)) {
                                ob_session_image = `<div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                    <span>${observationsData.session_image}</span> 
                                    <div class="action-btn">
                                        <a href="{{ url('') }}/image/student/observation/${observationsData.session_image}" class="btn btn-success btn-sm" download="">
                                            <i class="fa fa-download"></i> 
                                        </a>  
                                        <a onclick="deleteSessionImage(${observationsData.id})" class="btn btn-danger btn-sm" id="attachId${observationsData.id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>`;
                            }
                            
                            var remarkable_note = '';
                            if(!$.isEmptyObject(observationsData.remarkable_note)) {
                                remarkable_note = observationsData.remarkable_note;
                            }
                            observation += `<div class="observation-box" id="observation-box${observationCnt}">
                                                    <div class="form-group">
                                                        <label for="level">Select Level</label>
                                                        <a href="javascript:void(0)" class="text-danger trash_title" onclick="deleteObservation(${observationsData.id})" id="ob${observationsData.id}"><i class="fas fa-trash"></i></a>
                                                        <select class="form-control level" name="level[]" id="level${observationCnt}" data-id="${observationCnt}" required>
                                                            <option value="">---Select Level----</option>
                                                            ${level}
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="stream">Select Stream</label>
                                                        <select class="form-control stream" name="stream[]" id="stream${observationCnt}" data-id="${observationCnt}" required>
                                                            <option value="">---Select Stream----</option>
                                                            ${stream}
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="session">Select Session</label>
                                                        <select class="form-control session" name="session[]" id="session${observationCnt}" data-id="${observationCnt}">
                                                            <option value="">---Select Session----</option>
                                                            ${session}
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="session_image">Upload Session Image</label>
                                                        <input type="file" class="form-control session_image" id="session_image${observationCnt}" name="session_image[]" accept="image/*">
                                                        ${ob_session_image}
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="remarkable_note">Add Anecdotal Record to Demonstrate Anything Remarkable</label>
                                                        <textarea class="form-control" name="remarkable_note[]" id="remarkable_note${observationCnt}">${remarkable_note}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="skills">Pick the top three demonstrated skills (Tip - Use projects to assess skills)</label>
                                                        ${skills}
                                                    </div>
                                                    ${mindset}
                                                    <div class="form-group">
                                                        <label for="remarkable_note">Allocate Points for any Exceptional Achievements</label>
                                                        <input type="number" class="form-control" id="achievement_points${observationCnt}" name="achievement_points[]" min="0" value="${observationsData.achievement_points}" disabled>
                                                    </div>
                                                    <input type="hidden" name="observationIdList[]" value="${observationsData.id}">
                                                </div>
                                            `;
                        });
                    }

                    var disable_class = '';
                    var export_data_link = '';
                    if($.isEmptyObject(observation)) {
                        disable_class = 'download-profile-disable';
                    } else {
                        export_data_link = `{{ route('trainer.observation.downloadData', 'student_id') }}`;
                        export_data_link = export_data_link.replace('student_id',studentId);
                        export_data_link = `href="${export_data_link}"`;
                    }

                    /* var html = `<form action="{{ route('trainer.observation.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card card-primary">
                                        <div class="card-header custom_padding bg-skyblue">
                                            <h4 class="m-0">Student Observations</h4>
                                            <div class="btn-group ${disable_class}">
                                                <a ${export_data_link}>
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-body card-max-height">
                                                <div class="observation-div">
                                                    ${observation}
                                                </div>
                                                <input type="hidden" name="student_id" value="${studentId}">
                                                <div class="">
                                                    <a href="javascript:void(0);" id="addMoreLink" onclick="add_observation();">Add Observation</a>
                                                    <input type="submit" id="submit_observation" name="submit_observation" value="Submit" class="btn btn-sm btn-primary float-right">
                                                </div>
                                        </div>
                                    </div>
                                </form>
                            `;


                    $('.body_add').html(html);

                    if(!$('.observation-box').length) {
                        $('#submit_observation').hide();
                    } */
                    var tbl_data = `<tr><td colspan="2">No data found</td></tr>`;
                    if(!$.isEmptyObject(data.certificationData)) {
                        tbl_data = '';
                        $.each(data.certificationData, function( index, certification ) {   
                            tbl_data += `<tr><td>${certification.level_name}</td><td>${certification.released_date}</td></tr>`;
                        });    
                    }
                    $(".certificate-table tbody").empty();
                    $(".certificate-table tbody").append(tbl_data);

                    $('.student-detail-div').show();
                    $('#c_stud_id').val(studentId);
                    $('.add-certificate').show();
                }
            });
        }

        $(document).on('change', '.session_image', function(){
            var session_image = $(this).attr('id');
            var fileExtension = ['jpeg', 'jpg', 'png'];
            if ($(`#${session_image}`).val() && $.inArray($(`#${session_image}`).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only JPG, JPEG or PNG files are allowed.");
                $(`#${session_image}`).val(''); 
            }
        });

        function assessmentChange(studentid, levelId) {
            $("#level").prop("disabled",true);
            studentFeedback(studentid, levelId);
        }

        function add_observation() {
            var observationCnt = $('.observation-box').length;
            
            var level = '';
			if(!$.isEmptyObject(allGrades)) {
                if(!$.isEmptyObject(allGrades.primary)) {
                    level += `<optgroup label="Levels">`;
                    $.each(allGrades.primary, function( index, value ) {    
                        level += `<option value="${value.id}">${value.grade}</option>`;
                    });
                    level += `</optgroup">`;
                }
                if(!$.isEmptyObject(allGrades.addons)) {
                    level += `<optgroup label="Content Add-Ons">`;
                    $.each(allGrades.addons, function( index, value ) {    
                        level += `<option value="${value.id}">${value.grade}</option>`;
                    });
                    level += `</optgroup">`;
                }
			}

            var skills = '';
            skills += `<option value="">---Select Skill----</option>`;
            var allow_add_skill = true;
            if(!$.isEmptyObject(skill_list)) {
                $.each(skill_list, function( index, value ) {    
                    skills += `<option value="${value.id}">${value.skill_name}</option>`;
                });
            } else {
                allow_add_skill = false;
            }

            var add_skill_link = '';
            if(allow_add_skill) {
                add_skill_link = `<a href="javascript:void(0)" class="float-right" id="addMoreSkill${observationCnt}" onclick="add_skill(${observationCnt});">+ Add</a>`;
            }

            var mindset = '';
            if(!$.isEmptyObject(mindset_list)) {
                var tot_mindset = 0;
                mindset += `<label>Assess mindset using a slider scale (Min - 1, Max - 10)</label> `;
                $.each(mindset_list, function( index, value ) {    
                    tot_mindset++;
                    if(index%2 == 0) {
                        mindset += `<div class="row">`;    
                    }
                    mindset += `<div class="col-md-6">
                                    <div class="form-group">
                                        <label>${value.mindset_name}</label> 
                                        <div class="d-flex flex-column align-items-center">
                                            <output>1</output>
                                            <input type="range" min="1" max="10" class="form-control my-0" name="mindset${observationCnt}[]" value="1" oninput="this.previousElementSibling.value = this.value">
                                        </div>
                                    </div>
                                </div>                                
                            `;
                    if(index%2 == 1) {
                        mindset += `</div>`;    
                    }
                });
                if(tot_mindset%2 == 1) {
                    mindset += `</div>`;    
                }
            }

            var observation = '';
            
            observation += `<div class="observation-box" id="observation-box${observationCnt}">
                                    <div class="form-group">
                                        <label for="level">Select Level</label>
                                        <a href="javascript:void(0)" class="text-danger trash_title" onclick="removeobservation(${observationCnt})"><i class="fas fa-trash"></i></a>
                                        <select class="form-control level" name="level[]" id="level${observationCnt}" data-id="${observationCnt}" required>
                                            <option value="">---Select Level----</option>
                                            ${level}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="stream">Select Stream</label>
                                        <select class="form-control stream" name="stream[]" id="stream${observationCnt}" data-id="${observationCnt}" required>
                                            <option value="">---Select Stream----</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="session">Select Session</label>
                                        <select class="form-control session" name="session[]" id="session${observationCnt}" data-id="${observationCnt}">
                                            <option value="">---Select Session----</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="session_image">Upload Session Image</label>
                                        <input type="file" class="form-control session_image" id="session_image${observationCnt}" name="session_image[]" accept="image/*">
                                    </div>
                                    <div class="form-group">
                                        <label for="remarkable_note">Add Anecdotal Record to Demonstrate Anything Remarkable</label>
                                        <textarea class="form-control" name="remarkable_note[]" id="remarkable_note${observationCnt}"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="skills">Pick the top three demonstrated skills (Tip - Use projects to assess skills)</label>
                                        <select class="form-control skill-select${observationCnt}" name="skills[${observationCnt}][]" id="skills[${observationCnt}][]">
                                            ${skills}
                                        </select>
                                        ${add_skill_link}
                                    </div>
                                    ${mindset}
                                    <div class="form-group">
                                        <label for="remarkable_note">Allocate Points for any Exceptional Achievements</label>
                                        <input type="number" class="form-control" id="achievement_points${observationCnt}" name="achievement_points[]" min="0" disabled>
                                    </div>
                                </div>
                            `;
            
            if(!observationCnt) {
                $(`.observation-div`).append(observation);
                $('#submit_observation').show();
            } else {
                $(`#observation-box${observationCnt-1}`).after(observation);
            }
            
            $("#addMoreLink").html(' <span>Add More Observation</span> ');

        }

        function add_skill(skillId) {
            var skillCnt = $(`.skill-select${skillId}`).length;
            var skills = '';
            if(skillCnt < 3) {
                skills = `<select class="form-control skill-select${skillId} mt-2" name="skills[${skillId}][]" id="skills[${skillId}][]">`;
                skills += `<option value="">---Select Skill----</option>`;
                $.each(skill_list, function( index, value ) {    
                    skills += `<option value="${value.id}">${value.skill_name}</option>`;
                });
                skills += `</select>`;
                $(`.skill-select${skillId}:last`).after(skills);
                if($(`.skill-select${skillId}`).length == 3) {
                    $(`#addMoreSkill${skillId}`).hide();
                }
            }
        }

        function removeobservation(observationId) {
            $(`#observation-box${observationId}`).remove();
            var observationCnt = $('.observation-box').length;
            if(!observationCnt) {
                $('#submit_observation').hide();
                $("#addMoreLink").text('Add Observation');
            }
        }

        $(document).on('change', '.level', function(){  
            var attrId = $(this).data('id');
            $(`#stream${attrId} option:not(:first)`).remove();
            $(`#session${attrId} option:not(:first)`).remove();
            $.ajax({
                type: "POST",
                url: "{{ route('trainer.getStream') }}",
                data: {agegroupId : $(this).find(":selected").val() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $(`select[id="level${attrId}"]`).prop("disabled", true);
                    $(`select[id="stream${attrId}"]`).prop("disabled", true);
                    $(`select[id="session${attrId}"]`).prop("disabled", true);
                },
                success: function (data) {
                    if(!$.isEmptyObject(data)) {
                        var streamList = '';
                        $.each(data, function (index, stream) {
                            streamList += "<option value='" + stream.id + "'>" + stream.title + "</option>";
                        });
                        $(`#stream${attrId}`).append(streamList);
                    } 
                    $(`select[id="level${attrId}"]`).prop("disabled", false);
                    $(`select[id="stream${attrId}"]`).prop("disabled", false);
                    $(`select[id="session${attrId}"]`).prop("disabled", false);
                }
            });
        }); 

        $(document).on('change', '.stream', function(){  
            var attrId = $(this).data('id');
            $(`#session${attrId} option:not(:first)`).remove();
            $.ajax({
                type: "POST",
                url: "{{ route('trainer.getSession') }}",
                data: {
                    agegroupId : $(`#level${attrId}`).find(":selected").val(),
                    sessionId : $(this).find(":selected").val() 
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $(`select[id="stream${attrId}"]`).prop("disabled", true);
                    $(`select[id="session${attrId}"]`).prop("disabled", true);
                },
                success: function (data) {
                    if(!$.isEmptyObject(data)) {
                        var sessionList = '';
                        $.each(data, function (index, stream) {
                            sessionList += "<option value='" + stream.id + "'>" + stream.title + "</option>";
                        });
                        $(`#session${attrId}`).append(sessionList);
                    } 
                    $(`select[id="stream${attrId}"]`).prop("disabled", false);
                    $(`select[id="session${attrId}"]`).prop("disabled", false);
                }
            });
        }); 

        $(document).on("click",".add-certificate", function () {
            $(this).hide();
            $("#cLevel option").prop("selected", false);
            $('.certi-box').show();
        });

        function deleteSessionImage(id) {
            if(confirm("Are you sure want to delete Session Image?")) {
                $(`#attachId${id}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('trainer.observation.deleteSessionImage') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        observationId: id,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Session Image deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteObservation(id) {
            if(confirm("Are you sure want to delete Observation Data?")) {
                $(`#ob${id}`).css({'pointer-events': 'none', 'opacity' : 0.5});
                $.ajax({
                    url: "{{ route('trainer.deleteObservation') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        observationId: id,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Observation Data deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        // ── Observation Report Modal ──────────────────────────────────────────
        function obsModalLock() {
            var m = $('#obsReportModal').data('bs.modal');
            if (m) { m._config.backdrop = 'static'; m._config.keyboard = false; }
            $('#obsReportModal [data-dismiss="modal"]').prop('disabled', true);
        }

        function obsModalUnlock() {
            var m = $('#obsReportModal').data('bs.modal');
            if (m) { m._config.backdrop = true; m._config.keyboard = true; }
            $('#obsReportModal [data-dismiss="modal"]').prop('disabled', false);
        }

        function obsReportReset() {
            obsModalUnlock();
            $('#obsDateFields').show();
            $('#obsLoadingState').hide();
            $('#obsDownloadSection').hide().empty();
            $('#obsErrorMsg').hide().text('');
            $('#obsReportSubmitBtn').prop('disabled', false)
                .html('<i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i> Generate PDF')
                .show();
            $('#obsFromDate').removeAttr('disabled');
            $('#obsToDate').removeAttr('disabled');
        }

        $(document).on('show.bs.modal', '#obsReportModal', function () {
            obsReportReset();
        });

        $(document).on('click', '#obsReportSubmitBtn', function () {
            var $btn     = $(this);
            var fromDate = $('#obsFromDate').val();
            var toDate   = $('#obsToDate').val();
            var url      = '{{ route("trainer.observation_report", "__STID__") }}'
                           .replace('__STID__', studentid);
            var params   = [];
            if (fromDate) params.push('from_date=' + fromDate);
            if (toDate)   params.push('to_date='   + toDate);
            if (params.length) url += '?' + params.join('&');

            // Show loading state — lock modal so it cannot be closed
            $('#obsDateFields').hide();
            $('#obsErrorMsg').hide().text('');
            $('#obsDownloadSection').hide().empty();
            $('#obsLoadingState').show();
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Generating...');
            $('#obsFromDate, #obsToDate').prop('disabled', true);
            obsModalLock();

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (response) {
                if (!response.ok) {
                    return response.text().then(function (txt) {
                        var msg = 'Server error (' + response.status + ')';
                        try { var j = JSON.parse(txt); msg = j.error || msg; } catch (e) {}
                        throw new Error(msg);
                    });
                }
                // Determine filename from Content-Disposition header
                var filename = 'observation-report.pdf';
                var cd = response.headers.get('Content-Disposition') || '';
                var match = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (match) filename = match[1].replace(/['"]/g, '').trim();
                return response.blob().then(function (blob) {
                    return { blob: blob, filename: filename };
                });
            })
            .then(function (data) {
                var blobUrl = window.URL.createObjectURL(data.blob);
                $('#obsLoadingState').hide();
                $('#obsDownloadSection').html(
                    '<div class="text-center">' +
                    '<i class="material-icons text-success" style="font-size:36px;">check_circle</i>' +
                    '<p class="mt-1 mb-3 text-muted" style="font-size:12px;">Report generated successfully!</p>' +
                    '<a href="' + blobUrl + '" download="' + data.filename + '" class="btn btn-success btn-sm px-4">' +
                    '<i class="material-icons" style="font-size:14px;vertical-align:middle;">download</i> Download Report' +
                    '</a></div>'
                ).show();
                obsModalUnlock();
                $btn.html('<i class="material-icons" style="font-size:14px;vertical-align:middle;">refresh</i> Regenerate')
                    .prop('disabled', false);
                $('#obsFromDate, #obsToDate').prop('disabled', false);
            })
            .catch(function (err) {
                obsModalUnlock();
                $('#obsLoadingState').hide();
                $('#obsDateFields').show();
                $('#obsErrorMsg').text('Error: ' + err.message).show();
                $('#obsFromDate, #obsToDate').prop('disabled', false);
                $btn.prop('disabled', false)
                    .html('<i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i> Generate PDF');
            });
        });

        $(document).on('change', '#obsFromDate', function () {
            if ($('#obsToDate').val() && $('#obsToDate').val() < $(this).val()) {
                $('#obsToDate').val('');
            }
            $('#obsToDate').attr('min', $(this).val());
        });

        $(document).on('change', '#obsToDate', function () {
            $('#obsFromDate').attr('max', $(this).val());
        });
    </script>

    <!-- Observation Report Modal -->
    <div class="modal fade" id="obsReportModal" tabindex="-1" role="dialog" aria-labelledby="obsReportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width:360px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="obsReportModalLabel">Generate Observation Report</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">

                    {{-- Date fields --}}
                    <div id="obsDateFields">
                        <p class="text-muted mb-3" style="font-size:12px;">Select a date range to filter observations. Leave blank to include all.</p>
                        <div class="form-group">
                            <label class="font-weight-bold" style="font-size:13px;">From Date</label>
                            <input type="date" class="form-control form-control-sm" id="obsFromDate">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold" style="font-size:13px;">To Date</label>
                            <input type="date" class="form-control form-control-sm" id="obsToDate">
                        </div>
                    </div>

                    {{-- Loading state --}}
                    <div id="obsLoadingState" style="display:none; text-align:center; padding:24px 0;">
                        <div class="spinner-border" role="status" style="width:2.5rem;height:2.5rem;color:#4F46E5;"></div>
                        <p class="mt-3 mb-1 font-weight-bold" style="font-size:13px;">Generating report…</p>
                        <small class="text-muted" style="font-size:11px;">AI summary generation may take a few seconds.</small>
                    </div>

                    {{-- Download section (shown after success) --}}
                    <div id="obsDownloadSection" style="display:none;"></div>

                    {{-- Error message --}}
                    <div id="obsErrorMsg" class="alert alert-danger mt-2 mb-0" style="display:none; font-size:12px;"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm" id="obsReportSubmitBtn" style="background-color:#4F46E5;border-color:#4F46E5;color:#fff;">
                        <i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i> Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
