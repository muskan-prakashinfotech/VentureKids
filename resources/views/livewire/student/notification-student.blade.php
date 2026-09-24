<div class="card-body">
    @if($notifications->count())
    <div class="mailbox-controls">
        <!-- Check all button -->
        <div class="btns-group">
            <div class="icheck-primary">
                <input class="check_all" wire:model="allSelected" wire:click='updateCom' type="checkbox" id="check"
                    data-id=''>
                <label for="check"></label>
            </div>
            <button type="button" class="btn btn-sm iconBtn btn-danger notification_delete">
                <i class="material-icons">delete</i>
            </button>

            <button type="button" wire:click='resetPage()' class="btn btn-sm iconBtn btn-primary refresh_page">
                <i class="material-icons">loop</i>
            </button>
        </div>
       
        <div class="btns-group">
            {{ $notifications->firstItem() }} -
            {{ $notifications->lastItem() }}/{{ $notifications->total() }}
            <button class="btn btn-sm iconBtn btn-primary" type="button" @if ($notifications->onFirstPage()) disabled @endif wire:click='previousPage'>
                <i class="material-icons">west</i>
            </button>
            <button class="btn btn-sm iconBtn btn-primary" type="button" wire:click='nextPage' @if (!$notifications->hasMorePages()) disabled @endif>
                <i class="material-icons">east</i>
            </button>
        </div>
    </div>
    @endif

    <div class="table-responsive mailbox-messages">
        <table id="" class="table table-hover table-striped">
            <tbody>
                @forelse ($notifications as $notification)
                <tr>
                    <td>
                        <div class="icheck-primary">
                            <input class="check_all_checkbox" wire:model="selectedNotifications" wire:click="selectOn"
                                type="checkbox" value="{{ $notification->id }}" id="check{{ $notification->id }}" />
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