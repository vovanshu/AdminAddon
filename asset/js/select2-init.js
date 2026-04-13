(function ($) {

    $(document).ready(function() {

        $(".chosen-select").select2({
            tags: true,
            tokenSeparators: [',', ' ']
        })

    });


})(jQuery);
