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
                        {{-- <h1 class="m-0">{{ __('admin/trainer.add_trainer') }}</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/trainer.add_trainer') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if (session()->has('email_faild'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('email_faild') }}
                    </div>
                @endif

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4>Add Trainer</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('backend.trainerlist.trainerList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                            </div>
                            <form id="trainer-add-form" action="{{ route('backend.storetrainer.storeTrainer') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="inchargename">{{ __('admin/trainer.trainer_name') }}</label>
                                        <input type="text" class="form-control" id="inchargename" placeholder="" name="trainer_name" value="{{ old('trainer_name') }}">
                                        @error('trainer_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="inchargeemail">{{ __('admin/trainer.trainer_email_id') }}</label>
                                        <input type="text" class="form-control" id="inchargeemail" placeholder="" name="email" value="{{ old('email') }}">
                                        @error('email')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Trainer Level</label>
                                        @foreach ($trainerLevel as $level)
                                            <div class="form-check">
                                                <input name="grade_id[]" class="form-check-input trainer-level-checkbox" type="checkbox" value="{{ $level->id }}" id="grade_id_{{ $level->id }}" @if (is_array(old('grade_id')) && in_array($level->id, old('grade_id'))) checked @endif >
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    {{ $level->grade }}
                                                </label>
                                            </div>
                                        @endforeach
                                        @error('grade_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                        <strong class="text-danger d-none" id="grade-required-error">Please select at least one Trainer Level.</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="inchargecontact">{{ __('admin/trainer.trainer_fee') }}</label>
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                @if (isPartnerUser())
                                                    <input type="hidden" name="currency" value="{{ $partnerCurrency }}">
                                                    <input type="text" class="form-control" value="{{ strtoupper($partnerCurrency === 'dollar' ? 'usd' : $partnerCurrency) }}" disabled>
                                                @else
                                                    <select class="form-control" name="currency">
                                                        <option value="inr" @if (old('currency') == 'inr') selected @endif>{{ __('admin/trainer.currency_inr') }}</option>
                                                        <option value="dollar" @if (old('currency') == 'dollar') selected @endif>USD</option>
                                                        <option value="sgd" @if (old('currency') == 'sgd') selected @endif>{{ __('admin/trainer.currency_doller') }}</option>
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" class="form-control" placeholder="Fee" name="trainer_fee" value="{{ old('trainer_fee') }}">
                                                @error('trainer_fee')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin/trainer.contact_no') }}</label>
                                        <input type="tel" class="form-control" placeholder="" name="contact_no" id="phone" data-country-name="{{ $partnerCountryName }}" value="{{ old('contact_no') }}">
                                        @error('contact_no')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin/trainer.trainer_city') }}</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="schoolname" name="city" placeholder="City" value="{{ old('city') }}">
                                        @error('city')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="joiningdate">{{ __('admin/trainer.joining_date') }}</label>
                                        <input type="date" class="form-control" id="joiningdate" name="join_date" value="{{ old('join_date') }}" required>
                                        @error('join_date')
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
                                            <select class="form-control" name="country">
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
                                </div>
                                <div class="card-footer">
                                    <button type="submit"
                                        class="btn btn-primary">{{ __('admin/trainer.submit') }}</button>
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
        $(function () {
            $('#trainer-add-form').on('submit', function (event) {
                var valid = $('.trainer-level-checkbox:checked').length > 0;
                $('#grade-required-error').toggleClass('d-none', valid);
                if (!valid) event.preventDefault();
            });
        });
        window.addEventListener('load', function () {
            var phone = $('#phone');
            if (!phone.length || !{{ isPartnerUser() ? 'true' : 'false' }}) return;
            var configured = false;
            var configurePhone = function () {
                if (configured) return;
                var data = window.intlTelInputGlobals ? window.intlTelInputGlobals.getCountryData() : [];
                var name = String(phone.data('country-name') || '').toLowerCase().trim();
                var aliases = {
                    kazakhstan: 'kz',
                    'republicofkazakhstan': 'kz',
                    india: 'in',
                    singapore: 'sg',
                    'unitedstates': 'us',
                    'unitedstatesofamerica': 'us',
                    'unitedkingdom': 'gb'
                };
                var normalized = name.replace(/[^a-z0-9]/g, '');
                var iso = aliases[normalized];
                var country = iso ? data.find(function (item) { return item.iso2 === iso; }) : data.find(function (item) {
                    var itemName = item.name.toLowerCase();
                    return itemName === name || itemName.indexOf(name) !== -1 || name.indexOf(itemName) !== -1;
                });
                if (!country) return;
                var dialPrefix = '+' + country.dialCode;
                var currentValue = String(phone.val() || '').trim();
                if (currentValue.indexOf(dialPrefix) === 0) {
                    phone.val(currentValue.slice(dialPrefix.length).replace(/^[\s-]+/, ''));
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
                configured = true;
            };
            window.setTimeout(configurePhone, 1000);
        });
    </script>
@endsection
