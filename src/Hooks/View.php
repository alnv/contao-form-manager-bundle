<?php

namespace Alnv\ContaoFormManagerBundle\Hooks;

use Alnv\ContaoCatalogManagerBundle\Helper\Toolkit;
use Alnv\ContaoFormManagerBundle\Library\MemberPermissions;

class View
{

    public function parseEntity(&$arrRow, $strTable, $arrOptions, $objView)
    {

        $arrRow['editUrl'] = function () use ($arrRow, $strTable, $arrOptions, $objView) {
            if (!$arrOptions['formPage']) {
                return '';
            }
            $objPermission = new MemberPermissions();
            if (!$objPermission->hasPermission($strTable, $arrRow)) {
                return '';
            }
            return Toolkit::parseDetailLink($objView->arrFormPage, $arrRow['alias']);
        };

        $arrRow['deleteUrl'] = function () use ($arrRow, $strTable) {
            $objPermission = new MemberPermissions();
            if (!$objPermission->hasPermission($strTable, $arrRow)) {
                return '';
            }
            return '/form-manager/deleteItem/' . $arrRow['id'];
        };
    }
}