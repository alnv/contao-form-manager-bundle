<?php

namespace Alnv\ContaoFormManagerBundle\Widgets;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class FormWizard extends \Widget {


    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function generate() {

        if ( $this->readOnly ) {

            return $this->readOnly();
        }

        $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->name];
        $arrAttributes = $this->getAttributesFromDca( $arrField, $this->name, $this->default, $this->name, $this->strTable );
        $strEval = htmlspecialchars(json_encode($arrAttributes),ENT_QUOTES,'UTF-8');
        $strValue = htmlspecialchars(json_encode(\StringUtil::deserialize($this->varValue,true)),ENT_QUOTES,'UTF-8');

        return
            '<div class="v-component">
                <form-wizard :values="'. $strValue .'" :eval="'. $strEval .'" name="'. $this->name .'"></form-wizard>
            </div>';
    }


    protected function readOnly() {

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