<div id="mutlifilter_content">
[{if $oView->getArticleList()|@count > 0 || $oView->getIsFiltered() || $actCategory->getId() == 'xlsearch'}]
    [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/widget/multifilter/z_multifilter_list_sidebar.tpl")}]
[{/if}]
</div>
