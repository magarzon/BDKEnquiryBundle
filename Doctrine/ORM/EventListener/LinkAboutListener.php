<?php

namespace Bodaclick\BDKEnquiryBundle\Doctrine\ORM\EventListener;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Doctrine ORM Listener to manage the relationship between the enquiry and the about object in ORM
 * The about object is of an unknown class, so we "serialize" it in a special field call aboutDefinition
 * when the enquiry object is persisted, and it's "deserialized" when an enquiry object is loaded from the database.
 */
class LinkAboutListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof \Bodaclick\BDKEnquiryBundle\Entity\Enquiry)
        {
            $enquiry = $event->getEntity();
            $about = $enquiry->getAbout();

            //The class metadata is used to get the identifiers, that could be compound
            $metadata = $event->getEntityManager()->getClassMetadata(get_class($about));
            $enquiry->setAboutDefinition($metadata->getName(),$metadata->getIdentifierValues($about));

        }
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof \Bodaclick\BDKEnquiryBundle\Entity\Enquiry)
        {
            $enquiry = $event->getEntity();
            $definition = $enquiry->getAboutDefinition();

            //The geAboutDefinition method returns an array with the names of the variables and their value
            //The php extract method is used to "make" that variables, $className and $ids
            extract($definition);

            //Only a reference (proxy) to the about object is set, so no query to the database is needed
            //until one of the about object's field is accessed.
            $about=$event->getEntityManager()->getReference($className,$ids);

            $enquiry->setAbout($about);
        }
    }
}
