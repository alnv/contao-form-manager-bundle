<?php

namespace Alnv\ContaoFormManagerBundle\Hybrids;

class FormWidget extends \Widget {

    public function validate() {

        $varValues = $this->getPost( $this->strName );

        if ( is_string( $varValues ) ) {
            $varValues = json_decode( $varValues, true );
        }

        if ( $this->mandatory && !$this->hasValue( $varValues ) ) {
            $this->addError( sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel ) );
        }

        $arrFields =  $GLOBALS['TL_DCA'][$this->strTable]['fields'][ $this->strName ]['eval']['form'];

        if ( is_array( $varValues ) && !empty( $varValues ) ) {
            foreach ( $varValues as $strIndex => $arrValue ) {

                foreach ( $arrValue as $strFieldname => $strValue ) {

                    $strPost = $this->strName . '_' . $strIndex . '_' . $strFieldname;
                    $strClass = $GLOBALS['TL_FFL'][ $arrFields[$strFieldname]['inputType'] ];
                    if ( !class_exists( $strClass ) ) {
                        continue;
                    }
                    \Input::setPost( $strPost, $strValue );
                    $arrFields[ $strFieldname ]['value'] = $strValue;
                    $arrAttributes = $strClass::getAttributesFromDca( $arrFields[ $strFieldname ], $strPost, $arrFields[ $strFieldname ]['default'], $strPost, $this->strTable );
                    $arrAttributes['name'] = $strPost;
                    $objField = new $strClass( $arrAttributes );
                    $objField->validate();
                    if ( $objField->hasErrors() ) {
                        $arrErrors = $objField->getErrors();
                        if ( is_array( $arrErrors ) && !empty( $arrErrors ) ) {
                            foreach ( $arrErrors as $strError ) {
                                $this->addError( $strError );
                            }
                        }
                    }
                }
            }
        }

        $this->varValue = $varValues;
    }

    protected function hasValue( $varValues ) {

        if ( !$varValues || empty( $varValues ) ) {
            return false;
        }

        if ( !is_array( $varValues ) ) {
            return false;
        }

        if ( isset( $varValues[0] ) && empty( $varValues[0] ) ) {
            return false;
        }

        return true;
    }

    public function generate() {}
}