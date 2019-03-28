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
}