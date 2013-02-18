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

abstract class Response
{
    /**
     * @var string Key to identify the response
     */
    protected $key;

    /**
     * @var string Type of the response. Can be anything that make senses to the application
     */
    protected $type='string';

    /**
     * @var string Value of the response.
     */
    protected $value;

    /**
     * Set key
     *
     * @param string $key
     * @return Response
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }


    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * Set type
     *
     * @param string $type
     * @return Response
     */
    public function setUser($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Response
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }
}
