<div class="card-body">
    @if($notifications->count())
    <div class="mailbox-controls">
        <!-- Check all button -->
        <div class="btn-group">
            <div class="icheck-primary"><input class="check_all" wire:model="allSelected" wire:click='updateCom'
                    type="checkbox" id="check" data-id=''><label for="check"></label></div>
            <button type="button" class="btn btn-default btn-sm notification_delete">
                <i class="far fa-trash-alt"></i>
            </button>
        </div>
        <!-- /.btn-group -->
        <button type="button" wire:click='resetPage()' class="btn btn-default btn-sm refresh_page">
            <i class="fas fa-sync-alt"></i>
        </button>
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
        <!-- /.float-right -->
    </div>
    @endif
    <div class="table-responsive mailbox-messages">
        <table id="" class="table table-hover table-striped">
            <tbody>
                @forelse ($notifications as $notification)
                    <tr>
                        <td>
                            <div class="icheck-primary">
                                <input class="check_all_checkbox" wire:model="selectedNotifications"
                                    wire:click="selectOn" type="checkbox" value="{{ $notification->id }}"
                                    id="check{{ $notification->id }}" />
                                <label for="check{{ $notification->id }}"></label>
                            </div>
                        </td>
                        <td class="mailbox-name">{{ $notification->title }}</td>
                        <td class="mailbox-subject">{{ $notification->description }}</td>
                        <td class="mailbox-date">{{ $notification->created_at->diffForHumans() }}</td>
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
