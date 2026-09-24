@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="mb-5 container-fluid ">
            @include('backend.external_session.form', ['route' => route('backend.external_session.update', $external_session->id), 'method' => 'PUT', 'external_session' => $external_session])
        </div>
    </section>
</div>
@endsection
