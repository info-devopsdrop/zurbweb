<?php


class z_multifilter_oxattributelist extends z_multifilter_oxattributelist_parent
{  
    protected $_aAttributeValues = array();
    protected $_aCachedAttributeValues = array();
    protected $_categoryAttributesByArtId = array();
    protected $_blInitialized = false;
    protected $_aSubcats = array();
    protected $_sSubcats = '';
    protected $_blShowPricesOnlyIfLoggedIn = false;
    protected $_blUseVarminprice = false;
    
    public function z_initialize( $sActCat = false ){
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        if (!$sActCat){
            $sActCat = oxRegistry::getConfig()->getRequestParameter( 'cnid' );
        }
        $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
        $aFilter = $aSessionFilter[$sActCat][$iLang];
        $aAllAttributeValues = $this->_getAllAttributeValues( $sActCat );
        if (is_array( $aFilter)){
            foreach ($aFilter as $sKey => $aVal){
                if (is_array($aAllAttributeValues[$sKey]["values"])){
                    $aFilter[$sKey] = array_intersect_key($aVal,$aAllAttributeValues[$sKey]["values"]);
                }
                else{
                    $aFilter[$sKey] = array();
                }
            }
            foreach ($aFilter as $sKey => $aVal){
                if (!count($aFilter[$sKey])){
                    unset ($aFilter[$sKey]);
                }
            }
        }
        $aSessionFilterNew[$sActCat][$iLang] = $aFilter;
        oxRegistry::getSession()->setVariable( 'session_attrfilter', $aSessionFilterNew );
        oxRegistry::getSession()->setVariable( 'session_attrfilter_last', $aFilter );
        $this->_blInitialized = true;
    }
    public function getCategoryAttributes( $sActCat, $iLang )
    {
        startProfile("mf_getCategoryAttributes");
        $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
        $aFilter = $aSessionFilter[$sActCat][$iLang];
                
        if ($this->getConfig()->getConfigParam('blMfEnableCaching') && !count($aFilter)){
            $iMaxCacheAge = $this->getConfig()->getConfigParam('iMfCachingMaxAge');
            $iCacheAge = oxRegistry::getUtils()->z_GetCacheAge( 'mf_filtercache_'.$this->z_getCacheId($sActCat,$iLang) );
            if ( $iCacheAge !== null && $iCacheAge < $iMaxCacheAge && !oxRegistry::getConfig()->getRequestParameter( 'forceFilterCacheUpdate' ) ){
                $aCachedFilters = oxRegistry::getUtils()->fromFileCache( 'mf_filtercache_'.$this->z_getCacheId($sActCat,$iLang) );
            }
            else {
                oxRegistry::getUtils()->z_ResetCacheAge( 'mf_filtercache_'.$this->z_getCacheId($sActCat,$iLang) );
            }
        }
        if (is_array($aCachedFilters) && count($aCachedFilters)){
            $this->_aArray = $aCachedFilters;
            return;
        }
        
        if (!$this->_blInitialized) $this->z_initialize($sActCat);
        
        //Add Breadcrumb to Category Filter
        if ($this->getConfig()->getConfigParam('blMfShowCategoryFilter')){
            $this->z_addBreadcrumb($aFilter);
        }
        
        $aAllAttributes = $this->_getAllAttributeValues( $sActCat, $iLang );
        
        //if there are any
        if (count($aAllAttributes)) {
            $oStr = getStr();
            
            $aSortedAttributes = array();
            foreach ($this->z_getConfiguredCategoryAttributes($sActCat) as $sAttname){
                if ($aAllAttributes[$this->_prepareTitle($sAttname)]){
                    $aSortedAttributes[$this->_prepareTitle($sAttname)] = $aAllAttributes[$this->_prepareTitle($sAttname)];
                }
            }
            
            startProfile("mf_assignattvalues");
            foreach ($aSortedAttributes as $sAttId => $aAttribute){
                
                //make empty attribute
                $oAttribute           = new stdClass();
                $oAttribute->title    = $aAttribute["title"];
                $oAttribute->aValues  = array();
                $oAttribute->type     = $aAttribute["type"];
                
                //assign to array
                if ($oAttribute->title != oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_CATEGORY',$iLang, false )){
                    $this->offsetSet( $sAttId, $oAttribute );
                }
                
                //make testset
                $aTestFilter = $aFilter;
                $aUseAndAttributes = $this->getConfig()->getConfigParam('aUseAndAttributes');
                if (!in_array($oAttribute->title,$aUseAndAttributes)){
                    unset($aTestFilter[$sAttId]);
                }
                $aCurrentAttributes = $this->_getAttributeValues( $sActCat,$aTestFilter, $iLang);
                //get price range
                $oSettings = new stdClass();
                $aRanges = oxRegistry::getUtils()->fromStaticCache("_aRanges");
                
                if ($oAttribute->type == 'price_slider'){
                    foreach ($aAttribute["values"] as $sValuekey => $aAttributeValue){
                        startProfile("mf_assignattvaluesprice");
                        $aCurrentIds = $this->getFilterIds($sActCat,$aTestFilter);
                        $aArticlePrices = $aRanges[$oAttribute->title];
                        $aPriceArr = array();
                        foreach ($aArticlePrices as $sOxid => $aArtId){
                            $aPriceArr[$sOxid] = $aArtId['price'];
                        }
                        if (count($aTestFilter)){
                            $aPriceArr = array_intersect_key( $aPriceArr, $aCurrentIds );
                        }
                        asort($aPriceArr);
                        $iMin = floor(current($aPriceArr));
                        $iMax = ceil(end($aPriceArr));
                        
                        $aMinMax = explode('-',$aAttributeValue['sInfoval']);
                        if ($aMinMax[0]== '' || $aMinMax[1] == ''){
                            $aMinMax = array ($iMin,$iMax);
                        }
                        $oSettings->min        = $iMin;
                        $oSettings->max        = $iMax;
                        $oSettings->minSelected = $aMinMax[0];
                        $oSettings->maxSelected = $aMinMax[1];
                        $oSettings->range = $aAttributeValue['sInfoval'];
                        $oSettings->unit = $this->z_getUnitName($oAttribute->title);
                        $oSettings->disabled = 0;
                        if ($iMin == $iMax) $oSettings->disabled = 1;
                        
                        stopProfile("mf_assignattvaluesprice");
                        break;
                    }
                }
                //collect elements
                foreach ($aAttribute["values"] as $sValuekey => $aAttributeValue){
                    $sAttId = $aAttributeValue["sAttId"];
                    $sAttTitle = $aAttributeValue["sAttTitle"];
                    $sAttValue = $aAttributeValue["sAttValue"];
                    $blDisabled = 0;
                    $blSelected = 0;

                    //value selected?
                    if (isset($aFilter[$sAttId][$sValuekey])){
                        $blSelected = 1;
                    }

                    //Disable unavailable
                    $iCount = count(array_unique($aCurrentAttributes[$sAttId]["values"][$sValuekey]["aArtIds"]));
                    if (!$blSelected && !$iCount) {
                        $blDisabled = 1;
                    }

                    //assign id, value, selected
                    $oValue             = new stdClass();
                    $oValue->id         = $sValuekey;
                    $oValue->value      = $oStr->htmlspecialchars( $sAttValue );
                    $oValue->blSelected = $blSelected;
                    $oValue->blDisabled = $blDisabled;
                    $oValue->count      = $iCount;
                    $oValue->infoval    = $aAttributeValue["sInfoval"];
                    $oValue->settings    = $oSettings;
                    
                    //Add Keep Filter Flag
                    $sKeepFilter = 'keepFilter=1';
                    if (empty($aFilter)) $sKeepFilter = '';
                    $oValue->keepfilter = $sKeepFilter;
                    
                    //re-add slider value
                    if (count((array)$oSettings)){
                        $oValue->id = 1;
                        $oValue->value = 1;
                        $sValuekey = 1;
                        if (!$this->getConfig()->getConfigParam('blMfShowInactiveSlider')){
                            $oValue->blDisabled = $oSettings->disabled;
                        }
                        else {
                            $oValue->blDisabled = 0;
                        }
                    }

                    //add to array
                    if (!($this->getConfig()->getConfigParam('blMfHideDisabled') && $oValue->blDisabled)){
                        $oAttribute = $this->offsetGet( $sAttId );
                        $oAttribute->aValues[$sValuekey] = $oValue;
                    }
                }
            }
            stopProfile("mf_assignattvalues");
        }
        
        //Sort Attributes
        if ($this->getConfig()->getConfigParam('blMfOrderByName')){
            foreach ($this->_aArray as $key => $val){
                if ($val->type != 'price' && $val->type != 'category'){
                    $aSortarray = $this->getConfig()->getMultifilterSortArray($val->title);
                    $this->_aArray[$key]->aValues = $this->z_arraysort($val->aValues,"value",false,$aSortarray,true);
                }
            }
        }
        if ($this->getConfig()->getConfigParam('blMfOrderByCount')){
            foreach ($this->_aArray as $key => $val){
                if ($val->type != 'price' && $val->type != 'category'){
                    $this->_aArray[$key]->aValues = $this->z_arraysort($val->aValues,"count",true,null,true);
                }
            }
        }
        if ($this->getConfig()->getConfigParam('blMfOrderBySelected')){
            foreach ($this->_aArray as $key => $val){
                if ($val->type != 'price' && $val->type != 'category'){
                    $this->_aArray[$key]->aValues = $this->z_arraysort($val->aValues,"blSelected",true,null,true);
                }
            }
        }
            
        //Caching
        if ($this->getConfig()->getConfigParam('blMfEnableCaching')){
            if(!count($aFilter)){
                if (is_array($this->_aArray) && count($this->_aArray)){
                    oxRegistry::getUtils()->toFileCache( 'mf_filtercache_'.$this->z_getCacheId($sActCat,$iLang), $this->_aArray );
                }
            }
        }
        stopProfile("mf_getCategoryAttributes");
    }
    protected function z_getUnitName($sAttTitle){
        $sPricetitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_PRICE',$iLang, false );
        if ($sAttTitle == $sPricetitle){
            return $this->z_getCurrencySign();
        }
        $aUnits = $this->getConfig()->getConfigParam( 'aSliderUnits' );
        return $aUnits[$sAttTitle];
    }
    protected function _getAttributeValues($sActCat, $aFilter, $iLang){
        startProfile("mf_getAttributeValues");
        $sGetAttributeValuesId = md5(serialize($aFilter));
        if ($this->_aCachedAttributeValues[$sGetAttributeValuesId]){
            stopProfile("mf_getAttributeValues");
            return $this->_aCachedAttributeValues[$sGetAttributeValuesId];
        }
        $aAllAttributeValues = $this->_getAllAttributeValues( $sActCat, $iLang );
        if (!count($aFilter)) {
            stopProfile("mf_getAttributeValues");
            return $aAllAttributeValues;
        }
        $aFilterIds = $this->getFilterIds(  $sActCat, $aFilter );
        
        foreach ($aAllAttributeValues as $sAttId => $aAttribute){
            foreach ($aAttribute["values"] as $sValKey => $aAttributeValue){
                $aIds = array_intersect_key($aFilterIds,$aAttributeValue["aArtIds"]);
                $aAllAttributeValues[$sAttId]["values"][$sValKey]["aArtIds"] = $aIds;
            }
        }
        $this->_aCachedAttributeValues[$sGetAttributeValuesId] = $aAllAttributeValues;
        stopProfile("mf_getAttributeValues");
        return $this->_aCachedAttributeValues[$sGetAttributeValuesId];
    }
    protected function z_getSubCats($sActCat){
        $oView = $this->getConfig()->getActiveView();
        $aRet = array();
        $aSubCats = array();
        if ($sActCat == 'xlsearch'){
            $oCategory = oxnew('oxcategory');
            $aSubCats = $oCategory->z_getRootCategories();
        }
        elseif ($oView){
            $oCategory = $oView->getActiveCategory();
            if ($oCategory && $oCategory->getId() == $sActCat){
                $aSubCats = $oCategory->getSubCats();
            }
        }
        foreach ($aSubCats as $oSubCat){
            $aCat = array();
            $aCat['oxid'] = $oSubCat->oxcategories__oxid->value;
            $aCat['sort'] = $oSubCat->oxcategories__oxsort->value;
            $aCat['link'] = $oSubCat->getLink();
            $oView = $this->getConfig()->getActiveView();
            if ($sAddDynParams = $oView->getAddUrlParams()){
                $aCat['link'] .= '?'.$sAddDynParams;
            }
            $aCat['name'] = html_entity_decode($oSubCat->oxcategories__oxtitle->value);
            $aCat['subcats'] = $oSubCat->z_getSubCategoryIds($aCat['oxid']);
            $aRet[$aCat['oxid']] = $aCat;
        }
        return $aRet;
    }
    protected function z_addBreadcrumb($aFilter){
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        //make empty attribute
        $oAttribute           = new stdClass();
        $oAttribute->title    = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_CATEGORY',$iLang, false );
        $oAttribute->aValues  = array();
        $oAttribute->type     = "category";
        $sAttId = $this->_prepareTitle($oAttribute->title);
        
        $this->offsetSet( $sAttId , $oAttribute );
        
        $oView = $this->getConfig()->getActiveView();
        $aCrumbs = $oView->getBreadcrumb();
        $i = 0;
        $blCurrent = 0;
        $sKeepFilter = 'keepFilter=1';
        if (empty($aFilter)) $sKeepFilter = '';
        
        //Alle Artikel der Kategorie
        $oActCat = $oView->getActiveCategory();
        if ($oActCat->getId() != 'xlsearch' && $oView->getAddUrlParams()){
            //assign id, value, selected
            $oValue             = new stdClass();
            $oValue->id         = 'allArticles';
            $oValue->value      = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_ALL_ARTICLES_IN',$iLang, false ).' '.$oActCat->getTitle();
            $oValue->blSelected = 0;
            $oValue->blDisabled = 0;
            $oValue->count      = 0;
            $oValue->infoval    = $oActCat->getLink();
            $oValue->current    = 0;
            $oValue->keepfilter = $sKeepFilter;
            //add to array
            $oAttribute = $this->offsetGet( $sAttId );
            $oAttribute->aValues[$oValue->id] = $oValue;
        }
        //Breadcrumb Filter
        foreach ($aCrumbs as $aCrumb){
            if (++$i == count($aCrumbs)) $blCurrent = 1;
            //assign id, value, selected
            $oValue             = new stdClass();
            $oValue->id         = $this->_prepareTitle($aCrumb['title']);
            $oValue->value      = $aCrumb['title'];
            $oValue->blSelected = 0;
            $oValue->blDisabled = 0;
            $oValue->count      = 0;
            $oValue->infoval    = $aCrumb['link'];
            $oValue->current    = $blCurrent;
            $oValue->keepfilter = $sKeepFilter;
            //add to array
            $oAttribute = $this->offsetGet( $sAttId );
            $oAttribute->aValues[$oValue->id] = $oValue;
        }
    }
    
    public function getFilterIds( $sActCat, $aFilter , $blTest = false){
        startProfile("mf_getFilterIds");
        if (!$this->_blInitialized) $this->z_initialize($sActCat);
        $aAllAttributeValues = $this->_getAllAttributeValues( $sActCat );
        if (!$aAllAttributeValues) return array();
        //get UseAndAttributeIds
        
        $aUseAndIds = array();
        $aUseAndAttributes = $this->getConfig()->getConfigParam('aUseAndAttributes');
        foreach ($aAllAttributeValues as $sAttId => $aAttribute){
            if (in_array($aAttribute["title"],$aUseAndAttributes)){
                $aUseAndIds[] = $sAttId;
            }
        }
        $aRet = array();
        $aAttrIds = array();
        if (is_array($aFilter)){
            //add multiple selections
            foreach ( $aFilter as $sAttId => $aAtrrfilter ) {
                if (is_array($aAtrrfilter)){
                    $aIntersectIds = array();
                    $iCnt = 0;
                    foreach ( $aAtrrfilter as $sAttValueId =>  $sAttValue ) {
                        if ($sAttValue) {
                            $aIds = $aAllAttributeValues["$sAttId"]["values"]["$sAttValueId"]["aArtIds"];
                            if (is_array($aIds)){
                                if ($iCnt){
                                    if (in_array($sAttId,$aUseAndIds)){
                                        $aIntersectIds = array_intersect_key($aIntersectIds,$aIds);
                                    }
                                    else{
                                        $aIntersectIds = $aIntersectIds + $aIds;
                                    }
                                }
                                else{
                                    $aIntersectIds = $aIds;
                                }
                                $iCnt++;
                            }
                        }
                    }
                    if (is_array($aIntersectIds)){
                        foreach ($aIntersectIds as $key=>$val){
                            $aAttrIds["$sAttId"][$key] = $val;
                        }
                    }
                }
            }
            //intersect attributes
            $iCnt = 0;
            foreach ($aAttrIds as $aIds){
                if ($iCnt){
                    $aRet = array_intersect_key($aRet,$aIds);
                }
                else{
                    $aRet = $aIds;
                }
                $iCnt++;
            }
            //remove fake price id
            if (count($aRet) == 1){
                foreach ($aRet as $sId){
                    if ($sId == '') return array();
                }
            }
        }
        stopProfile("mf_getFilterIds");
        return $aRet;
    }
    protected function _getAllAttributeValues( $sActCat, $iLang = "" ){
        startProfile("mf_getAllAttributeValues");
        $oCat = oxnew('oxcategory');
        
        if ($sActCat == "xlsearch"){
            $this->_aSubcats = $oCat->z_getRootCategoryIds();
            $this->_sSubcats = $oCat->z_getRootCategoryIdsForSelect();
            $this->_sUppercats = $oCat->z_getRootCategoryIdsForSelect();
        }
        else {
            $this->_aSubcats = $oCat->z_getSubCategoryIds($sActCat);
            $this->_sSubcats = $oCat->z_getSubCategoryIdsForSelect($sActCat);
            $this->_sUppercats = $oCat->z_getUpperCategoryIdsForSelect($sActCat);
        }
        $aSubcats = $this->z_getSubCats($sActCat);
        if ($this->getConfig()->getConfigParam('blInheritCategories')){
            $this->_blInheritCats = 1;
        }
        
        if (!oxRegistry::getUtils()->fromStaticCache("_aAttributeValues")){
            startProfile("mf_getActiveArticles");
            $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
            if (!$iLang) $iLang = oxRegistry::getLang()->getBaseLanguage();
            $aVarnames = array();
            $aActiveParents = array();
            $aRet = array();
            $aArticledatas = array();
            $aArticlePrices = array();
            $aArticlePricesById = array();
            
            $sManutitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_MANUFACTURERS',$iLang, false );
            $sPricetitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_PRICE',$iLang, false );
            $sCatFilterName = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_CATEGORY',$iLang, false );
            $blNetPriceMode = $this->getConfig()->getConfigParam( 'blEnterNetPrice' );
            $sAvailabletitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_AVAILABLE',$iLang, false );
            $sAvailabletext = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_AVAILABLETEXT',$iLang, false );
            
            $aCachedAttributes = false;
            if ($this->getConfig()->getConfigParam('blMfEnableCaching')){
                $iMaxCacheAge = $this->getConfig()->getConfigParam('iMfCachingMaxAge');
                $iCacheAge = oxRegistry::getUtils()->z_GetCacheAge( 'mf_attributecache_'.$this->z_getCacheId($sActCat,$iLang) );
                
                if ( $iCacheAge !== null && $iCacheAge < $iMaxCacheAge && !oxRegistry::getConfig()->getRequestParameter( 'forceFilterCacheUpdate' ) ){
                    startProfile("mf_fetchCache");
                    $aCachedAttributes = oxRegistry::getUtils()->fromFileCache( 'mf_attributecache_'.$this->z_getCacheId($sActCat,$iLang) );
                    stopProfile("mf_fetchCache");
                }
                else {
                    oxRegistry::getUtils()->z_ResetCacheAge( 'mf_attributecache_'.$this->z_getCacheId($sActCat,$iLang) );
                }
            }
            if ( is_array($aCachedAttributes)){
                $this->_aAttributeValues = $aCachedAttributes['values'];
                $aRanges = $aCachedAttributes['ranges'];
            }
            else {
                //Heap Table
                $oArtList = oxnew('oxarticlelist');
                $oArtList->zwGenerateIdTable($sActCat);
                //Parents
                $sSelect = $this->z_getActiveQuery($sActCat);
                $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
                $rs = $oDb->execute( $sSelect );
                if ($rs != false && $rs->recordCount() > 0) {
                    while ( !$rs->EOF) {
                        $aF = $rs->fields;
                        $aArticledatas[$aF['OXID']] = $aF;
                        $aCategories[$aF['OXID']][] = $aF['OXCATNID'];
                        $rs->moveNext();
                    }
                }
                //Variants
                $sSelect = $this->z_getActiveQuery($sActCat,1);
                $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
                $rs = $oDb->execute( $sSelect );
                if ($rs != false && $rs->recordCount() > 0) {
                    while ( !$rs->EOF) {
                        $aF = $rs->fields;
                        if (!$aArticledatas[$aF['OXID']]){
                            $aArticledatas[$aF['OXID']] = $aF;
                            $aCategories[$aF['OXID']][] = $aF['OXCATNID'];
                        }
                        $rs->moveNext();
                    }
                }
                $aAttrById = $this->z_getCategoryAttributesByArtId($sActCat);
                $aCategories = $this->z_getArticleCategories();
                $aManuNames = $this->z_getManufacturerNames();
            }
            $aConfAttr = $this->z_getConfiguredCategoryAttributes($sActCat);
            if (!$aConfAttr) return;
            stopProfile("mf_getActiveArticles");
            startProfile("mf_processArticles");
            if ( !is_array($aCachedAttributes)){
                foreach ($aArticledatas as $aF){
                    //Store Variantnames from parents
                    if ($aF['OXVARNAME']){
                        $aVarnames[$aF['OXID']] = $aF['OXVARNAME'];
                    }
                    //Check Visibility
                    //store active Parentids
                    if ($aF['OXVARCOUNT']){
                        $aActiveParents[$aF['OXID']] = 1;
                    }
                    //remove Variant if parent inactive
                    if ($aF['OXPARENTID']){
                        if (!isset($aActiveParents[$aF['OXPARENTID']])){
                            continue;
                        }
                    }
                    //Parent not buyable
                    if ( !$this->getConfig()->getConfigParam( 'blVariantParentBuyable' ) && $aF['OXVARCOUNT']){
                        continue;
                    }
                    //Parents and Variants Attributes
                    $aSetAttributeValues = array();
                    if ( isset($aAttrById[$aF['OXID']]) && is_array($aAttrById[$aF['OXID']])){
                        foreach ($aAttrById[$aF['OXID']] as $sAttrId => $aAtt){
                            if (in_array($aAtt['title'], $aConfAttr)){
                                $aAttValues = explode($this->getConfig()->getConfigParam( 'sMfAttributeValueSeparator' ),$aAtt['value']);
                                foreach ($aAttValues as $sAttvalue){
                                    if (trim($sAttvalue)){
                                        $sAttvalue = $this->getConfig()->getMapping($sAttvalue, $sActCat);
                                        $this->z_addAttribute('attribute',$aAtt['title'],trim($sAttvalue),$aF['OXID'],$aF['OXPARENTID']);
                                        $aSetAttributeValues[$this->_prepareTitle($aAtt['title'])] = 1;
                                    }
                                }
                            }
                        }
                    }
                    //inherit parent attributes to variant
                    if (isset($aAttrById[$aF['OXPARENTID']]) && is_array($aAttrById[$aF['OXPARENTID']])){
                        foreach ($aAttrById[$aF['OXPARENTID']] as $sAttrId => $aAtt){
                            if (in_array($aAtt['title'], $aConfAttr)){
                                if (!isset($aSetAttributeValues[$sAttrId])){
                                    $aAttValues = explode($this->getConfig()->getConfigParam( 'sMfAttributeValueSeparator' ),$aAtt['value']);
                                    foreach ($aAttValues as $sAttvalue){
                                        if (trim($sAttvalue)){
                                            $sAttvalue = $this->getConfig()->getMapping($sAttvalue, $sActCat);
                                            $this->z_addAttribute('parentAttribute',$aAtt['title'],trim($sAttvalue),$aF['OXID'],$aF['OXPARENTID']);
                                            $aSetAttributeValues[$this->_prepareTitle($aAtt['title'])] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //make attributes from variant names
                    if (count($aVarnames)){
                        if ($aF['OXPARENTID']){
                            $aAttTitles = explode("|",$aVarnames[$aF['OXPARENTID']]);
                            $aAttValues = explode("|",$aF['OXVARSELECT']);
                            foreach ($aAttValues as $iPos => $sAttvalue){
                                $sAttTitle = trim($aAttTitles[$iPos]);
                                if (in_array($sAttTitle, $aConfAttr)){
                                    if (!isset($aSetAttributeValues[$this->_prepareTitle($sAttTitle)])){
                                        if (trim($sAttvalue)){
                                            $sAttvalue = $this->getConfig()->getMapping($sAttvalue, $sActCat);
                                            $this->z_addAttribute('variant',$sAttTitle,trim($sAttvalue),$aF['OXID'],$aF['OXPARENTID']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //make attributes from manufacturers
                    if (in_array($sManutitle, $aConfAttr) && !oxRegistry::getConfig()->getRequestParameter( 'searchmanufacturer' )){
                        if ($sManuName = $aManuNames[$aF['OXMANUFACTURERID']]){
                            $this->z_addAttribute('manufacturer',$sManutitle,trim($sManuName),$aF['OXID'],$aF['OXPARENTID']);
                        }
                        elseif ($sManuName = $aManuNames[$aArticledatas[$aF['OXPARENTID']]['OXMANUFACTURERID']]){
                            $this->z_addAttribute('manufacturer',$sManutitle,trim($sManuName),$aF['OXID'],$aF['OXPARENTID']);
                        }
                    }
                    //make attributes from availability
                    if (in_array($sAvailabletitle, $aConfAttr) && oxRegistry::getConfig()->getConfigParam('blUseStock')){
                        if ($aF['OXSTOCKFLAG'] == 4 || $aF['OXSTOCK'] > 0){
                            $this->z_addAttribute('availability',$sAvailabletitle,trim($sAvailabletext),$aF['OXID'],$aF['OXPARENTID']);
                        }
                    }
                    //make attributes from categories
                    if ($this->getConfig()->getConfigParam('blMfShowCategoryFilter')){
                        if (in_array($sCatFilterName, $aConfAttr)){
                            if (is_array($aCategories[$aF['OXID']])){
                                foreach ($aCategories[$aF['OXID']] as $sCatId){
                                    foreach ($aSubcats as $aSubcat){
                                        if (in_array($sCatId, $aSubcat['subcats'])){
                                            $this->z_addAttribute('category',$sCatFilterName,trim($aSubcat['name']),$aF['OXID'],$aF['OXPARENTID'],$aSubcat['link'],$aSubcat['sort']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //Save price
                    if ($this->_blShowPricesOnlyIfLoggedIn ? $this->getUser()->oxuser__oxpassword->value : 1){
                        if (in_array($sPricetitle, $aConfAttr)){
                            if (!$this->_blUseVarminprice){
                                $iCurPrice = $aF['OXPRICE'];
                                
                                if (isset($aF['OXUSERPRICE'])){
                                    if ( !$this->getConfig()->getConfigParam( 'blOverrideZeroABCPrices' ) || $aF['OXUSERPRICE'] ) {
                                        $iCurPrice = $aF['OXUSERPRICE'];
                                    }
                                }
                                if (!$iCurPrice){
                                    $iCurPrice =  $aArticledatas[$aF['OXPARENTID']]['OXPRICE'];
                                    if (isset($aF['OXUSERPRICE'])){
                                        if ( !$this->getConfig()->getConfigParam( 'blOverrideZeroABCPrices' ) ) {
                                            $iCurPrice = 0;
                                        }
                                    }
                                }
                            }
                            else{
                                if ($aF['OXPARENTID']){
                                    $iCurPrice =  $aArticledatas[$aF['OXPARENTID']]['OXVARMINPRICE'];
                                }
                                else {
                                    $iCurPrice =  $aF['OXVARMINPRICE'];
                                }
                            }
                            if ($blNetPriceMode){
                                $iCurPrice = $this->z_ApplyVat($iCurPrice);
                            }
                            
                            $aArticlePricesById[$aF['OXID']] = array(
                                'oxid' => $aF['OXID'],
                                'oxparentid' => $aF['OXPARENTID'],
                                'price' => $iCurPrice,
                            );
                        }
                    }
                }
                unset ($aArticledatas);
                unset ($aAttrById);
                
                //Add Prices
                $aRanges[$sPricetitle] = $aArticlePricesById;
                unset ($aArticlePricesById);
                //Add Ranges
                foreach ($this->getConfig()->getConfigParam('aSliderAttributes') as $sRangeTitle){
                    $aRangeValuesById = array();
                    $aAttributeValues = $this->_aAttributeValues[$this->_prepareTitle($sRangeTitle)];
                    if (is_array($aAttributeValues['values'])){
                        foreach ($aAttributeValues['values'] as $aAttributeValue){
                            if (is_array($aAttributeValue["aArtIds"])){
                                foreach ($aAttributeValue["aArtIds"] as $sArtId => $sParentId){
                                    $aRangeValuesById[$sArtId] = array(
                                        'oxid' => $sArtId,
                                        'oxparentid' => $sParentId,
                                        'price' => (float) str_replace( ',', '.', $aAttributeValue["sAttValue"] ),
                                    );
                                }
                            }
                        }
                    }
                    unset($this->_aAttributeValues[$this->_prepareTitle($sRangeTitle)]);
                    if (count($aRangeValuesById)){
                        $aRanges[$sRangeTitle] = $aRangeValuesById;
                    }
                }
                
                //Caching
                if ($this->getConfig()->getConfigParam('blMfEnableCaching')){
                    $aCachedAttributes = array();
                    if ( count($this->_aAttributeValues) || count($aArticlePricesById)){
                        $aCachedAttributes['values'] = $this->_aAttributeValues;
                        $aCachedAttributes['ranges'] = $aRanges;
                    }
                    if (is_array($aCachedAttributes) && count($aCachedAttributes)){
                        oxRegistry::getUtils()->toFileCache( 'mf_attributecache_'.$this->z_getCacheId($sActCat,$iLang), $aCachedAttributes );
                        unset ($aCachedAttributes);
                    }
                }
            }
            stopProfile("mf_processArticles");
            
            startProfile("mf_generateRanges");
            //Add Price Attribute
            if (in_array($sPricetitle, $aConfAttr)){
                unset($this->_aAttributeValues[$this->_prepareTitle($sPricetitle)]);
                if (is_array($aRanges[$sPricetitle])){
                    foreach ($aRanges[$sPricetitle] as $key => $aArticlePrice){
                        $aRanges[$sPricetitle][$key]['price'] = oxprice::getPriceInActCurrency($aRanges[$sPricetitle][$key]['price']);
                    }
                    $this->z_generatePriceAttributes($aRanges[$sPricetitle], $sActCat);
                }
            }
            //Add other Ranges
            foreach ($this->getConfig()->getConfigParam('aSliderAttributes') as $sRangeTitle){
                if (is_array($aRanges[$sRangeTitle])){
                    $this->z_generatePriceSlider($aRanges[$sRangeTitle], $sActCat, $sRangeTitle);
                }
            }
            stopProfile("mf_generateRanges");
            
            //Sort Categories
            if (is_array($this->_aAttributeValues)){
                foreach ( $this->_aAttributeValues as $key => $AttributeValue){
                    if ($AttributeValue['type'] == 'category'){
                        if ($this->getConfig()->getConfigParam('blMfOrderCategoriesByName')){
                            $this->_aAttributeValues[$key]["values"] = $this->z_arraysort($AttributeValue['values'],"sAttValue");
                        }
                        else{
                            $this->_aAttributeValues[$key]["values"] = $this->z_arraysort($AttributeValue['values'],"iSort");
                        }
                    }
                }
            }
            
            //Cache in Static Cache
            oxRegistry::getUtils()->toStaticCache("_aRanges", $aRanges);
            oxRegistry::getUtils()->toStaticCache("_aAttributeValues", $this->_aAttributeValues);
            unset ($this->_aAttributeValues);
        }
        stopProfile("mf_getAllAttributeValues");
        return oxRegistry::getUtils()->fromStaticCache("_aAttributeValues");
    }
    protected function z_ApplyVat($iCurPrice){
        $iVat = $this->getConfig()->getConfigParam( 'dDefaultVAT' );
        $fVatFactor = 1 + ( $iVat / 100 );
        return round ( $iCurPrice * $fVatFactor, 2 );
    }
    protected function z_getActiveQuery($sActCat, $blGetVariants = false){
        $sTable = getViewName( 'oxarticles' );
        $sMt = getViewName( 'oxmanufacturers' );
        $oArticle = oxnew('oxarticle');
        if ($blGetVariants) $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfVariantsTable");
        else $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfParentsTable");
        
        $sAdditionalPriceField = $this->_getAdditionalPriceField();
        
        $sBaseSelect = " select $sHeapTable.OXID,
                                $sHeapTable.OXPARENTID, 
                                $sTable.OXVARNAME, 
                                $sTable.OXVARSELECT, 
                                $sTable.OXVARCOUNT, 
                                $sTable.OXPRICE, 
                                $sTable.OXVARMINPRICE, 
                                $sTable.OXMANUFACTURERID,
                                $sTable.OXSTOCK,
                                $sTable.OXSTOCKFLAG
                                ";
        if ($sAdditionalPriceField) {
            $sBaseSelect .= ", $sTable.{$sAdditionalPriceField} as OXUSERPRICE ";
        }
        $sBaseSelect .= " from $sHeapTable left join $sTable ";
        $sSelect = $sBaseSelect . " on ($sTable.oxid = $sHeapTable.oxid)";
            
        return $sSelect;
    }
    protected function z_addAttribute($sAttType,$sAttTitle,$sAttValue,$sArtId,$sParentId=null,$sInfoval="",$iSort=0){
        $sAttId = $this->_prepareTitle($sAttTitle);
        $sAttValueId = $this->_prepareTitle($sAttValue);
        if (!$sParentId) $sParentId = $sArtId;
        
        $this->_aAttributeValues[$sAttId]["title"] = $sAttTitle;
        $this->_aAttributeValues[$sAttId]["type"] = $sAttType;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["sInfoval"] = $sInfoval;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["iSort"] = $iSort;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["sAttId"] = $sAttId;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["sAttTitle"] = $sAttTitle;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["sAttValue"] = $sAttValue;
        $this->_aAttributeValues[$sAttId]["values"][$sAttValueId]["aArtIds"][$sArtId] = $sParentId;
    }
    protected function z_getCategoryAttributesByArtId($sActCat){
        startProfile("mf_getCategoryAttributesByArtId");
        $sO2A = getViewName('oxobject2attribute');
        $sAtt = getViewName('oxattribute');
        $aRet = array();
        
        $aConfiguredCategoryAttributes = $this->z_getConfiguredCategoryAttributes($sActCat, true);
        foreach ($aConfiguredCategoryAttributes as $sAttId){
            $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfParentsTable");
            $sSelect = "select  o2a.OXOBJECTID, o2a.OXVALUE,  att.oxtitle as title
                from $sHeapTable, $sO2A as o2a ,$sAtt as att 
                where $sHeapTable.oxid = o2a.oxobjectid and o2a.oxattrid = att.oxid
                and att.oxid = '$sAttId'";
            $this->z_AssignCategoryAttributesByArtId($sSelect, $aRet);
            $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfVariantsTable");
            $sSelect = "select  o2a.OXOBJECTID, o2a.OXVALUE,  att.oxtitle as title
                from $sHeapTable, $sO2A as o2a ,$sAtt as att 
                where $sHeapTable.oxid = o2a.oxobjectid and o2a.oxattrid = att.oxid
                and att.oxid = '$sAttId'";
            $this->z_AssignCategoryAttributesByArtId($sSelect, $aRet);
        }
        stopProfile("mf_getCategoryAttributesByArtId");
        return $aRet;
    }
    protected function z_AssignCategoryAttributesByArtId($sSelect, &$aRet){
        $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
        $rs = $oDb->execute( $sSelect );
        if ($rs != false && $rs->recordCount() > 0) {
            while ( !$rs->EOF) {
                $aF = $rs->fields;
                $sAttrId = $this->_prepareTitle($aF['title']);
                $aRet[$aF['OXOBJECTID']][$sAttrId] = array(
                    'title' => $aF['title'],
                    'value' => $aF['OXVALUE'],
                );
                $rs->moveNext();
            }
        }
    }
    protected function z_generatePriceAttributes($aArticlePricesById, $sActCat){
        if (!is_array($aArticlePricesById) || !count($aArticlePricesById)) return;
        
        $iMfDisplayPriceAs = $this->getConfig()->getConfigParam('iMfDisplayPriceAs');
        if ($iMfDisplayPriceAs == 0){
            $this->z_generatePriceCategories($aArticlePricesById, $sActCat);
        }
        elseif ($iMfDisplayPriceAs == 1){
            $sAttTitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_PRICE',$iLang, false );
            $this->z_generatePriceSlider($aArticlePricesById, $sActCat, $sAttTitle);
        }
    }
    protected function z_generatePriceSlider($aArticlePrices, $sActCat, $sAttTitle){
        $sAttId = $this->_prepareTitle($sAttTitle);
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
        $aSliderFilter = $aSessionFilter[$sActCat][$iLang][$sAttId];
        if (is_array($aSliderFilter)){
            foreach ($aSliderFilter as $sValue){
                $aValues = explode('-',$sValue);
                $iMinSelected = $aValues[0];
                $iMaxSelected = $aValues[1];
            }
        }
        if ( $iMaxSelected != '' && $iMaxSelected != '' ){
            foreach ($aArticlePrices as $sOxid => $aArtId){
                $iPrice = $aArtId['price'];
                if ($iPrice >= $iMinSelected && $iPrice <= $iMaxSelected){
                    $iArtCnt++;
                    $this->z_addAttribute('price_slider',$sAttTitle,'1',$aArtId['oxid'],$aArtId['oxparentid'], $iMinSelected."-".$iMaxSelected  );
                }
            }
            if (!$iArtCnt){
                $this->z_addAttribute('price_slider',$sAttTitle,'1','','',$iMinSelected."-".$iMaxSelected  );
            }
        }
        else {
            $this->z_addAttribute('price_slider',$sAttTitle,'','','');
        }
    }
    protected function z_getCurrencySign(){
        $myConfig = $this->getConfig();
        $iActCur = $myConfig->getShopCurrency();
        $aCurrencies = $myConfig->getCurrencyArray( $iActCur );
        $oActCur = $aCurrencies[$iActCur];
        $sCur = $oActCur->sign;
        return $sCur;
    }
    protected function z_generatePriceCategories($aArticlePrices, $sActCat){
        $sCur = $this->z_getCurrencySign();
        $iArtCount = count($aArticlePrices);
        
        $aPriceRanges = $this->getConfig()->getConfigParam('aMfPriceRangeLimits');
        $iPriceRangeCnt = 0;
        $iAttGroupCnt = 0;
        $iArtCount = 0;
        $iMinPrice = 0;
        $aPriceRanges[] = '';
        foreach ($aPriceRanges as $iPriceRangeCnt => $iPriceRange){
            $aAttgroup = array();
            $aAttgroup['ids'] = array();
            foreach ($aArticlePrices as $sOxid => $aArtId){
                $iArtPrice = $aArtId['price'];
                $iMaxPrice = $iPriceRange;
                if ($iArtPrice >= $iMinPrice){
                    if (!$iMaxPrice || $iArtPrice < $iMaxPrice){
                        $aAttgroup['ids'][] = $aArtId;
                        $iArtCount++;
                    }
                }
            }
            $aAttgroup['min'] = $iMinPrice;
            $aAttgroup['max'] = $iMaxPrice;  
            $iMinPrice = $iMaxPrice;
            $sAttTitle = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_PRICE',$iLang, false );
            if ($aAttgroup['max']) $sAttValue = $aAttgroup['min']." - ".$aAttgroup['max']." ".$sCur;
            else{
                $sGreaterThan = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_GREATER_THAN',$iLang, false );
                $sAttValue = $sGreaterThan." ".$aAttgroup['min'].$sCur;
            }
            if (is_array($aAttgroup['ids'])){
                foreach ($aAttgroup['ids'] as $aArtId){
                    $this->z_addAttribute('price',$sAttTitle,$sAttValue,$aArtId['oxid'],$aArtId['oxparentid']);
                }
            }
        }
    }
    protected function z_getConfiguredCategoryAttributes($sActCat, $blIds = false){
            
        $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
        $sAtt = getViewName('oxattribute');
        $aRet = array();
        
        
        if ($this->getConfig()->getConfigParam('blMfShowCategoryFilter')){
            $iLang = oxRegistry::getLang()->getBaseLanguage();
            $aRet[] = oxRegistry::getLang()->translateString( 'Z_MULTIFILTER_CATEGORY',$iLang, false );
        }
        
        //Global Search
        if ($sActCat == "xlsearch" && !$this->getConfig()->getConfigParam('blMfShowCategoryFilter')){
            $aLangSearchAttributes = $this->getConfig()->getConfigParam('aLangSearchAttributes');
            $iLang = oxRegistry::getLang()->getBaseLanguage();
            $aSearchAttributes = $aLangSearchAttributes[$iLang];
            $aRet = $aSearchAttributes;
        }
        else {
            //Check inheritance
            if ($this->_blInheritCats){
                $sCatIds = $this->_sUppercats;
                if (empty($sCatIds)) $sCatIds = "''";
                $sCatSelect = " o2a.oxobjectid in ($sCatIds)";
            }
            else {
                $sCatSelect = " o2a.oxobjectid = '$sActCat' ";
            }
            
            $sSelect = "select  att.oxid, att.OXTITLE from $sAtt as att, ".
                       "oxcategory2attribute as o2a ".
                       "where att.oxid = o2a.oxattrid ".
                       "and $sCatSelect ".
                       "order by att.oxpos, o2a.oxsort "
            ;
            $rs = $oDb->execute( $sSelect );
            if ($rs != false && $rs->recordCount() > 0) {
                while ( !$rs->EOF) {
                    $aRet[] = $rs->fields['OXTITLE'];
                    $rs->moveNext();
                }
            }
        }
        if (!count($aRet)) return array();
        if ($blIds){
            $sTitles = "";
            $blSep = false;
            foreach ($aRet as $sAttTitle){
                if ($blSep) $sTitles .= ", ";
                $sTitles .= "'$sAttTitle'";
                $blSep = true;
            }
            $aRet = array();
            $sSelect = "select  att.OXID from $sAtt as att ".
                       "where att.oxtitle in ($sTitles) ".
                       "order by att.oxpos "
            ;
            $rs = $oDb->execute( $sSelect );
            if ($rs != false && $rs->recordCount() > 0) {
                while ( !$rs->EOF) {
                    $aRet[] = $rs->fields['OXID'];
                    $rs->moveNext();
                }
            }
        }
        return $aRet;
    }
    protected function z_getManufacturerNames(){
        $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
        $sMt = getViewName( 'oxmanufacturers' );
        $aRet = array();
        $sSelect = "select OXID, OXTITLE from $sMt where oxactive=1";
        $rs = $oDb->execute( $sSelect );
        if ($rs != false && $rs->recordCount() > 0) {
            while ( !$rs->EOF) {
                $aRet[$rs->fields['OXID']] = $rs->fields['OXTITLE'];
                $rs->moveNext();
            }
        }
        return $aRet;
    }
    protected function z_getArticleCategories(){
        startProfile("mf_getArticleCategories");
        $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
        $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfParentsTable");
        $aRet = array();
        $sSelect = "select  o2c.OXOBJECTID, o2c.OXCATNID
            from $sHeapTable, oxobject2category as o2c, oxcategories
            where $sHeapTable.oxid = o2c.oxobjectid
            and o2c.oxcatnid = oxcategories.oxid
            and oxcategories.oxhidden = 0
            and oxcategories.oxactive = 1
            ";
        $rs = $oDb->execute( $sSelect );
        if ($rs != false && $rs->recordCount() > 0) {
            while ( !$rs->EOF) {
                $aRet[$rs->fields['OXOBJECTID']][] = $rs->fields['OXCATNID'];
                $rs->moveNext();
            }
        }
        $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfVariantsTable");
        $sSelect = "select  $sHeapTable.oxid, o2c.OXCATNID
            from $sHeapTable, oxobject2category as o2c, oxcategories
            where $sHeapTable.oxparentid = o2c.oxobjectid
            and o2c.oxcatnid = oxcategories.oxid
            and oxcategories.oxhidden = 0
            and oxcategories.oxactive = 1
            ";
        $rs = $oDb->execute( $sSelect );
        if ($rs != false && $rs->recordCount() > 0) {
            while ( !$rs->EOF) {
                $aRet[$rs->fields['oxid']][] = $rs->fields['OXCATNID'];
                $rs->moveNext();
            }
        }
        stopProfile("mf_getArticleCategories");
        return $aRet;
    }
    protected function z_getCategoriesMaxCount(){
        startProfile("mf_getCategoriesMaxCount");
        $oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
        $sHeapTable = oxRegistry::getUtils()->fromStaticCache("_mfParentsTable");
        $aRet = array();
        $sSelect = "select  count(o2c.OXID) as cnt, o2c.OXCATNID
            from $sHeapTable, oxobject2category as o2c
            where $sHeapTable.oxid = o2c.oxobjectid
            group by o2c.oxobjectid order by cnt";
        $rs = $oDb->execute( $sSelect );
        if ($rs != false && $rs->recordCount() > 0) {
            while ( !$rs->EOF) {
                $aRet[$rs->fields['OXCATNID']] = $rs->fields['cnt'];
                $rs->moveNext();
            }
        }
        stopProfile("mf_getCategoriesMaxCount");
        return $aRet;
    }
    protected function z_arraysort($array, $sortcol, $sort_descending=false, $aSortByArray = array(), $blSortObjects = false){
		$sortflag = SORT_NUMERIC; 
        if (empty($array)) return $array;
		foreach ($array as $key => $row){
            if ($blSortObjects) $sortVal = $row->$sortcol;
            else $sortVal = $row[$sortcol];
            $sortVal = str_replace(',','.',$sortVal);
            $sortarr[$key] = $sortVal;
            if (!((float)$sortVal!=0 || strpos($sortVal,'0') !== false )) $sortflag = SORT_STRING ; 
		}
        $order = range(1,count($sortarr));
        $keys = array_keys($sortarr);
        array_multisort($sortarr,$sortflag, $order, SORT_DESC, $keys);
        $sortarr = array_combine($keys, $sortarr);
        if ($aSortByArray){
            $this->z_sortbyarray($sortarr,$aSortByArray);
        }
		foreach ($sortarr as $key=>$val){
			$temp_array[$key] = $array[$key];
		}
		if ($sort_descending) {
			return array_reverse($temp_array);
		} else{
			return $temp_array;
		}
	}
    protected function z_sortbyarray(&$array,$aSortByArray){
        $aFlippedSort = array_flip($aSortByArray);
        $i = 1000;
        $keys = array_keys($array);
        foreach ($array as $key => $val){
            if (isset($aFlippedSort[$val])) $aSortArr[] = $aFlippedSort[$val];
            else $aSortArr[] = ++$i;
        }
        array_multisort($aSortArr, $array, $keys);
        $array = array_combine($keys, $array);
	}
    protected function _prepareTitle($sTitle){
        return $this->getConfig()->z_prepareAttributeTitle($sTitle);
    }
    protected function z_getCacheId($sActCat,$iLang){
        $sRet = $sActCat.'_'.$iLang;
        if ($sSearchParam = oxRegistry::getConfig()->getRequestParameter( 'searchparam' )){
            $sRet .= '_'.$sSearchParam;
        }
        if ($sSearchParam = oxRegistry::getConfig()->getRequestParameter( 'searchmanufacturer' )){
            $sRet .= '_'.$sSearchParam;
        }
        return $sRet;
    }
    protected function _getUserPriceSufix(){
        $sPriceSufix = '';
        $oUser = $this->getUser();

        if ( $oUser ) {
            if ( $oUser->inGroup( 'oxidpricea' ) ) {
                $sPriceSufix = 'a';
            } elseif ( $oUser->inGroup( 'oxidpriceb' ) ) {
                $sPriceSufix = 'b';
            } elseif ( $oUser->inGroup( 'oxidpricec' ) ) {
                $sPriceSufix = 'c';
            }
        }
        return $sPriceSufix;
    }
    protected function _getAdditionalPriceField(){
        $sPriceSufix = $this->_getUserPriceSufix();
        $sVarName = "oxprice{$sPriceSufix}";
        if (!$sPriceSufix) return false;
        return $sVarName;
    }
}
