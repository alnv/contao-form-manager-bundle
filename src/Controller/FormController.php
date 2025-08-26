<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Alnv\ContaoFormManagerBundle\Library\Upload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alnv\ContaoFormManagerBundle\Library\ResolveDca;
use Alnv\ContaoFormManagerBundle\Library\ResolveForm;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/form-manager', name: 'form-manager-controller', defaults: ['_scope' => 'frontend', '_token_check' => false])]
class FormController extends \Contao\CoreBundle\Controller\AbstractController {

    #[Route(path: '/upload', methods: ["POST"])]
    public function upload() {

        $this->container->get('contao.framework')->initialize();
        $objUpload = new Upload();
        $intStatus = 200;
        $arrUpload = $objUpload->upload([
            'identifier' => \Input::post('identifier'),
            'source' => \Input::post('source'),
            'table' => \Input::post('table')
        ]);
        if ( !$arrUpload['success'] ) {
            $intStatus = 400;
        }
        return new JsonResponse($arrUpload,$intStatus);
    }

    #[Route(path: '/getFiles', methods: ["POST"])]
    public function getFiles() {

        $this->container->get( 'contao.framework' )->initialize();
        $objUpload = new Upload();
        return new JsonResponse($objUpload->getFiles([
            'files' => \Input::post('files'),
            'table' => \Input::post('table'),
            'fieldname' => \Input::post('fieldname')
        ]));
    }

    #[Route(path: '/deleteFile', methods: ["POST"])]
    public function deleteFile() {

        $this->container->get( 'contao.framework' )->initialize();
        $objUpload = new Upload();
        return new JsonResponse($objUpload->delete([
            'file' => \Input::post('file'),
            'table' => \Input::post('table'),
            'fieldname' => \Input::post('fieldname')
        ]));
    }

    #[Route(path: '/getDcForm/{table}', methods: ["POST", "GET"])]
    public function getDcFormByTable($table) {

        $this->container->get('contao.framework')->initialize();

        \header("Access-Control-Allow-Origin: *");

        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: ($GLOBALS['TL_LANGUAGE'] ?? 'de');

        global $objPage;

        if ($objPage) {
            $objPage->language = ($GLOBALS['TL_LANGUAGE'] ?? 'de');
        }

        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];

        $objForm = new ResolveDca($table, $arrOptions);

        return new JsonResponse($objForm->getForm());
    }

    #[Route(path: '/getFormWizard/{table}', methods: ["POST", "GET"])]
    public function getFormWizard($table) {

        $this->container->get( 'contao.framework' )->initialize();

        header("Access-Control-Allow-Origin: *");

        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];

        global $objPage;
        if ($objPage) {
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
        }

        $arrOptions = [
            'wizard' => \Input::get('wizard') ?: null,
            'params' => \Input::get('params') ?: []
        ];
        $objForm = new ResolveDca($table, $arrOptions);
        return new JsonResponse($objForm->getWizard());
    }

    #[Route(path: '/save/dc/{table}', methods: ["POST", "GET"])]
    public function validateAndSaveDc($table) {

        $this->container->get('contao.framework')->initialize();

        header("Access-Control-Allow-Origin: *");

        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];

        global $objPage;
        if ($objPage) {
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
        }

        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['id'] = \Input::get('id') ?: null;
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca($table, $arrOptions);
        return new JsonResponse($objForm->save());
    }

    #[Route(path: '/validate/dc/{table}', methods: ["POST", "GET"])]
    public function validateDc($table) {

        $this->container->get( 'contao.framework' )->initialize();

        header("Access-Control-Allow-Origin: *");

        global $objPage;
        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];
        $objPage->language = $GLOBALS['TL_LANGUAGE'];

        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca($table, $arrOptions);
        return new JsonResponse($objForm->validate());
    }

    #[Route(path: '/getForm/{id}', methods: ["POST", "GET"])]
    public function getFormByTable($id) {

        $this->container->get('contao.framework')->initialize();

        header("Access-Control-Allow-Origin: *");

        global $objPage;
        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];
        if ($objPage) {
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
        }
        $arrOptions = [];
        $objForm = new ResolveForm($id, $arrOptions);
        return new JsonResponse($objForm->getForm());
    }

    #[Route(path: '/validate/form/{id}', methods: ["POST", "GET"])]
    public function validateForm($id) {

        $this->container->get('contao.framework')->initialize();

        header("Access-Control-Allow-Origin: *");

        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];

        global $objPage;
        if ($objPage) {
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
        }

        $arrOptions = [];
        $objForm = new ResolveForm( $id, $arrOptions );
        return new JsonResponse($objForm->validate());
    }

    #[Route(path: '/save/form/{id}', methods: ["POST", "GET"])]
    public function validateAndSaveForm($id) {

        $this->container->get( 'contao.framework' )->initialize();

        header("Access-Control-Allow-Origin: *");

        $arrOptions = [];
        $objForm = new ResolveForm( $id, $arrOptions );
        return new JsonResponse($objForm->save());
    }

    #[Route(path: '/save/multiform', methods: ["POST", "GET"])]
    public function saveMultiForm() {

        $this->container->get( 'contao.framework' )->initialize();
        $objMultiFormResolver = new \Alnv\ContaoFormManagerBundle\Library\MultiFormResolver();
        return new JsonResponse($objMultiFormResolver->save());
    }

    #[Route(path: '/list-view', methods: ["POST", "GET"])]
    public function getListView() {

        $this->container->get('contao.framework')->initialize();

        $objListView = new \Alnv\ContaoFormManagerBundle\Modules\ListView(\Input::post('module'));
        $arrReturn = $objListView->parse();
        $arrList = [];

        foreach ($arrReturn['list'] as $arrEntity) {
            $arrRow = [];
            foreach ($arrEntity as $strField => $varValue) {
                if (is_array($varValue) && !in_array($strField, ['operations'])) {
                    $varValue = \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::parse($varValue);
                }
                $arrRow[$strField] = $varValue;
            }
            $arrList[] = $arrRow;
        }

        $arrReturn['list'] = $arrList;

        return new JsonResponse($arrReturn);
    }

    #[Route(path: '/deleteItem/{id}', methods: ["POST"])]
    public function deleteItem($id) {

        $this->container->get( 'contao.framework' )->initialize();
        $objListView = new \Alnv\ContaoFormManagerBundle\Modules\ListView(\Input::post('module'));
        return new JsonResponse($objListView->delete($id));
    }

    #[Route(path: '/addOption', methods: ["POST"])]
    public function addOption() {

        $this->container->get('contao.framework')->initialize();

        $objCatalog = \Alnv\ContaoCatalogManagerBundle\Models\CatalogModel::findByTableOrModule(\Input::post('table'));
        if ($objCatalog === null) {
            return new JsonResponse([], 500);
        }

        $objField = \Alnv\ContaoCatalogManagerBundle\Models\CatalogFieldModel::findByFieldnameAndPid(\Input::post('name'), $objCatalog->id);
        if ($objField === null) {
            return new JsonResponse([], 500);
        }

        $strLabel = \StringUtil::decodeEntities(\Input::post('option'));
        $strValue = '';

        switch ($objField->optionsSource) {
            case 'options':
                $objOption = new \Alnv\ContaoCatalogManagerBundle\Models\CatalogOptionModel();
                $objOption->value = \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::generateAlias(\Input::post('option'), 'value', 'tl_catalog_option');
                $objOption->label = $strLabel;
                $objOption->pid = $objField->id;
                $objOption->tstamp = time();
                $objOption->save();

                $strValue = $objOption->value;
                break;
            case 'dbOptions':
                $arrSet = [
                    'tstamp' => time(),
                    'alias' => \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::generateAlias(\Input::post('option'), 'alias', $objField->dbTable),
                ];
                $arrSet[$objField->dbLabel] = $strLabel;
                if ($objField->dbKey != 'id') {
                    $arrSet[$objField->dbKey] = $strValue;
                }
                $objInsert = \Database::getInstance()->prepare('INSERT INTO ' . $objField->dbTable . ' %s')->set($arrSet)->execute();
                if ($objField->dbKey == 'id') {
                    $strValue = $objInsert->insertId;
                }
                break;
        }

        return new JsonResponse([
            'value' => $strValue,
            'label' => $strLabel
        ]);
    }

    #[Route(path: '/deleteOption', methods: ["POST"])]
    public function deleteOption() {

        $this->container->get( 'contao.framework' )->initialize();

        $objCatalog = \Alnv\ContaoCatalogManagerBundle\Models\CatalogModel::findByTableOrModule(\Input::post('table'));
        if ($objCatalog === null) {
            return new JsonResponse([], 500);
        }

        $objField = \Alnv\ContaoCatalogManagerBundle\Models\CatalogFieldModel::findByFieldnameAndPid(\Input::post('name'), $objCatalog->id);
        if ($objField === null) {
            return new JsonResponse([], 500);
        }

        switch ($objField->optionsSource) {
            case 'options':
                $objOption = \Alnv\ContaoCatalogManagerBundle\Models\CatalogOptionModel::findByValueAndPid(\Input::post('option'), $objField->id);
                if ($objOption) {
                    $objOption->delete();
                }
                break;
            case 'dbOptions':
                \Database::getInstance()->prepare('DELETE FROM ' . $objField->dbTable . ' WHERE `'.$objField->dbKey.'`=?')->execute(\Input::post('option'));
                break;
        }

        return new JsonResponse([
            'index' => \Input::post('index'),
            'value' => \Input::post('option')
        ]);
    }
}