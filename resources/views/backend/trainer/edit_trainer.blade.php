@extends('backend.layouts.app')

@section('content')
    @php
        $partnerCountryName = optional($countries->firstWhere('id', $selectedCountryId))->name ?? '';
    @endphp
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">{{ __('admin/trainer.edit_trainer') }} </h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/trainer.edit_trainer') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3>Edit Trainer</h3>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('backend.trainerlist.trainerList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (session()->has('email_faild'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('email_faild') }}
                    </div>
                @endif

                @if (session()->has('confirm_password_faild'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('confirm_password_faild') }}
                    </div>
                @endif

                @if (session()->has('old_password_faild'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('old_password_faild') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            {{-- <div class="card-header"></div> --}}
                            <form action="{{ route('backend.updatetrainer.updateTrainer') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">

                                    <input type="hidden" name="id" value="{{ $trainer['id'] }}">
                                    <input type="hidden" name="user_id" value="{{ $trainer['user_id'] }}">

                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin/trainer.trainer_name') }}</label>
                                        <input type="text" class="form-control" id="schoolname" placeholder=""
                                            name="trainer_name" value="{{ $trainer['trainer_name'] }}" required>
                                        @error('trainer_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Trainer Level</label>
                                        @foreach ($trainerLevel as $level)
                                            <div class="form-check">
                                                <input name="grade_id[]" class="form-check-input trainer-level-checkbox" type="checkbox" value="{{ $level->id }}" id="grade_id_{{ $level->id }}" @if (in_array($level->id, explode(',',$trainer['grade_id']))) checked @endif >
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    {{ $level->grade }}
                                                </label>
                                            </div>
                                        @endforeach
                                        @error('grade_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin/trainer.trainer_address') }}</label>
                                        <textarea class="form-control" id="address" cols="30" rows="4" name="address">{{ $trainer['address'] }}</textarea>

                                        @error('address')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="trainercity">{{ __('admin/trainer.trainer_city') }}</label>
                                        <input type="text" class="form-control" id="trainercity" placeholder=""
                                            name="city" value="{{ $trainer['city'] }}" required>
                                        @error('city')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="inchargename">{{ __('admin/trainer.trainer_country') }}</label>
                                        @if (isPartnerUser())
                                            <input type="hidden" name="country" value="{{ $selectedCountryId }}">
                                            <select class="form-control" disabled>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <select class="form-control" name="country" required>
                                                <option value="">-- Select Country --</option>
                                                @foreach ($countries as $country)
                                                    <option @if ($trainer['country_id'] == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        @error('country')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="inchargename">{{ __('admin/trainer.joining_date') }}</label>
                                        <input type="date" class="form-control" id="joiningdate" placeholder=""
                                            name="join_date" value="{{ $trainer['join_date'] }}" required>
                                        @error('join_date')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="official_email_id">{{ __('admin/trainer.official_emailId') }}</label>
                                        <input type="text" class="form-control" id="official_email_id" placeholder=""
                                            name="official_email_id" value="{{ $trainer['official_email_id'] }}" required>
                                        @error('official_email_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="partnername">{{ __('admin/trainer.contact_no') }} </label>
                                        <input type="text" class="form-control" id="phone" placeholder=""
                                            name="contact_no" value="{{ $trainer['contact_no'] }}" data-country-name="{{ $partnerCountryName }}" required>
                                        @error('contact_no')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                </div>

                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="card card-warning">
                            {{-- <div class="card-header"></div> --}}
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="exampleInputFile">{{ __('admin/trainer.identity_proof') }}</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    @error('image')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($trainer['image'])
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$trainer['image']}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/' .$trainer['image']) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerProfileImage({{$trainer['id']}})" class="btn btn-danger btn-sm" id="attachId{{$trainer['id']}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="mode">{{ __('admin/trainer.mode') }}</label>
                                    <select class="form-control" name="mode" required>
                                        <option value="1" <?php if ($trainer['mode'] == 1) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.mode_online') }}
                                        </option>
                                        <option value="2" <?php if ($trainer['mode'] == 2) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.mode_offline') }}
                                        </option>
                                        <option value="3" <?php if ($trainer['mode'] == 3) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.mode_hybrid') }}
                                        </option>
                                    </select>
                                    @error('mode')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="type">{{ __('admin/trainer.type') }}</label>
                                    <select class="form-control" name="type" required>
                                        <option value="1" <?php if ($trainer['type'] == 1) {
                                            echo 'selected';
                                        } ?>>Full Time</option>
                                        <option value="2" <?php if ($trainer['type'] == 2) {
                                            echo 'selected';
                                        } ?>>Part Time
                                        </option>
                                        <option value="3" <?php if ($trainer['type'] == 3) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.type_industry') }}
                                        </option>
                                        <option value="3" <?php if ($trainer['type'] == 4) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.type_expert') }}
                                        </option>
                                        <option value="3" <?php if ($trainer['type'] == 5) {
                                            echo 'selected';
                                        } ?>>{{ __('admin/trainer.type_other') }}
                                        </option>
                                    </select>
                                    @error('type')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="inchargecontact">{{ __('admin/trainer.trainer_fee') }}</label>
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            @if (isPartnerUser())
                                                <input type="hidden" name="currency" value="{{ $partnerCurrency }}">
                                                <input type="text" class="form-control" value="{{ strtoupper($partnerCurrency === 'dollar' ? 'usd' : $partnerCurrency) }}" disabled>
                                            @else
                                            <select class="form-control" name="currency" required>
                                                <option value="inr" @if (old('currency', $trainer['currency']) == 'inr') selected @endif>
                                                    {{ __('admin/trainer.currency_inr') }}</option>
                                                <option value="dollar" @if (old('currency', $trainer['currency']) == 'dollar') selected @endif>
                                                    USD</option>
                                                <option value="sgd" @if (old('currency', $trainer['currency']) == 'sgd') selected @endif>
                                                    {{ __('admin/trainer.currency_doller') }}
                                                </option>
                                            </select>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" placeholder="Fee"
                                                name="trainer_fee"
                                                value="{{ old('trainer_fee', $trainer['trainer_fee']) }}">
                                            @error('trainer_fee')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin/trainer.noofhours_perweek') }}</label>
                                    <input type="text" class="form-control" name="no_of_hour_per_week"
                                        value="{{ $trainer['no_of_hour_per_week'] }}" required>
                                    @error('no_of_hour_per_week')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">{{ __('admin/trainer.status') }}</label>
                                    <select class="form-control" name="status" required>
                                        <option value="2" <?php if ($trainer['user']['suspend'] == 2) { echo 'selected'; } ?>>
                                            Active
                                        </option>
                                        <option value="1" <?php if ($trainer['user']['suspend'] == 1) { echo 'selected'; } ?>>
                                            Deactive</option>
                                    </select>
                                    @error('status')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Upload Attachment</label><br>
                                    <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*">
                                    @error('attachment')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($trainer['attachment'])
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$trainer['attachment']}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/attachment/' .$trainer['attachment']) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerAttachment({{$trainer['id']}})" class="btn btn-danger btn-sm" id="attachmentId{{$trainer['id']}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Upload CV</label><br>
                                    <input type="file" class="form-control" id="cv" name="cv" accept=".doc,.docx,.pdf" />
                                    @error('cv')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($trainer['cv'])
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$trainer['cv']}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/cv/' .$trainer['cv']) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerCV({{$trainer['id']}})" class="btn btn-danger btn-sm" id="cvId{{$trainer['id']}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="assessmentdone"></label>
                                            <input type="checkbox" name="assessment_done" class="checkBox"
                                                id="assessmentdone" value="1" <?php if ($trainer['assessment_done'] == 1) {
                                                    echo 'checked';
                                                } ?>> Assessment Done
                                        </div>
                                        <div class="form-group">
                                            <label for="demovideo"></label>
                                            <input type="checkbox" name="demo_video" class="checkBox" id="demovideo"
                                                value="2" <?php if ($trainer['demo_video'] == 1) {
                                                    echo 'checked';
                                                } ?>> Demo video uploaded
                                        </div>
                                        <div class="form-group">
                                            <label for="traininghour"></label>
                                            <input type="checkbox" name="training_hour" class="checkBox"
                                                id="traininghour" value="3" <?php if ($trainer['training_hour'] == 1) {
                                                    echo 'checked';
                                                } ?>> Conducted Trial
                                            Session
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title font-weight-bold">Reset Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="newpassword">New Password</label>
                                            <div class="input-group mb-3">
                                                <input type="password" class="form-control" id="newpassword" name="new_password">
                                                <div class="input-group-append viewpassword" data-id="newpassword">
                                                    <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                </div>
                                            </div>
                                            @error('new_password')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="confirmpassword">Confirm Password</label>
                                            <div class="input-group mb-3">
                                                <input type="password" class="form-control" id="confirmpassword" name="confirm_password">
                                                <div class="input-group-append viewpassword" data-id="confirmpassword">
                                                    <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                </div>
                                            </div>
                                            @error('confirm_password')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ __('admin/trainer.submit') }}</button>
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel & Back</a>
                            </div>

                            </form>
                        </div>

                    </div>

                </div>


            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>


    <script>
        $(function() {


            $('.checkBox').on("change", function() {

                if ($(this).is(':checked')) {
                    var info = $(this).val();
                    var action = 'checked';
                } else {
                    var info = $(this).val();
                    var action = 'unchecked';
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('backend.trainer-checkinfo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        "info": info,
                        "id": "{{ $trainer['id'] }}",
                        "action": action,
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        //window.location.reload();
                    },
                });





            });

            $("#image, #attachment").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });

            $("#cv").change(function () {
                var fileExtension = ['doc', 'docx', 'pdf'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only DOC, DOCX or PDF files are allowed.");
                    $(this).val(''); 
                }
            });

        });

        function deleteTrainerProfileImage(trainerId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachId${trainerId}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('backend.deleteTrainerProfileImage') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trainerId: trainerId,
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

        function deleteTrainerAttachment(trainerId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#attachmentId${trainerId}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('backend.deleteTrainerAttachment') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trainerId: trainerId,
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

        function deleteTrainerCV(trainerId) {
            if(confirm("Are you sure want to Delete this File?")) {
                $(`#cvId${trainerId}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('backend.deleteTrainerCV') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trainerId: trainerId,
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
    </script>
    @if (isPartnerUser())
    <script>
        window.addEventListener('load', function () {
            var phone = $('#phone');
            if (!phone.length) return;
            var countryData = window.intlTelInputGlobals ? window.intlTelInputGlobals.getCountryData() : [];
            var countryName = String(phone.data('country-name') || '').toLowerCase().trim();
            var aliases = {
                kazakhstan: 'kz',
                'republicofkazakhstan': 'kz',
                india: 'in',
                singapore: 'sg',
                'unitedstates': 'us',
                'unitedstatesofamerica': 'us',
                'unitedkingdom': 'gb'
            };
            var normalized = countryName.replace(/[^a-z0-9]/g, '');
            var iso = aliases[normalized];
            var country = iso ? countryData.find(function (item) { return item.iso2 === iso; }) : countryData.find(function (item) {
                var itemName = item.name.toLowerCase();
                return itemName === countryName || itemName.indexOf(countryName) !== -1 || countryName.indexOf(itemName) !== -1;
            });
            if (!country) return;
            var dialPrefix = '+' + country.dialCode;
            var currentValue = String(phone.val() || '').trim();
            if (currentValue.indexOf(dialPrefix) === 0) {
                phone.val(currentValue.slice(dialPrefix.length).replace(/^\s+/, ''));
            }
            if (phone.data('plugin_intlTelInput')) phone.intlTelInput('destroy');
            phone.intlTelInput({
                initialCountry: country.iso2,
                allowDropdown: true,
                separateDialCode: false,
                autoPlaceholder: 'polite',
                formatOnDisplay: true,
                utilsScript: "../dist/js/utils.js"
            });
            phone.closest('.iti').find('.iti__flag-container').css('pointer-events', 'none');
        });
    </script>
    @endif
@endsection
