@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Skills Listing</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Skills Listing</li>
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
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('backend.skill.create') }}" class="btn btn-primary">Add Skill</a>
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
                                    <th>Skill Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($skill_list as $skill)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $skill->skill_name }}</td>
                                        <td class="d-flex" style="gap: 12px;">
                                            <a href="{{ route('backend.skill.edit', $skill->id) }}"
                                                class="btn btn-success"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('backend.skill.delete', $skill->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="deleteSkill(event, this)" type="button"
                                                    class="btn btn-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
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
        function deleteSkill(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            swal({
                    title: "Are you sure?",
                    text: "Skill data will also get deleted from student observation records. Are you sure want to delete this Skill!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Your Skill has been deleted!", {
                            icon: "success",
                        });
                        form.submit();
                    } else {
                        swal("Great! Skill records are safe.");
                    }
                });
        }
    </script>
@endsection
