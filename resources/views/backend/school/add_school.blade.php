@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                @if (session()->has('email_faild'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('email_faild') }}
                    </div>
                @endif

                @if (Session::has('message'))
                    <div class="alert alert-success">
                        {{ Session::get('message') }}
                    </div>
                @endif

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('admin.add_school') }}</h3>
                                <div class="card-tools">
                                    <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                </div>
                            </div>

                            <form action="{{ route('backend.schoolstore.schoolStore') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @php
                                    $isPartner = isPartnerUser();
                                    $partnerCountryId = partnerCountryId();
                                    $partnerCountryName = optional($countries->firstWhere('id', $partnerCountryId))->name ?? '';
                                @endphp
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin.school_name') }}</label>
                                        <input type="text"
                                            class="form-control @error('school_name') is-invalid @enderror" id="schoolname"
                                            name="school_name" placeholder="School Name" required>
                                        @error('school_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="schoolname">School Contact Person Full Name</label>
                                        <input type="text"
                                            class="form-control @error('principle_name') is-invalid @enderror"
                                            id="address" placeholder="School Contact Person Full Name" name="principle_name" required>
                                        @error('principle_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="yearestablished">School Official Email ID</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="yearestablished" placeholder="School Official Email ID" 
                                            name="email" name="School Official Email ID" required>
                                        @error('email')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="yearestablished">Offical Contact Number</label>
                                        <input type="tel"
                                            class="form-control @error('contact_number') is-invalid @enderror"
                                            id="phone" placeholder="Offical Contact Number"
                                            data-country-name="{{ $partnerCountryName }}"
                                            name="contact_number" >
                                        @error('contact_number')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="inchargename">Country</label>
                                        @if ($isPartner)
                                            <select class="form-control" name="country" id="school_country" disabled>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" {{ (string) $partnerCountryId === (string) $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="country" value="{{ $partnerCountryId }}">
                                            <small class="text-muted">Your country is locked by Super Admin.</small>
                                        @else
                                            <select class="form-control" name="country" id="school_country" required>
                                                <option value="">-- Select Country --</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{$country->id}}">{{$country->name}}</option>
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
                                            id="schoolname" name="city" placeholder="City" required>
                                        @error('city')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="inchargecontact">Fee Per Student</label>
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                @if ($isPartner)
                                                    <input type="hidden" name="currency_type" value="{{ $partnerCurrencyType }}">
                                                    <input type="text" class="form-control" value="{{ strtoupper($partnerCurrencyType) }}" disabled>
                                                @else
                                                    <select class="form-control" name="currency_type" id="currency_type" required>
                                                        <option value="inr">INR</option>
                                                        <option value="sgd">SGD</option>
                                                        <option value="usd">USD</option>
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number" class="form-control @error('fee_per_student') is-invalid @enderror"
                                                id="feeperstudent" placeholder="Fee Per Student" name="fee_per_student" >
                                                @error('fee_per_student')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="schoolname">{{ $isPartner ? 'Number of License' : __('admin.number_of_student') }}</label>
                                        <input type="number"
                                            class="form-control @error('number_of_student') is-invalid @enderror"
                                            placeholder="{{ $isPartner ? 'Number of License' : 'Number of Students' }}" name="number_of_student" value="" required>
                                        @error('number_of_student')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="schoolname">Course Start Date</label>
                                        <input type="date" class="form-control" id="course_start_date" name="course_start_date" required>
                                        @error('course_start_date')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="schoolname">Course Expiration Date</label>
                                        <input type="date" class="form-control" name="course_end_date" id="course_end_date" required>
                                        @error('course_end_date')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="batch_name">School Batches</label>
                                        <div class="batch-div"></div>
                                        <div class="addMore mt-0">
                                            <a href="javscript:void(0);" id="addBatchLink" onclick="addBatch()">Add Batch</a>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="domain">Add Domain</label>
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control @error('school_domain') is-invalid @enderror"
                                                    id="schooldomain" placeholder="School domain" name="school_domain" required rule-domain-validate="true">
                                                @error('school_domain')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="subDomainText">{{ config('tenancy.sub_domain') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    @if (!$isPartner)
                                    <div class="form-group">
                                        <label for="logout_redirect_url">Logout Redirect URL</label>
                                        <input type="text" class="form-control @error('logout_redirect_url') is-invalid @enderror"
                                            id="logout_redirect_url" placeholder="Logout Redirect URL" name="logout_redirect_url">
                                        @error('logout_redirect_url')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    @endif
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
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
                if (!country) return false;
                phone.intlTelInput('setCountry', country.iso2);
                phone.closest('.iti').find('.iti__selected-flag').css('pointer-events', 'none').attr('tabindex', '-1');
                phone.closest('.iti').find('.iti__arrow').hide();
                return true;
            };
            [0, 100, 500, 1000].forEach(function (delay) { window.setTimeout(setCountry, delay); });
            $('#school_country').on('change', setCountry);
        });

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

        jQuery.validator.addMethod("rule-domain-validate", function(value, element) {
            return this.optional(element) || (/^(http(s)?\/\/:)?(www\.)?[a-zA-Z\-]{3,}(\.(com|net|org))?$/.test(value));
        }, "Please enter valid domain");
    </script>
@endsection
