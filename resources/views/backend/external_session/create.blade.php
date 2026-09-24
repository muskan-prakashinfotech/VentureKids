@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="mb-5 container-fluid ">
            @include('backend.external_session.form', [
                'route' => route('backend.external_session.store'),
                'method' => 'POST',
                'external_session' => $form_session ?? null
            ])
        </div>
    </section>
</div>
@endsection
