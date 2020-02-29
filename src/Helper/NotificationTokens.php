<?php

namespace Alnv\ContaoFormManagerBundle\Helper;

class NotificationTokens {

    protected $strTable = null;
    protected $arrData = [];

    public function __construct($strTable,$strId) {

        $this->strTable = $strTable;
        \Controller::loadDataContainer($strTable);
        $this->arrData = (new \Alnv\ContaoCatalogManagerBundle\Views\Master( $strTable, [
            'alias' => $strId
        ]))->parse()[0];
    }

    public function getTokens($strDelimiter=', ') {

        $arrTokens = [];
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        foreach ( $this->arrData as $strFieldname => $varValue ) {

            if ( $strFieldname == 'origin' && is_array($varValue) ) {
                foreach ($varValue as $strOriginFieldname => $strValue) {
                    $arrTokens['origin_' . $strOriginFieldname] = $strValue;
                }
                continue;
            }

            $blnParsed = false;
            if ( $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strFieldname] ) {
                if ( isset( $arrField['inputType'] ) ) {
                    switch ($arrField['inputType']) {
                        case 'fileTree':
                            if ( isset( $arrField['eval']['isImage'] ) && $arrField['eval']['isImage'] == true ) {
                                $arrTokens['form_' . $strFieldname] = \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::parseImage($varValue);
                                $blnParsed = true;
                            }
                            break;
                    }
                }
                if ( is_array($varValue) && $arrField['eval']['multiple'] ) {
                    $varValue = array_filter($varValue);
                    $arrTokens['form_' . $strFieldname] = implode($strDelimiter, $varValue);
                    $blnParsed = true;
                }
            }
            if ($blnParsed) {
                continue;
            }

            \Haste\Util\StringUtil::flatten($varValue, 'form_'.$strFieldname, $arrTokens, $strDelimiter);
        }

        return $arrTokens;
    }
}