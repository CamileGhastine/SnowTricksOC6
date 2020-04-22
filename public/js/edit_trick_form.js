// Arrange display categories form
var $formLabel = $('legend.col-form-label');
$formLabel.removeClass('col-sm-2');

// $('div.formImageHidden').hide();
$('div.formImageHidden').hide();

$('a.add-form').click(function(){
        $('div.formImageHidden').toggle()
    }
);

$('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
});