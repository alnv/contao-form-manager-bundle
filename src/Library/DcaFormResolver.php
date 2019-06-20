<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class DcaFormResolver {


    protected $strTable = null;
    protected $arrFields = [];


    public function __construct( $strTable, $arrFields = [] ) {

        \Controller::loadDataContainer( $strTable );

        if ( !isset( $GLOBALS['TL_DCA'][ $strTable ] ) ) {

            return null;
        }

        if ( !isset( $GLOBALS['TL_DCA'][ $strTable ]['fields'] ) || !is_array( $GLOBALS['TL_DCA'][ $strTable ]['fields'] ) ) {

            return null;
        }

        if ( empty( $arrFields ) ) {

            $arrFields = array_keys( $GLOBALS['TL_DCA'][ $strTable ]['fields'] );
        }

        foreach ( $arrFields as $strField ) {

            $arrField = $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $strField ];

            if ( !$arrField ) {

                continue;
            }

            $this->arrFields[ $strField ] = $arrField;
        }

        $this->strTable = $strTable;
    }


    public function getFields() {

        $arrFields = [];

        foreach ( $this->arrFields as $strFieldname => $arrField ) {

            $arrAttributes = $this->parseAttributes( $strFieldname, $arrField );

            if ( !$arrAttributes ) {

                continue;
            }

            $arrAttributes['component'] = Toolkit::convertTypeToComponent( $arrAttributes['type'], $arrAttributes['rgxp'] );
            $arrFields[ $strFieldname ] = $arrAttributes;
        }

        return $arrFields;
    }


    protected function parseAttributes( $strFieldname, $arrField ) {

        $strClass = $GLOBALS['TL_FFL'][ $arrField['inputType'] ];

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        return $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
    }
}