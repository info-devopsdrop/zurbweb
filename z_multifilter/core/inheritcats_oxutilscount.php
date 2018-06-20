<?php
class inheritcats_oxutilscount extends inheritcats_oxutilscount_parent{
    public function setCatArticleCount( $aCache, $sCatId, $sActIdent )
    {
        if (!$this->getConfig()->getConfigParam('blInheritCategories')){
            return parent::setCatArticleCount( $aCache, $sCatId, $sActIdent );
        }
        $oArticle = oxNew( 'oxarticle' );
        $sTable   = $oArticle->getViewName();
        $sO2CView = getViewName( 'oxobject2category' );
        $oDb = oxDb::getDb();

        $oArtList = oxnew("oxarticlelist");
        $sQ = $oArtList->getCategoryCountSelect( $sCatId, null );
        $aCache[$sCatId][$sActIdent] = $oDb->getOne( $sQ );

        $this->_setCatCache( $aCache );
        return $aCache[$sCatId][$sActIdent];
    }
}
