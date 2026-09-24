<div class="card-body">
    <div class="mailbox-controls">
        <!-- Check all button -->
        <!-- /.btn-group -->
        <button type="button" wire:click='resetPage()' class="btn btn-default btn-sm refresh_page">
            <i class="fas fa-sync-alt"></i>
        </button>
        @if ($notifications->total() > 0)
            <div class="float-right">
                {{ $notifications->firstItem() }} -
                {{ $notifications->lastItem() }}/{{ $notifications->total() }}
                <div class="btn-group">
                    <button type="button" @if ($notifications->onFirstPage()) disabled @endif wire:click='previousPage'
                        class="btn btn-default btn-sm">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" wire:click='nextPage' @if (!$notifications->hasMorePages()) disabled @endif
                        class="btn btn-default btn-sm">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <!-- /.btn-group -->
            </div>
        @endif
        <!-- /.float-right -->
    </div>

    <div class="table-responsive mailbox-messages">
        <table id="" class="table table-hover table-striped">
            <tbody>
                @forelse ($notifications as $notification)
                    <tr>
                        @if($notification->school_id == 'All')
                            <td class="mailbox-name">
                                All School @if($notification->grade_id == 'All') / All Grade @endif
                            </td>
                        @else
                            <td class="mailbox-name">
                                @if(($notification->school != null) && isset($notification->school->school_name))
                                    {{ $notification->school->school_name }} 
                                    @if($notification->grade_id == 'All') / All Grade @endif
                                @endif                                
                            </td>
                        @endif

                        <td class="mailbox-name">{{ $notification->title }}</td>
                        <td class="mailbox-subject">{{ $notification->description }}</td>
                        <td class="mailbox-date">{{ $notification->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">
                            No Notification Found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- /.table -->
    </div>
    <!-- /.mail-box-messages -->
</div>
