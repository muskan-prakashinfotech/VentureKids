let browseFile = $(`#${uploadElement}`);
let resumable = new Resumable({
    target: target,
    query: function (file) {
        return {
            _token: token,
            moduleName: moduleName
        };
    },
    fileType: fileType,
    chunkSize: chunkSize, // default is 1*1024*1024, this should be less than your maximum limit in php.ini
    headers: {
        'Accept' : 'application/json'
    },
    testChunks: false,
    throttleProgressCallbacks: 1,
});

resumable.assignBrowse(browseFile[0]);

resumable.on('fileAdded', function (file) { // trigger when file picked
    showProgress();
    resumable.upload() // to actually start uploading.
});

resumable.on('fileProgress', function (file) { // trigger when file progress update
    updateProgress(Math.floor(file.progress() * 100));
});

resumable.on('fileSuccess', function (file, response) { // trigger when file upload complete
    response = JSON.parse(response);
    $('#uploadedFileName').val(response.name);
    switch(moduleName) {
        case 'scorm':
            $("#no_of_questions").prop('required',true);
            break;
        case 'trainer-content':
            $('#displayFileName').val(response.display_name);
            break;
        default:
            break;
    }
    $(`#${submitBtnName}`).removeAttr('disabled');
});

resumable.on('fileError', function (file, response) { // trigger when there is any error
    alert('file uploading error.')
    window.location.reload();
    hideProgress();
    if(moduleName == 'scorm' || moduleName == 'scormAssignment') {
        $('.scorm-view-download').addClass('d-flex');
        $('.scorm-view-download').show();
    }
    $(`#${submitBtnName}`).removeAttr('disabled');
});


let progress = $('.progress');
function showProgress() {
    progress.find('.progress-bar').css('width', '0%');
    progress.find('.progress-bar').html('0%');
    progress.find('.progress-bar').removeClass('bg-success');
    progress.show();
    if(moduleName == 'scorm' || moduleName == 'scormAssignment') {
        $('.scorm-view-download').removeClass('d-flex');
        $('.scorm-view-download').hide();
    }
    $(`#${submitBtnName}`).attr('disabled','disabled');
}

function updateProgress(value) {
    progress.find('.progress-bar').css('width', `${value}%`)
    progress.find('.progress-bar').html(`${value}%`)
}

function hideProgress() {
    progress.hide();
}