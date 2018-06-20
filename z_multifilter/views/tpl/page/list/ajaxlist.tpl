[{*Listview for Ajax calls*}]

[{capture name="content" assign="content"}]
    [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/widget/multifilter/z_multifilter_list_sidebar.tpl") attributes=$oView->getAttributes()}]
    [{oxscript}]
[{/capture}]
[{if $oView->getHideHead()}]
    [{$oView->toJson('content',$content)}]
[{else}]
    [{$oView->toJson('mutlifilter_content',$content)}]
[{/if}]

[{capture name="filters" assign="filters"}]
    [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/widget/multifilter/z_multifilter_sidebar.tpl") attributes=$oView->getAttributes()}]
    [{oxscript}]
[{/capture}]
[{$oView->toJson('multifilter_filters',$filters)}]

[{$oView->outputJson()}]
