<?php

namespace Bodaclick\BDKEnquiryBundle\DependencyInjection;

final class InheritanceTypes
{
    const SINGLE = "single";
    const JOINED = "joined";

    public static function getList()
    {
        return array(self::SINGLE, self::JOINED);
    }
}
