<?php

namespace Bodaclick\BDKEnquiryBundle\Model;

interface EnquiryInterface
{
    public function getAnswers();

    public function getForm();

    public function getAbout();
}
