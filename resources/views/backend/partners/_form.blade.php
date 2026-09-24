@php
    $isEdit = isset($partner);
    $ignoreId = $isEdit ? $partner->id : '';
    $uniqueUrl = route('backend.partners.checkUnique');
@endphp

<div class="form-group row">
    <label for="name" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-10">
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $partner->name ?? '') }}" required>
        <div class="invalid-feedback d-block" id="name-error">@error('name') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-10">
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $partner->email ?? '') }}" required>
        <div class="invalid-feedback d-block" id="email-error">@error('email') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="username" class="col-sm-2 col-form-label">Username</label>
    <div class="col-sm-10">
        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $partner->username ?? '') }}" required>
        <div class="invalid-feedback d-block" id="username-error">@error('username') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label">Password</label>
    <div class="col-sm-10">
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ $isEdit ? 'Leave blank to keep current password' : '' }}">
        <small class="text-muted">{{ $isEdit ? 'Optional. If left blank, the current password stays unchanged.' : 'Optional. Leave blank to auto-generate a password.' }}</small>
        <div class="invalid-feedback d-block" id="password-error">@error('password') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="mobile" class="col-sm-2 col-form-label">Mobile</label>
    <div class="col-sm-10">
        <input type="text" name="mobile" id="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $partner->mobile ?? '') }}" required>
        <div class="invalid-feedback d-block" id="mobile-error">@error('mobile') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="country" class="col-sm-2 col-form-label">Country</label>
    <div class="col-sm-10">
    <select name="country" id="country" class="form-control @error('country') is-invalid @enderror" required>
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}" {{ (string) old('country', $partner->country_id ?? '') === (string) $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback d-block" id="country-error">@error('country') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="partnership_start_date" class="col-sm-2 col-form-label">Partnership Start Date</label>
    <div class="col-sm-10">
        <input type="date" name="partnership_start_date" id="partnership_start_date" class="form-control @error('partnership_start_date') is-invalid @enderror" value="{{ old('partnership_start_date', !empty($partner->partnership_start_date) ? \Illuminate\Support\Carbon::parse($partner->partnership_start_date)->format('Y-m-d') : '') }}" required>
        <div class="invalid-feedback d-block">@error('partnership_start_date') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="partnership_end_date" class="col-sm-2 col-form-label">Partnership End Date</label>
    <div class="col-sm-10">
        <input type="date" name="partnership_end_date" id="partnership_end_date" class="form-control @error('partnership_end_date') is-invalid @enderror" value="{{ old('partnership_end_date', !empty($partner->partnership_end_date) ? \Illuminate\Support\Carbon::parse($partner->partnership_end_date)->format('Y-m-d') : '') }}" required>
        <div class="invalid-feedback d-block">@error('partnership_end_date') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="currency_id" class="col-sm-2 col-form-label">Currency</label>
    <div class="col-sm-10">
        <select name="currency_id" id="currency_id" class="form-control @error('currency_id') is-invalid @enderror" required>
            <option value="">Select Currency</option>
            @foreach($currencies as $currency)
                <option value="{{ $currency->id }}" {{ (string) old('currency_id', $partner->currency_id ?? '') === (string) $currency->id ? 'selected' : '' }}>{{ $currency->code }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-block">@error('currency_id') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="no_of_license_purchased" class="col-sm-2 col-form-label">No of License Purchase</label>
    <div class="col-sm-10">
        <input type="number" min="0" step="1" name="no_of_license_purchased" id="no_of_license_purchased" class="form-control @error('no_of_license_purchased') is-invalid @enderror" value="{{ old('no_of_license_purchased', $partner->no_of_license_purchased ?? 0) }}" required>
        <div class="invalid-feedback d-block" id="no_of_license_purchased-error">@error('no_of_license_purchased') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <label for="allow_add_trainers" class="col-sm-2 col-form-label">Trainers Allowed</label>
    <div class="col-sm-10">
        <input type="number" min="0" step="1" name="allow_add_trainers" id="allow_add_trainers" class="form-control @error('allow_add_trainers') is-invalid @enderror" value="{{ old('allow_add_trainers', $partner->allow_add_trainers ?? 0) }}" required>
        <small class="text-muted">Set how many trainers this partner is allowed to add. Use 0 to disable.</small>
        <div class="invalid-feedback d-block" id="allow_add_trainers-error">@error('allow_add_trainers') {{ $message }} @enderror</div>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-10 offset-sm-2">
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Update Partner' : 'Create Partner' }}
        </button>
        <a href="{{ route('backend.partners.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>
