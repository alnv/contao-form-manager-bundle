<?php

namespace Alnv\ContaoFormManagerBundle\Hooks;

use Alnv\ContaoCatalogManagerBundle\Library\Options;

class CatalogField
{

    public function parseCatalogField($arrField, $arrCatalogField)
    {

        if ($arrCatalogField['type'] == 'customOptionWizard') {

            $arrField['inputType'] = 'customOptionWizard';
            $arrField['eval']['tl_class'] = 'clr';
            $arrField['eval']['multiple'] = true;
            $arrField['filter'] = true;
            $arrField['eval']['csv'] = ',';
            $arrField['eval']['addButtonLabel1'] = 'Tag hinzufügen';
            $arrField['eval']['addButtonLabel2'] = 'Hinzufügen';

            $arrField['options_callback'] = function ($objDataContainer = null) use ($arrCatalogField) {
                $objOptions = Options::getInstance($arrCatalogField['fieldname'] . '.' . $arrCatalogField['pid']);
                $objOptions::setParameter($arrCatalogField, $objDataContainer);
                return $objOptions::getOptions();
            };

            if (isset($arrField['eval']['size'])) {
                unset($arrField['eval']['size']);
            }
        }

        return $arrField;
    }
}