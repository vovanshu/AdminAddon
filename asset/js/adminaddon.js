(function ($) {

    $(document).ready(function() {

        var window_height = $(window).height();
        var window_width = $(window).width();
        var objbody = $('body');
        var objheader = $('header');
        var objnavmenu = $('[id=menu]');
        var objcontent = $('[id=content]');
        var height_content_title = $(objcontent).find('h1').height();
        var height_content_controls = $(objcontent).find('[class="browse-controls"]').height();
        var height_content_tbbar = $(objcontent).find('form').find('[class="tablesaw-bar"]').height();
        var height_content_tbhead = $(objcontent).find('form').find('[class="tablesaw"]').find('thead').height();
        var height_footer = $('footer').height();
        var height_tbody = (window_width - height_content_title - height_content_controls - height_content_tbbar - height_content_tbhead - height_footer);
        // var content_inheight = objcontent.innerHeight();
        // var objheaderdivs = $('header').find('div');
        var headerdivs_height = 0;

        // $.each($('header').find('div'), function(index, value) {
        //     var objheiht = $(value).height();
        //     headerdivs_height = headerdivs_height + objheiht;

        // });


        // $( objbody ).css( "overflow-y", "hidden" );

        // $( objbody ).find('div.flex').css( "height", window_height + "px" );

        // $( objnavmenu ).css( "height", (window_height - headerdivs_height - 30) + "px" );
        // $( objnavmenu ).css( "overflow-y", "auto" );

        // console.log($(objcontent).find('form[id="batch-form"]').find('div').is('tablesaw-bar'));
        // console.log(window_height + ' | ' + height_tbody);
        // console.log(height_content_title + ' ' + height_content_controls + ' ' + height_content_tbbar + ' ' + height_content_tbhead + ' ' + height_footer);

    });

})(jQuery);
