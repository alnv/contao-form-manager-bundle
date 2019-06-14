<?php

namespace Alnv\ContaoFormManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class AlnvContaoFormManagerExtension extends Extension {


    public function load( array $configs, ContainerBuilder $container ) {

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load( 'services.yml' );
    }
}