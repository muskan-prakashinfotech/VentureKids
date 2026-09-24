@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>{{ __('admin/content.quiz') }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/content.quiz') }}</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <div class="card-title col-md-8">{{$quizDetail->title}} ({{$quizDetail->level}} - {{$quizDetail->session}})</div>
                <div class="inputs-group col-md-2">
                    <select class="form-control" name="school_id" id="school_id" wire:model='school'>
                        <option value="">---Select School---</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>    
                </div>
                <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>
            <!-- /.card-header -->

            <div class="card-body table-responsive">
                <div class="row">
                    <div class="col-md-12">  
                        @if($quizResult)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">School Name</th>
                                    <th scope="col">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quizResult as $res)
                                <tr>
                                    <td> {{ $res['name']}} </td>
                                    <td> {{ $res['school']}} </td>
                                    <td> {{ $res['score']}} </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else 
                        No Data Found 
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
    $('#school_id').change(function(){
        $.ajax({
            url: "{{ route('backend.quizResultFilter') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'quizId' : "{{ $quizId }}",
                'school_id' : $(this).val(),
                'school_name' : $(this).find("option:selected").text()
            },
            method: "POST",
            beforeSend: function() {
                $('#school_id').prop("disabled", true);
            },
            success: function(res) {
                if(! $.isEmptyObject(res)) {
                    var resData;
                    $.each(res, function( key, value ) {
                        resData =  resData + `<tr>
                                        <td>${value.name}</td>
                                        <td>${value.school}</td>
                                        <td>${value.score}</td>
                                    </tr>`;
                    });
                } else {
                    resData = 'No data dound';
                }
                $("tbody").empty();
                $("tbody").append(resData);
                $('#school_id').prop("disabled", false);

            }
        });
    });
</script>


@endsection