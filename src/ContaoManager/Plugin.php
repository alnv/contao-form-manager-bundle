<?php

namespace Alnv\ContaoFormManagerBundle\ContaoManager;

use Symfony\Component\HttpKernel\KernelInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;


class Plugin implements BundlePluginInterface, RoutingPluginInterface {


    public function getBundles( ParserInterface $parser ) {

        return [
            BundleConfig::create('Alnv\ContaoFormManagerBundle\AlnvContaoFormManagerBundle')
                ->setLoadAfter([
                    'Contao\CoreBundle\ContaoCoreBundle',
                    'Alnv\ContaoAssetsManagerBundle\AlnvContaoAssetsManagerBundle',
                    'Alnv\ContaoCatalogManagerBundle\AlnvContaoCatalogManagerBundle'
                ])
                ->setReplace(['contao-form-manager-bundle']),
        ];
    }


    public function getRouteCollection( LoaderResolverInterface $resolver, KernelInterface $kernel ) {

        return $resolver
            ->resolve( __DIR__ . '/../Resources/config/routing.yml' )
            ->load( __DIR__ . '/../Resources/config/routing.yml' );
    }
}