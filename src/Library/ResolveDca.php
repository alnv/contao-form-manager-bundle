<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use NotificationCenter\Model\Notification;
use Alnv\ContaoCatalogManagerBundle\Library\RoleResolver;
use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Alnv\ContaoFormManagerBundle\Helper\NotificationTokens;
use Alnv\ContaoFormManagerBundle\Helper\VirtualDataContainer;
use Contao\Database;
use Contao\FrontendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\ModuleModel;
use Contao\Controller;

class ResolveDca extends Resolver
{

    protected $objModule = null;

    protected $strTable = null;

    protected $arrOptions = [];

    protected $arrPalette = [];

    public function __construct($strTable, $arrOptions = [])
    {

        $this->strTable = $strTable;
        $this->arrOptions = $arrOptions;
        $this->strModuleId = $arrOptions['id'];

        if ($arrOptions['id']) {
            $this->objModule = ModuleModel::findByPk($arrOptions['id']);
        }

        System::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE']);
        System::loadLanguageFile($this->strTable, $GLOBALS['TL_LANGUAGE']);
        Controller::loadDataContainer($this->strTable);
    }

    public function getForm()
    {

        if (!$GLOBALS['TL_DCA'][$this->strTable]) {
            return [];
        }

        if (!$this->arrOptions['initialized']) {
            $this->arrOptions['type'] = $GLOBALS['TL_DCA'][$this->strTable]['fields']['type']['default'];
        }

        if (!$this->arrOptions['type']) {
            $this->arrOptions['type'] = 'default';
        }

        $strPalette = $GLOBALS['TL_DCA'][$this->strTable]['palettes'][$this->arrOptions['type']];
        $arrActiveSelectors = $this->getActiveSelector();
        $this->arrPalette = Toolkit::extractPaletteToArray($strPalette, $arrActiveSelectors);

        $arrPalettes = [];
        $arrSelectors = $GLOBALS['TL_DCA'][$this->strTable]['palettes']['__selector__'];

        foreach ($this->arrPalette as $strPalette => $arrPalette) {

            $objPalette = new \stdClass();
            $objPalette->label = $GLOBALS['TL_LANG'][$this->strTable][$strPalette];
            $objPalette->fields = [];
            $objPalette->name = $strPalette ?: '';
            $objPalette->hide = $arrPalette['blnHide'];

            foreach ($arrPalette['arrFields'] as $strFieldname) {

                $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strFieldname];
                $strClass = Toolkit::convertBackendFieldToFrontendField($arrField['inputType']);

                if (!class_exists($strClass)) {
                    continue;
                }

                if ($strFieldname == 'type') {
                    $arrField['default'] = $this->arrOptions['type'];
                }

                $arrAttributes = $strClass::getAttributesFromDca($arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable);
                $arrAttributes = $this->parseAttributes($arrAttributes);

                if ($arrAttributes === null) {
                    continue;
                }

                $this->addParentData($strFieldname, $arrAttributes);

                if (is_array($arrSelectors) && in_array($strFieldname, $arrSelectors) && $strFieldname != 'type') {
                    $arrAttributes['isSelector'] = true;
                }

                $objPalette->fields[] = $arrAttributes;
            }

            $arrPalettes[] = $objPalette;
        }

        return $arrPalettes;
    }

    protected function getActiveSelector()
    {

        $arrSubpalettes = [];
        if (is_array($this->arrOptions['subpalettes']) && !empty($this->arrOptions['subpalettes'])) {
            foreach ($this->arrOptions['subpalettes'] as $strSelector) {
                list($strFieldname, $strValue) = explode('::', $strSelector);
                if ($strValue == '1') {
                    $strValue = '';
                }
                $strPalette = $strFieldname . ($strValue ? '_' . $strValue : '');
                // $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$strFieldname . ($strValue ? '_' . $strValue : '')];
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$strPalette])) {
                    $arrSubpalettes[$strPalette] = $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$strPalette];
                }
            }
        }
        return $arrSubpalettes;
    }

    public function saveRecord($arrForm)
    {

        $arrSubmitted = [];
        $arrSubmitted['tstamp'] = time();
        $objMember = FrontendUser::getInstance();

        foreach ($arrForm as $objPalette) {
            foreach ($objPalette->fields as $arrField) {
                $strName = $arrField['name'];
                if (!Database::getInstance()->fieldExists($strName, $this->strTable)) {
                    continue;
                }
                $arrSubmitted[$strName] = Toolkit::getDbValue($arrField['postValue'], $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strName]);
            }
        }

        $objRoleResolver = RoleResolver::getInstance($this->strTable, $arrSubmitted);
        if ($strMemberField = $objRoleResolver->getFieldByRole('member')) {
            if ($objMember->id) {
                $arrSubmitted[$strMemberField] = $objMember->id;
            }
        }

        if ($strMemberField = $objRoleResolver->getFieldByRole('members')) {
            if ($objMember->id) {
                $arrMembers = [];
                if ($strEntityId = Input::post('id')) {
                    $objEntity = Database::getInstance()->prepare('SELECT * FROM ' . $this->strTable . ' WHERE id=?')->limit(1)->execute($strEntityId);
                    if ($strMembers = $objEntity->{$strMemberField}) {
                        $arrMembers = explode(',', $strMembers);
                    }
                }
                if (!in_array($objMember->id, $arrMembers)) {
                    $arrMembers[] = $objMember->id;
                    $arrSubmitted[$strMemberField] = implode(',', $arrMembers);
                }
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['prepareDataBeforeSave']) && is_array($GLOBALS['TL_HOOKS']['prepareDataBeforeSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] as $arrCallback) {
                System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrSubmitted, $arrForm, $this->arrOptions, $this);
            }
        }

        if (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['executeOnSave']) && is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['executeOnSave'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['executeOnSave'] as $arrCallback) {
                System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrSubmitted, false, $this->arrOptions, $this);
            }
            return null;
        }

        if (Input::post('id')) {
            $strNotification = 'onUpdate';
            Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set($arrSubmitted)->execute(Input::post('id'));
        } else {
            $strNotification = 'onCreate';
            $objInsert = Database::getInstance()->prepare('INSERT INTO ' . $this->strTable . ' %s')->set($arrSubmitted)->execute();
            Input::setPost('id', $objInsert->insertId);
        }

        $objDataContainer = new VirtualDataContainer($this->strTable);
        $objDataContainer->table = $this->strTable;
        $objDataContainer->activeRecord = Input::post('id');
        $objDataContainer->ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
        $objDataContainer->ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];

        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($objDataContainer);
                } elseif (\is_callable($arrCallback)) {
                    $arrCallback($objDataContainer);
                }
            }
        }

        $this->sendNotifications($strNotification);
    }

    protected function sendNotifications($strNotification)
    {

        if ($this->objModule === null || !$strNotification) {
            return null;
        }

        if (!in_array('notification_center', array_keys(System::getContainer()->getParameter('kernel.bundles')))) {
            return null;
        }

        $arrNotifications = StringUtil::deserialize($this->objModule->cmNotifications, true);

        if (empty($arrNotifications)) {
            return null;
        }

        foreach ($arrNotifications as $strId) {
            $objNotification = Notification::findByPk($strId);
            if ($objNotification->type == $strNotification) {
                $arrTokens = (new NotificationTokens($this->strTable, Input::post('id')))->getTokens($objNotification->flatten_delimiter);
                if (isset($GLOBALS['TL_HOOKS']['beforeSendFeNotification']) && is_array($GLOBALS['TL_HOOKS']['beforeSendFeNotification'])) {
                    foreach ($GLOBALS['TL_HOOKS']['beforeSendFeNotification'] as $arrCallback) {
                        $arrTokens =  System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrTokens, $strNotification, $this->strTable, Input::post('id'), $objNotification, $this);
                    }
                }
                $objNotification->send($arrTokens);
            }
        }
    }

    public function getTable()
    {

        return $this->strTable;
    }

    protected function addParentData($strFieldname, &$arrAttributes)
    {

        $arrAttributes['_source'] = 'dc';
        $arrAttributes['_table'] = $this->strTable;
        $arrAttributes['_identifier'] = $strFieldname;
    }

    public function getWizard()
    {

        $this->arrOptions['type'] = null;

        if (!$GLOBALS['TL_DCA'][$this->strTable]) {
            return null;
        }

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = $this->arrOptions['wizard'];
        $arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->arrOptions['wizard']]['eval']['form'] ?: [];

        foreach ($arrFields as $strFieldname => $arrField) {

            $strClass = Toolkit::convertBackendFieldToFrontendField($arrField['inputType']);

            if (!class_exists($strClass)) {
                continue;
            }

            $arrAttributes = $strClass::getAttributesFromDca($arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable);
            $arrAttributes = $this->parseAttributes($arrAttributes);
            if ($arrAttributes === null) {
                continue;
            }

            $this->addParentData($strFieldname, $arrAttributes);
            $objPalette->fields[] = $arrAttributes;
        }

        return [$objPalette];
    }
}