@extends('backend.layouts.app')

@section('title') {{ $title }} @endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Partner</h3>
                </div>
                <form action="{{ route('backend.partners.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('backend.partners._form')
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
(function () {
    var uniqueUrl = @json(route('backend.partners.checkUnique'));
    var ignoreId = '';
    var timers = {};

    function setFieldState(field, message) {
        var input = document.getElementById(field);
        var error = document.getElementById(field + '-error');
        if (!input || !error) {
            return;
        }
        if (message) {
            input.classList.add('is-invalid');
            error.textContent = message;
        } else {
            input.classList.remove('is-invalid');
            error.textContent = '';
        }
    }

    function checkUnique(field) {
        var input = document.getElementById(field);
        if (!input) {
            return;
        }

        var value = input.value.trim();
        if (!value) {
            setFieldState(field, '');
            return;
        }

        if (timers[field]) {
            clearTimeout(timers[field]);
        }

        timers[field] = setTimeout(function () {
            $.getJSON(uniqueUrl, {
                field: field,
                value: value,
                ignore_id: ignoreId
            }).done(function (response) {
                setFieldState(field, response.exists ? response.message : '');
            }).fail(function () {
                setFieldState(field, 'Unable to validate right now.');
            });
        }, 350);
    }

    ['email', 'username'].forEach(function (field) {
        var input = document.getElementById(field);
        if (!input) {
            return;
        }
        input.addEventListener('input', function () {
            checkUnique(field);
        });
        input.addEventListener('blur', function () {
            checkUnique(field);
        });
    });
})();
</script>
@endsection