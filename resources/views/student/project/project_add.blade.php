@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Add Project</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Add Project</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title">Add Details</h2>
                <a href="{{ route('student.list-project') }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i> Back
                </a>
            </div>
        </div>
        
        <form action="{{ route('student.save-project') }}" method="POST" enctype="multipart/form-data" id="prjAddForm">
            @csrf

            
            <div class="card">
                <div class="card-body new-text">
                    <!-- Grade -->
                    <div class="mb-4 add_project_heading">
                        <div class="icon project_details">
                            <img src="{{ asset('asset/dist/img/Project_Details.svg') }}"/>
                        </div>
                        <div class="icon_text">
                            <h4>Project Details</h4>
                            <p>Start by selecting your grade and project theme</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="grade">Grade Level</label>
                        <select name="grade" id="grade" class="form-control" required>
                            <option value="">Select a grade</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        @error('grade')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>

                    <!-- Project Theme -->
                    <div id="project_theme_container" class="mb-3" style="display: none;">
                        <label class="form-label fw-semibold" style="font-size: 1rem;">Project Theme</label>
                        <div id="project_theme_buttons" class="d-flex flex-wrap project_theme_badge"></div>
                        <input type="hidden" name="project_theme" id="project_theme">
                        @error('project_theme')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>

                    <!-- Project Title -->
                    <div class="mb-3">
                        <label for="title">Project Title</label>
                        <input name="title" id="title" placeholder="Project Title" class="form-control" required>
                        @error('title')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>
                </div>
            </div>
           
            <div class="card">
                <div class="card-body new-text">
                    <!-- Grade -->
                    <div class="mb-4 add_project_heading">
                        <div class="icon project_overview">
                            <img src="{{ asset('asset/dist/img/Project_Overview.svg') }}"/>
                        </div>
                        <div class="icon_text">
                            <h4>Project Overview</h4>
                            <p>Share your research and understanding of this topic</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="font-medium">What is your project about? Share the research you did and explain your understanding of this topic in detail</label>
                        <textarea name="overview" id="overview" class="form-control" rows="4" placeholder="Describe your project in detail..." required></textarea>
                        @error('overview')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>
                    <!-- <div class="mb-3">
                        <label class="font-medium">Supporting Materials</label>
                        <div class="upload-section">
                            <div class="upload-box">
                                <div class="upload-content">
                                    <div class="upload-icon">
                                        <img src="{{ asset('asset/dist/img/upload.svg') }}"/>
                                    </div>
                                    <p class="mb-0"><span class="choose-file">Choose file</span> or drag and drop</p>
                                    <small>PNG, JPG, PDF up to 10MB</small>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="mb-3">
                    <label class="font-medium">Supporting Materials</label>
                        <div id="file-upload-container" class="project-file-upload">
                            <div class="upload-item mb-3" data-index="0">
                                <div class="upload-section">
                                    <div class="upload-box position-relative">
                                        <div class="upload-content">
                                            <div class="upload-icon">
                                                <img src="{{ asset('asset/dist/img/upload.svg') }}"/>
                                            </div>
                                            <p class="mb-0"><label class="choose-file">Choose file
                                                <input type="file" class="d-none supporting-file" name="supporting_files[]" accept="image/jpeg,image/jpg,image/png,video/mp4,video/mov,video/avi,video/mkv,application/pdf" onchange="handleFileSelect(this)">
                                            </label> or drag and drop</p>
                                            <small class="text-muted">Images (PNG, JPG, JPEG)</small>
                                        </div>
                                        <div class="selected-file-info mt-2" style="display: none;">
                                            <span class="file-name"></span>
                                            <button type="button" class="btn btn-sm btn-danger ml-2" onclick="removeFile(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMoreFiles()">
                            <i class="fas fa-plus"></i> Add More Files
                        </button>
    
                        @error('supporting_files.*')
                            <strong class="text-danger d-block mt-2">{{ $message }}</strong>
                        @enderror
                    </div>
                    
                </div>
            </div>

            <div class="card">
                <div class="card-body new-text">
                    <!-- Grade -->
                    <div class="mb-4 add_project_heading">
                        <div class="icon step_by_step_process">
                            <img src="{{ asset('asset/dist/img/Step_by_Step_Process.svg') }}"/>
                        </div>
                        <div class="icon_text">
                            <h4>Step-by-Step Process</h4>
                            <p>Describe the process and skills you used along the way</p>
                        </div>
                    </div>
                    <!-- Step-by-Step Process -->
                    <div class="mb-3">
                        <label class="font-medium">What steps did you follow to complete your project? Describe the process and the skills you used along the way</label>
                        <textarea name="skills" id="skills" class="form-control" rows="4" placeholder="Step 1: First, I researched about..." required></textarea>
                        @error('skills')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body new-text">
                    <!-- Grade -->
                    <div class="mb-4 add_project_heading">
                        <div class="icon reflections_challenges">
                            <img src="{{ asset('asset/dist/img/Reflections_Challenges.svg') }}"/>
                        </div>
                        <div class="icon_text">
                            <h4>Reflections & Challenges</h4>
                            <p>Reflect on your thoughts, feelings, and learnings</p>
                        </div>
                    </div>
                    <!-- Reflection -->
                    <div class="mb-3">
                        <label class="font-medium">What challenges did you face during the project, and how did you keep yourself motivated?</label>
                        <textarea name="reflection" id="reflection" class="form-control" rows="4" placeholder="What was the most challenging part? How you overcame it?" required></textarea>
                        @error('reflection')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="font-medium">If you did this project again, what would you do differently?</label>
                        <textarea name="project_reflection" id="project_reflection" class="form-control" rows="4" placeholder="Share what you would change or improve..." required></textarea>
                        @error('project_reflection')<strong class="text-danger">{{ $message }}</strong>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-5">
                <span>Please complete all fields to submit</span>
                <div class="d-flex align-items-center">
                    <input type="hidden" name="projectPublish" id="projectPublish" value="0">
                    <button type="submit" class="btn btn-white bg-white mr-2" id="btnSaveDraft">Save Draft</button>
                    <button type="submit" class="btn btn-primary btn-gradiant" id="btnSubmit">Submit Project</button>
                </div>
            </div>
        </form>
            
    </section>
</div>

<!-- <script src="{{asset('asset/dist/js/resumable.min.js')}}"></script> -->
<script>

    
        // var uploadElement = "projectVideo";
        // var target = "{{ route('student.uploadProjectVideo') }}";
        // var fileType = ['mp4', 'mov', 'avi', 'mkv'];
        // var chunkSize = 10*1024*1024;
        // var submitBtnName = "btnSubmit";
        // var moduleName = "scorm";
        // var token = "{{ csrf_token() }}";

    $('#grade').change(function() {
        const grade = $(this).val();
        const container = $('#project_theme_buttons');
        const themeContainer = $('#project_theme_container');

        if (!grade) {
            themeContainer.hide();
            container.empty();
            $('#project_theme').val('');
            return;
        }

        themeContainer.show();
        container.empty();
        $('#project_theme').val('');

        $.get('/student/project/themes/' + grade, function(data) {
            container.empty();
            if (!data || data.length === 0) {
                container.append('<p class="text-muted">No themes available for this grade.</p>');
                return;
            }

            data.forEach(function(theme) {
                const btn = $('<button type="button" class="btn btn-outline-primary"></button>').text(theme);
                btn.click(function() {
                    container.find('button').removeClass('btn-primary active').addClass('btn-outline-primary');
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
                    $('#project_theme').val(theme);
                });
                container.append(btn);
            });
        });
    });

    let fileIndex = 1;

    function handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;
    
        // Validate file type
        const allowedExtensions = ['jpeg', 'jpg', 'png', 'mp4', 'mov', 'avi', 'mkv', 'pdf'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
    
        if (!allowedExtensions.includes(fileExtension)) {
            alert('Invalid file type. Only Images (JPG, PNG), Videos (MP4, MOV, AVI, MKV), and PDF files are allowed.');
            input.value = '';
            return;
        }
    
        const uploadItem = input.closest('.upload-item');
        const uploadBox = uploadItem.querySelector('.upload-box');
        const uploadContent = uploadBox.querySelector('.upload-content');
        const fileInfo = uploadBox.querySelector('.selected-file-info');
        const fileName = fileInfo.querySelector('.file-name');
    
        // Add file type badge
        let fileType = 'image';
        let badgeClass = 'badge-image';
        let badgeText = 'IMAGE';
    
        if (['mp4', 'mov', 'avi', 'mkv'].includes(fileExtension)) {
            fileType = 'video';
            badgeClass = 'badge-video';
            badgeText = 'VIDEO';
        } else if (fileExtension === 'pdf') {
            fileType = 'pdf';
            badgeClass = 'badge-pdf';
            badgeText = 'PDF';
        }
    
        const existingBadge = uploadBox.querySelector('.file-type-badge');
        if (existingBadge) {
            existingBadge.remove();
        }
    
        // Add new badge
        const badge = document.createElement('span');
        badge.className = `file-type-badge ${badgeClass}`;
        badge.textContent = badgeText;
        uploadBox.appendChild(badge);
    
        uploadBox.classList.add('file-selected');
        uploadContent.style.display = 'none';
        fileInfo.style.display = 'block';
        fileName.textContent = file.name;
    }

    function removeFile(button) {
        const uploadItem = button.closest('.upload-item');
        const uploadBox = uploadItem.querySelector('.upload-box');
        const uploadContent = uploadBox.querySelector('.upload-content');
        const fileInfo = uploadBox.querySelector('.selected-file-info');
        const input = uploadItem.querySelector('.supporting-file');
        const badge = uploadBox.querySelector('.file-type-badge');
    
        input.value = '';
    
        uploadBox.classList.remove('file-selected');
        uploadContent.style.display = 'block';
        fileInfo.style.display = 'none';
    
        if (badge) {
            badge.remove();
        }
    
        // Remove the entire upload item if it's not the first one
        if (uploadItem.dataset.index !== "0") {
            uploadItem.remove();
        }
    }

    function addMoreFiles() {
        const container = document.getElementById('file-upload-container');
    
        const newUploadItem = document.createElement('div');
        newUploadItem.className = 'upload-item mb-3';
        newUploadItem.dataset.index = fileIndex;
    
        newUploadItem.innerHTML = `
        <div class="upload-section">
            <div class="upload-box position-relative">
                <button type="button" class="btn btn-danger btn-sm position-absolute" 
                        style="top: 10px; left: 10px; z-index: 10;" 
                        onclick="removeUploadItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
                <div class="upload-content">
                    <div class="upload-icon">
                        <img src="{{ asset('asset/dist/img/upload.svg') }}"/>
                    </div>
                    <p class="mb-0">
                        <label class="choose-file">
                            Choose file
                            <input type="file" 
                                   class="d-none supporting-file" 
                                   name="supporting_files[]" 
                                   accept="image/jpeg,image/jpg,image/png,video/mp4,video/mov,video/avi,video/mkv,application/pdf"
                                   onchange="handleFileSelect(this)">
                        </label>
                        or drag and drop
                    </p>
                    <small class="text-muted">Images (PNG, JPG), Videos (MP4, MOV, AVI, MKV), or PDF up to 10MB</small>
                </div>
                <div class="selected-file-info mt-2" style="display: none;">
                    <span class="file-name"></span>
                    <button type="button" class="btn btn-sm btn-danger ml-2" onclick="removeFile(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>`;
    
        container.appendChild(newUploadItem);
        fileIndex++;
    }

    function removeUploadItem(button) {
        const uploadItem = button.closest('.upload-item');
        uploadItem.remove();
    }

    // Drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('file-upload-container');
    
        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const uploadBox = e.target.closest('.upload-box');
                if (uploadBox) {
                    uploadBox.style.borderColor = '#007bff';
                    uploadBox.style.backgroundColor = '#f0f8ff';
                }
            });
    
        container.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const uploadBox = e.target.closest('.upload-box');
            if (uploadBox && !uploadBox.classList.contains('file-selected')) {
                uploadBox.style.borderColor = '#ccc';
                uploadBox.style.backgroundColor = '#f9f9f9';
            }
        });
    
        container.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        
            const uploadBox = e.target.closest('.upload-box');
                if (uploadBox) {
                    uploadBox.style.borderColor = '#ccc';
                    uploadBox.style.backgroundColor = '#f9f9f9';
            
                const input = uploadBox.querySelector('.supporting-file');
                    if (input && e.dataTransfer.files.length > 0) {
                        input.files = e.dataTransfer.files;
                        handleFileSelect(input);
                    }
                }
            });

        const form = document.getElementById('prjAddForm');
        const btnSaveDraft = document.getElementById('btnSaveDraft');
        const btnSubmit = document.getElementById('btnSubmit');

        btnSaveDraft.addEventListener('click', function() {
            document.getElementById('projectPublish').value = 0;
            if (form.checkValidity()) {
                setTimeout(function() {
                    btnSaveDraft.disabled = true;
                    btnSubmit.disabled = true;
                }, 100);
            }
        });

        btnSubmit.addEventListener('click', function() {
            document.getElementById('projectPublish').value = 1;
            if (form.checkValidity()) {
                setTimeout(function() {
                    btnSaveDraft.disabled = true;
                    btnSubmit.disabled = true;
                }, 100);
            }
        });
    });

</script>

<!-- <script src="{{asset('asset/dist/js/chunk-functions.js')}}"></script> -->

@endsection
