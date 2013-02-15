<?php

namespace Bodaclick\BDKEnquiryBundle\Entity;

use Bodaclick\BDKEnquiryBundle\Model\Answer as BaseAnswer;

/**
 * Answer entity
 */
class Answer extends BaseAnswer
{
    /**
     * Add a response
     * @param Response $response
     */
    public function addResponse(\Bodaclick\BDKEnquiryBundle\Model\Response $response)
    {
        $this->responses->add($response);
        $response->setAnswer($this);
    }
}