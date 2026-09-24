@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @php
                    $isPartner = isPartnerUser();
                    $lockedCountryId = $selectedCountryId ?? ($school['country_id'] ?? null);
                    $lockedCountryName = '';
                    if (!empty($lockedCountryId)) {
                        $lockedCountryName = optional($countries->firstWhere('id', $lockedCountryId))->name ?? '';
                    }
                @endphp
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('admin.edit_school') }}</h3>
                                <div class="card-tools">
                                    <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                </div>
                            </div>
                            @if (session()->has('email_faild'))
                                <div class="alert alert-danger" style="text-align: center;">
                                    {{ session()->get('email_faild') }}
                                </div>
                            @endif
                            <form action="{{ route('backend.schoolupdate.schoolUpdate') }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card card-primary">
                                            {{-- <div class="card-header"></div> --}}
                                            <?php
                                            $number_of_student = $school['number_of_student'] / 120;
                                            $number_student = round($number_of_student);
                                            ?>
                                            <input type="hidden" value="{{ $school['user_id'] }}" name="user_id">
                                            <input type="hidden" value="{{ $school['id'] }}" name="school_id">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.school_name') }}</label>
                                                    <input type="text"
                                                           class="form-control @error('school_name') is-invalid @enderror" id="schoolname"
                                                           name="school_name" value="{{ $school['school_name'] }}" required>
                                                    @error('school_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="yearestablished">School Contact Person Full Name</label>
                                                    <input type="text"
                                                           class="form-control @error('principle_name') is-invalid @enderror"
                                                           id="principle_name" placeholder="School Contact Person Full Name"
                                                           name="principle_name" value="{{ $school['principle_name'] }}" required>
                                                    @error('principle_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="yearestablished">School Official Email ID</label>
                                                    <input type="email"
                                                           class="form-control @error('official_email_id') is-invalid @enderror"
                                                           id="yearestablished" placeholder="School Official Email ID" required
                                                           name="official_email_id" value="{{ $school['official_email_id'] }}">
                                                    @error('official_email_id')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="inchargecontact">Official Contact Number</label>
                                                    <input type="tel"
                                                           class="form-control @error('contact_number') is-invalid @enderror"
                                                           id="phone" placeholder="Offical Contact Number"
                                                           data-country-name="{{ $lockedCountryName }}"
                                                           name="contact_number" value="{{ $school['contact_number'] }}" 
                                                    >
                                                    @error('contact_number')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="inchargename">{{ __('admin.activity_in_charge_first_name') }}</label>
                                                    <input type="text" class="form-control @error('incharge_name') is-invalid @enderror"
                                                           id="inchargename" name="incharge_name" placeholder="Activity In-charge First Name" value="{{ $school['incharge_name'] }}">
                                                    @error('incharge_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="inchargeemail">{{ __('admin.in_charge_email') }}</label>
                                                    <input type="text"
                                                           class="form-control @error('incharge_email') is-invalid @enderror"
                                                           id="inchargeemail" name="incharge_email" placeholder="Activity In-charge Email ID"
                                                           value="{{ $school['incharge_email'] }}">
                                                    @error('incharge_email')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                {{-- <div class="form-group">
                                                    <label for="partnername">VentureKids Representative Full Name</label>
                                                    <input type="text" class="form-control" id="partnername" name="partner_name"
                                                        placeholder="VentureKids Representative Full Name" value="{{ $school['venturekids_representative'] }}">
                                                </div> --}}

                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.full_address') }}</label>
                                                    <textarea type="text" class="form-control @error('address') is-invalid @enderror" id="address" placeholder=""
                                                              name="address">{{ $school['school_address'] }}</textarea>
                                                    @error('address')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="eventend">{{ __('admin/event.event_country') }}</label>
                                                    @if ($isPartner)
                                                        <input type="hidden" name="country" value="{{ $lockedCountryId }}">
                                                        <input type="text" class="form-control" value="{{ $lockedCountryName }}" disabled>
                                                    @else
                                                        <select class="form-control" name="country" id="school_country" required>
                                                            <option value="">-- Select Country --</option>
                                                            @foreach ($countries as $country)
                                                                <option @if ($school['country_id'] == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                    @error('country')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="schoolname">City</label>
                                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                                           id="schoolname" name="city" placeholder="City"
                                                           value="{{ $school['city'] }}" required>
                                                    @error('city')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="yearestablished">{{ __('admin.year_established') }}</label>
                                                    <input type="number"
                                                           class="form-control @error('year_establish') is-invalid @enderror"
                                                           id="yearestablished" placeholder="" name="year_establish"
                                                           value="{{ $school['year_establish'] }}">
                                                    @error('year_establish')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                @php
                                                    $standardChecked = (bool) old('assessment_assignment_standard', $school['standard_assessment_assigned'] ?? false);
                                                    $realqChecked = (bool) old('assessment_assignment_realq', (!empty($realqAssignments) && $realqAssignments->count() > 0));
                                                @endphp
                                                <div class="form-group">
                                                    <label>Assessment Assignment</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="assessment_assignment_standard" id="assessment_assignment_standard" value="1"
                                                               {{ $standardChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="assessment_assignment_standard">Standard Assessment</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="assessment_assignment_realq" id="assessment_assignment_realq" value="1"
                                                               {{ $realqChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="assessment_assignment_realq">RealQ Assessment</label>
                                                    </div>
                                                    <small class="text-muted">Select one or both assessments for this school.</small>
                                                </div>

                                                <div id="standard_assessment_date_fields" class="assessment-box assessment-box--standard" @if(!$standardChecked) style="display:none;" @endif>
                                                    <div class="assessment-box__title">Standard Assessment</div>
                                                    <div class="form-group">
                                                        <label for="standard_assessment_enabled_from">Standard Assessment Start Date</label>
                                                        <input type="date" class="form-control" id="standard_assessment_enabled_from" name="standard_assessment_enabled_from"
                                                               value="{{ old('standard_assessment_enabled_from', $school['standard_assessment_enabled_from'] ?? '') }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="standard_assessment_enabled_to">Standard Assessment End Date</label>
                                                        <input type="date" class="form-control" id="standard_assessment_enabled_to" name="standard_assessment_enabled_to"
                                                               value="{{ old('standard_assessment_enabled_to', $school['standard_assessment_enabled_to'] ?? '') }}">
                                                    </div>
                                                </div>

                                                <div id="realq_assessment_fields" class="assessment-box assessment-box--realq" @if(!$realqChecked) style="display:none;" @endif>
                                                    <div class="assessment-box__title">RealQ Assessment</div>
                                                    <div class="form-group">
                                                        <label for="realq_assessment_enabled_from">RealQ Assessment Start Date</label>
                                                        @php
                                                            $defaultRealqFrom = old('realq_assessment_enabled_from');
                                                            if ($defaultRealqFrom === null && !empty($realqAssignments) && $realqAssignments->count() > 0) {
                                                                $defaultRealqFrom = optional($realqAssignments->first())->realq_assessment_enabled_from;
                                                            }
                                                            if ($defaultRealqFrom === null) {
                                                                $defaultRealqFrom = $school['realq_assessment_enabled_from'] ?? '';
                                                            }
                                                        @endphp
                                                        <input type="date" class="form-control" id="realq_assessment_enabled_from" name="realq_assessment_enabled_from" value="{{ $defaultRealqFrom }}" @if($realqChecked) required @endif>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="realq_assessment_enabled_to">RealQ Assessment End Date</label>
                                                        @php
                                                            $defaultRealqTo = old('realq_assessment_enabled_to');
                                                            if ($defaultRealqTo === null && !empty($realqAssignments) && $realqAssignments->count() > 0) {
                                                                $defaultRealqTo = optional($realqAssignments->first())->realq_assessment_enabled_to;
                                                            }
                                                            if ($defaultRealqTo === null) {
                                                                $defaultRealqTo = $school['realq_assessment_enabled_to'] ?? '';
                                                            }
                                                        @endphp
                                                        <input type="date" class="form-control" id="realq_assessment_enabled_to" name="realq_assessment_enabled_to" value="{{ $defaultRealqTo }}" @if($realqChecked) required @endif>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="realq_assessment_assigned_board_id">RealQ Assessment Select Board</label>
                                                        @php
                                                            $defaultBoard = old('realq_assessment_assigned_board_id');
                                                            if ($defaultBoard === null && !empty($realqAssignments) && $realqAssignments->count() > 0) {
                                                                $defaultBoard = $realqAssignments->first()->realq_assessment_assigned_board_id;
                                                            }
                                                        @endphp
                                                        <select class="form-control" id="realq_assessment_assigned_board_id" name="realq_assessment_assigned_board_id" @if($realqChecked) required @endif>
                                                            <option value="">-- Select Board --</option>
                                                            @foreach($studentBoards as $sBoard)
                                                                <option value="{{ $sBoard->id }}" {{ (string) $defaultBoard === (string) $sBoard->id ? 'selected' : '' }}>
                                                                    {{ $sBoard->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="realq_assessment_assigned_scale_id">RealQ Assessment Select Scale</label>
                                                        @php
                                                            $defaultScale = old('realq_assessment_assigned_scale_id');
                                                            if ($defaultScale === null && !empty($realqAssignments) && count($realqAssignments) > 0) {
                                                                $defaultScale = $realqAssignments->first()->realq_assessment_assigned_scale_id;
                                                            }
                                                        @endphp
                                                        <select class="form-control" id="realq_assessment_assigned_scale_id" name="realq_assessment_assigned_scale_id" @if($realqChecked) required @endif>
                                                            <option value="">-- Select Scale --</option>
                                                            @foreach($realqScales as $scale)
                                                                <option value="{{ $scale->id }}" {{ (string) $defaultScale === (string) $scale->id ? 'selected' : '' }}>
                                                                    {{ $scale->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>RealQ Assessment Select Grade & Parameters</label>
                                                        @php
                                                            $existingAssignments = old('realq_assessment_assigned_grade_id');
                                                            $rows = [];
                                                            if (is_array($existingAssignments)) {
                                                                foreach ($existingAssignments as $idx => $gradeId) {
                                                                    $rows[] = [
                                                                        'grade_id' => $gradeId,
                                                                        'parameter_ids' => old('realq_assessment_assigned_parameters_id.' . $idx, []),
                                                                    ];
                                                                }
                                                            } else {
                                                                foreach ($realqAssignments as $assignmentRow) {
                                                                    $rows[] = [
                                                                        'grade_id' => $assignmentRow->realq_assessment_assigned_grade_id,
                                                                        'parameter_ids' => !empty($assignmentRow->realq_assessment_assigned_parameters_id)
                                                                            ? explode(',', $assignmentRow->realq_assessment_assigned_parameters_id)
                                                                            : [],
                                                                    ];
                                                                }
                                                            }
                                                            if (empty($rows)) {
                                                                $rows[] = ['grade_id' => '', 'parameter_ids' => []];
                                                            }
                                                        @endphp

                                                        <div id="realq-assignment-rows" class="realq-assignment-rows">
                                                            @foreach($rows as $index => $row)
                                                                <div class="realq-assignment-row mb-3" data-index="{{ $index }}">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <label>Grade</label>
                                                                            <select class="form-control" name="realq_assessment_assigned_grade_id[]" @if($realqChecked) required @endif>
                                                                                <option value="">-- Select Grade --</option>
                                                                                @foreach($studentGrades as $sGrade)
                                                                                    <option value="{{ $sGrade->id }}" {{ (string) $row['grade_id'] === (string) $sGrade->id ? 'selected' : '' }}>
                                                                                        {{ $sGrade->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-7">
                                                                            <label>Parameters</label>
                                                                            <select class="form-control" name="realq_assessment_assigned_parameters_id[{{ $index }}][]" multiple @if($realqChecked) required @endif>
                                                                                @foreach($realqParameters as $parameter)
                                                                                    <option value="{{ $parameter->id }}" {{ in_array((string) $parameter->id, array_map('strval', $row['parameter_ids']), true) ? 'selected' : '' }}>
                                                                                        {{ $parameter->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-1 d-flex align-items-end">
                                                                            <button type="button" class="btn btn-danger realq-assignment-remove realq-assignment-remove-btn" {{ count($rows) === 1 ? 'disabled' : '' }}>
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <button type="button" class="btn btn-secondary mt-2 realq-assignment-add" id="realqAddAssignmentRow">+ Add Grade &amp; Parameters</button>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-warning">
                                            {{-- <div class="card-header"></div> --}}
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="inchargecontact">Fee Per Student</label>
                                                    <div class="row align-items-center">
                                                        <div class="col-md-4">
                                                            @if ($isPartner)
                                                                <input type="hidden" name="currency_type" value="{{ $partnerCurrencyType }}">
                                                                <input type="text" class="form-control" value="{{ strtoupper($partnerCurrencyType) }}" disabled>
                                                            @else
                                                                <select class="form-control" name="currency_type" id="currency_type" required>
                                                                    <option value="inr" @if($school['currency_type'] == 'inr') selected @endif>INR</option>
                                                                    <option value="sgd" @if($school['currency_type'] == 'sgd') selected @endif>SGD</option>
                                                                    <option value="usd" @if($school['currency_type'] == 'usd') selected @endif>USD</option>
                                                                </select>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="number" class="form-control @error('fee_per_student') is-invalid @enderror"
                                                                   id="feeperstudent" placeholder="Fee Per Student" name="fee_per_student" value="{{ $school['fee_per_student'] }}" >
                                                            @error('fee_per_student')
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="schoolname">{{ $isPartner ? 'Number of License' : __('admin.number_of_student') }}</label>
                                                    <div class="mb-3 input-group">
                                                        <input type="number" class="form-control" placeholder="{{ $isPartner ? 'Number of License' : 'Number of Students' }}"
                                                               name="number_of_student" value="{{ $school['number_of_student'] }}" required>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="schoolname">Course Start Date</label>
                                                    <input type="date" class="form-control" id="course_start_date" name="course_start_date" value="{{ $school['course_start_date'] }}" required>
                                                    @error('course_start_date')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="schoolname">Course Expiration Date</label>
                                                    <input type="date" class="form-control" name="course_end_date" id="course_end_date" value="{{ $school['course_end_date'] }}" required>
                                                    @error('course_end_date')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="card card-outline card-info mb-3">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Academic Year</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="border rounded p-3 mb-3 bg-light">
                                                            <h6 class="mb-3 text-primary">Add Academic Year</h6>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <label for="academic_year_start_date">Start Date</label>
                                                                        <input type="date" class="form-control" id="academic_year_start_date" name="academic_year_start_date" value="{{ old('academic_year_start_date') }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group mb-0">
                                                                        <label for="academic_year_end_date">End Date</label>
                                                                        <input type="date" class="form-control" id="academic_year_end_date" name="academic_year_end_date" value="{{ old('academic_year_end_date') }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-6 mb-3 mb-lg-0">
                                                                <div class="border rounded p-3 h-100 bg-white">
                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                        <h6 class="mb-0 text-primary">Active Academic Dates</h6>
                                                                        <!-- <small class="text-muted">Currently live range</small> -->
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Start Date</th>
                                                                                    <th>End Date</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @forelse($activeAcademicYears as $academicYear)
                                                                                    <tr>
                                                                                        <td>{{ \Carbon\Carbon::parse($academicYear->start_date)->format('d M Y') }}</td>
                                                                                        <td>{{ \Carbon\Carbon::parse($academicYear->end_date)->format('d M Y') }}</td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr>
                                                                                        <td colspan="3" class="text-center text-muted">No active academic dates found.</td>
                                                                                    </tr>
                                                                                @endforelse
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="border rounded p-3 h-100 bg-white">
                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                        <h6 class="mb-0 text-primary">Past Academic Dates</h6>
                                                                        <!-- <small class="text-muted">Automatically retired after the end date</small> -->
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Start Date</th>
                                                                                    <th>End Date</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @forelse($pastAcademicYears as $academicYear)
                                                                                    <tr>
                                                                                        <td>{{ \Carbon\Carbon::parse($academicYear->start_date)->format('d M Y') }}</td>
                                                                                        <td>{{ \Carbon\Carbon::parse($academicYear->end_date)->format('d M Y') }}</td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr>
                                                                                        <td colspan="2" class="text-center text-muted">No past academic dates found.</td>
                                                                                    </tr>
                                                                                @endforelse
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (!$isPartner)
                                                    <div class="form-group">
                                                        <div class="form-check">
                                                            <input name="white_label" class="form-check-input" type="checkbox" value="1" id="white_label" @if ($school['white_label']) checked @endif >
                                                            <label for="white_label">White Label</label>
                                                        </div>
                                                        @error('white_label')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group school-logo @if(!$school['white_label']) d-none @endif">
                                                        <label for="school_logo">{{ __('admin.school_logo') }} (1x1)</label>
                                                        <input type="file" class="form-control" id="school_logo" name="school_logo" accept="image/jpg, image/png, image/jpeg">
                                                        @error('school_logo')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                        @if($school['school_logo'] && $school['school_logo_path'])
                                                            <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                                <span>{{$school['school_logo']}}</span>
                                                                <div class="action-btn">
                                                                    <a href="{{ url($school['school_logo_path']) }}" class="btn btn-success btn-sm" download>
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                    <a onclick="deleteSchoolLogo({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="form-group school-cover-image @if(!$school['white_label']) d-none @endif">
                                                        <label for="school_cover_image">Upload School Cover Picture (2x1)</label>
                                                        <input type="file" class="form-control" id="school_cover_image" name="school_cover_image" accept="image/jpg, image/png, image/jpeg">
                                                        @error('school_cover_image')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                        @if($school['school_cover_image'] && $school['school_cover_image_path'])
                                                            <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                                <span>{{$school['school_cover_image']}}</span>
                                                                <div class="action-btn">
                                                                    <a href="{{ url($school['school_cover_image_path']) }}" class="btn btn-success btn-sm" download>
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                    <a onclick="deleteSchoolCoverLogo({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="form-group school-css @if(!$school['white_label']) d-none @endif">
                                                        <label for="school_cover_image">Upload School CSS</label>
                                                        <input type="file" class="form-control" id="school_css" name="school_css" accept=".css">
                                                        @error('school_css')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                        @if(isset($school['school_css']) && $school['school_css'] && $school['school_css_path'])
                                                            <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                                <span>{{$school['school_css']}}</span>
                                                                <div class="action-btn">
                                                                    <a href="{{ url($school['school_css_path']) }}" class="btn btn-success btn-sm" download>
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                    <a onclick="deleteSchoolCss({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="form-group school-loader @if(!$school['white_label']) d-none @endif">
                                                        <label for="school_loader">Upload School Loader Image</label>
                                                        <input type="file" class="form-control" id="school_loader" name="school_loader" accept=".png">
                                                        @error('school_loader')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                        @if(isset($school['school_loader']) && $school['school_loader'] && $school['school_loader_path'])
                                                            <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                                <span>{{$school['school_loader']}}</span>
                                                                <div class="action-btn">
                                                                    <a href="{{ url($school['school_loader_path']) }}" class="btn btn-success btn-sm" download>
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                    <a onclick="deleteSchoolLoader({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if (!$isPartner)
                                                    <div class="form-group logout-redirect-url @if(!$school['white_label']) d-none @endif">
                                                        <label for="logout_redirect_url">Logout Redirect URL</label>
                                                        <input type="text" class="form-control @error('logout_redirect_url') is-invalid @enderror"
                                                               id="logout_redirect_url" placeholder="Logout Redirect URL" name="logout_redirect_url" value="@if(!empty($school['logout_redirect_url'])){{ $school['logout_redirect_url'] }}@endif">
                                                        @error('logout_redirect_url')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                    </div>
                                                    @endif
                                                @endif

                                                <div class="form-group">
                                                    <label for="levels">Select Level(s)</label>
                                                    @php
                                                    $selected = (isset($school['default_grade']))
                                                            ? explode(',', $school['default_grade'])
                                                            : [];
                                                    $primaryLevels = $grade->where('is_primary', 1);
                                                    $addonLevels = $grade->where('is_primary', 0);
                                                    @endphp
                                                    <select name="default_grade[]" id="default_grade" class="form-control" multiple>
                                                        @if($primaryLevels->count())
                                                            <optgroup label="Levels">
                                                                @foreach($primaryLevels as $level)
                                                                    <option value="{{ $level->id }}"
                                                                        {{ in_array($level->id, old('default_grade', $selected ?? [])) ? 'selected' : '' }}>
                                                                        {{ $level->grade }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif

                                                        @if($addonLevels->count())
                                                            <optgroup label="Content Add-Ons">
                                                                @foreach($addonLevels as $level)
                                                                    <option value="{{ $level->id }}"
                                                                        {{ in_array($level->id, old('default_grade', $selected ?? [])) ? 'selected' : '' }}>
                                                                        {{ $level->grade }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                    </select>
                                                    @error('default_grade')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="batch_name">School Batches</label>
                                                    <div class="batch-div">
                                                        @if(!empty($school['batches']))
                                                            @foreach($school['batches'] as $key => $batch)
                                                                <div class="batch-box" id="batch-box{{$key}}">
                                                                    <div class="form-group">
                                                                        <label for="batch_name">Batch Name</label>
                                                                        <a href="javascript:void(0)" class="text-danger trash_title" onclick="deleteBatch({{$batch['id']}})"><i class="fas fa-trash"></i></a>
                                                                        <input type="text" class="form-control" id="batch_name${batchCnt}" name="batch_name[]" value="{{$batch['batch_name']}}" placeholder="Batch Name" required>
                                                                    </div>
                                                                    <input type="hidden" name="batchIdList[]" value="{{$batch['id']}}">
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="addMore @if(empty($school['batches'])) mt-0 @else mt-minus25 @endif">
                                                        <a href="javscript:void(0);" id="addBatchLink" onclick="addBatch()"> @if(empty($school['batches'])) Add Batch @else Add More @endif</a>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="domain">Add Domain</label>
                                                    <div class="row align-items-center">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control @error('school_domain') is-invalid @enderror"
                                                                   id="schooldomain" placeholder="School domain" name="school_domain" value="@if(!empty($school['domains']['domain'])){{ $school['domains']['domain'] }}@endif" required rule-domain-validate="true">
                                                            @error('school_domain')
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label for="subDomainText">{{ config('tenancy.sub_domain') }}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($schoolHasMultipleDomain->isNotEmpty())
                                                    <div class="form-group">
                                                        <label for="domain">Other Domains</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                @foreach($schoolHasMultipleDomain as $singleDomain)
                                                                    <span>{{ $singleDomain->domain }}</span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>

                                            <div class="card-footer">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                                <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-secondary">Cancel & Back</a>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <script>
        $(document).ready(function() {
            $("#school_logo, #school_cover_image").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val('');
                }
            });
            $("#school_loader").change(function () {
                var fileExtension = ['png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only PNG file is allowed.");
                    $(this).val('');
                }
                if(this.files[0].size > 50000) {
                    alert("File too Big, please select a file less than 50kb");
                    $(this).val('');
                }
            });

            @if (!$isPartner)
                $('#white_label').change(function() {
                    if ($(this).is(':checked')) {
                        $('.school-logo').removeClass('d-none');
                        $('.school-cover-image').removeClass('d-none');
                        $('.school-css').removeClass('d-none');
                        $('.school-loader').removeClass('d-none');
                        $('.logout-redirect-url').removeClass('d-none');
                    } else {
                        if (confirm("This will remove the school's uploaded logo, cover image, custom css, loader image, and logout redirect URL upon saving. Are you sure?")) {
                            $('.school-logo').addClass('d-none');
                            $('.school-cover-image').addClass('d-none');
                            $('.school-css').addClass('d-none');
                            $('.school-loader').addClass('d-none');
                            $('.logout-redirect-url').addClass('d-none');
                        } else {
                            $(this).prop('checked', true);
                        }
                    }
                });
            @endif
        });
        function deleteSchoolLogo(schoolId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachId${schoolId}`).addClass('disabled');
                $.ajax({
                    url: "{{ route('backend.school.deleteSchoolLogo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        schoolId: schoolId,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("File deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        function deleteSchoolCoverLogo(schoolId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachId${schoolId}`).addClass('disabled');
                $.ajax({
                    url: "{{ route('backend.school.deleteSchoolCoverLogo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        schoolId: schoolId,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("File deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        function addBatch() {
            var batchCnt = $('.batch-box').length;
            var batch = `<div class="batch-box" id="batch-box${batchCnt}">
                            <div class="form-group">
                                <label for="eventend">Batch Name</label>
                                <a href="javascript:void(0)" class="text-danger trash_title" onclick="removeBatch(${batchCnt})"><i class="fas fa-trash"></i></a>
                                <input type="text" class="form-control" id="batch_name${batchCnt}" name="batch_name[]" placeholder="Batch Name" required>
                            </div>
                        </div>`;

            if(!batchCnt) {
                $(`.batch-div`).append(batch);
                $('.addMore').removeClass("mt-0").addClass("mt-minus25");
            } else {
                $(`#batch-box${batchCnt-1}`).after(batch);
            }

            $("#addBatchLink").html(' <span>Add More</span> ');
        }
        function removeBatch(batchId) {
            $(`#batch-box${batchId}`).remove();
            var batchCnt = $('.batch-box').length;
            if(!batchCnt) {
                $('.addMore').removeClass("mt-minus25").addClass("mt-0");
                $("#addBatchLink").text('Add Batch');
            }
        }
        function deleteBatch(batchId) {
            if(confirm("Are you sure want to Delete this Batch?")) {
                $.ajax({
                    url: "{{ route('backend.school.deleteBatch') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        batchId: batchId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Batch Deleted!");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        function deleteSchoolCss(schoolId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachId${schoolId}`).addClass('disabled');
                $.ajax({
                    url: "{{ route('backend.school.deleteSchoolCss') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        schoolId: schoolId,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("File deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        $('#logout_redirect_url').on('blur', function() {
            var url = $(this).val();
            if (url != '') {
                try {
                    const newUrl = new URL(url);
                    return newUrl.protocol === 'http:' || newUrl.protocol === 'https:';
                } catch (err) {
                    alert('Invalid URL');
                    $(this).val('');
                    return false;
                }
            }
        });
        function deleteSchoolLoader(schoolId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachId${schoolId}`).addClass('disabled');
                $.ajax({
                    url: "{{ route('backend.school.deleteSchoolLoader') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        schoolId: schoolId,
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("File deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        const toggleAssessmentBlocks = () => {
            const standardOn = $('#assessment_assignment_standard').is(':checked');
            const realqOn = $('#assessment_assignment_realq').is(':checked');

            if (standardOn) {
                $('#standard_assessment_date_fields').show();
            } else {
                $('#standard_assessment_date_fields').hide();
            }

            if (realqOn) {
                $('#realq_assessment_fields').show();
                $('#realq_assessment_fields').find('input[type="date"]').prop('required', true);
                $('#realq_assessment_fields').find('select').prop('required', true);
            } else {
                $('#realq_assessment_fields').hide();
                $('#realq_assessment_fields').find('input[type="date"]').prop('required', false);
                $('#realq_assessment_fields').find('select').prop('required', false);
            }
        };

        $('#assessment_assignment_standard, #assessment_assignment_realq').on('change', toggleAssessmentBlocks);
        toggleAssessmentBlocks();

        (function () {
            const wrapper = document.getElementById('realq-assignment-rows');
            const addBtn = document.getElementById('realqAddAssignmentRow');
            if (!wrapper || !addBtn) return;

            const buildRow = (index) => {
                const div = document.createElement('div');
                div.className = 'realq-assignment-row mb-3';
                div.dataset.index = index;
                div.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <label>Grade</label>
                            <select class="form-control" name="realq_assessment_assigned_grade_id[]">
                                <option value="">-- Select Grade --</option>
                                @foreach($studentGrades as $sGrade)
                                    <option value="{{ $sGrade->id }}">{{ $sGrade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label>Parameters</label>
                            <select class="form-control" name="realq_assessment_assigned_parameters_id[${index}][]" multiple>
                                @foreach($realqParameters as $parameter)
                                    <option value="{{ $parameter->id }}">{{ $parameter->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger realq-assignment-remove realq-assignment-remove-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                return div;
            };

            const refreshRemoveButtons = () => {
                const rows = wrapper.querySelectorAll('.realq-assignment-row');
                rows.forEach((row) => {
                    const btn = row.querySelector('.realq-assignment-remove');
                    if (btn) {
                        btn.disabled = rows.length === 1;
                    }
                });
            };

            addBtn.addEventListener('click', () => {
                const nextIndex = wrapper.querySelectorAll('.realq-assignment-row').length;
                wrapper.appendChild(buildRow(nextIndex));
                refreshRemoveButtons();
            });

            wrapper.addEventListener('click', (event) => {
                const target = event.target.closest('.realq-assignment-remove');
                if (!target) return;
                const row = target.closest('.realq-assignment-row');
                if (row) {
                    row.remove();
                    refreshRemoveButtons();
                }
            });

            refreshRemoveButtons();
        })();

        window.addEventListener('load', function () {
            var phone = $('#phone');
            if (!phone.length) return;
            var normalize = function (value) {
                return String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            };
            var setCountry = function () {
                var countryData = window.intlTelInputGlobals ? window.intlTelInputGlobals.getCountryData() : [];
                var selectedName = $('#school_country option:selected').text().trim() || phone.data('country-name');
                var country = countryData.find(function (item) {
                    return normalize(item.name) === normalize(selectedName);
                });
                if (!country) return;
                phone.intlTelInput('setCountry', country.iso2);
                phone.closest('.iti').find('.iti__selected-flag').css('pointer-events', 'none').attr('tabindex', '-1');
                phone.closest('.iti').find('.iti__arrow').hide();
            };
            [0, 100, 500, 1000].forEach(function (delay) { window.setTimeout(setCountry, delay); });
            $('#school_country').on('change', setCountry);
        });
    </script>
@endsection

