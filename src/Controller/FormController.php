<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Alnv\ContaoFormManagerBundle\Library\Upload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alnv\ContaoFormManagerBundle\Library\ResolveDca;
use Alnv\ContaoFormManagerBundle\Library\ResolveForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 *
 * @Route("/form-manager", defaults={"_scope"="frontend", "_token_check"=false})
 */
class FormController extends \Contao\CoreBundle\Controller\AbstractController {

    /**
     *
     * @Route("/upload", name="upload")
     * @Method({"POST"})
     */
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

    /**
     *
     * @Route("/getFiles", name="getFiles")
     * @Method({"POST"})
     */
    public function getFiles() {

        $this->container->get( 'contao.framework' )->initialize();
        $objUpload = new Upload();
        return new JsonResponse($objUpload->getFiles([
            'files' => \Input::post('files'),
            'table' => \Input::post('table'),
            'fieldname' => \Input::post('fieldname')
        ]));
    }

    /**
     *
     * @Route("/deleteFile", name="deleteFile")
     * @Method({"POST"})
     */
    public function deleteFile() {

        $this->container->get( 'contao.framework' )->initialize();
        $objUpload = new Upload();
        return new JsonResponse($objUpload->delete([
            'file' => \Input::post('file'),
            'table' => \Input::post('table'),
            'fieldname' => \Input::post('fieldname')
        ]));
    }

    /**
     *
     * @Route("/getDcForm/{table}", name="getDcFormByTable")
     * @Method({"GET"})
     */
    public function getDcFormByTable($table) {

        $this->container->get('contao.framework')->initialize();

        header("Access-Control-Allow-Origin: *");

        $GLOBALS['TL_LANGUAGE'] = \Input::get('language') ?: $GLOBALS['TL_LANGUAGE'];

        global $objPage;
        if ($objPage) {
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
        }

        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca($table, $arrOptions);
        return new JsonResponse($objForm->getForm());
    }

    /**
     *
     * @Route("/getFormWizard/{table}", name="getFormWizard")
     * @Method({"GET"})
     */
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

    /**
     *
     * @Route("/save/dc/{table}", name="validateAndSaveDc")
     * @Method({"POST"})
     */
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

    /**
     *
     * @Route("/validate/dc/{table}", name="validateDc")
     * @Method({"POST"})
     */
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

    /**
     *
     * @Route("/getForm/{id}", name="getFormById")
     * @Method({"GET"})
     */
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

    /**
     *
     * @Route("/validate/form/{id}", name="validateForm")
     * @Method({"POST"})
     */
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

    /**
     *
     * @Route("/save/form/{id}", name="validateAndSaveForm")
     * @Method({"POST"})
     */
    public function validateAndSaveForm($id) {

        $this->container->get( 'contao.framework' )->initialize();

        header("Access-Control-Allow-Origin: *");

        $arrOptions = [];
        $objForm = new ResolveForm( $id, $arrOptions );
        return new JsonResponse($objForm->save());
    }

    /**
     *
     * @Route("/save/multiform", name="saveMultiForm")
     * @Method({"POST"})
     */
    public function saveMultiForm() {

        $this->container->get( 'contao.framework' )->initialize();
        $objMultiFormResolver = new \Alnv\ContaoFormManagerBundle\Library\MultiFormResolver();
        return new JsonResponse($objMultiFormResolver->save());
    }

    /**
     *
     * @Route("/list-view", name="getListView")
     * @Method({"POST"})
     */
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

    /**
     *
     * @Route("/deleteItem/{id}", name="deleteItem")
     * @Method({"POST"})
     */
    public function deleteItem($id) {

        $this->container->get( 'contao.framework' )->initialize();
        $objListView = new \Alnv\ContaoFormManagerBundle\Modules\ListView(\Input::post('module'));
        return new JsonResponse($objListView->delete($id));
    }

    /**
     *
     * @Route("/addOption", name="addOption")
     * @Method({"POST"})
     */
    public function addOption() {

        $this->container->get('contao.framework')->initialize();

        $objField = \Alnv\ContaoCatalogManagerBundle\Models\CatalogFieldModel::findByFieldname(\Input::post('name'));
        if ($objField === null) {
            return new JsonResponse([], 500);
        }
        $objOption = new \Alnv\ContaoCatalogManagerBundle\Models\CatalogOptionModel();
        $objOption->value = \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::generateAlias(\Input::post('option'), 'value', 'tl_catalog_option', $objField->id);
        $objOption->label = \StringUtil::decodeEntities(\Input::post('option'));
        $objOption->pid = $objField->id;
        $objOption->tstamp = time();
        $objOption->save();

        return new JsonResponse([
            'value' => $objOption->value,
            'label' => $objOption->label
        ]);
    }

    /**
     *
     * @Route("/deleteOption", name="deleteOption")
     * @Method({"POST"})
     */
    public function deleteOption() {

        $this->container->get( 'contao.framework' )->initialize();
        $objField = \Alnv\ContaoCatalogManagerBundle\Models\CatalogFieldModel::findByFieldname(\Input::post('name'));
        if ($objField === null) {
            return new JsonResponse([], 500);
        }
        $objOption = \Alnv\ContaoCatalogManagerBundle\Models\CatalogOptionModel::findByValueAndPid(\Input::post('option'), $objField->id);
        if ($objOption === null) {
            return new JsonResponse([], 500);
        }
        $objOption->delete();
        return new JsonResponse([
            'index' => \Input::post('index'),
            'value' => \Input::post('option')
        ]);
    }
}