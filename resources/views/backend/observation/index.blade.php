@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Observations Listing</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Observations Listing</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if (session()->has('success'))
                    <div class="alert alert-success" style="text-align: center;">
                        {{ session()->get('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('backend.observation.create') }}" class="btn btn-primary">Add Observation</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Observation Name</th>
                                    <th>Category</th>
                                    <th>Icon</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($observation_list as $observation)
                                    @php $isUsed = $usedObservationIds->contains($observation->id); @endphp
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $observation->name }}</td>
                                        <td>
                                            @if($observation->category === 'skill')
                                                <span class="badge badge-primary">Skill</span>
                                            @elseif($observation->category === 'mindset')
                                                <span class="badge badge-success">Mindset</span>
                                            @else
                                                <span class="badge badge-secondary">—</span>
                                            @endif
                                        </td>
                                        <td><img src="{{ asset('observations/' . $observation->icon) }}" alt="icon" width="50" height="50"></td>
                                        <td class="d-flex" style="gap: 12px;">
                                            <a href="{{ route('backend.observation.edit', $observation->id) }}"
                                                class="btn btn-success"><i class="fas fa-edit"></i></a>
                                            @if($isUsed)
                                                <button type="button" class="btn btn-danger" disabled
                                                    title="Cannot delete: this observation has already been recorded for one or more students.">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('backend.observation.delete', $observation->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="deleteObservation(event, this)" type="button"
                                                        class="btn btn-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>

    <script>
        function deleteObservation(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            swal({
                    title: "Are you sure you want to delete this observation?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Your Observation has been deleted!", {
                            icon: "success",
                        });
                        form.submit();
                    } else {
                        swal("Great! Observation records are safe.");
                    }
                });
        }
    </script>
@endsection
