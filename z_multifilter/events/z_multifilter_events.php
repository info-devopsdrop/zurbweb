<?php
class z_multifilter_events
{
    public static function onactivate(){
        $aAtts = array(
            array ('DE' => 'Hersteller', 'EN' => 'Manufacturer'),
            array ('DE' => 'Preis', 'EN' => 'Price'),
            array ('DE' => 'Verfügbarkeit', 'EN' => 'Availability'),
        );
        z_multifilter_events::insertAttributes($aAtts);
    }
    public static function ondeactivate(){
        
    }
    
    protected static function insertAttributes($aAtts){
        $oLang = oxregistry::getLang();
        $aLangs = $oLang->getAllShopLanguageIds();
        $sShopId = oxregistry::getConfig()->getShopId();
        foreach ($aAtts as $sAtt){
            if ($aLangs[0] == 'de'){
                $sTitleDE = $sAtt['DE'];
                $sTitleEN = $sAtt['EN'];
                $sAttId = 'mf_'.$sAtt['EN'];
            }
            else {
                $sTitleEN = $sAtt['DE'];
                $sTitleDE = $sAtt['EN'];
                $sAttId = 'mf_'.$sAtt['EN'];
            }
            $oDb = oxDb::getDb();
            $blAttPresent = (bool) $oDb->getOne('Select oxid from oxattribute where oxtitle=' . $oDb->quote($sTitleDE));
                            
            if (!$blAttPresent){
                $sSql = "
                    INSERT INTO `oxattribute` (`OXID`, `OXSHOPID`, `OXTITLE`, `OXTITLE_1`) VALUES
                    (" . $oDb->quote($sAttId) . ", " . $oDb->quote($sShopId) . ", " . $oDb->quote($sTitleDE) . ", " . $oDb->quote($sTitleEN) . ")";
                
                if ($blIsUtf){
                    $sSql = utf8_encode($sSql);
                }
                $oDb->execute( $sSql );
            }
        }
    }
}
