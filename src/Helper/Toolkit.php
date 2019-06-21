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

                $arrFields = self::getSubPalettesFields( $arrFields, $arrSubPalettes );
                /*
                foreach ( $arrFields as $strFieldname ) {

                    if ( $arrMatches = preg_grep( '/'. $strFieldname .'/', array_keys( $arrSubPalettes ) ) ) {

                        $arrSubFields = [];

                        if ( isset( $arrSubPalettes[ $arrMatches[0] ] ) ) {

                            $arrSubFields = \StringUtil::trimsplit( ',', $arrSubPalettes[ $arrMatches[0] ] );
                        }

                        if ( isset( $arrSubPalettes[ $strFieldname ] ) ) {

                            $arrSubFields = \StringUtil::trimsplit( ',', $arrSubPalettes[ $strFieldname ] );
                        }

                        $arrFields = array_merge( $arrFields, $arrSubFields );
                    }
                }
                */
                // http://catalog-manager-dev:8888/form-manager/getForm/tl_catalog?type=catalog&subPalettes%5Bmode%5D%5B%5D=mode&subPalettes%5Bmode%5D%5B%5D=mode_flex&subPalettes%5BshowColumns%5D%5B%5D=showColumns&subPalettes%5BshowColumns%5D%5B%5D=showColumns_1
                // var_dump($arrFields);
            }

            else {

                $strLegend = $intLegendCount++;
            }

            $arrPalette[ $strLegend ] = compact( 'arrFields', 'blnHide' );
        }

        exit;

        return $arrPalette;
    }


    protected static function getSubPalettesFields( $arrFields, $arrSubPalettes ) {

        foreach ( $arrFields as $strFieldname ) {

            if ( $arrMatches = preg_grep( '/'. $strFieldname .'/', array_keys( $arrSubPalettes ) ) ) {

                $arrSubFields = [];

                if ( isset( $arrSubPalettes[ $arrMatches[0] ] ) ) {

                    $arrSubFields = \StringUtil::trimsplit( ',', $arrSubPalettes[ $arrMatches[0] ] );
                }

                if ( isset( $arrSubPalettes[ $strFieldname ] ) ) {

                    $arrSubFields = \StringUtil::trimsplit( ',', $arrSubPalettes[ $strFieldname ] );
                }

                if ( !empty( $arrSubFields ) ) {

                    var_dump($arrSubFields);

                    $arrSubFields = array_merge( $arrFields, self::getSubPalettesFields( $arrSubFields, $arrSubPalettes ) );
                }

                return $arrSubFields;
            }
        }

        return $arrFields;
    }
}