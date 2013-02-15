<?php


namespace Bodaclick\BDKEnquiryBundle\Model;

interface EnquiryRepositoryInterface
{
    public function getEnquiriesFor($object);

}