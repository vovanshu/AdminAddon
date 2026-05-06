$(document).ready(function() {

    $('[id=property-queries]').on('focus click input', '.query-text', function() {
        let val = $(this).val();
        var fieldContainer = $(this).parents('.value');
        var property_selector = $(fieldContainer).find('.query-property');
        if ( property_selector.length && property_selector[0].length ){
            current = property_selector[0];
            var selectedOption = current.options[current.selectedIndex];
            var term = $(selectedOption).data('term');
            var property_id = $(selectedOption).data('property-id');
            var ApiUrl = window.location.origin + '/api/admin-addon/suggestions';
            if( window.OmekaSiteSlug ) {
                ApiUrl = ApiUrl + '/' + window.OmekaSiteSlug;
            }
            var allfileds = true;
            if ( Array.isArray(AdminAdonNeededFields) && AdminAdonNeededFields.length > 0 && Object.keys(AdminAdonNeededFields[0]).length > 0 ) {
                allfileds = false;
            }
            if(allfileds || !allfileds && AdminAdonNeededFields.includes(term)){
                $(this).autocomplete({
                    source: function(request, callback) {
                        $.ajax({
                            url: ApiUrl,
                            data: {
                                term: term,
                                property_id: property_id,
                                value: val,
                                controller: OmekaAdonController,
                                action: OmekaAdonAction,
                                site_slug: OmekaSiteSlug
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.success && data.suggestions) {
                                    callback(data.suggestions);
                                } else {
                                    callback([]);
                                }
                            },
                            error: function() {
                                callback([]);
                            }
                        });
                    },
                    appendTo: fieldContainer,
                    minLength: 0,
                    maxHeight: 200,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        return false;
                    },
                    focus: function(event, ui) {
                        $(this).val(ui.item.label);
                        return false;
                    }
                });
                $(this).autocomplete("search", $(this).val());

                // if (items.length > 20)
                // ul.removeClass( 'ui-autocomplete-noscroll' ).addClass( 'ui-autocomplete-scroll' );
                // else
                // ul.removeClass( 'ui-autocomplete-scroll' ).addClass( 'ui-autocomplete-noscroll' );

            }else{
                if( $(fieldContainer).find('.ui-autocomplete').length > 0 ){
                    $(this).autocomplete("destroy");
                }
            }
        }

    });

});
