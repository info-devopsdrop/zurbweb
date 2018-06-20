[{oxscript include=$oViewConf->getModuleUrl('z_multifilter','out/src/js/jquery.form.js') priority=10 }]
[{capture name="multifilterjs" assign="multifilterjs"}]
var afterAjax = function(){
    var options = { 
        dataType:  'json', 
        beforeSerialize:  showRequest,
        success:   processJson
    };
    $('#filterList').ajaxForm(options); 
}
$(document).ready(function() { 
    afterAjax();
}); 
function showRequest(formData, jqForm, options) {
    $('input[name=ajax]').val('1');
    $('#listcontent').css('min-height','300px');
    $('#listcontent').css('width','100%');
    $("#listcontent").html('<div style="text-align:center; margin-top:150px"><img src="[{$oViewConf->getModuleUrl('z_multifilter','out/img/ajaxload_black.gif')}]"></div>');
    $("#multifilter_filters").css('opacity','0.6');
    return true; 
}

    function processJson(data) {
        mfloadcontent(data.content, "listcontent");
        mfloadcontent(data.filters, "multifilter_filters");
        $("#mfmask").hide();
        afterAjax(data.baseurl);
    }
    function mfloadcontent(data, target) {
        $('#'+target)[0].innerHTML = (data);
        oxAjax.evalScripts($('#'+target)[0]);
    }
[{/capture}]
[{oxscript add=$multifilterjs}]