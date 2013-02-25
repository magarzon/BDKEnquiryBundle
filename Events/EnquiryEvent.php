<?php

namespace Bodaclick\BDKEnquiryBundle\Events;

use Symfony\Component\EventDispatcher\Event;

class EnquiryEvent extends Event
{
    protected $enquiry;
    protected $stopAction;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function setEnquiry($enquiry)
    {
        $this->enquiry = $enquiry;
        return $this;
    }

    public function getEnquiry()
    {
        return $this->enquiry;
    }

    public function setStopAction($stopAction)
    {
        $this->stopAction = $stopAction;
    }

    public function getStopAction()
    {
        return $this->stopAction;
    }
}
