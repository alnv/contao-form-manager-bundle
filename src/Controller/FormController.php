<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Alnv\ContaoFormManagerBundle\Library\FormResolver;
use Alnv\ContaoFormManagerBundle\Library\DcaFormResolver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        if ( \Input::get('subPalettes') !== null && is_array( \Input::get('subPalettes') ) ) {
            $arrOptions['subPalettes'] = \Input::get('subPalettes');
        }
        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objDcaFormResolver->getForm(), 512 );
        exit;
    }


    /**
     *
     * @Route("/getForm/{id}", name="getFormById")
     * @Method({"GET"})
     */
    public function getFormByTable( $id ) {

        $arrOptions = [];
        $objFormResolver = new FormResolver( $id, $arrOptions );
        header('Content-Type: application/json');
        echo json_encode( $objFormResolver->getForm(), 512 );
        exit;
    }
}