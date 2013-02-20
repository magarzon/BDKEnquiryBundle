<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
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

        //Load mapping of subclasses of Response
        $responseClasses = $config['responses']['mapping'];
        $inheritanceType = $config['responses']['inheritance'];

        //Set the listener that configure the response mapping, depending on configuration
        $def = $container->getDefinition('bdk.response_mapping.listener');

        $def->addArgument($responseClasses);

        if ($config['db_driver']=='orm') {
                $def->addArgument($inheritanceType);
                $def->addTag('doctrine.event_listener', array('event'=>'loadClassMetadata'));
        } else {
            $def->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'loadClassMetadata'));
        }


        //Load the common services
        $loader->load('services.yml');
    }
}
