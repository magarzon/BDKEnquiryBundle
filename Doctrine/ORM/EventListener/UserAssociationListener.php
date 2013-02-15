<?php

namespace Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener;


use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Doctrine ORM Listener updating the Answer entity to set a one to one
 * relationship with the user class defined in configuration
 */
class UserAssociationListener
{
    protected $userClassname;

    /**
     * @param string $user_class
     */
    public function __construct($userClassname)
    {
        $this->userClassname = $userClassname;
    }

    /**
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->getName()!=='Bodaclick\BDKEnquiryBundle\Entity\Answer')
            return;

        //Setting the one to one relationship
        $builder = new ClassMetadataBuilder($args->getClassMetadata());

        $builder->addOwningOneToOne('user',$this->userClassname);
    }
}
