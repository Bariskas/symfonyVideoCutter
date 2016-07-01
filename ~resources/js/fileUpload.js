$(function() {
    initCustomFileInput();
    initVideoLoadForm();
});

function initCustomFileInput()
{
    var hiddenInputFile = $('.input_file');
    $('.load_file_button').click( function() {
        hiddenInputFile.click();
    });
    $(document).on('change', '.input_file' , function() {
        var spliParts = hiddenInputFile.val().split('\\');
        $('.load_file_name').val(spliParts[spliParts.length - 1]);
    });
}

function initVideoLoadForm()
{
    var form = $('#loadForm');
    var progress = $('.progress');
    var bar = $('.bar');
    var percent = $('.percent');
    var status = $('#status')
    form.ajaxForm({
        beforeSend: function() {
            progress.show();
            var percentVal = '0%';
            bar.width(percentVal);
            percent.html(percentVal);
            status.text('Загрузка на сервер');
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal);
            percent.html(percentVal);
            if (percentComplete == 100)
            {
                status.text('Обработка видео на сервере');
            }
        },
        complete: function(xhr) {
            progress.hide();
            status.hide();
            var response = JSON.parse(xhr.responseText);
            $('.load_form_container').html(response['html']);
            initSlider(response['duration']);
            initButton();
        }
    });
}

function  initSlider(maxValue)
{
    var slider = $('#slider');
    var video = $('video')[0];
    var beginTimeOffset = 0;
    var endTimeOffset = Math.floor(maxValue);
    var fromInput = $('#from');
    var toInput = $('#to');

    video.addEventListener("timeupdate", function() {
        if (video.currentTime >= endTimeOffset)
        {
            video.currentTime = endTimeOffset;
            video.pause();
        }
        else if (video.currentTime <= beginTimeOffset)
        {
            video.currentTime = beginTimeOffset;
        }
    }, false);

    var saveResult = function (data) {
        fromInput.attr("value", data.from);
        toInput.attr("value", data.to);
        video.currentTime = data.from;
        endTimeOffset = data.to;
        beginTimeOffset = data.from;
        if (data.to - data.from < 15)
        {
            $('.sizes_input_group').show();
        }
        else
        {
            $('.sizes_input_group').hide();
        }
    };

    fromInput.attr("value", 0);
    toInput.attr("value", maxValue);

    slider.ionRangeSlider({
        hide_min_max: true,
        keyboard: true,
        min: 0,
        max: Math.floor(maxValue),
        from: 1,
        min_interval: 2,
        to: Math.floor(maxValue) - 1,
        type: 'double',
        step: 1,
        grid: true,
        onLoad: saveResult,
        onFinish: saveResult,
        prettify: function(value){
            var timeString = '';
            var minutes = Math.floor(value / 60);
            if (minutes)
            {
                timeString = minutes + ' м ';
            }
            timeString += Math.round(value % 60) + ' с'
            return timeString;
        }
    });
}

function initButton()
{
    $('.clip_button').on("click", function() {
        var preloader = $('.preloader');
        var downloadButton = $('.download_btn');
        var clipButton = $('.clip_button')
        preloader.show();
        clipButton.hide();
        $("#cutForm").ajaxSubmit({
            url: 'cut-video',
            type: 'post',
            data: { newWidth: $('#newWidth:visible').val(), newHeight: $('#newHeight:visible').val() },
            complete: function(xhr) {
                $('.preloader').hide();
                var response = JSON.parse(xhr.responseText);
                $('.download_link').attr({href: response.link});
                downloadButton.show();
                clipButton.show();
            },
            error: function() {
                clipButton.show();
            }
        });
    });
}