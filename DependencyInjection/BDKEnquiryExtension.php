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

        $driver = $config['db_driver'];

        //Load configuration for the db_driver given
        $loader->load(sprintf('%s.yml', $driver));

        //Load mapping of subclasses of Response
        $responseClasses = $config['responses']['mapping'];
        $inheritanceType = $config['responses']['inheritance'];

        $defaultResponses = $container->getParameter('bdk.response_mapping');

        //Only enable the listeners for mapping Response classes if there are more than one
        if (count($defaultResponses) > 1 || !empty($responseClasses)) {

            //Set the listener that configure the response mapping, depending on configuration
            $this->enableListener($container,'bdk.response_mapping.listener',array($responseClasses,$inheritanceType),$driver);
        }

        //Set prefix to table or collection name
        if (!empty($config['db_prefix']))
        {
            $this->enableListener($container,'bdk.db_prefix.listener',array($config['db_prefix']),$driver);
        }


        //Load the common services
        $loader->load('services.yml');
    }

    protected function enableListener($container,$id,$arguments,$driver)
    {
        $def = $container->getDefinition($id);

        foreach($arguments as $argument)
            $def->addArgument($argument);

        if ($driver=='orm') {
            $def->addTag('doctrine.event_listener', array('event'=>'loadClassMetadata'));
        } else {
            $def->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'loadClassMetadata'));
        }
    }
}
