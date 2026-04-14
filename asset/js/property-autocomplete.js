$(document).ready(function() {

    $('[id=property-queries]').on('input', '.query-text', function() {
        let val = $(this).val();
        var fieldContainer = $(this).parents('.value');
        var property_selector = $(fieldContainer).find('.query-property');
        if ( property_selector.length && property_selector[0].length){
            current = property_selector[0];
            var selectedOption = current.options[current.selectedIndex];
            var term = $(selectedOption).data('term');
            var property_id = $(selectedOption).data('property-id');

            $(this).autocomplete({
                source: function(request, callback) {
                    $.ajax({
                        url: AdminAdonAutocompleteURL,
                        data: {
                            term: term,
                            property_id: property_id,
                            value: val,
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

    });

});
