<?php

namespace Alnv\ContaoFormManagerBundle\Widgets;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class FormWizard extends \Widget {


    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function generate() {

        // @todo widget

        if ( !$this->varValue ) {

            return '<p>-</p>';
        }

        if ( !is_array( $this->varValue ) || empty( $this->varValue ) ) {

            return '<p>-</p>';
        }

        $strTemplate = '<p>';
        $arrFields =  $GLOBALS['TL_DCA']['tl_order']['fields'][ $this->strName ]['eval']['form'];

        foreach ( $this->varValue as $arrValue ) {

            foreach ( $arrValue as $strFieldname => $strValue ) {

                $strClass = $GLOBALS['TL_FFL'][ $arrFields[$strFieldname]['inputType'] ];

                if ( !class_exists( $strClass ) ) {

                    return null;
                }

                $arrAttributes = $strClass::getAttributesFromDca( $arrFields[ $strFieldname ], $strFieldname, $arrFields[ $strFieldname ]['default'], $strFieldname, $this->strTable );
                $varCleanValue = Toolkit::getLabelValue( $strValue, $arrAttributes );
                if ( is_array( $varCleanValue ) ) {
                    $varCleanValue = implode( ',', $varCleanValue );
                }
                $strTemplate .= $arrAttributes['label'] . ': ' . $varCleanValue . '<br>';
            }
        }

        $strTemplate .= '</p>';

        return $strTemplate;
    }
}