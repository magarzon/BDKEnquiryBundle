<?php

namespace Bodaclick\BDKEnquiryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class BDKEnquiryExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //The bdk.user_class parameter gives the user class used in the system. Is required
        $container->setParameter('bdk.user_class', $config['user_class']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        //Load configuration for the db_driver given
        $loader->load(sprintf('%s.yml', $config['db_driver']));

        //Load the common services
        $loader->load('services.yml');
    }
}
