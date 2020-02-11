<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Alnv\ContaoFormManagerBundle\Library\Upload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alnv\ContaoFormManagerBundle\Library\ResolveDca;
use Alnv\ContaoFormManagerBundle\Library\ResolveForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Alnv\ContaoFormManagerBundle\Library\MultiFormResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 *
 * @Route("/form-manager", defaults={"_scope" = "frontend", "_token_check" = false})
 */
class FormController extends Controller {

    /**
     *
     * @Route("/upload", name="upload")
     * @Method({"POST"})
     */
    public function upload() {
        $this->container->get( 'contao.framework' )->initialize();
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
    public function getDcFormByTable( $table ) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca( $table, $arrOptions );
        return new JsonResponse($objForm->getForm());
    }

    /**
     *
     * @Route("/getFormWizard/{table}", name="getFormWizard")
     * @Method({"GET"})
     */
    public function getFormWizard( $table ) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [
            'wizard' => \Input::get('wizard') ?: null
        ];
        $objForm = new ResolveDca( $table, $arrOptions );
        return new JsonResponse($objForm->getWizard());
    }

    /**
     *
     * @Route("/save/dc/{table}", name="validateAndSaveDc")
     * @Method({"POST"})
     */
    public function validateAndSaveDc($table) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca( $table, $arrOptions );
        return new JsonResponse($objForm->save());
    }

    /**
     *
     * @Route("/validate/dc/{table}", name="validateDc")
     * @Method({"POST"})
     */
    public function validateDc($table) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = \Input::get('attributes') ?: [];
        $arrOptions['type'] = \Input::get('type') ?: '';
        $arrOptions['initialized'] = \Input::get('initialized') ?: '';
        $arrOptions['subpalettes'] = \Input::get('subpalettes') ?: [];
        $objForm = new ResolveDca( $table, $arrOptions );
        return new JsonResponse($objForm->validate());
    }

    /**
     *
     * @Route("/getForm/{id}", name="getFormById")
     * @Method({"GET"})
     */
    public function getFormByTable( $id ) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objForm = new ResolveForm( $id, $arrOptions );
        return new JsonResponse($objForm->getForm());
    }

    /**
     *
     * @Route("/validate/form/{id}", name="validateForm")
     * @Method({"POST"})
     */
    public function validateForm($id) {
        $this->container->get( 'contao.framework' )->initialize();
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
        $objMultiFormResolver = new MultiFormResolver();
        return new JsonResponse( $objMultiFormResolver->save() );
    }
}