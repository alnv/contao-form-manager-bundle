<?php

namespace Alnv\ContaoFormManagerBundle\Hooks;

class CatalogField {

    public function parseCatalogField($arrField, $arrCatalogField) {

        if ($arrCatalogField['type'] == 'customOptionWizard') {
            $arrField['inputType'] = 'customOptionWizard';
            $arrField['options_callback'] = function ( $objDataContainer = null ) use ( $arrCatalogField ) {
                $objOptions = \Alnv\ContaoCatalogManagerBundle\Library\Options::getInstance( $arrCatalogField['fieldname'] . '.' . $arrCatalogField['pid'] );
                $objOptions::setParameter( $arrCatalogField, $objDataContainer );
                return $objOptions::getOptions();
            };
        }

        return $arrField;
    }
}