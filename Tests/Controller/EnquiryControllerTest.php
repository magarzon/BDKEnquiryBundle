<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bodaclick\BDKEnquiryBundle\Model\EnquiryManager;

class EnquiryControllerTest extends WebTestCase
{
    protected $enquiryManager;
    protected $objectManager;
    protected $enquiry;
    protected $json;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnCallback(array($this, 'createClassMetadata')));

        $this->enquiry = $this->getMockForAbstractClass('Bodaclick\BDKEnquiryBundle\Model\Enquiry');

        $answer = $this->createAnswer();

        $this->enquiry->setName('test');
        $this->enquiry->addAnswer($answer);

        $this->json = '{"enquiry":{"id":null,"name":"test","answers":[{"answer":{"responses":[{"key":"test","value":"test"}]}}]}}';

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $defaultClass = get_class($answer->getResponses()->first());
        $this->enquiryManager = new EnquiryManager($this->objectManager, $dispatcher, $defaultClass , array() );

    }

    public function testGetEnquiry()
    {
        $this->setUpObjectRepository();

        $client = static::createClient();

        $container = $client->getContainer();

        //Change the service to mock access to the database
        $container->set('bdk.enquiry.manager', $this->enquiryManager);

        $client->request('GET', '/enquiry/id');

        $content = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($this->json, $content);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testGetEnquiryByName()
    {
        $this->setUpObjectRepository();

        $client = static::createClient();

        $container = $client->getContainer();

        //Change the service to mock access to the database
        $container->set('bdk.enquiry.manager', $this->enquiryManager);

        $client->request('GET', '/enquiry/by_name/name');

        $content = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($this->json, $content);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testSaveAnswer()
    {
        $this->setUpObjectRepository();

        $client = static::createClient();

        $container = $client->getContainer();

        //Change the service to mock access to the database
        $container->set('bdk.enquiry.manager', $this->enquiryManager);

        $responses = '{"answer":{"responses":[{"key":"test","value":"test"}]}}';

        $client->request(
            'POST',
            '/answer/save/enquiryId',
            array(),
            array(),
            array('CONTENT_TYPE'=> 'application/json'),
            $responses
        );

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $content = $client->getResponse()->getContent();

        $this->assertEmpty($content);

    }

    protected function setUpObjectRepository()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($this->enquiry));

        $repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->enquiry));

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('BDKEnquiryBundle:Enquiry'))
            ->will($this->returnValue($repository));
    }

    protected function createAnswer()
    {
        $answer = $this->getMockForAbstractClass('Bodaclick\BDKEnquiryBundle\Model\Answer');

        $answer->addResponse($this->createResponse());

        return $answer;
    }

    protected function createResponse()
    {
        $response = $this->getMockForAbstractClass('Bodaclick\BDKEnquiryBundle\Model\Response');

        $response->setKey('test');
        $response->setValue('test');

        return $response;
    }

    public function createClassMetadata($classname)
    {
        $entityClasses = array('BDKEnquiryBundle:Enquiry', 'BDKEnquiryBundle:Answer');

        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        if ($classname == 'BDKEnquiryBundle:Enquiry') {
            $class->expects($this->any())
                ->method('getReflectionClass')
                ->will($this->returnValue(new \ReflectionClass($this->enquiry)));
        }

        if ($classname == 'BDKEnquiryBundle:Answer') {
            $answer = $this->createAnswer();
            $class->expects($this->any())
                ->method('getReflectionClass')
                ->will($this->returnValue(new \ReflectionClass($answer)));
            $class->expects($this->any())
                ->method('getName')
                ->will($this->returnValue(get_class($answer)));
        }

        if (in_array($classname, $entityClasses)) {
            return $class;
        } else {
            throw new \Exception('Not an entity class');
        }
    }
}
