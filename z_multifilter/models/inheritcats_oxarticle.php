<?php
class inheritcats_oxarticle extends inheritcats_oxarticle_parent
{
    //used by oxcmpcategories to select actcat in detail view
    protected function _generateSelectCatStr($sOXID, $sCatId, $dPriceFromTo = false)
    {
        if (!$this->getConfig()->getConfigParam('blInheritCategories')){
            return parent::_generateSelectCatStr($sOXID, $sCatId, $dPriceFromTo);
        }
        $sCategoryView = getViewName('oxcategories');
        $sO2CView = getViewName('oxobject2category');

        $oDb    = oxDb::getDb();
        $sOXID  = $oDb->quote($sOXID);
        $sCatIdQuoted = $oDb->quote($sCatId);
        
        $oCat = oxnew("oxcategory");
        $sCatIds = $oCat->z_getSubCategoryIdsForSelect($sCatId);
        if (!$sCatIds) $sCatIds = "''";

        if (!$dPriceFromTo) {
            $sSelect  = "select oxobject2category.oxcatnid from $sO2CView as oxobject2category ";
            $sSelect .= "left join $sCategoryView as oxcategories on oxcategories.oxid = oxobject2category.oxcatnid ";
            $sSelect .= "where oxobject2category.oxcatnid in ($sCatIds) and oxobject2category.oxobjectid=$sOXID ";
            $sSelect .= "and oxcategories.oxactive = 1 order by oxobject2category.oxtime ";
        } else {
            $dPriceFromTo = $oDb->quote($dPriceFromTo);
            $sSelect  = "select oxcategories.oxid from $sCategoryView as oxcategories where ";
            $sSelect .= "oxcategories.oxid=$sCatIdQuoted and $dPriceFromTo >= oxcategories.oxpricefrom and ";
            $sSelect .= "$dPriceFromTo <= oxcategories.oxpriceto ";
        }
        return $sSelect;
    }
    
    //used by oxseoencoderarticle to build seo url in list view
    protected function _getSelectCatIds( $sOXID, $blActCats = false )
    {
        $sSelect = parent::_getSelectCatIds( $sOXID, $blActCats );
        if ($this->getConfig()->getConfigParam('blInheritCategories')){
            $sSelect = $this->_getUpperCategoryIdsSelect($sSelect);
        }
        return $sSelect;
    }
    //used since 4.9
    protected function _getCategoryIdsSelect($blActCats = false)
    {
        $sSelect = parent::_getCategoryIdsSelect( $blActCats );
        if ($this->getConfig()->getConfigParam('blInheritCategories')){
            $sSelect = $this->_getUpperCategoryIdsSelect($sSelect);
        }
        return $sSelect;
    }
    protected function _getUpperCategoryIdsSelect($sSelect)
    {
        $oDb    = oxDb::getDb();
        $oCat = oxnew("oxcategory");
        $aAllCats = array();
        
        $aCatIds = $oDb->getAll( $sSelect );
        if (is_array($aCatIds)){
            foreach ($aCatIds as $aCatId){
                $sCatId = current( $aCatId );
                $aUpperCatIds = $oCat->z_getUpperCategoryIds($sCatId);
                $aAllCats = array_merge($aAllCats,$aUpperCatIds);
            }
        }
        array_unique($aAllCats);
        
        $sIds = "";
        foreach ($aAllCats as $sCat){
            if ($sIds){
                $sIds .= ", ";
            }
            $sIds .= $oDb->quote($sCat);
        }
        if ($sIds){
            $sSelect = "select oxid as oxcatnid from oxcategories where oxid in ($sIds)";
        }
        else {
            $sSelect = "select oxid as oxcatnid from oxcategories where 0";
        }
        
        return $sSelect;
    }
}