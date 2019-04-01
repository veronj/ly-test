<?php

namespace App\Controller;

use App\Entity\PaymentRequest;
use GuzzleHttp\Client as httpClient;
use App\Repository\PaymentRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin.index")
     */
    public function index(PaymentRequestRepository $repo) 
    {
        $paymentRequests = $repo->findAll();

        return $this->render('admin/index.html.twig', [
            'paymentRequests' => $paymentRequests
        ]);
    }

    /**
     * @Route("/check_state/all", name="check_state_all")
     */
    public function checkStateAll(PaymentRequestRepository $repo, ObjectManager $em)
    {
        $paymentRequests = $repo->findAll();

        foreach ($paymentRequests as $paymentRequest)
        {
            if ($paymentRequest->GetState() === 0)
            {
                $client = new httpClient();
        
                $param = array(
                'request_id'   => $paymentRequest->GetRequestId(),
                );
        
                $response = $client->request('POST', 'https://homologation.lydia-app.com/api/request/state.json', [
                    'multipart' => $this->jsonToFormData($param)
                ]);
                $data = json_decode($response->getBody());
                
                if ($data->state != $paymentRequest->getState())
                {
                    $paymentRequest->setState($data->state);
                    $em->persist($paymentRequest);
                    $em->flush();
                }
            }
        }
            
        return $this->redirectToRoute('admin.index');
    }

    /**
     * @Route("/check_state/{id}", name="check_state")
     */
    public function checkState(PaymentRequest $paymentRequest, ObjectManager $em)
    {
        $client = new httpClient();
        
        $param = array(
        'request_id'   => $paymentRequest->GetRequestId(),
        );
        
        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/request/state.json', [
            'multipart' => $this->jsonToFormData($param)
        ]);
        $data = json_decode($response->getBody());
        
        if ($data->state != $paymentRequest->getState())
        {
            $paymentRequest->setState($data->state);
            $em->persist($paymentRequest);
            $em->flush();
        }

        return $this->redirectToRoute('admin.index');
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