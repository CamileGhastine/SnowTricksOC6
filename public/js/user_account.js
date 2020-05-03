// Show form on click
$('.form').hide();
$('.upload').click(function(e){
    e.preventDefault();
    $('.form').show();

})

// display uploaded file
$('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
});