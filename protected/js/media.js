$(document).ready(function(){
    $("body").on('click', '.media-show-panel', function(){
        $(this).parent().find('.media')
                .css('overflow', 'visible')
                .css('max-height', '100%')
                .parent().css('padding-bottom', '10px');
        $(this).remove();
        return false;
    });
});