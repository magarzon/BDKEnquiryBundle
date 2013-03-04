<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

abstract class Answer
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    /**
     * @var Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface
     */
    protected $enquiry;

    /**
     * @var Collection
     */
    protected $responses;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

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
    * Set user
    *
    * @param Symfony\Component\Security\Core\User\UserInterface $user
    * @return Answer
    */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set enquiry
     *
     * @param  Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface $enquiry
     * @return Answer
     */
    public function setEnquiry(EnquiryInterface $enquiry)
    {
        $this->enquiry = $enquiry;

        return $this;
    }

    /**
     * Get enquiry
     *
     * @return Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface $enquiry
     */
    public function getEnquiry()
    {
        return $this->enquiry;
    }

    /**
     * Set responses
     *
     * @param  Collection $responses
     * @return Answer
     */
    public function setResponses(Collection $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * Get responses
     *
     * @return Collection $responses
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Add a response
     * @param Response $response
     */
    public function addResponse(Response $response)
    {
        $this->responses->add($response);
    }
}
