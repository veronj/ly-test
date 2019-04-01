<?php

namespace App\Controller;

use App\Entity\PaymentRequest;
use App\Form\PaymentRequestType;
use GuzzleHttp\Client as httpClient;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home.index")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/payment_request", name="home.payment_request")
     */
    public function paymentRequest(Request $request, ObjectManager $em)
    {

        $paymentRequest = new PaymentRequest();
        $form = $this->createForm(PaymentRequestType::class, $paymentRequest);
        
        $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid())
        {
            $data = $this->requestDo($paymentRequest);
                      
            $paymentRequest->setRequestId($data->request_id);
            $paymentRequest->setRequestUuid($data->request_uuid);
            $paymentRequest->setMobileUrl($data->mobile_url);
            $paymentRequest->setMessage($data->message);
            $paymentRequest->setState(0);
            $paymentRequest->setAmount(12);
            $paymentRequest->setCurrency("EUR");
            $paymentRequest->setCreatedAt(new \DateTime());

            $em->persist($paymentRequest);
            $em->flush();
              
            return $this->render('home/result.html.twig', [
                
                'paymentRequest' => $paymentRequest
                
            ]);
        }

        return $this->render('home/request.html.twig', [
            'paymentRequest' => $paymentRequest,
            'form' => $form->createView()
        ]); 
    }

    /**
     * @Route("/request_do", name="request_do")
     */
    public function requestDo(PaymentRequest $paymentRequest)
    {
        $client = new httpClient();
        
        $param = array(
        'amount'   => '12',
        'vendor_token' => '58385365be57f651843810',
        'recipient' => $paymentRequest->GetEmail(),
        'message' => 'Yummy pizza',
        'currency' => 'EUR',
        'type' => 'email',
        );
        
        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/request/do.json', [
            'multipart' => $this->jsonToFormData($param)
        ]);
        $data = json_decode($response->getBody());

        return $data;
    }

    public function jsonToFormData($array)
    {
        $converted = array();
        foreach ($array as $key => $value)
        {
            $step = array(
                'name' => $key,
                'contents' => $value  
            );
            array_push($converted, $step);
        }
        return $converted;
    }
}
