<?php

namespace Alnv\ContaoFormManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/getForm/{table}", name="getFormByTable")
     * @Method({"GET"})
     */
    public function getProduct( $table ) {

        $this->container->get( 'contao.framework' )->initialize();
        $arrOptions = [];

        if ( \Input::get('type') != '' || \Input::get('type') != null ) {

            $arrOptions['type'] = \Input::get('type');
        }

        if ( \Input::get('subPalettes') !== null && is_array( \Input::get('subPalettes') ) ) {

            $arrOptions['subPalettes'] = \Input::get('subPalettes');
        }

        $objDcaFormResolver = new DcaFormResolver( $table, $arrOptions );

        header('Content-Type: application/json');
        echo json_encode( $objDcaFormResolver->getForm(), 512 );
        exit;
    }
}