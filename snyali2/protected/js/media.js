$(document).ready(function(){
    $("body").on('click', 'a.media-show', function(){
        $(this).parent().parent().find('.media').css('overflow', 'visible').css('max-height', '100%');
        $(this).remove();
        return false;
    });
});