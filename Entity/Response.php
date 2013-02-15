<?php

namespace Bodaclick\BDKEnquiryBundle\Entity;

use Bodaclick\BDKEnquiryBundle\Model\Response as BaseResponse;

/**
 * Response entity
 */
class Response extends BaseResponse
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Answer The answers that this response belongs to
     */
    protected $answer;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Answer
     *
     * @param Answer $answer
     * @return Response
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
        return $this;
    }


    /**
     * Get Answer
     *
     * @return Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }


}