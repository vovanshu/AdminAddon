$(document).ready(function() {

    $('.resource-property').on('focus click input', '.input-value', function() {
        let val = $(this).val();
        var fieldContainer = $(this).parents('.resource-property');
        if ( fieldContainer.length ){
            var term = $(fieldContainer).data('property-term');
            var property_id = $(fieldContainer).data('property-id');
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
                                action: OmekaAdonAction
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
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        return false;
                    },
                    focus: function(event, ui) {
                        $(this).val(ui.item.label);
                        return false;
                    }
                });
            }
        }

    });

});
