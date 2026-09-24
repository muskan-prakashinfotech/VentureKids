@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">{{ __('admin/event.event_add') }}</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/event.event_add') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ __('admin/event.event_add') }}</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('backend.eventlist.eventlist') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="addEventForm" action="{{ route('backend.eventstore.eventstore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                {{-- <div class="card-header"></div> --}}
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="inchargename">{{ __('admin/event.event_name') }}</label>
                                        <input type="text" class="form-control @error('event_name') is-invalid @enderror"
                                            id="inchargename" placeholder="Event name" name="event_name" required>
                                        @error('event_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="eventimages">{{ __('admin/event.event_image') }}</label>
                                        <input type="file"
                                            class="form-control @error('event_image') is-invalid @enderror" id="eventimage"
                                            name="event_image" accept=".jpg,.jpeg,.png" required>
                                        @error('event_image')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                        <p>
                                            <mark class="d-block"><b>Note:</b> Only JPG, JPEG or PNG files are allowed. Image should be 1920*500 pixel</mark>
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label for="eventstart">{{ __('admin/event.event_date') }}</label>
                                        <input type="date"
                                            class="form-control  @error('event_date') is-invalid @enderror" id="eventstart"
                                            name="event_date" required>
                                        @error('event_date')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="eventend">{{ __('admin/event.submit_entry') }}</label>
                                        <input type="date"
                                            class="form-control  @error('event_last_date') is-invalid @enderror"
                                            id="eventend" name="event_last_date" required>
                                        @error('event_last_date')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="eventend">{{ __('admin/event.event_address') }}</label>
                                        <input type="text"
                                            class="form-control  @error('event_address') is-invalid @enderror"
                                            id="eventend" name="event_address" placeholder="address">
                                        @error('event_address')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="visibility_type">{{ __('Visibility') }}</label><br>
                                        @if (isPartnerUser())
                                            <input type="hidden" name="visibility_type" value="2">
                                            <input type="hidden" name="country" value="{{ optional($partnerCountry)->id }}">
                                            <div class="alert alert-info mb-0">
                                                Country-Specific only
                                                @if($partnerCountry)
                                                    for <strong>{{ $partnerCountry->name }}</strong>
                                                @endif
                                            </div>
                                        @else
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="visibility_type" id="global" value="1" checked>
                                                <label class="form-check-label" for="global">Global/Worldwide</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="visibility_type" id="country-specific" value="2">
                                                <label class="form-check-label" for="country-specific">Country-Specific</label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group" id="country-dropdown" style="display: none;">
                                        <label for="eventend">{{ __('admin/event.event_country') }}</label>
                                        <select class="form-control" name="country" id="country-select">
                                            <option value="">-- Select Country --</option>
                                            @foreach ($countries as $country)
                                                <option value="{{$country->id}}">{{$country->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('event_country')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="inchargecontact">{{ __('admin/event.event_fee_currency') }}</label>
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                @if (isPartnerUser())
                                                    <input type="hidden" name="currency" value="{{ $partnerCurrency }}">
                                                    <input type="text" class="form-control" value="{{ strtoupper($partnerCurrency) }}" disabled>
                                                @else
                                                    <select class="form-control" name="currency" required>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ strtolower($currency->code) }}">{{ $currency->code }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number"
                                                    class="form-control @error('event_fee') is-invalid @enderror"
                                                    placeholder="Fee" name="event_fee" required>
                                                @error('event_fee')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="eventend">{{ __('admin/event.event_video') }}</label>
                                        <input type="text" id="event_video"
                                            class="form-control" name="event_video" placeholder="YouTube video url" onblur="validateYouTubeUrl()">
                                    </div>
                                     <div class="form-group">
                                        <label for="eventend">Save as Draft OR Publish</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="eventPublish" id="isDraft" value="0" checked>
                                            <label class="form-check-label" for="isDraft">Draft</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="eventPublish" id="isPublish" value="1">
                                            <label class="form-check-label" for="isPublish">Publish</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-primary">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventend">{{ __('admin/event.resource_guide') }}</label>
                                    </div>
                                    <div class="resource-div">
                                    </div>
                                    <div class="addMore">
                                        <a href="javscript:void(0);" class="btn btn-info btn-sm" id="addMoreLink" onclick="addMore()">Add Highlights</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-warning">
                                {{-- <div class="card-header"></div> --}}
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">{{ __('admin/event.decription') }}</label>
                                        <textarea id="eventdescription" class="form-control @error('event_description') is-invalid @enderror" name="event_description"></textarea>
                                        @error('event_description')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                        <strong class="text-danger" id="eventdescription-error" style="display:none;">The event description field is required.</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="eventstart">{{ __('admin/event.reward_amount') }}</label>
                                        <input type="number" class="form-control @error('reward_amount') is-invalid @enderror"
                                                    placeholder="Amount" name="reward_amount">
                                                @error('reward_amount')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="eventstart">{{ __('admin/event.reward_description') }}</label>
                                        <textarea id="reward_description" class="form-control" name="reward_description" placeholder="Description"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="eventend">{{ __('admin/event.upload_poster') }}</label>
                                        <span>(Only PDF file is allowed)</span>
                                    </div>
                                    <div class="poster-div">
                                        <div class="poster-box" id="poster-box0">
                                            <div class="form-group">
                                                <label for="poster_attachment0">{{ __('admin/event.poster_attachment') }}</label>
                                                <input type="file" class="form-control @error('poster_attachment') is-invalid @enderror" id="poster_attachment0" name="poster_attachment[]" accept=".pdf,application/pdf" onchange="validatePosterAttachment(this.id)" required>
                                                @error('poster_attachment')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                                @error('poster_attachment.0')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="addMore">
                                        <a href="javscript:void(0);" class="btn btn-info btn-sm" id="addMorePosterLink" onclick="addMorePoster()">Add Poster</a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">{{ __('admin/event.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <script>
        $(document).ready(function() {
            $("#eventimage").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });

            // Summernote hides the real "event_description" textarea, so the browser's
            // native `required` validation can never focus it. Validate it manually instead.
            $('#addEventForm').on('submit', function(e) {
                if ($('#eventdescription').summernote('isEmpty')) {
                    e.preventDefault();
                    $('#eventdescription-error').show();
                    var $noteEditor = $('#eventdescription').next('.note-editor');
                    if ($noteEditor.length) {
                        $('html, body').animate({
                            scrollTop: $noteEditor.offset().top - 100
                        }, 300);
                    }
                } else {
                    $('#eventdescription-error').hide();
                }
            });
        });

        function validateYouTubeUrl() {
            var url = $('#event_video').val();
            if (url != '') {
                var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                var match = url.match(regExp);
                if (match && match[2].length == 11) {
                    // $('#event_video').attr('src', 'https://www.youtube.com/embed/' + match[2] + '?autoplay=0');
                } else {
                    alert("Please enter valid YouTube Url");
                    $('#event_video').val('');
                    return false;
                }
            }
        }

        function addMore() {
            var resourceCnt = $('.resource-box').length;
            var resource = `<div class="resource-box" id="resource-box${resourceCnt}">
                            <div class="form-group">
                                <label for="eventend">{{ __('admin/event.resource_title') }}</label>
                                <a href="javascript:void(0)" class="text-danger trash_title" onclick="removeResource(${resourceCnt})"><i class="fas fa-trash"></i></a>
                                <input type="text"
                                    class="form-control" id="resource_title${resourceCnt}" name="resource_title[]" placeholder="title" required>
                            </div>
                            <div class="form-group">
                                <label for="eventend">{{ __('admin/event.resource_description') }}</label>
                                <textarea class="form-control" id="resource_description${resourceCnt}" name="resource_description[]" placeholder="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="eventend">{{ __('admin/event.resource_icon') }}</label>
                                <input type="file" class="form-control" id="resource_icon${resourceCnt}" name="resource_icon[]" onchange="validateIcon(this.id)" required>
                            </div>
                        </div>`;
           
            if(!resourceCnt) {
                $(`.resource-div`).append(resource);
            } else {
                $(`#resource-box${resourceCnt-1}`).after(resource);
            }
            
            $("#addMoreLink").html(' <span>Add More</span> ');
        }

        function removeResource(resourceId) {
            $(`#resource-box${resourceId}`).remove();
            var resourceCnt = $('.resource-box').length;
            if(!resourceCnt) {
                $("#addMoreLink").text('Add Highlights');
            }
        }

        function validateIcon(id){
            var fileExtension = ['jpeg', 'jpg', 'png'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only JPG, JPEG or PNG files are allowed.");
                $("#"+id).val(''); 
            }
        }

        function addMorePoster() {
            var posterCnt = $('.poster-box').length;
            var poster = `<div class="poster-box" id="poster-box${posterCnt}">
                            <div class="form-group">
                                <label for="eventend">{{ __('admin/event.poster_attachment') }}</label>
                                <a href="javascript:void(0)" class="text-danger trash_title" onclick="removePoster(${posterCnt})"><i class="fas fa-trash"></i></a>
                            <input type="file" class="form-control" id="poster_attachment${posterCnt}" name="poster_attachment[]" accept=".pdf,application/pdf" onchange="validatePosterAttachment(this.id)" required>
                            </div>
                        </div>`;
           
            if(!posterCnt) {
                $(`.poster-div`).append(poster);
            } else {
                $(`#poster-box${posterCnt-1}`).after(poster);
            }
            
            $("#addMorePosterLink").html(' <span>Add More</span> ');
        }

        function removePoster(posterId) {
            $(`#poster-box${posterId}`).remove();
            var posterCnt = $('.poster-box').length;
            if(!posterCnt) {
                $("#addMorePosterLink").text('Add Poster');
            }
        }

        function validatePosterAttachment(id){
            var fileExtension = ['pdf'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only PDF file is allowed.");
                $("#"+id).val(''); 
            }
        }
        
        document.addEventListener('DOMContentLoaded', function () {
        const countryDropdown = document.getElementById('country-dropdown');
        const countrySelect = document.getElementById('country-select');
        @if (!isPartnerUser())
            const radios = document.querySelectorAll('input[name="visibility_type"]');

            function toggleDropdown() {
                if (document.getElementById('country-specific').checked) {
                    countryDropdown.style.display = 'block';
                    countrySelect.required = true;
                } else {
                    countryDropdown.style.display = 'none';
                    countrySelect.required = false;
                    countrySelect.value = '';
                }
            }

            radios.forEach(radio => {
                radio.addEventListener('change', toggleDropdown);
            });

            toggleDropdown(); // initial call
        @else
            if (countryDropdown) {
                countryDropdown.style.display = 'none';
            }
        @endif
    });
    </script>
@endsection
