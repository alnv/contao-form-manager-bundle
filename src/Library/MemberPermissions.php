<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoCatalogManagerBundle\Helper\Toolkit;
use Alnv\ContaoCatalogManagerBundle\Library\RoleResolver;
use Contao\Database;
use Contao\FrontendUser;
use Contao\StringUtil;

class MemberPermissions
{

    protected $arrGroups = [];

    protected $strId = null;

    public function __construct()
    {

        $objMember = FrontendUser::getInstance();

        $this->strId = $objMember->id;
        $this->arrGroups = StringUtil::deserialize($objMember->groups, true);
    }

    public function isLogged()
    {

        return $this->strId ? true : false;
    }

    public function hasCredentials($strTable)
    {

        $objRoleResolver = RoleResolver::getInstance($strTable);
        if ($objRoleResolver->getFieldByRole('member') || $objRoleResolver->getFieldByRole('members') || $objRoleResolver->getFieldByRole('group')) {
            return $this->isLogged();
        }

        return true;
    }

    public function hasPermission($strTable, $arrEntity)
    {

        $objRoleResolver = RoleResolver::getInstance($strTable, $arrEntity);

        if (!$this->isLogged()) {
            return false;
        }

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return true;
        }

        if ($objRoleResolver->getFieldByRole('member')) {
            if (!in_array($this->strId, explode(',', Toolkit::parse($objRoleResolver->getValueByRole('member'), ',', 'value')))) {
                return false;
            }
        }

        if ($objRoleResolver->getFieldByRole('members')) {
            if (!in_array($this->strId, explode(',', Toolkit::parse($objRoleResolver->getValueByRole('members'), ',', 'value')))) {
                return false;
            }
        }

        if ($strGroupField = $objRoleResolver->getFieldByRole('group')) {
            if (empty(array_intersect($this->arrGroups, StringUtil::deserialize($objRoleResolver->getValueByRole('group'), true)))) {
                return false;
            }
        }

        return true;
    }

    public function hasAddButton($strTable)
    {

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return true;
        }

        if (is_numeric($arrGroupRight['maxEntries']) && $this->isLogged()) {
            $objRoleResolver = RoleResolver::getInstance($strTable);
            if ($strMemberField = $objRoleResolver->getFieldByRole('member')) {
                $objEntities = Database::getInstance()->prepare('SELECT id FROM ' . $strTable . ' WHERE `' . $strMemberField . '`=?')->execute($this->strId);
                if ($objEntities->numRows >= $arrGroupRight['maxEntries']) {
                    return false;
                }
            }

            if ($strMemberField = $objRoleResolver->getFieldByRole('members')) {
                $objEntities = Database::getInstance()->prepare('SELECT id FROM ' . $strTable . ' WHERE FIND_IN_SET(?, ' . $strMemberField . ')')->execute($this->strId);
                if ($objEntities->numRows >= $arrGroupRight['maxEntries']) {
                    return false;
                }
            }
        }

        return in_array('add', $arrGroupRight['operations']);
    }

    public function hasOperations($strTable, $arrSelectedOperations = [])
    {

        $arrGroupRight = $this->getGroupRights($strTable);
        if ($arrGroupRight['admin']) {
            return $arrSelectedOperations;
        }
        if (empty($arrSelectedOperations)) {
            return $arrSelectedOperations;
        }
        $arrReturn = [];
        foreach ($arrSelectedOperations as $strSelectedOperation) {
            if (in_array($strSelectedOperation, $arrGroupRight['operations'])) {
                $arrReturn[] = $strSelectedOperation;
            }
        }
        return $arrReturn;
    }

    protected function getGroupRights($strTable)
    {
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