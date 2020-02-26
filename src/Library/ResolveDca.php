<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

class ResolveDca extends Resolver {

    protected $strTable = null;
    protected $arrOptions = [];
    protected $arrPalette = [];

    public function __construct( $strTable, $arrOptions = [] ) {

        $this->strTable = $strTable;
        $this->arrOptions = $arrOptions;
        \System::loadLanguageFile('default');
        \Controller::loadDataContainer( $this->strTable );
        \System::loadLanguageFile( $this->strTable, $arrOptions['language'] ?: null );
        parent::__construct();
    }

    public function getForm() {

        if ( !$GLOBALS['TL_DCA'][ $this->strTable ] ) {

            return [];
        }

        if ( !$this->arrOptions['initialized'] ) {

            $this->arrOptions['type'] = $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['type']['default'];
        }

        if ( !$this->arrOptions['type'] ) {

            $this->arrOptions['type'] = 'default';
        }

        $strPalette = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $this->arrOptions['type'] ];
        $arrActiveSelectors = $this->getActiveSelector();
        $this->arrPalette = Toolkit::extractPaletteToArray( $strPalette, $arrActiveSelectors );

        $arrPalettes = [];
        $arrSelectors = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'];

        foreach ( $this->arrPalette as $strPalette => $arrPalette ) {

            $objPalette = new \stdClass();
            $objPalette->label = $GLOBALS['TL_LANG'][ $this->strTable ][$strPalette];
            $objPalette->fields = [];
            $objPalette->name = $strPalette ?: '';
            $objPalette->hide = $arrPalette['blnHide'];

            foreach ( $arrPalette['arrFields'] as $strFieldname ) {

                $arrField = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strFieldname ];
                $strClass = Toolkit::convertBackendFieldToFrontendField( $arrField['inputType'] );

                if ( !class_exists( $strClass ) ) {

                    continue;
                }

                if ( $strFieldname == 'type' ) {

                    $arrField['default'] = $this->arrOptions['type'];
                }

                $arrAttributes = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
                $arrAttributes = $this->parseAttributes( $arrAttributes );
                $this->addParentData( $strFieldname, $arrAttributes );

                if ( $arrAttributes === null ) {

                    continue;
                }

                if ( is_array( $arrSelectors ) && in_array( $strFieldname, $arrSelectors ) && $strFieldname != 'type' ) {

                    $arrAttributes['isSelector'] = true;
                }

                $objPalette->fields[] = $arrAttributes;
            }

            $arrPalettes[] = $objPalette;
        }

        return $arrPalettes;
    }

    protected function getActiveSelector() {

        $arrSubpalettes = [];

        if ( is_array( $this->arrOptions['subpalettes'] ) && !empty( $this->arrOptions['subpalettes'] ) ) {

            foreach ( $this->arrOptions['subpalettes'] as $strSelector ) {

                list( $strFieldname, $strValue ) = explode( '::', $strSelector );

                if ( $strValue == '1' ) {

                    $strValue = '';
                }

                $strPalette = $strFieldname . ( $strValue ? '_' . $strValue : '' ); $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strFieldname . ( $strValue ? '_' . $strValue : '' ) ];

                if ( isset( $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strPalette ] ) ) {

                    $arrSubpalettes[ $strPalette ] = $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strPalette ];
                }
            }
        }

        return $arrSubpalettes;
    }

    public function saveRecord( $arrForm ) {

        $arrSubmitted = [];
        $arrSubmitted['tstamp'] = time();
        foreach ( $arrForm as $objPalette ) {
            foreach ( $objPalette->fields as $arrField ) {
                $arrSubmitted[ $arrField['name'] ] = Toolkit::getDbValue( $arrField['postValue'], $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $arrField['name'] ] );
            }
        }

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($this->strTable,$arrSubmitted);
        if ( $strMemberField = $objRoleResolver->getFieldByRole('member') ) {
            $objMember = \FrontendUser::getInstance();
            if ($objMember->id) {
                $arrSubmitted[$strMemberField] = $objMember->id;
            }
        }

        if ( isset( $GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] ) && is_array($GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] ) ) {
            foreach ( $GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] as $arrCallback ) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($arrSubmitted, $arrForm, $this->arrOptions, $this);
                }
                elseif (\is_callable($arrCallback)) {
                    $arrCallback($arrSubmitted, $arrForm, $this->arrOptions, $this);
                }
            }
        }

        if ( isset( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] ) ) {
            foreach ( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] as $arrCallback ) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($arrSubmitted, false, $this->arrOptions, $this);
                }
                elseif (\is_callable($arrCallback)) {
                    $arrCallback($arrSubmitted, false, $this->arrOptions, $this);
                }
            }
            return null;
        }

        if ( \Input::post('id') ) {
            \Database::getInstance()->prepare('UPDATE '. $this->strTable .' %s WHERE id=?')->set($arrSubmitted)->execute(\Input::post('id'));
        } else {
            $objInsert = \Database::getInstance()->prepare('INSERT INTO '. $this->strTable .' %s')->set($arrSubmitted)->execute();
            \Input::setPost('id', $objInsert->insertId);
        }

        $objDataContainer = new \Alnv\ContaoFormManagerBundle\Helper\VirtualDataContainer($this->strTable);
        $objDataContainer->table = $this->strTable;
        $objDataContainer->activeRecord = \Input::post('id');
        $objDataContainer->ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
        $objDataContainer->ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];

        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($objDataContainer);
                }
                elseif (\is_callable($arrCallback)) {
                    $arrCallback($objDataContainer);
                }
            }
        }
    }

    protected function addParentData($strFieldname, &$arrAttributes) {

        $arrAttributes['_source'] = 'dc';
        $arrAttributes['_table'] = $this->strTable;
        $arrAttributes['_identifier'] = $strFieldname;
    }

    public function getWizard() {

        $this->arrOptions['type'] = null;

        if ( !$GLOBALS['TL_DCA'][ $this->strTable ] ) {

            return null;
        }

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = $this->arrOptions['wizard'];
        $arrFields = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $this->arrOptions['wizard'] ]['eval']['form'] ?: [];

        foreach ( $arrFields as $strFieldname => $arrField ) {

            $strClass = Toolkit::convertBackendFieldToFrontendField( $arrField['inputType'] );

            if ( !class_exists( $strClass ) ) {

                continue;
            }

            $arrAttributes = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
            $arrAttributes = $this->parseAttributes( $arrAttributes );
            $this->addParentData( $strFieldname, $arrAttributes );

            if ( $arrAttributes === null ) {

                continue;
            }

            $objPalette->fields[] = $arrAttributes;
        }

        return [$objPalette];
    }
}