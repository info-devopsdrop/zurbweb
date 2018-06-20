[{assign var="pageNavigation" value=$oView->getPageNavigation()}]
[{assign var="actCategory" value=$oView->getActiveCategory()}]


    [{if $attributes }]
            [{assign var=showreset value=false}]
            [{foreach from=$attributes item=oFilterAttr key=sAttrID name=testAttr}]
                [{foreach from=$oFilterAttr->aValues item=oValue name=testInput}]
                    [{ if $oValue->blSelected }][{assign var=showreset value=true}][{/if}]
                [{/foreach}]
            [{/foreach}]
    [{/if}]

[{if !$ajax}]
    <div id="listcontent">
[{/if}]
    [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/widget/multifilter/z_multifilter_listcontent_mobile.tpl")}]
[{if !$ajax}]            
    </div>
[{/if}]
