@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Notification</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Notification</li>
                    </ol>
                </div>
            </div>
            <div class="alert alert-danger notification_show" style="text-align:center; display:none;">
               Successfully Notificaation Deleted
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                     <ul class="nav nav-pills flex-column">
                            <li class="nav-item active">
                                <a href="{{route('school.school-notification')}}" class="nav-link">
                                <i class="fas fa-inbox"></i> Notification List
                                <span class="badge bg-primary float-right">{{$total_notification}}</span>
                                </a>
                            </li>
                        </ul>
                </div>
            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Notification</h3>
                        <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        <div class="card-tools">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Search Mail">
                                <div class="input-group-append">
                                    <div class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-tools -->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="mailbox-controls">
                            <!-- Check all button -->
                            <div class="btn-group">
                                 @if($total_notification == 0)
                                <div class="icheck-primary"><input class="check_all" type="checkbox" id="check" data-id='' disabled><label for="check"></label></div>
                                <button type="button" class="btn btn-default btn-sm notification_delete" disabled>
                                    <i class="far fa-trash-alt"></i>
                                </button>
                                @else
                                <div class="icheck-primary"><input class="check_all" type="checkbox" id="check" data-id=''><label for="check"></label></div>
                                <button type="button" class="btn btn-default btn-sm notification_delete">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                                @endif
                            </div>
                            <!-- /.btn-group -->
                            <button type="button" class="btn btn-default btn-sm refresh_page">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="float-right">
                                1-50/200
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <!-- /.btn-group -->
                            </div>
                            <!-- /.float-right -->
                        </div>

                        <div class="table-responsive mailbox-messages">
                            <table id="" class="table table-hover table-striped">
                            @if($message)
                                    <div class="alert alert-danger mt-2" style="text-align: center;">
                                        {{ $message }}
                                    </div>
                            @endif
                            <tbody>
                                @foreach($schoolNotifications as $key=>$notification)
                                <tr>
                                        <td><div class="icheck-primary"><input class="check_all_checkbox" type="checkbox" value="{{$notification->id}}" id="check<?= $key ?>"><label for="check<?= $key ?>"></label></div></td>

                                        <td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td>
                                        <td class="mailbox-name"><a href="read-mail.html">{{ $notification->title }}</a></td>
                                        <td class="mailbox-subject">{{ $notification->description }}</td>
                                        <td class="mailbox-attachment"></td><td class="mailbox-date">5 mins ago</td>
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                            <!-- /.table -->
                        </div>
                        <!-- /.mail-box-messages -->
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>

<script>
        $( document ).ready(function() {

//Refresh Page---------------
$('.refresh_page').click(function(e){

    window.location.reload();
});

$('.check_all').click(function(e){

    if($(this).is(":checked"))
    {

        $('.check_all_checkbox').attr('checked', 'checked');
        $('.check_all').attr('data-id',1);
        $(this).val('all');
    }
    else
    {
        $('.check_all_checkbox').removeAttr('checked');
        $('.check_all').attr('data-id','');
        $(this).removeAttr('value');
    }

});

$('.check_all_checkbox').click(function(e){

  if($(this).is(":checked"))
  {
      $('.check_all').attr('data-id',1);
  }
  else
  {
      $('.check_all').attr('data-id','');
  }

});

$('.notification_delete').click(function(e){
    var empty_check=$('.check_all').attr('data-id');
    if(empty_check != '')
    {
    var notification_id;
    let check_all=$('.check_all').val();
    if(check_all == 'all')
    {
        notification_id= check_all;
    }
    else
    {
        var arr = [];
        $(".check_all_checkbox").each(function( index ) {
               if($(this).is(":checked"))
               {
                    let notificaton_id=$(this).val();
                    arr.push(notificaton_id);

               }
        });

        notification_id=arr.toString();
    }
    jQuery.ajax({
    type: 'post',
    url: "{{route('school.notification_delete')}}",
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
        notification_id: notification_id,
    },
    success: function(data) {

        if(data == 1)
        {
            $('.notification_show').css('display','block');
            location.reload();
        }
    }
   });

 }
 else{
    alert('Please choose');
 }

});



});
    // function notificationAutoLoad() {
    //     var notificationId = $('.school_notification').attr('data-id');
    //     $.ajax({
    //         type: "POST",
    //         url: "{{route('school.single-notification')}}",
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: {
    //             notificationId: notificationId
    //         },
    //         dataType: 'JSON',
    //         success: function(data) {

    //             var html = '';

    //             html += '<tr><td><div class="icheck-primary"><input type="checkbox" value="" id="check1"><label for="check1"></label></div></td><td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td><td class="mailbox-name"><a href="read-mail.html">' + data.title + '</a></td><td class="mailbox-subject">' + data.description + '</td><td class="mailbox-attachment"></td><td class="mailbox-date">5 mins ago</td></tr>';

    //             $('.notification_box').html(html);

    //         },
    //     });
    // }

    // $(function() {

    //     notificationAutoLoad();

    //     $(".school_notification").on("click", function() {
    //         var notificationId = $(this).attr('data-id');

    //         $.ajax({
    //             type: "POST",
    //             url: "{{route('school.single-notification')}}",
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             data: {
    //                 notificationId: notificationId
    //             },
    //             dataType: 'JSON',
    //             success: function(data) {

    //                 var html = '';

    //                 html += '<tr><td><div class="icheck-primary"><input type="checkbox" value="" id="check1"><label for="check1"></label></div></td><td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td><td class="mailbox-name"><a href="read-mail.html">' + data.title + '</a></td><td class="mailbox-subject">' + data.description + '</td><td class="mailbox-attachment"></td><td class="mailbox-date">5 mins ago</td></tr>';

    //                 $('.notification_box').html(html);

    //             },
    //         });
    //     });


    // });
</script>

@endsection
