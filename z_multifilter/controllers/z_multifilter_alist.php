<?php
class z_multifilter_alist extends z_multifilter_alist_parent
{
    protected $_blIsFiltered = 0;
    
    public function isColorCategory($sCatname){
        $aColorSwatchCategories = $this->getConfig()->getConfigParam('aColorSwatchCategories');
        if (in_array($sCatname, $aColorSwatchCategories)) return true;
    }
    public function isDropdownCategory($sCatname){
        $aDropdownCategories = $this->getConfig()->getConfigParam('aDropdownCategories');
        if (in_array($sCatname, $aDropdownCategories)) return true;
    }
    public function getSwatchBg($sColor){
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $oStr = getStr();
        $aColorSwatchColors = $this->getConfig()->getConfigParam('aColorSwatchColors');
        $sCol = $aColorSwatchColors[$iLang][$oStr->strtolower(trim($sColor))];
        if ($sCol) return $sCol;
        $aColorSwatchImages = $this->getConfig()->getConfigParam('aColorSwatchImages');
        $sImg = $aColorSwatchImages[$iLang][$oStr->strtolower(trim($sColor))];
        if ($sImg) return "url(".$this->getViewConfig()->getModuleUrl('z_multifilter')."out/img/colorswatches/".$sImg.")";
    }
    public function init(){
        $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
        $sActCat = oxRegistry::getConfig()->getRequestParameter( 'cnid' );
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        //if different cat remove sessionvar
        if (is_array($aSessionFilter)){
            foreach ($aSessionFilter as $sFiltercat=>$val) {
                if ($sActCat != $sFiltercat){
                    unset($aSessionFilter[$sFiltercat]);
                }
            }
        }
        oxRegistry::getSession()->setVariable("session_attrfilter", $aSessionFilter);
        parent::init();
        if (!$aSessionFilter[$sActCat][$iLang] && oxRegistry::getConfig()->getRequestParameter( 'keepFilter' )){
            $aSessionFilter[$sActCat][$iLang] = oxRegistry::getSession()->getVariable("session_attrfilter_last");
            oxRegistry::getSession()->setVariable("session_attrfilter", $aSessionFilter);
        }
        if (!empty($aSessionFilter[$sActCat][$iLang])){
            $this->_blIsFiltered = 1;
        }
    }
    public function getIsFiltered(){
        return $this->_blIsFiltered;
    }
    public function executefilter()
    {
        $this->_blIsFiltered = 0;
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $aFilter = oxRegistry::getConfig()->getRequestParameter( 'attrfilter', 1 );
        $sActCat = oxRegistry::getConfig()->getRequestParameter( 'cnid' );
        
        //If Single Select
        $aSingleSelect = oxRegistry::getConfig()->getRequestParameter( 'attrfilter_single', 1 );
        if ($aSingleSelect && is_array($aSingleSelect )){
            foreach ($aSingleSelect as $key => $val){
                $aFilter[$key][$val] = 1;
            }
        }
        
        //reset
        if ($sReset = oxRegistry::getConfig()->getRequestParameter( 'multifilter_reset' )){
            if ($sReset == 'all' ){
                $aFilter = array();
            }
            elseif (isset($aFilter[$sReset])) {
                $aFilter[$sReset] = array();
            }
        }
        
        //Remove empty Values
        if (is_array( $aFilter ) ) {
            if (isset($aFilter['time'])) unset($aFilter['time']);
            foreach ($aFilter as $Filterkey => $aFiltervals){
                foreach ($aFiltervals as $sValuekey => $sValue){
                    if (!$sValue){
                        unset ($aFilter[$Filterkey][$sValuekey]);
                    }
                }
            }
        }
        if (is_array( $aFilter ) ) {
            foreach ($aFilter as $Filterkey => $aFiltervals){
                if (!count($aFiltervals)){
                    unset ($aFilter[$Filterkey]);
                }
            }
        }
        
        $aSessionFilter = array();  
        $aSessionFilter[$sActCat][$iLang] = $aFilter;
        oxRegistry::getSession()->setVariable( 'session_attrfilter', $aSessionFilter );
        oxRegistry::getSession()->setVariable("session_attrfilter_last", $aFilter);
        if (!empty($aSessionFilter[$sActCat][$iLang])){
            $this->_blIsFiltered = 1;
        }
    }
    public function render(){
        if ($blAjax = oxRegistry::getConfig()->getRequestParameter( 'ajax' )){
            parent::render();
            $this->addTplParam('ajax', '1' );
            //$sBaseurl = $this->getCanonicalUrl();
            if (!$sBaseurl){
                $sBaseurl = $this->getViewConfig()->getSelfLink().htmlspecialchars_decode($this->getAdditionalParams());
            }
            $sBaseurl = str_replace('&amp;','&',$sBaseurl);
            $this->toJson('baseurl',$sBaseurl);
            $oTheme = oxNew('oxTheme');
            $sThemeId = $oTheme->getActiveThemeId();
            if ($sThemeId == 'mobile'){
                return "ajaxlist_mobile.tpl";
            }
            return "ajaxlist.tpl";
        }
        if (oxRegistry::getConfig()->getRequestParameter( 'forceFilterCacheUpdate' )){
            $this->getAttributes();
            oxRegistry::getUtils()->commitFileCache();
            die ("Update Cache successful");
        }
        if (oxRegistry::getConfig()->getRequestParameter( 'resetFilterCache' )){
            oxRegistry::getUtils()->resetMfAttributesCache();
            die ("Delete Cache successful");
        }
        if (!$this->getConfig()->getConfigParam('blMfEnableCaching')){
            oxRegistry::getUtils()->resetMfAttributesCache();
        }
        else {
            oxRegistry::getUtils()->cleanMfAttributesCache();
        }
        return parent::render();
    }
    public function getTime(){
        return time();
    }
    public function toJson($sName,$sText){
        if (!oxRegistry::getConfig()->isUtf()){
            $sText = str_replace(chr(164),'&euro;',$sText);
            $sText = utf8_encode($sText);
        }
        if ($this->getShopEdition() == "EE"){
            $oCache = oxNew('oxCache');
            if ($oCache->isViewCacheable( $this->getClassName() ) && $this->canCache()) {
                $sText = $oCache->processCache($sText);
            }
        }
        $this->z_aJsonParts[$sName] = $sText;
    }
    public function outputJson(){
        $this->z_stopDebug();
        return json_encode($this->z_aJsonParts);
    }
    protected function z_stopDebug(){
        $this->getConfig()->setConfigParam( 'iDebug', 0 );
        if ($oConfigFile = OxRegistry::get("OxConfigFile")){
            $oConfigFile->setVar('iDebug', 0);
        }
    }
    protected function _processListArticles()
    {
        parent::_processListArticles();
        if ( $aArtList = $this->getArticleList() ) {
            foreach ( $aArtList as $oArticle ) {
                $sAddSelectionParams = $this->z_getSelectionParams($oArticle);
                // appending seo urls
                if ( $sAddSelectionParams ) {
                    $oArticle->appendLink( $sAddSelectionParams );
                }
            }
        }
    }
    public function z_getSelectionParams($oArticle)
    {
        //var_dump($oArticle->oxarticles__oxtitle->value);
        $aFiltervals = array();
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $sActCat = oxRegistry::getConfig()->getRequestParameter( 'cnid' );
        $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
        $aFilter = $aSessionFilter[$sActCat][$iLang];
        if (is_array($aFilter)){
            foreach ($aFilter as $aFiltervars){
                if (is_array($aFiltervars)){
                    foreach ($aFiltervars as $Filterkey => $Filterval){
                        //var_dump($this->getConfig()->z_unprepareAttributeTitle($Filterkey));
                        $aFiltervals[] = $this->getConfig()->z_unprepareAttributeTitle($Filterkey);
                    }
                }
            }
        }
        $aVariantSelections = $oArticle->getVariantSelections();
        $aSetFilters = array();
        if (is_array($aVariantSelections)){
            foreach($aVariantSelections["selections"] as $sKey => $oList){
                if (!$oList->getActiveSelection()){
                    $aSelections = $oList->getSelections();
                    foreach ($aSelections as $oSelection){
                        $sVal = $oSelection->getValue();
                        $sName = $this->getConfig()->getMapping($oSelection->getName(),$sActCat);
                        if (in_array($sName, $aFiltervals)){
                            $aSetFilters[$sKey] = $sVal;
                        }
                    }
                }
            }
        }
        foreach ($aSetFilters as $sKey => $sVal){
            $sAddParams .= "&amp;varselid[$sKey]=$sVal";
        }
        
        return $sAddParams;
    }
    public function getHideAfter(){
        return (int) $this->getConfig()->getConfigParam('blMfShowMoreFrom');
    }
    public function getDisplayTop(){
        return (int) $this->getConfig()->getConfigParam('blMfDisplayTop');
    }
    public function getHideHead(){
        return (int) ($this->getConfig()->getConfigParam('blMfHideHead') && !$this->getConfig()->getConfigParam('blMfDisplayTop'));
    }
    public function showFilterArticleCount(){
        return (int) $this->getConfig()->getConfigParam('blMfShowArticleCount');
    }
}
