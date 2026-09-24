@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">External Session Calendar</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">External Session Calendar</li>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('backend.external_session.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Session
                        </a>
                    </div>
                    <a href="{{ route('backend.external_session.list') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <!-- /.card-header -->
                <div class="card-body calendar-body admin-session-calendar">
                    <div id="calendar"></div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Session Details Modal -->
<div class="modal fade" id="externalSessionModal" tabindex="-1" role="dialog" aria-labelledby="externalSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="externalSessionModalLabel">Session Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Title:</strong> <span id="modalSessionTitle">-</span></p>
                <p><strong>Speaker:</strong> <span id="modalSessionSpeaker">-</span></p>
                <p><strong>Session Type:</strong> <span id="modalSessionType">-</span></p>
                <p><strong>Date & Time:</strong> <span id="modalSessionTime">-</span></p>
                <p><strong>Recurrence:</strong> <span id="modalSessionRecurrence">One-time</span></p>
                <p id="modalSessionAgendaContainer" style="display: none;"><strong>Agenda:</strong> <span id="modalSessionAgenda">-</span></p>
                <p id="modalSessionZoomLinkContainer" style="display: none;"><strong>Zoom Link:</strong> <a href="#" target="_blank" id="modalSessionZoomLink" class="btn btn-sm btn-info">Join Meeting</a></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" id="modalDeleteOccurrenceBtn" style="display:none;">
                    <i class="fas fa-trash"></i> Delete This Occurrence
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var adminTimeZone = 'Asia/Singapore';

    function formatEventTime(date) {
        return new Intl.DateTimeFormat('en-US', {
            timeZone: adminTimeZone,
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(date);
    }

    function formatEventDateTime(date) {
        return new Intl.DateTimeFormat('en-GB', {
            timeZone: adminTimeZone,
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(date);
    }

    function getCalenderHeight() {
        const table = document.querySelector('.fc-scrollgrid-sync-table');
        const frames = document.querySelectorAll('.fc-daygrid-day-frame');

        if (table && frames.length > 0) {
            const tableHeight = table.offsetHeight;
            const frameHeight = (tableHeight / 6) - 7;

            frames.forEach(frame => {
                frame.style.height = `${frameHeight}px`;
                frame.style.overflowY = 'auto';
            });
        }
    }

    function setupCalendarAdjustments(calendar) {
        getCalenderHeight();

        const resizeHandler = () => getCalenderHeight();
        window.addEventListener('resize', resizeHandler);

        calendar.setOption('datesSet', () => {
            setTimeout(getCalenderHeight, 10);
        });

        window.addEventListener('beforeunload', () => {
            window.removeEventListener('resize', resizeHandler);
        });
    }

    function initializeExternalSessionCalendar() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'today'
            },
            initialView: 'dayGridMonth',
            editable: false,
            selectable: false,
            dayMaxEvents: false, // show all sessions in the (now scrollable) day cell instead of collapsing into "+more"
            allDaySlot: false,
            events: function(fetchInfo, successCallback, failureCallback) {
                jQuery.ajax({
                    type: 'get',
                    url: '{{ route("backend.external_session.calendar_data") }}',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    },
                    dataType: 'json',
                    success: function(events) {
                        if (Array.isArray(events)) {
                            successCallback(events);
                        } else {
                            failureCallback(new Error('Invalid events data'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading calendar events:', error);
                        failureCallback(new Error('Failed to load events'));
                    }
                });
            },
            eventContent: function(arg) {
                return {
                    html: `<div class="admin-event-card">
                            <span class="admin-event-title">${arg.event.title || ''}</span>
                            <span class="admin-event-time">${formatEventTime(arg.event.start)}</span>
                        </div>`
                };
            },
            eventsSet: function() {
                document.querySelectorAll('#calendar .fc-daygrid-day-frame.has-session').forEach(function(frame) {
                    frame.classList.remove('has-session');
                });
                document.querySelectorAll('#calendar .fc-daygrid-event-harness').forEach(function(harness) {
                    var frame = harness.closest('.fc-daygrid-day-frame');
                    if (frame) {
                        frame.classList.add('has-session');
                    }
                });
            },
            eventClick: function(info) {
                var event = info.event;
                var props = event.extendedProps;
                
                // Set basic info
                document.getElementById('modalSessionTitle').textContent = event.title || '-';
                document.getElementById('modalSessionSpeaker').textContent = props.speaker || '-';
                document.getElementById('modalSessionType').textContent = props.session_type === 1 ? 'Online' : 'Offline';
                document.getElementById('modalSessionTime').textContent = event.start 
                    ? formatEventDateTime(new Date(event.start)) 
                    : '-';
                document.getElementById('modalSessionRecurrence').textContent = props.recurrence_summary || 'One-time';
                
                // Agenda
                if (props.agenda) {
                    document.getElementById('modalSessionAgenda').textContent = props.agenda;
                    document.getElementById('modalSessionAgendaContainer').style.display = 'block';
                } else {
                    document.getElementById('modalSessionAgendaContainer').style.display = 'none';
                }
                
                // Zoom Link
                if (props.zoom_link && props.session_type === 1) {
                    document.getElementById('modalSessionZoomLink').href = props.zoom_link;
                    document.getElementById('modalSessionZoomLinkContainer').style.display = 'block';
                } else {
                    document.getElementById('modalSessionZoomLinkContainer').style.display = 'none';
                }
                
                // Show delete button only for recurring sessions
                var deleteBtn = document.getElementById('modalDeleteOccurrenceBtn');
                if (props.is_recurring) {
                    deleteBtn.style.display = 'block';
                    deleteBtn.dataset.sessionId = props.session_id;
                    deleteBtn.dataset.occurrenceDateTime = event.start.toISOString();
                } else {
                    deleteBtn.style.display = 'none';
                }
                
                jQuery('#externalSessionModal').modal('show');
            }
        });
        
        setupCalendarAdjustments(calendar);
        calendar.render();

        // Handle delete occurrence button
        var deleteBtn = document.getElementById('modalDeleteOccurrenceBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                var sessionId = this.dataset.sessionId;
                var occurrenceDateTime = this.dataset.occurrenceDateTime;
                
                if (!confirm('Are you sure you want to delete this specific occurrence? Other recurring instances will remain active.')) {
                    return;
                }
                
                jQuery.ajax({
                    type: 'POST',
                    url: '{{ route("backend.external_session.deleteOccurrence") }}',
                    data: {
                        session_id: sessionId,
                        occurrence_date_time: occurrenceDateTime,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(response) {
                        jQuery('#externalSessionModal').modal('hide');
                        // Refresh the calendar
                        calendar.refetchEvents();
                        // Show success message
                        var alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = '<strong>Success!</strong> ' + response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>';
                        document.querySelector('.card-body').insertBefore(alertDiv, document.getElementById('calendar'));
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON || {};
                        alert('Error: ' + (response.error || 'Failed to delete occurrence'));
                    }
                });
            });
        }
    }

    // Initialize on document ready
    document.addEventListener('DOMContentLoaded', initializeExternalSessionCalendar);
})();
</script>
@endsection
