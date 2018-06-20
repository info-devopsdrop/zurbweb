            <fieldset class="attrrow ninjastyle">
            [{assign var=showreset value=false}]
            [{foreach from=$attributes item=oFilterAttr key=sAttrID name=testAttr}]
                <div class="attrcol">
                    <div class="attrhead">
                        <label id="test_attrfilterTitle_[{$sAttrID}]_[{$smarty.foreach.testAttr.iteration}]">[{ $oFilterAttr->title }]</label>
                    </div>
                
                [{*PRICE SLIDER----------------------------------------*}]
                [{if $oFilterAttr->type=='price_slider'}]
                    [{foreach from=$oFilterAttr->aValues item=oValue name=testInput}]
                        [{assign var="aSettings" value=$oValue->settings}]
                        [{assign var="pricemin" value=$aSettings->min}]
                        [{assign var="pricemax" value=$aSettings->max}]
                        [{assign var="priceminselected" value=$aSettings->minSelected}]
                        [{assign var="pricemaxselected" value=$aSettings->maxSelected}]
                        [{assign var="pricerange" value=$aSettings->range}]
                        [{assign var="disabled" value=$aSettings->disabled}]
                    [{/foreach}]
                    <div class="slider-range" data-values="[{$pricemin}]|[{$pricemax}]|[{$priceminselected}]|[{$pricemaxselected}]" data-disabled="[{$disabled}]">
                        <input class="mf_filter" class="slider_input"  type="hidden"  name="attrfilter[[{ $sAttrID }]][[{ $oValue->id }]]"  value="[{$pricerange}]">
                    </div>     
                    <div class="slider-amount"></div>
                    <div style="clear:both"></div>
                    
                    [{oxscript include=$oViewConf->getModuleUrl('z_multifilter','out/src/js/widgets/mfslider_mobile.js') priority=10 }]
                    [{oxscript add="$( '.slider-range' ).mfSlider();"}]
                    
                [{*CATEGORIES----------------------------------------*}]
                [{elseif $oFilterAttr->type=='category'}]
                    [{assign var="blIsBreadcrumb" value=true}]
                    [{assign var="marginleft" value=0}]
                    [{foreach from=$oFilterAttr->aValues item=oValue name=testInput}]
                        [{if $blIsBreadcrumb}]
                            [{if $oValue->current && !$smarty.foreach.testInput.first}]
                                [{assign var="marginleft" value=$marginleft+10}]
                            [{/if}]
                    <p class="attrfilter" style="padding-left: [{$marginleft}]px">
                            [{if $oValue->current}]
                        <span>
                                <b>[{ $oValue->value }]</b>
                        </span>
                                [{assign var="blIsBreadcrumb" value=false}]
                            [{else}]
                        <a href="[{ $oValue->infoval|oxaddparams:$oValue->keepfilter}]">
                                &lt; [{ $oValue->value }]
                        </a>
                            [{/if}]
                            [{assign var="marginleft" value=$marginleft+10}]
                    </p>
                        [{else}]
                    <p class="attrfilter" style="padding-left: [{$marginleft}]px">
                            [{if $oValue->blDisabled}]
                        <span class="lgrey">
                                [{ $oValue->value }][{ if $oView->showCategoryArticlesCount()}]&nbsp;([{ $oValue->count }])[{/if}]
                        </span>
                            [{else}]
                        <a href="[{ $oValue->infoval|oxaddparams:$oValue->keepfilter }]">
                                [{ $oValue->value }][{ if $oView->showCategoryArticlesCount()}]&nbsp;([{ $oValue->count }])[{/if}]
                        </a>
                            [{/if}]
                    </p>
                        [{/if}]
                    [{/foreach}]    
                    
                [{*COLOR SWATCHES----------------------------------------*}]
                [{elseif $oView->isColorCategory($oFilterAttr->title)}]
                    [{foreach from=$oFilterAttr->aValues item=oValue name=testInput}]
                    <p class="attrfilter">
                    <input id="attrfilter_[{ $sAttrID }]_[{ $oValue->id }]" [{if $oValue->blDisabled}]disabled="disabled"[{/if}] type="checkbox" name="attrfilter[[{ $sAttrID }]][[{ $oValue->id }]]"  value="1" [{ if $oValue->blSelected }]checked[{/if}]>
                    <label for="attrfilter_[{ $sAttrID }]_[{ $oValue->id }]">
                        <span style="background: [{ $oView->getSwatchBg($oValue->value)}]" class="colorpick"></span>
                                <span id="attrtitle_[{ $sAttrID }]_[{ $oValue->id }]" [{if $oValue->blDisabled}] class="lgrey"[{/if}]>
                                    [{ $oValue->value }][{ if $oView->showCategoryArticlesCount()}]&nbsp;([{ $oValue->count }])[{/if}]
                                </span>
                    </label>
                    </p>
                        [{ if $oValue->blSelected }][{assign var=showreset value=true}][{/if}]
                    [{/foreach}]
                
                [{*CHECKBOXES----------------------------------------*}]
                [{else}]
                    [{foreach from=$oFilterAttr->aValues item=oValue name=testInput}]
                    <p class="attrfilter">
                    <input id="attrfilter_[{ $sAttrID }]_[{ $oValue->id }]" [{if $oValue->blDisabled}]disabled="disabled"[{/if}] type="checkbox" name="attrfilter[[{ $sAttrID }]][[{ $oValue->id }]]"  value="1" [{ if $oValue->blSelected }]checked[{/if}]>
                    <label for="attrfilter_[{ $sAttrID }]_[{ $oValue->id }]">
                                <span id="attrtitle_[{ $sAttrID }]_[{ $oValue->id }]" [{if $oValue->blDisabled}] class="lgrey"[{/if}]>
                                    [{ $oValue->value }][{ if $oView->showCategoryArticlesCount()}]&nbsp;([{ $oValue->count }])[{/if}]
                                </span>
                    </label>
                    </p>
                        [{ if $oValue->blSelected }][{assign var=showreset value=true}][{/if}]
                    [{/foreach}]
                [{/if}]
                    <div class="multifilter_submit" onclick="$('#filterList').submit();">[{ oxmultilang ident="Z_MULTIFILTER_APPLY_FILTERS" }]</div>
                    <div style="clear:both"></div>
                </div>
            [{/foreach}]
            </fieldset>
            