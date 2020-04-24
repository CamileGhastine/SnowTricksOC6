// Arrange display categories form
var $formLabel = $('legend.col-form-label');
$formLabel.removeClass('col-sm-2');

var $categoriesFieldset =  $('.categories > fieldset > div')
var $categoriesFieldsetLegend = $('.categories > fieldset > div > legend');
$categoriesFieldsetLegend.addClass('col-4');
$categoriesFieldset.find('div').removeClass('col-sm-10');
$categoriesFieldset.find('div').addClass('col-6');

//hide the image upload label when form not valid
$formLabel.hide();
$categoriesFieldsetLegend.show();

// setup an "add an image" link
var $addImageLink = $('<a href="#" class="add_image_link"><i class="fas fa-plus-circle"></i></a>');
var $newLink = $('<div class="text-center"></div>').append($addImageLink);

jQuery(document).ready(function() {
    // Get the ul that holds the collection of images
    var $collectionHolder = $('div.images');

    // add the "add an image" anchor and li to the images ul
    $collectionHolder.append($newLink);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addImageLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new image form (see code block below)
        addImageForm($collectionHolder, $newLink);
    });


});

function addImageForm($collectionHolder, $newLink) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '$$name$$' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add an image" link li
    var $newForm = $('<div class="row justify-content-center"></div>').append(newForm);

    // also add a remove button, just for this example
    $newForm.append('<a href="#" class="remove-image col-2"><i class="far fa-window-close"></i></a>');

    $newLink.before($newForm);

    // handle the removal, just for this example
    $('.remove-image').click(function(e) {
        e.preventDefault();

        $(this).parent().remove();

        return false;
    });

    // display uploaded file
    $('.custom-file-input').on('change', function() {
        var $el = $(this),
            files = $el[0].files,
            label = files[0].name;
        if (files.length > 1) {
            label = label + " and " + String(files.length - 1) + " more files"
        }
        $el.next('.custom-file-label').html(label);
    });
}

// setup an "add a tag" link
var $addVideoLink = $('<a href="#" class="add_video_link"><i class="fas fa-plus-circle"></i></a>');
var $newLinkVideo = $('<div class="text-center"></div>').append($addVideoLink);

$('#explanation').hide();

jQuery(document).ready(function() {
    // Get the ul that holds the collection of tags
    var $collectionHolderVideo = $('div.videos');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolderVideo.append($newLinkVideo);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolderVideo.data('index', $collectionHolderVideo.find(':input').length);

    $addVideoLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        $('#explanation').show();

        // add a new tag form (see code block below)
        addVideoForm($collectionHolderVideo, $newLinkVideo);

    });


});

function addVideoForm($collectionHolder, $newLink) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '$$name$$' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormVideo = $('<div class="row justify-content-center"></div>').append(newForm);

    // also add a remove button, just for this example
    $newFormVideo.append('<a href="#" class="remove-tag col-2"><i class="far fa-window-close"></i></a>');

    $newLink.before($newFormVideo);


    // handle the removal, just for this example
    $('.remove-tag').click(function(e) {
        e.preventDefault();

        $(this).parent().remove();

        return false;
    });
}


