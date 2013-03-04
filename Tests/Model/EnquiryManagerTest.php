<?php

namespace Bodaclick\BDKEnquiryBundle\Tests\Model;

use Bodaclick\BDKEnquiryBundle\Model\EnquiryManager;
use Bodaclick\BDKEnquiryBundle\Entity\Enquiry;
use ReflectionClass;

class EnquiryManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $enquiryManager;
    protected $objectManager;
    protected $repository;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Bodaclick\BDKEnquiryBundle\Model\EnquiryRepositoryInterface');

        $this->repository->expects($this->any())
            ->method('getEnquiriesFor')
            ->will($this->returnValue(array(new Enquiry())));

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('BDKEnquiryBundle:Enquiry'))
            ->will($this->returnValue($this->repository));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnCallback(array($this, 'createClassMetadata')));

        $this->enquiryManager = $this->createEnquiryManager($this->objectManager);
    }

    public function testSaveEnquiry()
    {
        $form = 'testForm';
        $name = 'testName';

        $enquiry = $this->enquiryManager->saveEnquiry(new DummyAbout(), $form, $name);

        $this->assertInstanceOf('Bodaclick\BDKEnquiryBundle\Model\Enquiry', $enquiry);
        $this->assertEquals($form, $enquiry->getForm());
        $this->assertEquals($name, $enquiry->getName());
    }

    public function testSaveEnquiryWithAboutNotEntity()
    {
        $form = 'testForm';
        $name = 'testName';

        try {
            $this->enquiryManager->saveEnquiry(new Dummy(),$form,$name);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised');
    }

    public function testGetEnquiriesFor()
    {
        $enquiries = $this->enquiryManager->getEnquiriesFor(new DummyAbout());

        $this->assertCount(1, $enquiries);
    }

    protected function createEnquiryManager($objectManager)
    {
        return new EnquiryManager($objectManager);
    }

    public function createClassMetadata($classname)
    {
        $entityClasses = array('BDKEnquiryBundle:Enquiry','Bodaclick\BDKEnquiryBundle\Tests\Model\DummyAbout');

        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        if ($classname == 'BDKEnquiryBundle:Enquiry') {
            $class->expects($this->any())
                ->method('getReflectionClass')
                ->will($this->returnValue(new ReflectionClass('Bodaclick\BDKEnquiryBundle\Entity\Enquiry')));
        }

        if (in_array($classname, $entityClasses)) {
            return $class;
        } else {
            throw new \Exception('Not an entity class');
        }
    }
}

class DummyAbout implements \Bodaclick\BDKEnquiryBundle\Model\AboutInterface
{
}

class Dummy implements \Bodaclick\BDKEnquiryBundle\Model\AboutInterface
{
}
