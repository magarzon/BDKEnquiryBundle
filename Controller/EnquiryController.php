<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bodaclick\BDKEnquiryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Bodaclick\BDKEnquiryBundle\Model\Enquiry;
use Symfony\Component\HttpFoundation\Response;
use Bodaclick\BDKEnquiryBundle\Model\ResponseNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Serializer;

/**
 * Controller class
 */
class EnquiryController extends Controller
{
    /**
     * Action to get an enquiry by id, in json or xml format
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getEnquiryAction($id)
    {
        //Use the service to get the enquiry
        $em = $this->get('bdk.enquiry.manager');

        $enquiry = $em->getEnquiry($id);

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        //Build a response based on the _format request parameter
        return $this->buildResponse($enquiry);
    }

    /**
     * Action to get an enquiry by name, in json or xml format
     *
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getEnquiryByNameAction($name)
    {
        //Use the service to get the enquiry
        $em = $this->get('bdk.enquiry.manager');

        $enquiry = $em->getEnquiryByName($name);

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        //Build a response based on the _format request parameter
        return $this->buildResponse($enquiry);
    }

    /**
     * Save an user response/s to an existing enquiry specified by its id
     * The responses are given in the content of a POST request, in the following format:
     * "answer": { "responses": [ {"key"="examplekey","value"="examplevalue",...}, {...},...]}
     *
     * @param $enquiryId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveAnswerAction($enquiryId)
    {

        //Get the responses in json format from the body of the POST request
        $request = $this->getRequest();
        $content = $request->getContent();
        $content_type = $request->headers->get('Content-Type');

        //Check that it's the right content type and the content is not empty
        if (strpos($content_type,'application/json')===false || empty($content)) {
            throw new HttpException(405);
        }

        //Parse the answer in form of json post request
        $serializer = $this->getSerializer();

        $em = $this->get('bdk.enquiry.manager');

        //Use the service function to create an empty answer only to get the right class
        //FIX: Maybe this can be coded in a better way
        $answer = $em->createAnswer();

        //Deserialize the answer
        $answer = $serializer->deserialize($content, get_class($answer), $request->getRequestFormat());

        //If the answer is null or has no responses, something is bad in the request
        if ($answer == null || $answer->getResponses()->count()==0) {
            throw new InvalidArgumentException('The json request is malformed or not valid');
        }

        //Get the enquiry by id, using the service
        $enquiry = $em->getEnquiry($enquiryId);

        //If not found, return 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        //Save the answer deserialized above, using the service
        $em->saveAnswer($enquiry, $answer, $this->getUser());

        //Return an empty response
        return new Response();
    }

    /**
     * Construct a serializer object with the right normalizers and supported encoders
     * Used to parse the request and serialize the response in the format requested.
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected function getSerializer()
    {
        return new Serializer(
            array(new ResponseNormalizer($this->container), new CustomNormalizer()),
            array(new XmlEncoder(), new JsonEncoder())
        );
    }

    /**
     * Serialize the enquiry object and return it in the format requested.
     *
     * @param  \Bodaclick\BDKEnquiryBundle\Model\Enquiry  $enquiry
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse(Enquiry $enquiry)
    {
        $serializer = $this->getSerializer();
        $content = $serializer->serialize($enquiry, $this->getRequest()->getRequestFormat());

        return new Response($content);

    }
}
