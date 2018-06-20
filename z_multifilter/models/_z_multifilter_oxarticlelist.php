<?php
class z_multifilter_oxarticlelist extends z_multifilter_oxarticlelist_parent{
    
    public function loadCategoryArticles( $sCatId, $aSessionFilter, $iLimit = null )
    {
        startProfile("loadCategoryArticles");
        $sArticleFields = $this->getBaseObject()->getSelectFields();

        $sSelect = $this->_getCategorySelect( $sArticleFields, $sCatId, $aSessionFilter );
        
        // calc count - we can not use count($this) here as we might have paging enabled
        // #1970C - if any filters are used, we can not use cached category article count
        $iArticleCount = null;
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        if ( $aSessionFilter[$sCatId][$iLang]) {
            $oAttrList = oxNew( "oxAttributeList" );
            $aIds = array_unique($oAttrList->getFilterIds(  $sCatId, $aSessionFilter[$sCatId][$iLang] ));
            $iArticleCount = count($aIds);
        }
        else{
            $iArticleCount = oxDb::getDb()->getOne( $this->_getCategoryCountSelect( $sCatId, $aSessionFilter ) );
        }

        if ($iLimit = (int) $iLimit) {
            $sSelect .= " LIMIT $iLimit";
        }
        
        $this->selectString( $sSelect );

        stopProfile("loadCategoryArticles");
        return $iArticleCount;
    }
    public function zwGenerateIdTable($sActCat)
    {
        startProfile("mf_createHeapTable");
        $sArticleTable = $this->getBaseObject()->getViewName();
        $sSelect = $this->_getCategorySelect( $sArticleTable.'.oxid as oxid', $sActCat, array() );  
        $oDB = oxDb::getDb();
        $sTable = "oxarticles";
        
        //checking stock status
        $sStockSelect = " where $sTable.oxactive = 1 ";
        if ( $this->getConfig()->getConfigParam( 'blUseStock' ) ) {
            $sStockSelect .= " and ( $sTable.oxstockflag != 2 or ( $sTable.oxstock + $sTable.oxvarstock ) > 0  ) ";
        }
        
        $sParentsTableName = 'zw_mfactiveparents_'.oxUtilsObject::getInstance()->generateUID();
        //$sParentsTableName = 'zw_mfactiveparents';
        $this->_zwCreateHeapTable($sParentsTableName);
        $sSql = "insert ignore into $sParentsTableName (oxid) select oxid from (".$sSelect.") as t";
        $oDB->execute( $sSql );
        oxRegistry::getUtils()->toStaticCache("_mfParentsTable", $sParentsTableName);
        
        $sVariantsTableName = 'zw_mfactivevariants_'.oxUtilsObject::getInstance()->generateUID();
        //$sVariantsTableName = 'zw_mfactivevariants';
        $this->_zwCreateHeapTable($sVariantsTableName);
        $sSql = "insert ignore into $sVariantsTableName (oxid, oxparentid) 
            select $sTable.oxid, $sTable.oxparentid from $sParentsTableName, $sTable 
            $sStockSelect and $sParentsTableName.oxid  = $sTable.oxparentid";
        $oDB->execute( $sSql );
        oxRegistry::getUtils()->toStaticCache("_mfVariantsTable", $sVariantsTableName);
        
        stopProfile("mf_createHeapTable");
    }
    protected function _zwCreateHeapTable($sHeapTable)
    {
        $blDone = false;
        $sTableCharset = $this->_generateTableCharSet( $sMysqlVersion );
        $oDB = oxDb::getDb();
        $oRs = $oDB->execute( "SHOW VARIABLES LIKE 'version'" );
        $sTableCharset = $this->_generateTableCharSet( $oRs->fields[1] );
        $oDB = oxDb::getDb();
        $sQ = "CREATE TEMPORARY TABLE IF NOT EXISTS {$sHeapTable} ( `OXID` CHAR(32) NOT NULL default '' PRIMARY KEY,  `OXPARENTID` CHAR(32) NOT NULL default '') ENGINE=memory {$sTableCharset}";
        //$sQ = "CREATE TABLE IF NOT EXISTS {$sHeapTable} ( `OXID` CHAR(32) NOT NULL default '' PRIMARY KEY,  `OXPARENTID` CHAR(32) NOT NULL default '' ) ENGINE=memory {$sTableCharset}";
        if ( ( $oDB->execute( $sQ ) ) !== false ) {
            $blDone = true;
            $oDB->execute( "TRUNCATE TABLE {$sHeapTable}" );
        }
        return $blDone;
    }
    protected function _generateTableCharSet( $sMysqlVersion )
    {
        $sTableCharset = "";

        //if MySQL >= 4.1.0 set charsets and collations
        if ( version_compare( $sMysqlVersion, '4.1.0', '>=' ) > 0 ) {
            $oDB = oxDb::getDb( oxDB::FETCH_MODE_ASSOC );
            $oRs = $oDB->execute( "SHOW FULL COLUMNS FROM `oxarticles` WHERE field like 'OXID'" );
            if ( isset( $oRs->fields['Collation'] ) && ( $sMysqlCollation = $oRs->fields['Collation'] ) ) {
                $oRs = $oDB->execute( "SHOW COLLATION LIKE '{$sMysqlCollation}'" );
                if ( isset( $oRs->fields['Charset'] ) && ( $sMysqlCharacterSet = $oRs->fields['Charset'] ) ) {
                    $sTableCharset = "DEFAULT CHARACTER SET {$sMysqlCharacterSet} COLLATE {$sMysqlCollation}";
                }
            }
        }
        return $sTableCharset;
    }
    
    protected function _getFilterSql( $sCatId, $aFilter )
    {
        if (!count($aFilter)) return;
    
        $oAttrList = oxNew( "oxAttributeList" );
        $aIds = array_unique($oAttrList->getFilterIds(  $sCatId, $aFilter, true )); 
        $oDb = oxDb::getDb();
        $sArticleTable = getViewName( 'oxarticles' );
        $sIds = '';

        if ( $aIds ) {
            foreach ( $aIds as $sId ) {
                if ( $sIds ) {
                    $sIds .= ', ';
                }
                $sIds .= $oDb->quote( $sId );
            }

            if ( $sIds ) {
                $sFilterSql = " and $sArticleTable.oxid in ( $sIds ) ";
            }
        // bug fix #0001695: if no articles found return false
        } elseif ( !( current( $aFilter ) == '' && count( array_unique( $aFilter ) ) == 1 ) ) {
            $sFilterSql = " and false ";
        }
        return $sFilterSql;
    }
    
    //Inherit Categories
    protected function _getCategorySelect( $sFields, $sCatId, $aSessionFilter )
    {
        if (!$this->getConfig()->getConfigParam('blInheritCategories')){
            return parent::_getCategorySelect( $sFields, $sCatId, $aSessionFilter );
        }
        $sArticleTable = getViewName( 'oxarticles' );
        $sO2CView      = getViewName( 'oxobject2category' );

        // ----------------------------------
        // sorting
        $sSorting = '';
        if ( $this->_sCustomSorting ) {
            $sSorting = " {$this->_sCustomSorting} , ";
        }

        // ----------------------------------
        // filtering ?
        $sFilterSql = '';
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        if ( $aSessionFilter && isset( $aSessionFilter[$sCatId][$iLang] ) ) {
            $sFilterSql = $this->_getFilterSql($sCatId, $aSessionFilter[$sCatId][$iLang]);
        }
        
        $oCat = oxnew("oxcategory");
        $sCatIds = $oCat->z_getSubCategoryIdsForSelect($sCatId);
        if (empty($sCatIds)) $sCatIds = "''";

        $oDb = oxDb::getDb();

        $sSelect = "SELECT distinct $sFields FROM $sO2CView as oc left join $sArticleTable
                    ON $sArticleTable.oxid = oc.oxobjectid
                    WHERE ".$this->getBaseObject()->getSqlActiveSnippet()." and $sArticleTable.oxparentid = ''
                    and oc.oxcatnid in ($sCatIds) $sFilterSql ORDER BY $sSorting oc.oxpos, oc.oxobjectid";

        return $sSelect;
    }
    protected function _getCategoryCountSelect( $sCatId, $aSessionFilter )
    {
        if (!$this->getConfig()->getConfigParam('blInheritCategories')){
            return parent::_getCategoryCountSelect( $sCatId, $aSessionFilter );
        }
        $sArticleTable = getViewName( 'oxarticles' );
        $sO2CView      = getViewName( 'oxobject2category' );


        // ----------------------------------
        // filtering ?
        $sFilterSql = '';
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        if ( $aSessionFilter && isset( $aSessionFilter[$sCatId][$iLang] ) ) {
            $sFilterSql = $this->_getFilterSql($sCatId, $aSessionFilter[$sCatId][$iLang]);
        }
        
        $oCat = oxnew("oxcategory");
        $sCatIds = $oCat->z_getSubCategoryIdsForSelect($sCatId);
        if (empty($sCatIds)) $sCatIds = "''";

        $oDb = oxDb::getDb();

        $sSelect = "SELECT COUNT(DISTINCT $sArticleTable.oxid) FROM $sO2CView as oc left join $sArticleTable
                    ON $sArticleTable.oxid = oc.oxobjectid
                    WHERE ".$this->getBaseObject()->getSqlActiveSnippet()." and $sArticleTable.oxparentid = ''
                    and oc.oxcatnid in ($sCatIds) $sFilterSql ";

        return $sSelect;
    }
    public function getCategoryCountSelect( $sCatId, $aSessionFilter )
    {
        return $this->_getCategoryCountSelect( $sCatId, $aSessionFilter );
    }
}
