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


    public static function extractPaletteToArray( $strPalette, $arrPermitted = [] ) {

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

                if ( !empty( $arrPermitted ) && is_array( $arrPermitted ) ) {

                    $arrPermittedFields = [];

                    foreach ( $arrFields as $strFieldname ) {

                        if ( in_array( $strFieldname, $arrPermitted ) ) {

                            $arrPermittedFields[] = $strFieldname;
                        }
                    }

                    $arrFields = $arrPermittedFields;
                    unset( $arrPermittedFields );
                }

                if ( empty( $arrFields ) ) {

                    continue;
                }
            }

            else {

                $strLegend = $intLegendCount++;
            }

            $arrPalette[ $strLegend ] = compact( 'arrFields', 'blnHide' );
        }

        return $arrPalette;
    }
}