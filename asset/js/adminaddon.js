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

        $.each($('header').find('div'), function(index, value) {
            var objheiht = $(value).height();
            headerdivs_height = headerdivs_height + objheiht;

        });

        // $( objbody ).css( "overflow-y", "hidden" );

        // $( objbody ).find('div.flex').css( "height", window_height + "px" );

        // $( objnavmenu ).css( "height", (window_height - headerdivs_height - 30) + "px" );
        // $( objnavmenu ).css( "overflow-y", "auto" );

        // console.log($(objcontent).find('form[id="batch-form"]').find('div').is('tablesaw-bar'));
        // console.log(window_height + ' | ' + height_tbody);
        // console.log(height_content_title + ' ' + height_content_controls + ' ' + height_content_tbbar + ' ' + height_content_tbhead + ' ' + height_footer);

    });


})(jQuery);


// (function ($) {
//     $(document).on('click','.tablesaw th[data-sort_by]', function(e) {
//         let realTarget = document.elementFromPoint(e.clientX, e.clientY);
//         if ( realTarget.tagName !== 'TH' ) {
//             return;
//         }
//         let item = $(this);
//         let sortOrder = $("select[name='sort_order']");
//         let sortBy = $("select[name='sort_by']");

//         sortBy.val(item.data('sort_by')).change();
//         if ( item.hasClass('sort-active') ) {
//             sortOrder.val( sortOrder.val() === 'desc' ? 'asc' : 'desc' ).change();
//         }
//         $("form.sorting").submit();
//     });

//     $(document).on('click', '.sortable-down', function() {
//         let block = $(this).closest('.block');
//         block.insertAfter(block.next());
//     });

//     $(document).on('click', '.sortable-up', function() {
//         let block = $(this).closest('.block');
//         block.insertBefore(block.prev());
//     })

    // $('.block-options-icon').on('click', function(e) {
    //     e.preventDefault();
    //     console.log('ola')
    //     $(this).closest('.block').find('.block-options').toggleClass('active')
    // })
// })(jQuery);
