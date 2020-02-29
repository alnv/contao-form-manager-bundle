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

    public function hasPermission($strTable,$arrEntity) {

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($strTable, $arrEntity);

        if ( !$this->isLogged() ) {
            return false;
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
}