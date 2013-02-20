<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\Doctrine\ODM\MongoDB\EventListener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

/**
 * Doctrine ODM Listener to map the Responses as Embedded documents in Answer,
 * with targetDocument or with discriminatorMap, depending on configuration
 */
class ResponseMappingListener
{
    protected $responseClasses;
    protected $defaultMapping;

    /**
     * Constructor
     *
     * @param array $responseClasses Array with data mapping from configuration
     */
    public function __construct($defaultMapping, $responseClasses)
    {
        $this->responseClasses = $responseClasses;
        $this->defaultMapping = $defaultMapping;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->getName()!=='Bodaclick\BDKEnquiryBundle\Document\Answer') {
            return;
        }

        //If only one default Response class and no subclasses given in configuration,
        //no changes to the mapping are made
        if (count($this->defaultMapping)<=1 && empty($this->responseClasses)) {
            return;
        }

        //If there are more than one default Response class or in configuration, override the default mapping
        $map = array();

        foreach($this->defaultMapping as $type=>$class) {
            $map[$type]=$class;
        }

        foreach($this->responseClasses as $type=>$class) {
            $map[$type]=$class['class'];
        }

        $classMetadata->mapManyEmbedded(array('name'=>'responses','discriminatorMap'=>$map,'strategy'=>'pushAll','discriminatorField'=>'type'));


    }
}
