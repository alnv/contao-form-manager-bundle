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


    public static function convertValue( $strValue, $strType, $strRgxp = null ) {

        if ( in_array( $strType, [ 'select', 'checkbox' ] ) && !is_array( $strValue ) ) {

            return $strValue ? [ $strValue ] : [];
        }

        return $strValue;
    }


    public static function extractPaletteToArray( $strPalette, $arrSubPalettes = [] ) {

        $arrPalette = [];

        if ( !$strPalette ) {

            return $arrPalette;
        }

        $intLegendCount = 0;
        $arrGroups = \StringUtil::trimsplit( ';', $strPalette );

        foreach ( $arrGroups as $strGroup ) {

            if ( !$strGroup ) {

                continue;
            }

            $blnHide = false;
            $arrFields = \StringUtil::trimsplit( ',', $strGroup );

            if ( preg_match('#\{(.+?)(:hide)?\}#', $arrFields[0], $arrMatches ) ) {

                //@todo add label
                $strLegend = $arrMatches[1];
                $blnHide = count( $arrMatches ) > 2 && ':hide' === $arrMatches[2];
                array_shift( $arrFields );
                $arrFields = self::pluckSubPalettes( $arrFields, $arrSubPalettes );
            }

            else {

                $strLegend = $intLegendCount++;
            }

            $arrPalette[ $strLegend ] = compact( 'arrFields', 'blnHide' );
        }

        return $arrPalette;
    }


    protected static function pluckSubPalettes( $arrFields, $arrSubPalettes ) {

        $arrReturn = [];

        foreach ( $arrFields as $strFieldname ) {

            $arrSubFields = self::findSubPaletteMatch( $strFieldname, $arrSubPalettes );
            $arrReturn[] = $strFieldname;

            if ( !empty( $arrSubFields ) ) {

                $arrReturn = array_filter( $arrReturn );
                $arrReturn = array_merge( $arrReturn, self::pluckSubPalettes( $arrSubFields, $arrSubPalettes ) );
            }
        }

        return $arrReturn;
    }


    protected static function findSubPaletteMatch( $strFieldname, &$arrSubPalettes ) {

        if ( $arrMatches = preg_grep( '/'. $strFieldname .'/', array_keys( $arrSubPalettes ) ) ) {

            if ( isset( $arrSubPalettes[ $arrMatches[0] ] ) ) {

                return \StringUtil::trimsplit( ',', $arrSubPalettes[ $arrMatches[0] ] );
            }

            if ( isset( $arrSubPalettes[ $strFieldname ] ) ) {

                return \StringUtil::trimsplit( ',', $arrSubPalettes[ $strFieldname ] );
            }
        }

        return [];
    }
}