<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Alnv\ContaoFormManagerBundle\Library\FormResolver;
use Alnv\ContaoFormManagerBundle\Library\DcaFormResolver;
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
     * @Route("/getDcForm/{table}", name="getDcFormByTable")
     * @Method({"GET"})
     */
    public function getDcFormByTable( $table ) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [
            'type' => \Input::get('type') ?: '',
            'initialized' => \Input::get('initialized') === 'true'
        ];
        if ( \Input::get('subpalettes') !== null && is_array( \Input::get('subpalettes') ) ) {
            $arrOptions['subpalettes'] = \Input::get('subpalettes');
        }
        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objDcaFormResolver->getForm(), 512 );
        exit;
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
        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode(  $objDcaFormResolver->getWizard(), 512 );
        exit;
    }


    /**
     *
     * @Route("/getForm/{id}", name="getFormById")
     * @Method({"GET"})
     */
    public function getFormByTable( $id ) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objFormResolver = new FormResolver( $id, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objFormResolver->getForm(), 512 );
        exit;
    }

    /**
     *
     * @Route("/validate/form/{id}", name="validateForm")
     * @Method({"POST"})
     */
    public function validateForm($id) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objFormResolver = new FormResolver( $id, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objFormResolver->validate(), 512 );
        exit;
    }

    /**
     *
     * @Route("/save/form/{id}", name="validateAndSaveForm")
     * @Method({"POST"})
     */
    public function validateAndSaveForm($id) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objFormResolver = new FormResolver( $id, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objFormResolver->save(), 512 );
        exit;
    }


    /**
     *
     * @Route("/save/dc/{table}", name="validateAndSaveDc")
     * @Method({"POST"})
     */
    public function validateAndSaveDc($table) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objDcaFormResolver->save(), 512 );
        exit;
    }

    /**
     *
     * @Route("/validate/dc/{table}", name="validateDc")
     * @Method({"POST"})
     */
    public function validateDc($table) {
        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];
        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objDcaFormResolver->validate(), 512 );
        exit;
    }


    /**
     *
     * @Route("/save/multiform", name="saveMultiForm")
     * @Method({"POST"})
     */
    public function saveMultiForm() {
        $this->container->get( 'contao.framework' )->initialize();
        $objMultiFormResolver = new MultiFormResolver();
        header('Content-Type: application/json');
        echo json_encode( $objMultiFormResolver->save(), 512 );
        exit;
    }
}