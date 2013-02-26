<?php

namespace Bodaclick\BDKEnquiryBundle\Events;

final class Events
{
    const PRE_PERSIST = 'bdk.enquiry.event.pre_persist';
    const POST_PERSIST = 'bdk.enquiry.event.post_persist';
    const PRE_PERSIST_ANSWER = 'bdk.enquiry.event.pre_persist_answer';
    const POST_PERSIST_ANSWER = 'bdk.enquiry.event.post_persist_answer';
    const PRE_REMOVE = 'bdk.enquiry.event.pre_remove';
    const POST_REMOVE = 'bdk.enquiry.event.post_remove';

}
