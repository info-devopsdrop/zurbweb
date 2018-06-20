( function ( $ ) {
    
    var windowWidth = 768;
    
    var afterAjax = function(baseurl){
        var options = { 
            dataType:  'json', 
            beforeSerialize:  showRequest,
            success:   processJson
        };
        $('#filterList').ajaxForm(options);
        mfSetUrl(baseurl);
    }
    var mfSetUrl = function(baseurl){
        var querydata = {};
        var querystring = baseurl;
        if (!querystring) querystring  =  window.location.href;
        if ((querystring.indexOf("executefilter") != -1)) return false;
        
        $('.mf_filter').each(function() {
        if ( $(this).attr('type') == 'checkbox' ){
            if ( $(this).attr('checked') ){
                querydata[$(this).attr('name')] = $(this).val();
            }
        }
        else if ( $(this).attr('type') == 'hidden' ){
            if ($(this).val()){
                querydata[$(this).attr('name')] = $(this).val();
            }
        }
        else {
            if ( $(this).val() ){
                querydata[$(this).attr('name')] = $(this).val();
            }
        }
        });
        if (!$.isEmptyObject(querydata)){
            querydata.fnc = 'executefilter';
            if (querystring.indexOf("?") == -1) querystring += '?';
            else querystring += '&';
            querystring += $.param(querydata);
        }
        history.replaceState({data:''}, '', querystring);
    }

    function showRequest(formData, jqForm, options) {
        $('input[name=ajax]').val('1');
        $("#mfmask").show();
        return true; 
    }
    function processJson(data) {
        for(var index in data) { 
            if (data.hasOwnProperty(index)) {
                mfloadcontent(data[index], index);
            }
        }
        $("#mfmask").hide();
        afterAjax(data.baseurl);
    }
    function mfloadcontent(data, target) {
        if (target != "baseurl"){
            $('#'+target).html(data);
        }
    }
    $(".multifilter_reset_icon, .multifilter_reset_link" ).live({
        click: function(e){
            $('#multifilter_reset').val($(this).attr('data-ident'));
            $('#filterList').submit(); 
            return false;
        }
    });    
    $( ".colorpickerjs" ).live({
        click: function (e){
            var inputElem = $(this).children('input');
            var currentVal = inputElem.val();
            if(!currentVal){
                inputElem.val('1');
            }
            else {
                inputElem.val('');   
            }
            $("#filterList").submit();
        }
    });    
    $( ".attrfilter a" ).live({
        click: function (e){
            $("#mfmask").show();
        }
    });
    $('.searchBox').append('<input type=\"hidden\" name=\"resetfilter\" value=\"1\" />');
    afterAjax();
    
    $('#multifilter_filters .attrhead').live({
        click: function () {
            $(this).toggleClass("isOpenAttrHead");
            $(this).parent('.attrcol').find('.attrbody').toggleClass("hidden");
        }
    });

    $('#multifilter_filters .sectionHead').live({
        click: function () {
            $(this).toggleClass("isOpenSectionHead");
            $(this).parent('.categoryBox').find('.listFilter').toggleClass("hidden");
        }
    });

    updateFilterVisibility();
    
    $(window).resize(function () {
        updateFilterVisibility();
    });

    function updateFilterVisibility() {

        var filterHeader = $('#multifilter_filters .sectionHead'),
                filterBody = filterHeader.parent('.categoryBox').find('.listFilter');

        if ($(window).width() < windowWidth) {
            filterHeader.addClass("isOpenSectionHead");
            filterBody.addClass("hidden");
        } else {
            filterHeader.removeClass("isOpenSectionHead");
            filterBody.removeClass("hidden");
        }
    }
})( jQuery );
