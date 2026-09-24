@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper" style="padding: 0px 20px;">
    <!-- Content Header (Page header) -->
    <!-- <div class="pageTitle">
        <h2>{{ __('admin/content.content_list_trainer') }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/content.content_list_trainer') }}</li>
        </ol>
    </div> -->
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        @if (session()->has('success'))
        <div class="alert alert-success" style="text-align: center;">
            {{ session()->get('success') }}
        </div>
        @endif

        @if (session()->has('update_success'))
        <div class="alert alert-success" style="text-align: center;">
            {{ session()->get('update_success') }}
        </div>
        @endif

        @if (session()->has('delete_success'))
        <div class="alert alert-success" style="text-align: center;">
            {{ session()->get('delete_success') }}
        </div>
        @endif

       

        <div class="CustomBody">
            <div class="row">
                @forelse ($allGrade as $grade)
                    <div class="col-md-3">
                        <div class="InnerContentCard h-auto">
                            
                            <div class="ContentBody TrainerContentBody p-0">

                                <img src="{{ asset('image/level/trainer/' . $grade->image) }}" class="trainer-level-image card-img-top" alt="..." >

                                <div class="p-3 text-center trainer-content-info">
                                    <h5 class="card-title text-center w-100 d-block">{{ $grade->grade }}
                                    @if($hasAccess && in_array($grade->id, explode(",", $trainerGradeId)))
                                        <span class="content-ninja-verified fas fa-circle" title="Content available" aria-label="Content available"></span>
                                    @endif
                                    </h5>

                                    <div>
                                    @if($hasAccess && in_array($grade->id, explode(",", $trainerGradeId)))
                                    <a href="{{ route('trainer.contentlist.streamlist', $grade->id) }}" class="btn ThemeBtnContent btn-primary">View Content</a>
                                    @else 
                                    <button class="btn ThemeBtnContent btn-primary get_access_btn">View Content</button>
                                    @endif
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {

    $(document).on('click', '.get_access_btn', function() {
        swal("You don't have access to this content yet. Please contact system administrator.", {
            button: "Ok",
        });
    });

    // Get all child elements of the parent
    const childElements = document.querySelectorAll('.CustomBody .InnerContentCard');

    // Function to get a random color from the predefined set
    function getRandomColor() {
        const colorsArray = getComputedStyle(document.documentElement).getPropertyValue('--colors').trim();
        const colors = colorsArray.split(', ');
        const randomIndex = Math.floor(Math.random() * colors.length);
        return colors[randomIndex];
    }

    // Assign a random color to each child element
    childElements.forEach((child) => {
        child.style.setProperty('--random-color', getRandomColor());
    });
    
    $("#contentListDatatable").dataTable({
        "searching": true
    });

    //Get a reference to the new datatable
    var table = $('#contentListDatatable').DataTable();

    $("#contentListDatatable_filter.group-filter").append($("#group"));
    // $("#contentListDatatable_filter.stream-filter").append($("#stream"));

    // $("#contentListDatatable_filter.dataTables_filter").append($("#testCategory"));

    var groupIndex = 0;
    var streamIndex = 0;
    $("#contentListDatatable th").each(function(i) {
        console.log($($(this)).html());
        if ($($(this)).html() == "Select Age Group") {

            groupIndex = i;
            return true;
        } else if ($($(this)).html() == "Stream") {
            streamIndex = i;
            return true;
        }
    });

    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var groupItem = $('#group').val();
            var streamItem = $('#stream').val();

            if (groupItem != '') {
                var group = data[groupIndex];
                if (groupItem === "" || group.includes(groupItem)) {
                    return true;
                }
            } else if (streamItem != '') {
                var stream = data[streamIndex];
                if (streamItem === "" || stream.includes(streamItem)) {
                    return true;
                }
            } else {
                return true;
            }





        }
    );


    $("#group").change(function(e) {
        table.draw();
    });
    $("#stream").change(function(e) {
        table.draw();
    });

    table.draw();

});
</script>
@endsection