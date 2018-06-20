[{if $oView->getClassName() == "alist"}]
<div id="multifilter_filters">
    [{assign var="attributes" value=$oView->getAttributes()}]
    [{if $attributes }]
    <div class="categoryBox">
        <div class="sectionHead">[{oxmultilang ident="Z_MULTIFILTER_FILTERS"}]</div>
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
            <input type="hidden" name="multifilter_reset" id="multifilter_reset" value="">
            <input type="hidden" name="attrfilter[time]" value="[{$oView->getTime()}]">
            [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/inc/multifilter_attrrow.tpl")}]
            [{foreach from=$attributes item=oFilterAttr key=sAttrID name=testAttr}]
                [{foreach from=$oFilterAttr->aValues item=oValue}]
                    [{ if $oValue->blSelected }]
                        [{assign var="globalFilterActive" value=1}]
                    [{/if}]
                [{/foreach}]
            [{/foreach}]
            [{if $globalFilterActive}]
            <div class="multifilter_reset_link" data-ident="all"><a href="#">[{ oxmultilang ident="Z_MULTIFILTER_RESET_FILTERS_ALL" }]</a></div>
            [{/if}]
        </div>
        </form>
    </div>
    <div id="mfmask"><img src="[{$oViewConf->getModuleUrl('z_multifilter','out/img/ajaxload.gif')}]" class="ajax-loader"/></div>
    [{/if}]
</div>
[{/if}]
