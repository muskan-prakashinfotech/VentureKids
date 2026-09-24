@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Import Students</h2>
        <a href="{{ route('school.student-list') }}" class="btn btn-sm btn-warning float-right"> <i
                            class="material-icons">west</i> Back</a>
    </div>
    <!-- /.content-header -->

    @if (Session::has('message'))
    <div class="alert alert-success">
        {{ Session::get('message') }}
    </div>
    @endif
    
    @if (Session::has('capacity_failed'))
    <div class="alert alert-danger">
        {{ Session::get('capacity_failed') }}
    </div>
    @endif
    @php
        $isPartnerSchool = !empty($school) && (($school->created_type ?? 'admin') === 'partner');
    @endphp
    
    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <form action="{{ route('school.student-import-store') }}" method="POST" enctype="multipart/form-data" id="frm_student_import">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <p>
                            <div class="d-block stud-import-notes">
                                <div>
                                    <h5>Instructions for Importing Students via CSV</h5>
                                </div>  

                                <div class="mt-3">
                                    <strong>Required Fields</strong> (Highlighted in Blue in Sample CSV):
                                </div>
                                <div>
                                    Student First Name, Gender, Grade, Batch @if($isPartnerSchool), Student Level @endif
                                </div>

                                <div class="mt-2">
                                    <strong>Non-mandatory Fields:</strong>
                                </div>
                                <div>
                                    Student Last Name, User Name, Student Email, Date of Birth, Parent Full Name, Parent Email @if(!$isPartnerSchool), Student Level @endif - these are optional, but recommended wherever available.
                                </div>

                                <div class="mt-2">
                                    <strong>Optional Details:</strong>
                                </div>
                                <div>
                                    Additional columns have been provided for any extra information you may want to include, such as Roll No., Admission Number, Alternate Email, etc.
                                </div>

                                <div class="mt-2">
                                    <strong>User Name:</strong>
                                </div>
                                <ul>
                                    <li>Each Username must be unique.</li>
                                    <li>Example : studentfirstnamestudentlastnameDDMM or studentfirstname.studentlastnameDDMM or studentfirstnameGrade_RollNo.</li>
                                    <li>Only letters, numbers, dots (.), and underscores (_) are allowed.</li>
                                    <li>If the Username is not added, it will be auto-generated in the following format: schoolname.studentfirstnamestudentlastnameDDMM </li>
                                    <li>Example: venturekids.natashaarora2810</li>
                                </ul>
                                
                                <div class="mt-2">
                                    <strong>Password:</strong>
                                </div>
                                <div>
                                    Password length must be at least 6 characters and not more than 10 characters. The Password can be:
                                    <ul>
                                        <li>Unique for each child, or</li>
                                        <li>Common for a batch, or</li>
                                        <li>Common for the entire school (for ease of recall).</li>
                                    </ul>
                                </div>

                                <div class="mt-2">
                                    <strong>Student Email:</strong>
                                </div>
                                <div>
                                    If provided, the Student Email must be unique for each student.
                                </div>

                                <div class="mt-2">
                                    <strong>Date of Birth:</strong>
                                </div>
                                <div>
                                    If provided, must be in the format: dd-mm-yyyy
                                </div>
                                <div>
                                    Example: 28-10-2000
                                </div>

                                <div class="mt-2">
                                    <strong>Gender:</strong>
                                </div>
                                <div>
                                    Must be one of the following: Male, Female, Other
                                </div>

                                <div class="mt-2">
                                    <strong>Grade:</strong>
                                </div>
                                <div>
                                    Must match one of these values exactly:
                                </div>
                                <div>
                                    {{implode(",", $student_grade_list)}}
                                </div>

                                <div class="mt-2">
                                    <strong>Student Level:</strong>
                                </div>
                                <div>
                                    It is used to assign a level to each student, specify unique code separated by comma for each level.
                                </div>

                                @if($school_batch_list->count())
                                <div class="mt-2">
                                    <strong>Batch:</strong>
                                </div>
                                <div>
                                    This should be the unique identifying batch code for each group of children assigned to a specific teacher.
                                </div>
                                <div>
                                    Examples: {{implode(",", $school_batch_list->pluck('batch_name')->toArray())}}
                                </div>
                                @endif
                                
                                <div class="mt-2">
                                    <strong>Confirmation Email:</strong>
                                </div>
                                <div>
                                    Once the data has been successfully uploaded, a confirmation email will be sent to the registered school email ID.
                                </div>

                                <div class="mt-2">
                                    <strong>Support:</strong>
                                </div>
                                <div>
                                    For any queries or issues, please contact the VentureKids team at:
                                </div>
                                <div>
                                    enquiry@venderkids.com
                                </div>

                            </div>
                        </p>
                        <label for="upload_csv">Upload CSV </label>
                        <input type="file" class="form-control" id="upload_csv" placeholder=""
                                            name="upload_csv"  accept=".csv" required>
                        <p>
                            <a href="{{ asset('csv/template/student-import-template.csv') }}"
                                class="text-danger">DOWNLOAD SAMPLE CSV</a>
                        </p> 
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <p>
                                <i class="fas fa-exclamation-triangle"></i> @lang('Please fix the following errors & try again!')
                            </p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-primary btn-student-import">Import</button>
                </div>
            </div> 
            </form>
        </div>
    </section>
    <!-- /.content -->
</div>
<script>
    $(document).ready(function() {
        $("#upload_csv").on("change", function() {
            var file = this.files[0];
            if(file) {
                var mbSize = file.size/1024/1024;   
                var fileIsMp4 = (file.type === "text/csv");
                if(!fileIsMp4)  // mbSize > 1
                {
                    alert("Only CSV File is allowed.");
                    $(this).val('');
                    $(".btn-student-import").html('Import');
                    $(".btn-student-import").attr('disabled', false);
                    return false;
                }
            }
        });
        $(".btn-student-import").on("click", function() {
            if($.trim($('#upload_csv').val().length) > 0) {
                $(this).html('please wait...');
                $(this).attr('disabled', true);
                $("#frm_student_import").trigger("submit");
            } else {
                alert("The upload csv field is required");
                $(this).html('Import');
                $(this).attr('disabled', false);
            }
        });
    });
</script>
@endsection