<?php

namespace Bodaclick\BDKEnquiryBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Bodaclick\BDKEnquiryBundle\Entity\Enquiry;
use Bodaclick\BDKEnquiryBundle\Model\EnquiryRepositoryInterface;

/**
 * Enquiry repository
 */
class EnquiryRepository extends EntityRepository implements EnquiryRepositoryInterface
{

    /**
     * Gets all the enquiries associated with an object
     *
     * @param mixed $object
     * @return mixed
     */
    public function getEnquiriesFor($object)
    {
        //Generate the definition and find using the string generated
        $metadata = $this->getClassMetadata($object);
        $definition = Enquiry::generateAboutDefinition($metadata->getName(),$metadata->getIdentifierValues($object));

        $qb=$this->createQueryBuilder('e')
            ->where('aboutDefinition = :definition')
            ->setParameter('definition',$definition);


        return $qb->getQuery()->execute();
    }
}