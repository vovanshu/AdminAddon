(function ($) {
    $(document).ready(function() {

        $.each($('.facet-range-double, .range-doubleform-control'), function(index, container) {
            if (!container.hasAttribute('data-initialized')) {
                initializeRangeSlider(container);
                container.setAttribute('data-initialized', 'true');
            }
        });

        $('.search-facets').on('click', '.facets-reset', function(e) {
            // e.preventDefault();
            // console.log(this);
            $.each($('.facets-list').find('[data-type="faset"]'), function(index, container) {
                // console.log(container);
            });

            getListFasets(true);

            $('.facets-form').submit();

        });

        // $.each($('.facets-list').find('[data-type="faset"]'), function(index, container) {
        //     console.log(container);
        // });

        $('.search-facets').ready(function() {
            getListFasets();
        });

        

        function getListFasets(reset = false){
            var query = {};
            $.each($('[data-type="query"]'), function(index, container) {
                query[container.name] = container.value;
            });
            $.ajax({
                url: AdminAdonListFasetsURL,
                data: {
                    controller: AdminAdonController,
                    action: AdminAdonAction,
                    site_slug: AdminAdonSiteSlug,
                    query: query
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success && data.list) {
                        // console.log(data.list);
                        $.each(data.list, function(fasetid, fasetvalue) {
                            var value_set_query = $('[data-value-set="query"]');

                            if ( fasetvalue.range ){
                                var container = $('[id="' + fasetid + '"]');
                                var fromSlider = container.find('.range-slider-from');
                                var toSlider = container.find('.range-slider-to');
                                var fromInput = container.find('.range-numeric-from');
                                var toInput = container.find('.range-numeric-to');

                                if ( fasetvalue.range.min_value && fasetvalue.range.max_value ){
                                    var vmin = fasetvalue.range.min_value;
                                    var vmax = fasetvalue.range.max_value;
                                    // console.log('set range max = ' + vmax + ' min = ' + vmin);
                                    container.attr('data-range', `${Math.round(vmin)} - ${Math.round(vmax)}`);
                                    fromSlider.attr('min', vmin).attr('max', vmax).attr('aria-valuemin', vmin).attr('aria-valuemax', vmax);
                                    fromInput.attr('min', vmin).attr('max', vmax);
                                    toSlider.attr('min', vmin).attr('max', vmax).attr('aria-valuemin', vmin).attr('aria-valuemax', vmax);
                                    toInput.attr('min', vmin).attr('max', vmax);
                                    if ( !value_set_query.length || reset ){
                                        // console.log('set val max = ' + vmax + ' min = ' + vmin);
                                        fromSlider.attr('aria-valuenow', vmin).val(vmin);
                                        fromInput.val(vmin);
                                        toSlider.attr('aria-valuenow', vmax).val(vmax);
                                        toInput.val(vmax);
                                    }else{
                                        var valfrom = container.find('.range-slider-from').val();
                                        var valto = container.find('.range-slider-to').val();
                                        console.log('set valuenow max = ' + valto + ' min = ' + valfrom);
                                        fromSlider.attr('aria-valuenow', valfrom);
                                        toSlider.attr('aria-valuenow', valto);
                                    }
                                    initializeRangeSlider(container[0]);
                                    
                                }

                            }

                            
                        });
                        
                    } else {
                        console.log(data);
                    }
                },
                error: function() {
                    console.log('Error:' + AdminAdonListFasetsURL);
                }
            });

        }

        function initializeRangeSlider(container) {
            const fromSlider = container.querySelector('.range-slider-from');
            const toSlider = container.querySelector('.range-slider-to');
            const fromInput = container.querySelector('.range-numeric-from');
            const toInput = container.querySelector('.range-numeric-to');
            
            if (!fromSlider || !toSlider || !fromInput || !toInput) {
                console.warn('Range slider elements not found in container:', container);
                return;
            }
            
            const newFromSlider = fromSlider.cloneNode(true);
            const newToSlider = toSlider.cloneNode(true);
            fromSlider.parentNode.replaceChild(newFromSlider, fromSlider);
            toSlider.parentNode.replaceChild(newToSlider, toSlider);
            
            const fromSliderEl = newFromSlider;
            const toSliderEl = newToSlider;
            
            const min = parseFloat(fromSliderEl.min);
            const max = parseFloat(fromSliderEl.max);
            
            function updateSliderBackground() {
                const fromValue = parseFloat(fromSliderEl.value);
                const toValue = parseFloat(toSliderEl.value);
                
                const fromPercent = ((fromValue - min) / (max - min)) * 100;
                const toPercent = ((toValue - min) / (max - min)) * 100;
                
                const gradient = `linear-gradient(to right, 
                    #e9ecef 0%, 
                    #e9ecef ${fromPercent}%, 
                    #0d6efd ${fromPercent}%, 
                    #0d6efd ${toPercent}%, 
                    #e9ecef ${toPercent}%, 
                    #e9ecef 100%)`;
                
                toSliderEl.style.background = gradient;
            }
            
            function validateRange() {
                let fromValue = parseFloat(fromSliderEl.value);
                let toValue = parseFloat(toSliderEl.value);
                
                if (fromValue > toValue) {
                    [fromValue, toValue] = [toValue, fromValue];
                    fromSliderEl.value = fromValue;
                    toSliderEl.value = toValue;
                    fromInput.value = fromValue;
                    toInput.value = toValue;
                }
                
                updateSliderBackground();
                updateRangeDisplay(container, fromValue, toValue);
            }
            
            function updateRangeDisplay(container, fromValue, toValue) {
                container.setAttribute('data-range', `${Math.round(fromValue)} - ${Math.round(toValue)}`);           
                fromSliderEl.setAttribute('aria-valuemin', min);
                fromSliderEl.setAttribute('aria-valuemax', max);
                fromSliderEl.setAttribute('aria-valuenow', fromValue);
                toSliderEl.setAttribute('aria-valuemin', min);
                toSliderEl.setAttribute('aria-valuemax', max);
                toSliderEl.setAttribute('aria-valuenow', toValue);
            }
            
            fromSliderEl.addEventListener('input', function() {
                fromInput.value = this.value;
                validateRange();
            });
            
            toSliderEl.addEventListener('input', function() {
                toInput.value = this.value;
                validateRange();
            });
            
            fromInput.addEventListener('change', function() {
                let value = parseFloat(this.value);
                value = Math.max(min, Math.min(max, value));
                this.value = value;
                fromSliderEl.value = value;
                validateRange();
            });
            
            toInput.addEventListener('change', function() {
                let value = parseFloat(this.value);
                value = Math.max(min, Math.min(max, value));
                this.value = value;
                toSliderEl.value = value;
                validateRange();
            });
            
            [fromInput, toInput].forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const submitBtn = container.querySelector('.range-double-submit');
                        if (submitBtn) {
                            submitBtn.click();
                        } else {
                            const form = this.closest('form');
                            if (form) form.submit();
                        }
                    }
                });
            });
            updateSliderBackground();
            updateRangeDisplay(container, parseFloat(fromSliderEl.value), parseFloat(toSliderEl.value));
            addSliderAnimations(fromSliderEl, toSliderEl);
        }
        
        function addSliderAnimations(fromSlider, toSlider) {
            [fromSlider, toSlider].forEach(slider => {
                slider.addEventListener('mousedown', function() {
                    this.classList.add('active');
                });
                
                slider.addEventListener('mouseup', function() {
                    this.classList.remove('active');
                });
                
                slider.addEventListener('touchstart', function() {
                    this.classList.add('active');
                });
                
                slider.addEventListener('touchend', function() {
                    this.classList.remove('active');
                });
            });
        }
        
    });
})(jQuery);
