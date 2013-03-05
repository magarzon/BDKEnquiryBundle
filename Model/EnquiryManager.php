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

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Bodaclick\BDKEnquiryBundle\Events\Events;
use Symfony\Component\EventDispatcher\Event;
use Bodaclick\BDKEnquiryBundle\Events\EnquiryEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Enquiry manager.
 * Access point to all the bundle's features and factory service to create database objects
 */
class EnquiryManager
{

    /**
     * ObjectManager to access the database, by ORM or ODM
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Dispatcher for the events
     *
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Default Response class, used to build responses if needed.
     * Must be a descendant of Response abstract class in the bundle model.
     *
     * @var Bodaclick\BDKEnquiryBundle\Model\Response
     */
    protected $defaultResponseClass;

    /**
     * Optional logger to log service activity
     *
     * @var LoggerInterface $logger
     */
    protected $logger=null;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $dispatcher,
        $defaultResponseClass
    )
    {
        $this->objectManager = $objectManager;
        $this->dispatcher = $dispatcher;
        $this->defaultResponseClass = $defaultResponseClass;
    }

    /**
     * Setter for the optional logger parameter
     *
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the last enquiry associated to an object
     *
     * @param AboutInterface $object
     */
    public function getEnquiryFor(AboutInterface $object)
    {
        $enquiries = $this->getEnquiriesFor($object);

        if (is_array($enquiries)) {
            return array_pop($enquiries);
        } elseif ($enquiries instanceof \Iterator) {
            $enquiries->next();
            return $enquiries->current();
        } else {
            throw new \UnexpectedValueException(
                'EnquiryRepository method must return an array or object implementing Iterator interface'
            );
        }
    }

    /**
     * Get all the enquiries previously associated to an object
     *
     * @param AboutInterface $object
     */
    public function getEnquiriesFor(AboutInterface $object)
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
     * Get an enquiry by id
     *
     * @param $id
     */
    public function getEnquiry($id)
    {
        $enquiry = $this->objectManager->getRepository('BDKEnquiryBundle:Enquiry')->find($id);

        return $enquiry;
    }

    /**
     * Create an enquiry (database object, it'll be an entity or a document,
     * depending on the configuration), and return it
     *
     * @param AboutInterface $about The object that the enquiry is associated with. Can be of any type,
     *                     but always an entity or a document implementing AboutInterface
     * @param string $form The name of a form associated to the enquiry. Only used for reference. Optional.
     * @param string $name A name associated to the enquiry. Optional. If specified, must be unique
     *
     * @return Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface The enquiry database object created
     */
    public function saveEnquiry(AboutInterface $about, $form = null, $name = null)
    {

        //Check if the "about" object is persisted (has an identifier value),
        //if not, persist it to get the right id when associated with enquiry
        try {
            $aboutMetadata = $this->objectManager->getClassMetadata(get_class($about));
        } catch(\Exception $e) {
            $msg = 'The about parameter must be a valid entity or a valid document';
            if ($this->logger) {
                $this->logger->crit($msg);
            }
            throw new \InvalidArgumentException($msg);
        }

        $ids = $aboutMetadata->getIdentifierValues($about);

        if (count($ids)==0) {
                if ($this->logger) {
                    $this->logger->debug('About object not saved yet, proceed to save it');
                }
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

        $event = new EnquiryEvent($enquiry);

        //Dispatch event before persist object, and get the $enquiry object, in case the listener change it
        $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);

        $enquiry = $event->getEnquiry();

        //Persist the enquiry object
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();

        //Dispatch event to inform object is persisted
        $this->dispatcher->dispatch(Events::POST_PERSIST, $event);

        if ($this->logger) {
            $this->logger->info(
                sprintf(
                    'Enquiry saved with about object of class %s, form value %s and name %s',
                    get_class($about),
                    $form,
                    $name
                )
            );
        }

        return $enquiry;
    }

    /**
     * Delete an enquiry from the database by name or specifying the object itself
     *
     * @param Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface | string The enquiry
     * object or the name of the enquiry that is going to be deleted
     */
    public function deleteEnquiry($enquiry)
    {
        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        $event = new EnquiryEvent($enquiry);

        $this->objectManager->remove($enquiry);
        $this->objectManager->flush();


        if ($this->logger) {
            $this->logger->info(sprintf('Enquiry %s removed', $enquiry->getName()));
        }
    }

    /**
     * Save the answers to an enquiry to the database.
     * The enquiry can be specified by its database object representation or by name
     * The responses come in an array of Response objects
     *
     * @param Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry
     * @param Bodaclick\BDKEnquiryBundle\Model\Answer $answer An answer object containing the responses given
     * @param \Symfony\Component\Security\Core\User\UserInterface $user The user that the answers belongs to. Optional.
     */
    public function saveAnswer($enquiry, Answer $answer, UserInterface $user = null)
    {

        //Get the actual database enquiry object, if name is specified in the param
        $enquiry = $this->resolveEnquiryParam($enquiry);

        //Associate the answer to the enquiry
        $enquiry->addAnswer($answer);

        //Associate the user if any
        if ($user!=null) {
            $answer->setUser($user);
        }

        //Dispatch event and get the $enquiry object, in case the listener change it
        $event = new EnquiryEvent($enquiry);

        $this->dispatcher->dispatch(Events::PRE_PERSIST_ANSWER, $event);

        $enquiry = $event->getEnquiry();

        //Save to the database
        $this->objectManager->persist($enquiry);
        $this->objectManager->flush();

        //Dispatch event to inform object has persisted
        $this->dispatcher->dispatch(Events::POST_PERSIST_ANSWER, $event);

        if ($this->logger) {
            if ($user) {
                $msg = sprintf(
                    'Answer from user %s to enquiry %s saved',
                    $user->getUsername(),
                    $enquiry->getName()
                );
            } else {
                $msg = sprintf('Answer from anonymous user to enquiry %s saved', $enquiry->getName());
            }
            $this->logger->info($msg);
        }
    }

    /**
     * Save the responses to an enquiry to the database.
     * The enquiry can be specified by its database object representation or by name
     * The responses come in an array of Response objects
     *
     * @param Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface | string The enquiry object or the name of the enquiry
     * @param array $responses Array of Response objects or raw key=>value pair
     * @param \Symfony\Component\Security\Core\User\UserInterface $user The user that the answers belongs to. Optional.
     */
    public function saveResponses($enquiry, array $responses, UserInterface $user=null)
    {
        //Check the type of the responses, if they are raw key-value pairs, convert into default Response objects
        //Throw and exception if they are objects of classes not descendant of default Response class
        $checkFunction = function(&$value, $key, $defaultResponseClass) {
            if (!is_object($value)) {
                $response = new $defaultResponseClass;
                $response->setValue($value);
                $response->setKey($key);
                $value = $response;
            } elseif (!($value instanceof Response)) {
                throw new \Exception();
            }
        };

        try {
            array_walk($responses, $checkFunction, $this->defaultResponseClass);
        } catch(\Exception $e) {
            $msg = 'The responses parameter must contain an array of Response objects or key-value pairs';
            if ($this->logger) {
                $this->logger->crit($msg);
            }
            throw new \InvalidArgumentException($msg);
        }

        //Create the answer instance object
        $answer = $this->createAnswer();

        //Add the responses to the answer instance
        $answer->setResponses(new ArrayCollection($responses));

        //Call the saveAnswer method with the new answer created
        $this->saveAnswer($enquiry, $answer, $user);
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
        return new $this->defaultResponseClass;
    }

    /**
     * Helper function used to check the enquiry param in some methods
     * If it's a string, guess it's the enquiry name, if not, the enquiry object itself
     *
     * @param string | Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface $enquiry
     *          The enquiry object or the enquiry's name
     * @return Bodaclick\BDKEnquiryBundle\Model\EnquiryInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveEnquiryParam($enquiry)
    {

        if (is_string($enquiry)) {
            $name = $enquiry;
            $enquiry = $this->objectManager
                ->getRepository('BDKEnquiryBundle:Enquiry')->findOneBy(array('name'=>$name));
            if ($enquiry===null) {
                throw new \InvalidArgumentException(
                    sprintf("There isn't any enquiry in the database with the name %s", $name)
                );
            }
        } elseif (!($enquiry instanceof EnquiryInterface))
            throw new \InvalidArgumentException(
                sprintf(
                    "The method param must be an object implementing EnquiryInterface
                    or a string containing the name of an enquiry"
                )
            );

        return $enquiry;
    }
}
