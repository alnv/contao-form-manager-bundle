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

        $arrSubPalettes = [];
        $this->container->get( 'contao.framework' )->initialize();

        if ( \Input::get('subPalettes') !== null && is_array( \Input::get('subPalettes') ) ) {

            $arrSubPalettes = \Input::get('subPalettes');
        }

        $objDcaFormResolver = new DcaFormResolver( $table, [

            'type' => \Input::get('type'),
            'subPalettes' => $arrSubPalettes
        ]);

        header('Content-Type: application/json');
        //echo json_encode( $objDcaFormResolver->getForm(), 512 );
        exit;
    }
}