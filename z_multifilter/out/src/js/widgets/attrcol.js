( function( $ ) {

    attrCol = {
        options: {
        },
        _create: function() {
            var self = this,
            options = self.options,
            el         = self.element;
            
            var hideafter = el.data('hideafter') - 1;
            if (!hideafter) return;
            var attrbody = el.find('.attrbody');
            var showmore = el.find('.mfshowmore');
            var showless = el.find('.mfshowless');
            var moreps = attrbody.find('p:gt('+hideafter+')');
            if (moreps.length){
                moreps.hide();
                showmore
                    .show()
                    .click( function(){
                        $(this).siblings(':hidden')
                        .animate({height: "toggle", opacity: "toggle"})
                        .end().hide();
                        showless.show();
                    });
                showless
                    .click( function(){
                        moreps
                            .animate({height: "toggle", opacity: "toggle"});
                        $(this).hide();
                        showmore.show();
                    });
            }
        }
    };

    $.widget("ui.attrCol", attrCol );

} )( jQuery );