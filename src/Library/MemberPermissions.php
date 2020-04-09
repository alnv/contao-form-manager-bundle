<?php

namespace Alnv\ContaoFormManagerBundle\Library;

class MemberPermissions {

    protected $arrGroups = [];
    protected $strId = null;

    public function __construct() {

        $objMember = \FrontendUser::getInstance();
        $this->strId = $objMember->id;
        $this->arrGroups = \StringUtil::deserialize($objMember->groups,true);
    }

    public function isLogged() {

        return $this->strId ? true : false;
    }

    public function hasCredentials($strTable) {

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($strTable);

        if ( $objRoleResolver->getFieldByRole('member') || $objRoleResolver->getFieldByRole('group') ) {
            return $this->isLogged();
        }

        return true;
    }

    public function hasPermission($strTable, $arrEntity) {

        return true;

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($strTable, $arrEntity);

        if ( !$this->isLogged() ) {
            return false;
        }

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return true;
        }

        if ( $strMemberField = $objRoleResolver->getFieldByRole('member') ) {
            if (!in_array($this->strId, explode(',', \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::parse($objRoleResolver->getValueByRole('member'), ',', 'value')))) {
                return false;
            }
        }

        if ( $strGroupField = $objRoleResolver->getFieldByRole('group') ) {
            if (empty(array_intersect($this->arrGroups, \StringUtil::deserialize($objRoleResolver->getValueByRole('group'), true)))) {
                return false;
            }
        }

        return true;
    }

    public function hasAddButton($strTable) {

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return true;
        }

        if ( is_numeric($arrGroupRight['maxEntries']) && $this->isLogged() ) {
            $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($strTable);
            $strMemberField = $objRoleResolver->getFieldByRole('member');
            if ($strMemberField) {
                $objEntities = \Database::getInstance()->prepare('SELECT id FROM ' . $strTable . ' WHERE `'.$strMemberField.'`=?' )->execute($this->strId);
                if ($objEntities->numRows >= $arrGroupRight['maxEntries']) {
                    return false;
                }
            }
        }

        return in_array('add', $arrGroupRight['operations']);
    }

    public function hasOperations($strTable,$arrSelectedOperations=[]) {

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return $arrSelectedOperations;
        }
        if (empty($arrSelectedOperations)) {
            return $arrSelectedOperations;
        }
        $arrReturn = [];
        foreach ($arrSelectedOperations as $strSelectedOperation) {
            if (in_array($strSelectedOperation,$arrGroupRight['operations'])) {
                $arrReturn[] = $strSelectedOperation;
            }
        }
        return $arrReturn;
    }

    protected function getGroupRights($strTable) {
        if ($GLOBALS['FORM_MANAGER'] && is_array($GLOBALS['FORM_MANAGER']['GROUP_RIGHTS']) && isset($GLOBALS['FORM_MANAGER']['GROUP_RIGHTS'][$strTable])) {
            return $GLOBALS['FORM_MANAGER']['GROUP_RIGHTS'][$strTable];
        }
        return [
            'admin' => false,
            'maxEntries' => null,
            'operations' => ['add', 'edit', 'delete']
        ];
    }
}