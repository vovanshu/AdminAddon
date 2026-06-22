// $(window).on('beforeunload', function() {
//     var $preloader = $('#page-preloader');
//     $preloader.show();
// });

$(document).ready(function() {

    var $preloader = $('#page-preloader');

    $preloader.addClass('fade-out');
    setTimeout(function() { $preloader.hide(); }, 400);

    // Варіант Б (альтернатива): Якщо треба чекати і важкі картинки/скрипти, 
    // розкоментуйте код нижче, а рядки Варіанту А вище — видаліть:
    /*
    $(window).on('load', function() {
        $preloader.addClass('fade-out');
        setTimeout(function() { $preloader.hide(); }, 400);
    });
    */

    $('a').on('click', function(e) {
        var href = $(this).attr('href');
        var target = $(this).attr('target');

        if (href && href !== '#' && href !== 'javascript:void(0);' && !target) {
            clearTimeout(loaderTimeout);
            $preloader.show().removeClass('fade-out');
            loaderTimeout = setTimeout(function() {
                $preloader.addClass('fade-out');
                setTimeout(function() { $preloader.hide(); }, 400);
                alert(Omeka.jsTranslate('There was an error connecting to the server. Please try again later.'));
            }, 25000);
        }
    });

    const observer = new MutationObserver(function(mutations, obs) {
        const target = $('.autocomplete-suggestions');
        if (target.length) {
            target.appendTo('#resource-values');
            obs.disconnect();
        }
        // const target = $('.autocomplete');
        // if (target.length) {
        //     target.autocomplete("option", "appendTo", "#resource-values");
        //     obs.disconnect();
        // }
    });

    // observer.observe(document.body, { childList: true, subtree: true });
    // $(".autocomplete").autocomplete("option", "appendTo", "#resource-values");

    // $.each($('textarea'), function(index, value) {
        //     var objheiht = $(value).height();
        //     headerdivs_height = headerdivs_height + objheiht;
        // var attrs = value.attributes;
        // console.log(index + ' - ' + attrs);
        // console.log(attrs.class);

    // });

    // const baseUrl = window.location.pathname.replace(/\/admin\/.*/, '/');

    // const templateData = $('#resource-values').data('template-data');

    // $.each($('.autocomplete-suggestions'), function(index, value) {
        // console.log(value);
    // });

    // var templateSelect = $('#resource-template-select');
    // var templateId = templateSelect.val();

    // // var resource_template_property;

    // if (templateId) {

    //     var url = templateSelect.data('api-base-url') + '/' + templateId;
    //     $.get(url)
    //         .done(function(data) {
    //             // var templateData = data['o:data'] ? data['o:data'] : {};
    //             // templateData['o:resource_class'] = data['o:resource_class'];
    //             // $('#resource-values').data('template-data', templateData);
    //             var resource_template_property = data['o:resource_template_property'];
    //             // console.log(data);
    //             resource_template_property.forEach(function(value, index) {
    //                 if(value['o:data'][0]['autocomplete']){
    //                     // console.log(value);
    //                     var filedset = $('[data-property-id=' + value['o:property']['o:id'] + ']');
    //                     var filed = $('[data-property-id=' + value['o:property']['o:id'] + '] .input-value');
    //                     // console.log(filed);
    //                     $(filed).autocomplete("option", "appendTo", "#resource-values");
                        
    //                     //data-property-id="2"
    //                 }
    //                 // console.log(value['o:data'][0]['autocomplete']);
    //             });

    //         })
    //         .fail(function() {
    //             console.log('Failed loading resource template from API');
    //         })
    //         .always(function() {

    //         });

            
    // }


    // const fields = $('#properties .resource-property .input-value');

    // if (typeof $('#resource-values').data('is-loaded') !== 'undefined') {
        // fields.each(function(index, field) {
            // console.log($(field));
        // });

        // console.log(templateData);
    // }
    // $.each($(templateData), function(autofillerName) {
    //     $.get(baseUrl + 'admin/autofiller/settings', {
    //         service: autofillerName,
    //         template: $('#resource-template-select').val(),
    //     })
    //     .done(function(data) {
    //         console.log(data);
    //     })
    //     .fail(function(jqXHR, textStatus) {

    //     })
    //     .always(function() {

    //     });
    // });

});
