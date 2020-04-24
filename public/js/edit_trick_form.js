// Arrange display categories form
var $formLabel = $('legend.col-form-label');
$formLabel.removeClass('col-sm-2');

// $('div.formImageHidden').hide();
$('div.form-image-hidden').hide();

$('a.add-image-form').click(function(){
        $('div.form-image-hidden').toggle()
    }
);

// display uploaded file
$('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
});


// $('div.formImageHidden').hide();
$('div.form-video-hidden').hide();

$('a.add-video-form').click(function(){
        $('div.form-video-hidden').toggle()
    }
);
