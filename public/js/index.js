// load More button
var $page = 1;

$(document).ready(function(){
    $("#button").click(function(e){
        e.preventDefault();
        var $url = this.href;
        $.post($url,
            { 'page' : $page },
            function ($data) {
                $('#ajax-load-more').before($data);
                $page = $page + 1;
            }
        );
    });
});