<?php

namespace Alnv\ContaoFormManagerBundle\Helper;


class Toolkit {


    public static function convertTypeToComponent( $strType, $strRgxp = null ) {

        if ( !isset( $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] ) && !is_array( $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] ) ) {

            return null;
        }

        $arrTypes = $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'][ $strType ];

        if ( !is_array( $arrTypes ) || empty( $arrTypes ) ) {

            return null;
        }

        if ( $strRgxp ) {

            return isset( $arrTypes[ $strRgxp ] ) ? $arrTypes[ $strRgxp ] : null;
        }

        return isset( $arrTypes[ 'default' ] ) ? $arrTypes[ 'default' ] : null;
    }
}