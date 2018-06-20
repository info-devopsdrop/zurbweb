            <div id="filterresults">
            [{if $showreset}]
                <div class="filterset" id="filterset">
                [{foreach from=$attributes item=oFilterAttr key=sAttrID name=testAttr}]
                    [{assign var="filterset" value=''}]
                    [{assign var=showfilter value=false}]
                    [{foreach from=$oFilterAttr->aValues item=oValue}]
                        [{ if $oValue->blSelected }]
                            [{assign var=showfilter value=true}]
                            [{capture append="filterset"}]
                                [{if $oFilterAttr->type=='price_slider'}]
                                    [{ assign var="currency"  value=$oView->getActCurrency() }]
                                    [{$oValue->settings->minSelected}] - [{$oValue->settings->maxSelected}] [{$oValue->settings->unit}]
                                [{else}]
                                    [{$oValue->value}]
                                [{/if}]
                            [{/capture}]
                        [{/if}]
                    [{/foreach}]
                    [{if $showfilter}]
                    <div class="resetline">
                        <a title="[{ oxmultilang ident="Z_MULTIFILTER_RESET_FILTERS" }]" class="multifilter_reset_icon" data-ident="[{$sAttrID}]" href="#">
                            <img src="[{$oViewConf->getModuleUrl('z_multifilter','out/img/remove.png')}]">
                        </a>
                        <b>[{ $oFilterAttr->title }]:</b>
                        [{foreach from=$filterset name=filterset item="filtername"}]
                            [{if $filtername}]
                                [{$filtername}]
                                [{if $smarty.foreach.filterset.last}]
                                <br>
                                [{else}]
                                /
                                [{/if}]
                            [{/if}]
                        [{/foreach}]
                    </div>
                    [{/if}]
                [{/foreach}]
                </div>
                <div style="float:left" class="multifilter_reset_link" data-ident="all"><a href="#">[{ oxmultilang ident="Z_MULTIFILTER_RESET_FILTERS_ALL" }]</a></div>
                <div align="right"><b>[{ $oView->getArticleCount() }] [{ oxmultilang ident="Z_MULTIFILTER_ARTICLES_FOUND" }]</b></div>
            [{/if}]
            </div>