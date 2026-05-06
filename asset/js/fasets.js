(function ($) {
    $(document).ready(function() {

        const currentPageUrl = window.location.origin + window.location.pathname;
        const facetsRenderJS = document.querySelector('#facets-render-js');
        const facetsListHolder = document.querySelector('#facets-list-holder');

        if( facetsRenderJS ){
            renderFacets();
        }

        $('.search-facets').on('click', '.facets-reset', function(e) {
            e.preventDefault();
            resetFacetRangeDouble();
            const params = new URLSearchParams();
            $.each($('[data-type="query"]'), function(index, input) {
                params.append(input.name, input.value);
            });
            const queryString = params.toString();
            window.location.href = currentPageUrl + "/?" + queryString;

        });

        initializingFacets();

        if( facetsListHolder ){
            $(facetsListHolder).on('facets-list-loaded', function(e) {
                initializingFacets();
            });
        }

        function initializingFacets() {
            $.each($('.facet-range-double, .range-doubleform-control'), function(index, container) {
                if (!container.hasAttribute('data-initialized')) {
                    initializeRangeSlider(container);
                    container.setAttribute('data-initialized', 'true');
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
       
        function resetFacetRangeDouble() {
            const containers = $('.facet-range-double');
            
            if (!containers.length) return;
            
            $.each(containers, function(index, container) {

                const fromSlider = container.querySelector('.range-slider-from');
                const toSlider = container.querySelector('.range-slider-to');
                const fromInput = container.querySelector('.range-numeric-from');
                const toInput = container.querySelector('.range-numeric-to');
                
                if (fromSlider && toSlider && fromInput && toInput) {

                    fromSlider.value = fromSlider.min;
                    toSlider.value = toSlider.max;
                    fromSlider.setAttribute('aria-valuenow', fromSlider.min);
                    toSlider.setAttribute('aria-valuenow', toSlider.max);
                    fromInput.value = fromSlider.min;
                    toInput.value = toSlider.max;
                   
                    const event = new Event('input');
                    fromSlider.dispatchEvent(event);
                }
            });            
        }

        function getComplexParams() {
            const params = new URLSearchParams(window.location.search);
            const result = {};
            for (const [key, value] of params.entries()) {
                const parts = key.split(/[\[\]]+/).filter(Boolean);               
                let current = result;
                for (let i = 0; i < parts.length; i++) {
                    const part = parts[i];
                    const isLast = i === parts.length - 1;
                    if (isLast) {
                        if (current[part]) {
                            if (!Array.isArray(current[part])) {
                                current[part] = [current[part]];
                            }
                            current[part].push(value);
                        } else {
                            current[part] = value;
                        }
                    } else {
                        if (!current[part]) current[part] = {};
                        current = current[part];
                    }
                }
            }
            return result;
        }

        function renderFacets(){
            var facetsListHolder = $('#facets-list-holder');
            let params = getComplexParams();
            var ApiUrl = window.location.origin + '/api/admin-addon/list-fasets';
            if( window.OmekaSiteSlug ) {
                ApiUrl = ApiUrl + '/' + window.OmekaSiteSlug;
            }
            $.ajax({
                url: ApiUrl,
                data: params,
                type: 'GET',
                success: function(data) {
                    facetsListHolder.empty();
                    facetsListHolder.html(data);
                    facetsListHolder.trigger('facets-list-loaded');
                },
                error: function() {
                    facetsListHolder.empty();
                    facetsListHolder.html('Fail get facets!');
                }
            });
        }
        
    });
})(jQuery);
