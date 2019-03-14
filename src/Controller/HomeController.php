<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use GuzzleHttp\Client as httpClient;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/client", name="client")
     */
    public function client(Request $request, ObjectManager $em)
    {

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        
        $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid())
        {


            $this->em->persist($client);
            $this->em->flush();
            return $this->redirectToRoute('home.client.index');
        }

        return $this->render('home/index.html.twig', [
            'client' => $client,
            'form' => $form->createView()
        ]); 
    }

    /**
     * @Route("/api", name="api")
     */
    public function access()
    {
        $client = new httpClient();
        $res = $client->request('POST', 'https://homologation.lydia-app.com/api/request/do.json', [
        'message' => 'hello world',
        'amount'   => '12.20',
        'currency' => 'EUR',
        'type' => 'phone',
        "recipient" => "+33600000001",
        "vendor_token" => "xx",
        "user_token" => "+33600000001"
        ]);

        return new Response($res->getBody());
    }

    /**
     * @Route("/git", name="git")
     */
    public function github()
    {
        $client = new httpClient();
        $res = $client->request('GET', 'https://api.github.com/user');
        echo $res->getStatusCode();
        // "200"
        echo $res->getHeader('content-type')[0];
        // 'application/json; charset=utf8'
        echo $res->getBody();
    }

    /**
     * @Route("/sig", name="sig")
     */
    public function makeSig()
    {
        // Prepare data
        $param = array(
            'message' => 'hello world',
            'amount'   => '12.20',
            'currency' => 'EUR',
            'type' => 'phone',
            "recipient" => "+33600000001",
            "vendor_token" => "xx",
            "user_token" => "+33600000001"
        );

        ksort($param); // alphabetical sorting

        $sig = array();

        foreach ($param as $key => $val) {
            $sig[] .= $key.'='.$val;
        }
        
        
        // Concat the private token (provider one or vendor one) and has the result
        $callSig = md5(implode("&", $sig)."xx");

        return $this->render('base.html.twig', [
            'callSig' => $callSig,
            'param' => $param
        ]);

    }
    

}
