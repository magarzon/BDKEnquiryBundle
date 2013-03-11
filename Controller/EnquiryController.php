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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        //Get the enquiry in the format requested (json or xml)
        $enquiry = $em->getEnquiry($id, $this->getRequest()->getRequestFormat());

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        return new Response($enquiry);
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

        //Get the enquiry in the format requested (json or xml)
        $enquiry = $em->getEnquiryByName($name, $this->getRequest()->getRequestFormat());

        //If not found, return a 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        return new Response($enquiry);
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

        $em = $this->get('bdk.enquiry.manager');

        //Get the enquiry by id, using the service
        $enquiry = $em->getEnquiry($enquiryId);

        //If not found, return 404 HTTP code
        if ($enquiry==null) {
            throw $this->createNotFoundException();
        }

        //Save the answer, using the service
        $em->saveResponses($enquiry, $content, $this->getUser());

        //Return an empty response
        return new Response();
    }
}
