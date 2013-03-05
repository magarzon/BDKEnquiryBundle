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

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Normalizer to serialize/deserialize Response objects
 */
class ResponseNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param  object       $object object to normalize
     * @param  string       $format format the normalization result will be encoded as
     * @return array|scalar
     */
    public function normalize($object, $format = null)
    {
        $normalized = array('key'=>$object->getKey(), 'value'=>$object->getValue());

        $class = get_class($object);
        $default = $this->container->getParameter('bdk.enquiry.default_response_class');

        if ($class!=$default) {
            $responseClasses = $this->container->getParameter('bdk.enquiry.response_classes');
            $normalized['type'] = array_search($class, $responseClasses);
        }

        return $normalized;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param  mixed   $data   Data to normalize.
     * @param  string  $format The format being (de-)serialized from or into.
     * @return Boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Response;
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param  mixed  $data   data to restore
     * @param  string $class  the expected class to instantiate
     * @param  string $format format the given data was extracted from
     * @return object
     */
    public function denormalize($data, $class, $format = null)
    {
        if (!isset($data['type']) || $data['type']=='default') {
            $class = $this->container->getParameter('bdk.enquiry.default_response_class');
        } else {
            $type = $data['type'];
            $responseClasses = $this->container->getParameter('bdk.enquiry.response_classes');
            if (!isset($responseClasses[$type])) {
                throw new \Symfony\Component\Serializer\Exception\UnexpectedValueException(
                    sprintf('Response type not defined denormalizing response data: %s', $type)
                );
            }
            $class = $responseClasses[$type];
        }

        $object = new $class;
        if (isset($data['key'])) {
            $object->setKey($data['key']);
        }

        if (isset($data['value'])) {
            $object->setValue($data['value']);
        }

        return $object;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param  mixed   $data   Data to denormalize from.
     * @param  string  $type   The class to which the data should be denormalized.
     * @param  string  $format The format being deserialized from.
     * @return Boolean
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        //We use a special type to check support, later in denormalize method is changed to the right class
        return $type=='Response';
    }
}
