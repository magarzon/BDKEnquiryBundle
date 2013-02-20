<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Doctrine ORM Listener to map the inheritance of Response subclasses if defined in configuration
 */
class ResponseMappingListener
{
    protected $responseClasses;
    protected $inheritanceType;
    protected $defaultMapping;

    /**
     * Constructor
     *
     * @param array $responseClasses Array with data mapping from configuration
     * @param string $inheritanceType Type of inheritance
     */
    public function __construct($defaultMapping, $responseClasses, $inheritanceType)
    {
        $this->responseClasses = $responseClasses;
        $this->inheritanceType = $inheritanceType;
        $this->defaultMapping = $defaultMapping;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->getName()!=='Bodaclick\BDKEnquiryBundle\Entity\Response') {
            return;
        }

        //Only if there are more than one Response class either in default classes
        //or in configuration, they are mapped as single or class table inheritance
        if (count($this->defaultMapping)<=1 && empty($this->responseClasses)) {
            return;
        }

        //Setting the hierarchy of Response classes and subclasses
        //depending on mapping given in configuration
        $builder = new ClassMetadataBuilder($args->getClassMetadata());

        switch($this->inheritanceType) {
            case 'single':
                $builder->setSingleTableInheritance();
                break;
            case 'joined':
                $builder->setJoinedTableInheritance();
                break;
        }

        $builder->setDiscriminatorColumn('type');

        foreach($this->defaultMapping as $type=>$class) {
            $builder->addDiscriminatorMapClass($type, $class);
        }

        foreach($this->responseClasses as $type=>$class) {
            $builder->addDiscriminatorMapClass($type, $class['class']);
        }

    }
}
