<?php

namespace Alnv\ContaoFormManagerBundle\Hooks;

class View {

    public function parseEntity(&$arrRow, $strTable, $arrOptions, $objView) {

        $arrRow['editUrl'] = function () use ($arrRow, $strTable, $arrOptions, $objView) {
            if (!$arrOptions['formPage']) {
                return '';
            }
            $objPermission = new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions();
            if (!$objPermission->hasPermission($strTable, $arrRow)) {
                return '';
            }
            return \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::parseDetailLink($objView->arrFormPage, $arrRow['alias']);
        };

        $arrRow['deleteUrl'] = function () use ($arrRow, $strTable) {
            $objPermission = new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions();
            if (!$objPermission->hasPermission($strTable, $arrRow)) {
                return '';
            }
            return '/form-manager/deleteItem/' . $arrRow['id'];
        };
    }
}