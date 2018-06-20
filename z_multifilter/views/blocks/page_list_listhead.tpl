

[{if $oView->getClassName() == "alist"}]
    [{if $oView->getListType() != 'search' }]
        [{if !($oView->getHideHead() && $oView->getIsFiltered())}]
            [{$smarty.block.parent}]
        [{/if}]
    [{/if}]
    [{include file=$oViewConf->getModulePath('z_multifilter',"views/blocks/inc/page_list_listhead.tpl")}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]
