    [{if $attributes }]
        [{oxstyle include=$oViewConf->getModuleUrl('z_multifilter',"out/src/css/jquery.ui.slider.css")}]
        [{oxstyle include=$oViewConf->getModuleUrl('z_multifilter',"out/src/css/jquery.ui.theme_mobile.css")}]
        [{oxscript include="js/widgets/oxajax.js" priority=10 }]
    
        [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/inc/multifilter_ajax_mobile_js.tpl")}]

        [{assign var="actCategory" value=$oView->getActiveCategory()}]
        [{ assign var="currency"  value=$oView->getActCurrency() }]
        <form method="post" action="[{ $oViewConf->getSelfActionLink() }]" name="_filterlist" id="filterList">
        <div class="listFilter" style="height:auto">
            [{ $oViewConf->getHiddenSid() }]
            [{ $oViewConf->getNavFormParams() }]
            <input type="hidden" name="cl" value="[{ $oViewConf->getActiveClassName() }]">
            <input type="hidden" name="tpl" value="[{$oViewConf->getActTplName()}]">
            <input type="hidden" name="fnc" value="executefilter">
            <input type="hidden" name="fname" value="">
            <input type="hidden" name="ajax" id="isajax" value="">
            [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/inc/multifilter_attrrow_mobile.tpl")}]
        </div>
        </form>
    [{/if}]