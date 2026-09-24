@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Old Grades</th>
                                            <th>New Grades</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($result as $row)
                                            <tr>
                                                <td>{{ $row['student_name'] }}</td>
                                                <td>{{ implode(', ', $row['old_grades']) }}</td>
                                                <td>{{ implode(', ', $row['new_grades']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
