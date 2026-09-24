@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <div class="pageTitle">
        <h2>Class Schedule</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item active">Class Schedule</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="row">

                <!-- /.col -->
                <div class="col-md-12">
                    <div class="card">
                        <!-- <div class="card-header">
                            <h4 class="card-title">Class Schedule</h4>
                            <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning float-right"> <i
                            class="material-icons">west</i> Back</a>

                        </div> -->
                        <div class="card-body calendar-body">
                            <!-- THE CALENDAR -->
                            <div id="calendar"></div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
</div>

<!-- Modal -->
<div class="modal fade calendar-session-popup" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content rounded-xl background-light-yellow">
        <div class="modal-header">
            <h5 class="modal-title" id="sessionModalLabel">Session Details</h5>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
        </div>
        <div class="modal-body">
            <div class="session-info">
                <p><strong>Title:</strong> <span id="modalTitle"></span></p>
                <p><strong>Speaker:</strong> <span id="modalSpeaker"></span></p>
                <p><strong>Time:</strong> <span id="modalTime"></span></p>
                <p class="modalZoomLink"><strong>Zoom Link:</strong> <a href="#" target="_blank" id="modalZoomLink">Join</a></p>
                <p><strong>Agenda:</strong> <span id="modalAgenda"></span></p>
            </div>
        </div>
        </div>
    </div>
</div>

<script>

    function getCalenderHeight() {
        const table = document.querySelector('.fc-scrollgrid-sync-table');
        const frames = document.querySelectorAll('.fc-daygrid-day-frame');
    
        if (table && frames.length > 0) {
        const tableHeight = table.offsetHeight;
        const frameHeight = (tableHeight / 6) - 7;
    
        frames.forEach(frame => {
            frame.style.height = `${frameHeight}px`;
        });
        }
    }
    
    function setupCalendarAdjustments(calendar) {
        // Run initially
        getCalenderHeight();
    
        // On window resize
        const resizeHandler = () => getCalenderHeight();
        window.addEventListener('resize', resizeHandler);
    
        // On calendar view/month change
        calendar.setOption('datesSet', () => {
            // Delay to ensure DOM updates
            setTimeout(getCalenderHeight, 10);
        });
    
        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            window.removeEventListener('resize', resizeHandler);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            customButtons: {
                viewSessionReport: {
                    text: 'View All Session Report',
                    click: function() {
                        window.location.href = "{{ route('school.session_report') }}";
                    }
                }
            },
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'viewSessionReport'
            },
            //initialDate: '2022-06-01',
            initialView: 'dayGridMonth',
            editable: true,
            selectable: true,
            businessHours: true,
            dayMaxEvents: true, // allow "more" link when too many events
            droppable: true, //for drag drop
            allDaySlot: false,
            events: function (fetchInfo, successCallback, failureCallback) {
                //alert(localStorage.schoolid);
                jQuery.ajax({
                    type: 'get',
                    url: "{{ route('school.school_classSchedule') }}",
                    data: {
                        //school_id: localStorage.schoolid,
                    },
                    success: function (events) {
                        var new_event = JSON.parse(events);
                        successCallback(new_event);
                    }
                });
            },
            eventContent: function(arg) {
                 const eventStart = arg.event.start;
                 const now = new Date();
                 const eventEnd = new Date(eventStart.getTime() + 60*60*1000); 
                 const isActive = (now < eventStart || (now >= eventStart && now <= eventEnd));
                 const joinClass = isActive ? 'join-now-active' : 'join-now-disabled';
                 //const isUpcoming = eventStart > now;
                 // const joinClass = isUpcoming ? 'join-now-active' : 'join-now-disabled';
                 var zoom_link_btn = '';
                 if(arg.event.extendedProps.session_type) {
                    zoom_link_btn = `<div class="fc-event-button fc-event-button-join">
                                        <a href="${arg.event.extendedProps.zoom_link}" target="_blank" class="${joinClass}">JOIN NOW</a>
                                    </div>`;
                 }
                return {
                    html: `<div class="has-meeting">
                            <div class="fc-event-title">
                                <img src="{{ asset('asset/dist/img/zoom-logo.png') }}" alt="zoom" />
                            </div>
                            <div class="fc-event-desc">
                                <img src="{{ asset('asset/dist/img/calender.svg') }}" alt="zoom" /> 
                                <span>${arg.event.extendedProps.time}</span>
                            </div>
                            ${zoom_link_btn}
                        </div>`
                };
            },
            eventClick: function(info) {
                const clickedEl = info.jsEvent.target;

                // Skip popup if clicking on JOIN NOW button
                if (clickedEl.closest('.fc-event-button-join')) {
                    return;
                }

                const eventStart = new Date(info.event.start);
                const now = new Date();
                const eventEnd = new Date(eventStart.getTime() + 60*60*1000);
                const isActive = (now < eventStart || (now >= eventStart && now <= eventEnd));

                document.getElementById('modalTitle').textContent = info.event.extendedProps.session_title;
                document.getElementById('modalSpeaker').textContent = info.event.extendedProps.speaker;
                document.getElementById('modalTime').textContent = info.event.extendedProps.time;
                if(info.event.extendedProps.session_type) {
                    document.getElementById('modalZoomLink').href = isActive ? info.event.extendedProps.zoom_link : 'javascript:void(0);';
                    document.querySelector('.modalZoomLink').style.display = isActive ? 'block' : 'none';
                } else {
                    document.querySelector('.modalZoomLink').style.display = 'none';
                }
                document.getElementById('modalAgenda').textContent = info.event.extendedProps.agenda;

                let modal = new bootstrap.Modal(document.getElementById('sessionModal'));
                modal.show();
            }
        });
        setupCalendarAdjustments(calendar); // Attach logic
        calendar.render();
    });

</script>

@endsection