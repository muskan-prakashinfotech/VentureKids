<footer class="main-footer">
    <strong>Copyright &copy; {{ now()->format('Y') }}-{{ now('Y')->addYear()->format('Y') }} VentureKids</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.0
    </div>
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->


<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('asset/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- DataTables  & Plugins -->
<script src="{{ asset('asset/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('asset/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- BS-Stepper -->
<script src="{{ asset('asset/plugins/bs-stepper/js/bs-stepper.min.js') }}"></script>

<!-- dropzonejs -->
<script src="{{ asset('asset/plugins/dropzone/min/dropzone.min.js') }}"></script>
<script src="{{ asset('asset/plugins/fullcalendar/main.js') }}"></script>
<script src="{{ asset('asset/dist/js/jquery.metalClone.min.js') }}"></script>
<script src="{{ asset('asset/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('asset/dist/js/intlTelInput-jquery.min.js') }}"></script>

<script>
    jQuery(function() {

        $('input[type="file"]').change(function(e) {
            if(e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                $(this).next('.custom-file-label').html(fileName);
            }
        });
        //delete Student sweetalert
        $(document).on('click', '#deleteStudent', function(e) {
            e.preventDefault();
            var Id = $(this).attr('href');

            swal({
                    title: "Delete request",
                    text: "Send request to admin, to delete this student!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Student delete request send successfully.", {
                            icon: "success",
                        });

                        window.location.href = Id;

                    } else {
                        swal("Your file is safe!");
                    }

                });
        });

        $('#example1').DataTable();
        $('#example2').DataTable();

        $('.educationclone').multifield({
            section: '.toClone_example1',
            btnAdd: '.addBtn',
            btnRemove: '.btnRemove'
        });

        if ($("#phone").attr('id')) {
            $("#phone").intlTelInput({
                autoPlaceholder: "polite",
                customPlaceholder: null,
                formatOnDisplay: true,
                utilsScript: "../dist/js/utils.js",

            });
        }
        if ($("#mobile").attr('id')) {
            $("#mobile").intlTelInput({
                autoPlaceholder: "polite",
                customPlaceholder: null,
                formatOnDisplay: true,
                utilsScript: "../dist/js/utils.js",

            });
        }

        $('.section_add').multifield({
            section: '.row_check',
            btnAdd: '.addBtn',
            btnRemove: '.removeBtn'
        });

        $('.section_add_more').multifield({
            section: '.row_check_new',
            btnAdd: '.addBtn1',
            btnRemove: '.removeBtn1'
        });
        $('.section_add_more_new').multifield({
            section: '.row_check_new1',
            btnAdd: '.addBtn1',
            btnRemove: '.removeBtn1'
        });

        $('.form-group').multifield({
            section: '.project_example',
            btnAdd: '.addBtn',
            btnRemove: '.btnRemove'
        });

    });
    Dropzone.options.myDropzone = {
        url: 'upload.php',
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 5,
        maxFiles: 5,
        maxFilesize: 1,
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
    }

    $(function() {
        //Add text editor
        $('#eventdescription').summernote({
            placeholder: 'Event description',
            tabsize: 2,
            height: 385,
        })
        $('#assignmentdescription').summernote({
            placeholder: 'Assignment description',
            tabsize: 2,
            height: 385,
        })
        $('#compose-textarea').summernote({
            placeholder: 'techer',
            tabsize: 2,
            height: 385,
        })
        $('#contentdescription').summernote({
            placeholder: 'Content description',
            tabsize: 2,
            height: 50,
        })

        $('#compose-textarea1').summernote({
            placeholder: 'student',
            tabsize: 2,
            height: 385,
        });
        $('#compose-textarea2').summernote({
            placeholder: 'school',
            tabsize: 2,
            height: 385,
        });

        $('#description').summernote({
            placeholder: 'Project description',
            tabsize: 2,
            height: 385,
        })

    })

    //Add text editor
    $('#contentdescription_1').summernote({
        placeholder: 'Outcome of session',
        tabsize: 2,
        height: 50,
    })

    $('#contentdescription_2').summernote({
        placeholder: 'Questions to assess prior knowledge',
        tabsize: 2,
        height: 50,
    })

    $('#contentdescription_3').summernote({
        placeholder: 'How to introduce the topic to the students',
        tabsize: 2,
        height: 50,
    })

    $('#contentdescription_4').summernote({
        placeholder: 'Related Activity 1',
        tabsize: 2,
        height: 50,
    })

    $('#contentdescription_5').summernote({
        placeholder: 'Related Activity 2',
        tabsize: 2,
        height: 50,
    })
    $('#contentdescription_6').summernote({
        placeholder: 'Vocabulary',
        tabsize: 2,
        height: 50,
    })

    $('#contentdescription_7').summernote({
        placeholder: 'Tips for parents/home assignments',
        tabsize: 2,
        height: 50,
    })

    $.widget.bridge('uibutton', $.ui.button);

    function fileUploadName(e, ele) {
        var fileName = e.target.files[0].name;
        $(ele).next('.custom-file-label').html(fileName);
        let previewid = $(ele).data('img-preview');
        let reader = new FileReader();
        reader.onload = function(event) {
            $(previewid).attr('src', event.target.result);
            $(previewid).show();
        }
        reader.readAsDataURL(e.target.files[0]);
    }
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('asset/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('asset/plugins/chart.js/Chart.min.js') }}"></script>

<!-- jQuery Knob Chart -->
<script src="{{ asset('asset/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('asset/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('asset/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('asset/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('asset/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('asset/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('asset/dist/js/adminlte.js') }}"></script>


<!-- AdminLTE for demo purposes -->
<!-- <script src="dist/js/demo.js"></script> -->
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->



@livewireScripts()
</body>

</html>
