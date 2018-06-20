( function( $ ) {

    mfSlider = {
        options: {
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
            
            $(amount).html( (valArray[2] - 0) + '-' + (valArray[3] - 0));
            
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
    			min: valArray[0] - 0,
    			max: valArray[1] - 0,
    			values: [ valArray[2] - 0,  valArray[3] - 0],
    			slide: function( event, ui ) {
    			    $(amount).html( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    			},
    			stop: function( event, ui ) {
    				$(amount).html( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    				$(sliderinput).val( ui.values[ 0 ] + '-' +  ui.values[ 1 ]);
    			}
            });
        }
    };

    $.widget("ui.mfSlider", mfSlider );

} )( jQuery );