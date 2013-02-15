<?php

namespace Bodaclick\BDKEnquiryBundle\Model;


use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Enquiry manager.
 * Access point to all the bundle's features and factory service to create database objects
 */
class EnquiryManager
{
    /**
     * Security Context. Used to get the connected user if none is provided
     *
     * @var
     */
    protected $securityContext;

    /**
     * ObjectManager to access the database, by ORM or ODM
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param $securityContext
     */
    public function __construct(ObjectManager $objectManager,$securityContext)
    {
        $this->securityContext = $securityContext;
        $this->objectManager = $objectManager;
    }

    /**
     * Get the last enquiry associated to an object
     *
     * @param mixed $object
     */
    public function getEnquiryFor($object)
    {
        $enquiries = $this->getEnquiriesFor($object);

        return array_pop($enquiries);
    }

    /**
     * Get all the enquiries previously associated to an object
     *
     * @param mixed $object
     */
    public function getEnquiriesFor($object)
    {
        //A custom repository is used, so each type of database driver (orm, mongodb,...)
        //can build the most eficient query
        $enquiries = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->getEnquiriesFor($object);

        return $enquiries;
    }

    /**
     * Get an enquiry previously saved with a name
     *
     * @param string $name Name associated to the enquiry
     */
    public function getEnquiryByName($name)
    {
        $enquiry = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->findOneBy(array('name'=>$name));

        return $enquiry;
    }

    /**
     * Create an enquiry (database object, it'll be an entity or a document, depending on the configuration), and return it
     *
     * @param mixed $about The object that the enquiry is associated with. Can be of any type, but always an entity or a document
     * @param string $form The name of a form associated to the enquiry. Only used for reference. Optional.
     * @param string $name A name associated to the enquiry. Optional. If specified, must be unique
     *
     * @return Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface The enquiry database object created
     */
    public function createEnquiry($about,$form=null, $name = null)
    {

        if (!is_object($about))
            throw new \InvalidArgumentException('The about parameter must be an object and cannot be null');

        //Check if the "about" object is persisted (has an identifier value),
        //if not, persist it to get the right id when associated with enquiry
        try
        {
            $aboutMetadata = $this->objectManager->getClassMetadata(get_class($about));
        }
        catch(\Exception $e)
        {
            throw new \InvalidArgumentException('The about parameter must be a valid entity or a valid document');
        }

        $ids = $aboutMetadata->getIdentifierValues($about);

        if (count($ids)==0)
        {
            $this->objectManager->persist($about);
            $this->objectManager->flush();
        }

        //Using the metadata to create a new database object, no matter which db driver is used
        $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Enquiry');
        $enquiry = $metadata->getReflectionClass()->newInstance();

        //Set the "about" object into the enquiry one
        $enquiry->setAbout($about);

        $enquiry->setName($name);
        $enquiry->setForm($form);

        //Persist the enquiry object
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();


        return $enquiry;

    }

    /**
     * Delete an enquiry from the database by name or specifying the object itself
     *
     * @param Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry that is going to be deleted
     */
    public function deleteEnquiry($enquiry)
    {
        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        $this->objectManager->remove($enquiry);
        $this->objectManager->flush();

    }

    /**
     * Save the answers to an enquiry to the database.
     * The enquiry can be specified by its database object representation or by name
     *
     * @param Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry
     * @param Bodaclick\BDKEnquiryBundle\Model\Answer $answer An answer object containing the responses given
     * @param \Symfony\Component\Security\Core\User\UserInterface $user The user that the answers belongs to. If none specified, the connected one is used.
     */
    public function answerEnquiry($enquiry,Answer $answer, UserInterface $user = null)
    {

        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        //Associate the answers
        $enquiry->addAnswer($answer);

        //Associate the user, if none given, get the connected one
        if ($answer->getUser()==null)
        {
            if ($user==null)
            {
                $token=$this->securityContext->getToken();
                if ($token!=null)
                    $user=$token->getUser();
            }

            if ($user) $answer->setUser($user);
        }

        //Save to the database
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();

    }

    /**
     * Create an empty answer entity or document
     *
     * @return Bodaclick\BDKEnquiryBundle\Model\Answer
     */
    public function createAnswer()
    {
        $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Answer');
        $answer = $metadata->getReflectionClass()->newInstance();

        return $answer;
    }

    /**
     * Create an empty response entity or document
     *
     * @return Bodaclick\BDKEnquiryBundle\Model\Response
     */
    public function createResponse()
    {
        $metadata = $this->objectManager->getClassMetadata('BDKEnquiryBundle:Response');
        $response = $metadata->getReflectionClass()->newInstance();

        return $response;
    }

    /**
     * Helper function used to check the enquiry param in some methods
     * If it's a string, guess it's the enquiry name, if not, the enquiry object itself
     *
     * @param string | Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface $enquiry The enquiry object or the enquiry's name
     * @return Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveEnquiryParam($enquiry)
    {

        if (is_string($enquiry))
        {
            $name = $enquiry;
            $enquiry = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->findOneBy(array('name'=>$name));
            if ($enquiry===null)
                throw new \InvalidArgumentException(sprintf("There isn't any enquiry in the database with the name %s",$name));
        }
        elseif (!($enquiry instanceof EnquiryInterface))
            throw new \InvalidArgumentException(sprintf("The method param must be an object implementing EnquiryInterface or a string containing the name of an enquiry"));

        return $enquiry;
    }
}
