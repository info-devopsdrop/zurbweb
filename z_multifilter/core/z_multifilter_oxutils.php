<?php
class z_multifilter_oxutils extends z_multifilter_oxutils_parent{
    
    protected $_sMfAttributesPattern = "/c_mf_/i";
    
    public function z_GetCacheAge( $sKey )
    {
        $sFilePath = isset( $aMeta["cachepath"] ) ? $aMeta["cachepath"] : $this->getCacheFilePath( $sKey );
        if ( file_exists( $sFilePath ) ) {
            return time() - filemtime( $sFilePath );
        }
    }
    public function z_ResetCacheAge( $sKey )
    {
        $sFilePath = isset( $aMeta["cachepath"] ) ? $aMeta["cachepath"] : $this->getCacheFilePath( $sKey );
        if ( file_exists( $sFilePath ) && filesize( $sFilePath ) ) {
            touch( $sFilePath );
        }
    }
    public function resetMfAttributesCache()
    {
        $aFiles = glob( $this->getCacheFilePath( null, true ) . '*' );
        if ( is_array( $aFiles ) ) {
            // delete all menu cache files
            $sPattern = $this->_sMfAttributesPattern;
            $aFiles = preg_grep( $sPattern, $aFiles );
            foreach ( $aFiles as $sFile ) {
                @unlink( $sFile );
            }
        }
    }
    public function cleanMfAttributesCache()
    {
        $aFiles = glob( $this->getCacheFilePath( null, true ) . '*' );
        if ( is_array( $aFiles ) ) {
            $iMaxCacheAge = $this->getConfig()->getConfigParam('iMfCachingMaxAge');
            // delete all menu cache files
            $sPattern = $this->_sMfAttributesPattern;
            $aFiles = preg_grep( $sPattern, $aFiles );
            foreach ( $aFiles as $sFile ) {
                if (time() - filemtime( $sFile ) > $iMaxCacheAge){
                    @unlink( $sFile );
                }
            }
        }
    }
}