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
                                    [{ $currency->sign}] [{$oValue->settings->minSelected}] - [{ $currency->sign}] [{$oValue->settings->maxSelected}]
                                [{else}]
                                    [{$oValue->value}]
                                [{/if}]
                            [{/capture}]
                        [{/if}]
                    [{/foreach}]
                    [{if $showfilter}]
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
                    [{/if}]
                [{/foreach}]
                </div>
                <div style="float: left;"><a href="[{$actCategory->getLink()}]?reset=1&fnc=executefilter"><b>[{ oxmultilang ident="Z_MULTIFILTER_RESET_FILTERS" }]</b></a> </div>
                <div align="right"><b>[{ $oView->getArticleCount() }] [{ oxmultilang ident="Z_MULTIFILTER_ARTICLES_FOUND" }]</b></div>
            [{/if}]
            </div>