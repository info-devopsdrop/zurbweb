( function( $ ) {

    mfSlider = {
        options: {
            step: 1
        },
        _create: function() {
            var self = this,
            options = self.options,
            el         = self.element;
                
            var filterlist = $('#filterList');
            var sliderinput = el.find('.mf_filter');
            var slidersubmit = el.find('.mf_filter');
            var amount = el.next(".slider-amount");
            var slidermin = $(amount).find('.slidermin');
            var slidermax = $(amount).find('.slidermax');
            var slidersubmit = $(amount).find('.slidersubmitbutton');
            var valArray = el.data("values").split("|");
            var disabled = el.data('disabled');
            var min = options.step * Math.floor((valArray[0] - 0) / options.step);
            var max = options.step * Math.ceil((valArray[1] - 0) / options.step);
            var minsel = options.step * Math.floor((valArray[2] - 0) / options.step);
            var maxsel = options.step * Math.ceil((valArray[3] - 0) / options.step);
            		
            $(slidermin).val( minsel );
            $(slidermax).val( maxsel );
                       
            $(slidermin).add($(slidermax)).keydown(function() {
                if (event.keyCode == 13) {
                    $(sliderinput).val( $(slidermin).val() + '-' + $(slidermax).val() );
                    $(filterlist).submit(); 
                    return false;
                 }
            });
            $(slidersubmit).click(function() {
                $(sliderinput).val( $(slidermin).val() + '-' + $(slidermax).val() );
                $(filterlist).submit(); 
                return false;
            });
            
            el.slider({
                disabled: disabled,
    			range: true,
    			min: min,
    			max: max,
                step: options.step,
    			values: [ minsel,  maxsel ],
    			slide: function( event, ui ) {               				
    			    $(slidermin).val(ui.values[ 0 ]);
    				$(slidermax).val(ui.values[ 1 ]);
    			},
    			stop: function( event, ui ) {                				
    			    $(slidermin).val( ui.values[ 0 ]);
    				$(slidermax).val( ui.values[ 1 ]);
    				$(sliderinput).val( ui.values[ 0 ] + '-' +  ui.values[ 1 ]);
                    $(filterlist).submit();
    			}
            });
        }
    };

    $.widget("ui.mfSlider", mfSlider );

} )( jQuery );