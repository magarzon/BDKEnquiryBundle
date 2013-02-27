<?php

namespace Bodaclick\BDKEnquiryBundle\DependencyInjection;

final class DriversSupported
{
    const ORM = "orm";
    const MONGODB = "mongodb";

    public static function getList()
    {
        return array(self::ORM, self::MONGODB);
    }
}
