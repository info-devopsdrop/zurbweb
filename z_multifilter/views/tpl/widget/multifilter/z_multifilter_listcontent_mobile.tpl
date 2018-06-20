[{*bottom line multifilter*}]
    [{if $showreset}]
    <div class="listRefine" style="padding: 5px 10px; margin: 0; position: static">
        [{include file=$oViewConf->getModulePath('z_multifilter',"views/tpl/inc/multifilter_bottom_mobile.tpl")}]
    </div>
    [{/if}]

[{*products list*}]    
        [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() attributes=$oView->getAttributes() listDisplayType=true sort=true}]
        [{* List types: grid|line|infogrid *}]
        [{include file="widget/product/list.tpl" type=$oView->getListDisplayType() listId="productList" products=$oView->getArticleList() blDisableToCart=true}]
        [{include file="widget/locator/listlocator.tpl" locator=$oView->generatePageNavigation(5) place="bottom"}]
