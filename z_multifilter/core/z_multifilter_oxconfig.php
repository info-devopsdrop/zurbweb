<?php
class z_multifilter_oxconfig extends z_multifilter_oxconfig_parent{
    public function init()
    {
        // Duplicated init protection
        if ($this->_blInit) {
           return;
        }
        parent::init();
        require getShopBasePath().'/modules/zunderweb/z_multifilter/config_multifilter.php';
    }
    public function getMultifilterColorMappings($sActCat){
        $aLangColorMappings = $this->getConfigParam('aLangColorMappings');
        $aLangColorCategoryMappings = $this->getConfigParam('aLangColorCategoryMappings');
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $aColorMappings = $aLangColorMappings[$iLang];
        if (!is_array($aColorMappings)){
             $aColorMappings = array();
        }
        if (is_array($aLangColorCategoryMappings[$iLang][$sActCat])){
            $aColorMappings = $this->z_array_merge($aColorMappings, $aLangColorCategoryMappings[$iLang][$sActCat]);
        }
        return $aColorMappings;
    }
    public function getMultifilterSortArray($sAttTitle){
        $aLangSortArrays = $this->getConfig()->getConfigParam('aLangSortArrays');
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $aLangSortArray = $aLangSortArrays[$iLang][$sAttTitle];
        if ($aLangSortArray){
            return $aLangSortArray;
        }
        return array();
    }
    public function getMapping($sAttVal, $sActCat){
        $aColorMappings = $this->getConfig()->getMultifilterColorMappings($sActCat);
        $sAttVal = trim($sAttVal);
        $sMappedVal = $aColorMappings[$sAttVal] ? $aColorMappings[$sAttVal] : $sAttVal;
        return $sMappedVal;
    }
    public function z_prepareAttributeTitle($sTitle){
        $sTitle = urlencode(str_replace(' ', '_', $sTitle));
        return $sTitle;
    }
    public function z_unprepareAttributeTitle($sTitle){
        $sTitle = str_replace('_', ' ', urldecode($sTitle));
        return $sTitle;
    }
    protected function z_array_merge($a1,$a2){
        foreach ($a2 as $key => $val){
            $a1[$key] = $val;
        }
        return $a1;
    }
}