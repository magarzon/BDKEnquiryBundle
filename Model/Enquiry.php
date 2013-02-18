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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Enquiry implements EnquiryInterface
{
    /**
     * @var integer
     *
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Collection
     */
    protected $answers;

    /**
     * @var string
     */
    protected $form;

    /**
     * @var stdClass
     */
    protected $about;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Enquiry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set answers
     *
     * @param Collection $answers
     * @return Enquiry
     */
    public function setAnswers(Collection $answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get answers
     *
     * @return Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add answer
     * @param Answer $answer
     */
    public function addAnswer(Answer $answer)
    {
        $this->answers->add($answer);
        $answer->setEnquiry($this);
    }

    /**
     * Set form
     *
     * @param string $form
     * @return Enquiry
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set about
     *
     * @param stdClass $about
     * @return Enquiry
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return stdClass
     */
    public function getAbout()
    {
        return $this->about;
    }
}
