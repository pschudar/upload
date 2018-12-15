;
(function ($) {
    var fileList = [],
            fileInput = $('input[type="file"]')[0],
            resetBtn = $('#reset'),
            submitBtn = $('#submit');
    resetBtn.toggle();
    submitBtn.prop('disabled', true);

    resetSubmitBtn = function () {
        submitBtn.prop('disabled', true);
    };
    resetFileList = function () {
        fileList = [];
        $('input[type="file"]').val('');
        $('label').find('span.badge.badge-light').remove();
        resetSubmitBtn();
    };
    resetForm = function () {
        resetBtn.on('click', function () {
            resetBtn.hide();
            $('div#leftCard').find('table#uploadPreview').html('');
            resetFileList();
        });
    };
    validateSize = function (size) {
        var max_upload = 209175200;
        switch (size > max_upload || size <= 0) {
            case true:
                return false;
                break;
            default:
                return true;
                break;
        }
    };
    validateType = function (file) {
        switch (/\.(jpe?g|png|gif|pdf|mov|mp4|mp3|doc)$/i.test(file.name)) {
            case false:
                return false;
                break;
            default:
                return true;
                break;
        }
    };
    formatBytes = function (bytes, decimals) {
        if (bytes === 0)
            return '0 KB';
        var k = 1000,
                dm = decimals + 1 || 3,
                sizes = ['Bytes', 'KB', 'MB', 'GB'],
                i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };
    buildFileBtn = function (file, i) {
        var icon = 'images';
        switch (validateType(file)) {
            case true:
                var istyle = 'text-success',
                        icolor = 'text-success';
                break;
            case false:
                icon = 'exclamation-circle',
                        istyle = 'text-danger',
                        icolor = 'text-danger';
                break;
        }
        return $('div.card-body').find('span.prev').eq(i).append('<a class="btn btn-app" href="#"><span class="vl vl-' + icon + ' ' + icolor + '"></span><span class="' + istyle + '">' + file.type + '</span></a>');
    };
    buildSizeBtn = function (file, i) {
        switch (validateSize(file.size)) {
            case true:
                var sizeIcon = 'paperclip',
                        sizeColor = 'text-success';
                break;
            case false:
                var sizeIcon = 'exclamation-circle',
                        sizeColor = 'text-danger';
                break;
        }
        return '<a class="btn btn-app"><span class="vl vl-' + sizeIcon + ' ' + sizeColor + '"></span><span class="' + sizeColor + '">' + formatBytes(file.size, 1) + '</span></a>';
    };
    buildBtn = function (btnClass, btnIcon, btnTitle) {
        return '<a class="' + btnClass + '"><span class="' + btnIcon + '"></span>' + btnTitle + '</a>';
    };
    previewFiles = function (prev, file, i) {
        if (!prev) {
            buildFileBtn(file, i);
        } else {
            preview = function (file) {
                switch (validateType(file)) {
                    case true:
                        var reader = new FileReader(),
                                img = new Image();
                        switch (validateSize(file.size)) {
                            case true:
                                reader.onload = function (e) {
                                    img.title = file.type;
                                    img.src = reader.result;
                                    $('div.card-body').find('span.prev').eq(i).append('<a style="-o-background-size: cover; -moz-background-size: cover; -webkit-background-size: cover; background-size: cover; background-repeat: no-repeat; background-image: url(' + img.src + ');" class="bimg btn btn-app"></a>');
                                };
                                break;
                            case false:
                                buildFileBtn(file, i);
                                break;
                        }
                        reader.readAsDataURL(file);
                        break;
                    default:
                        buildFileBtn(file, i);
                        break;
                }
            };
            preview(file);
        }
    };
    handleFiles = function () {
        var container = $('div#leftCard'),
                row = $('<tr/>', {
                    position: 'relative'
                }).css('zIndex', 'initial'),
                cell = $('<td/>'),
                table;
        //create or select table
        if (!container.find('table').length) {
            table = $('<table/>', {
                id: 'uploadPreview',
                class: 'table table-hover table-striped table-condensed'
            });
        } else {
            table = $('div#leftCard').find('table#uploadPreview');
            // clear previous selection
            table.html('');
        }
        //build table rows
        $.each(fileInput.files, function (i, file) {
            var fileRow = row.clone();
            cell.clone().html('<span class="prev"></span>').appendTo(fileRow);
            cell.clone().html(buildSizeBtn(file)).appendTo(fileRow);
            cell.clone().html(buildBtn('btn btn-app', 'vl vl-tag', file.name)).appendTo(fileRow);
            cell.clone().html(buildBtn('btn btn-app', 'vl vl-upload', 'Progress: <span id="progress">--</span>')).appendTo(fileRow);
            cell.clone().html(buildBtn('btn btn-app js-rm', 'vl vl-times text-danger', '<span class="text-danger">Remove</span>')).appendTo(fileRow);
            fileRow.appendTo(table);
            fileList.push(file);
            switch (!container.find('table').length) {
                case true:
                    table.appendTo(container);
                    break;
            }
            previewFiles(true, file, i); // pass true to show image previews or false for an application button w/icon
        });
    };
    //update progressbar
    handleProgress = function (e, i, progress) {
        var complete = Math.round((e.loaded / e.total) * 100);
        $('span#progress').eq(i).text(complete + '%');
    };
    uploadSelected = function () {
        var allXHR = [],
                _messages = [];
        switch (submitBtn.prop('disabled')) {
            case true:
                // if submitBtn is disabled, don't do the upload!
                break;
            default:
                $.each(fileList, function (i, img) {
                    // All within the loop to allow for progress handling
                    var fd = new FormData(),
                            prog = $('div.progress-bar').eq(i);
                    fd.append('upload', 'vLotXHR');
                    fd.append('file-' + i, img);
                    // Send FormData to script for processing
                    allXHR.push($.ajax({
                        type: 'POST',
                        url: 'bk-upload.php',
                        data: fd,
                        dataType: 'JSON',
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            _messages.push(data);
                            resetSubmitBtn();
                        },
                        error: function () {
                            $('tr').eq(i).find('td').append('<ul class="list-group upstat"><li class="list-group-item label-danger"><span class="vl vl-exclamation-triangle"></span> ' + img.name + ' (Size: ' + formatBytes(img.size, 1) + ' / Max Post: ' + formatBytes(8388608, 1) + ')</li></ul>');
                            $('tr').eq(i).find('td').eq(4).find('a.js-rm span:first-child').removeClass('vl vl-times').addClass('vl vl-exclamation-triangle');
                            $('span.vl.vl-upload').eq(i).addClass('text-danger');
                            $('tr').eq(i).find('td').eq(4).find('a.js-rm span:first-child').removeClass('text-danger vl vl-times').addClass('text-danger vl vl-exclamation-triangle');
                            $('tr').eq(i).find('td').eq(4).find('a.js-rm span:nth-child(2)').text('Error');
                            $('.upstat').show();
                            resetFileList();
                        },
                        xhr: function () {
                            //add progress handler
                            var xhr = jQuery.ajaxSettings.xhr();
                            if (xhr.upload) {
                                xhr.upload.onprogress = function (e) {
                                    handleProgress(e, i, prog);
                                };
                            }
                            return xhr;
                        }
                    }).done(function () {
                        $.each(_messages, function (idx, msg) {
                            switch (msg.error) {
                                case 'true':
                                    var color = 'danger',
                                            statusIcon = 'vl vl-exclamation-circle',
                                            statusTxt = 'Failed';
                                    break;
                                case 'false':
                                    var color = 'success',
                                            statusIcon = 'vl vl-hdd',
                                            statusTxt = 'File Saved';
                                    break;
                            }
                            $('tr').eq(i).find('td').append('<ul class="list-group upstat"><li class="list-group-item label-' + msg.class + '"><span class="vl vl-' + msg.icon + '"></span> ' + msg.msg + '</li></ul>');
                            $('tr').eq(i).find('td').eq(4).find('a.js-rm span:first-child').removeClass('text-danger vl vl-times').addClass('text-' + color + ' ' + statusIcon);
                            $('tr').eq(i).find('td').eq(4).find('a.js-rm span:nth-child(2)').removeClass('text-danger').addClass('text-' + color).text(statusTxt);
                            $('.upstat').show();
                            $('span.vl.vl-upload').eq(i).addClass('text-' + color);
                            _messages = [];
                        });
                        resetFileList();
                    }));
                });
        }
    };
    $('div#leftCard').on('click', 'a.btn.btn-app', function (e) {
        e.preventDefault();
    });
    $('div.card-body').find('span#toggleLeft').on('click', function () {
        toggleLeft('toggle');
    });
    resetBtn.on('click', resetForm());
    submitBtn.on('click', function (e) {
        e.preventDefault();
        $(this).trigger('blur');
        uploadSelected();
    });
    $('div#leftCard').on('click', 'a.js-rm', function () {
        var link = $(this),
                removed,
                filename = link.closest('tr').children().eq(2).text();
        $.each(fileList, function (i, item) {
            if (item.name === filename) {
                removed = i;
            }
        });
        fileList.splice(removed, 1);
        switch (fileList.length) {
            case 0:
                resetBtn.click();
                break;
            default:
                $('label').find('span.badge.badge-light').text(fileList.length);
                break;
        }
        link.closest('tr').remove();
    });
    $(fileInput).on('change', function () {
        fileList = [];
        resetBtn.show();
        submitBtn.prop('disabled', false);
        if ($('div#uploadCard').find('span#toggleLeft').hasClass('vl-toggle-off')) {
            $('div#uploadCard').find('span#toggleLeft').click();
        }
        handleFiles();
    });
}(jQuery));