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

    public function makeSig($param)
    {
        // Prepare data
        /* $param = array(
            'message' => 'hello world',
            'amount'   => '12.20',
            'currency' => 'EUR',
            'type' => 'phone',
            "recipient" => "+33600000001",
            "vendor_token" => "xx",
            "user_token" => "+33600000001"
        ); */

        ksort($param); // alphabetical sorting

        $sig = array();

        foreach ($param as $key => $val) {
            $sig[] .= $key.'='.$val;
        }
                
        // Concat the private token (provider one or vendor one) and has the result
        $callSig = md5(implode("&", $sig)."58xx");

        return $callSig;
    }

    /**
     * @Route("/register", name="register")
     */
    public function register()
    {
        $client = new httpClient();
        $param = array(
            'vendor_token' => '58xx',
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '0606060606',
            'password' => 'password',
        );
        
        $param['signature'] = $this->makeSig($param);

        $res = $client->request('POST', 'https://homologation.lydia-app.com/api/auth/register.json', $param);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $res,
            'param' => $param
        ]); 
    }
    
    /**
     * @Route("/create_account", name="create_account")
     */
    public function create_account()
    {
        $client = new httpClient();
        $sigParam = array(
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '+33606060606'
        );

        $param = array(
            'vendor_token' => '58xx',
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '+33606060606',
            'password' => 'password',
            'provider_token' => '58xx'
        );
        
        $param['signature'] = $this->makeSig($sigParam);

        $res = $client->request('POST', 'https://homologation.lydia-app.com/api/auth/create_account.json', $param);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $res,
            'param' => $param
        ]); 
    }

/**
     * @Route("/request_do", name="request_do")
     */
    public function request_do()
    {
        $client = new httpClient();
        $sigParam = array(
            'amount'   => '12.20',
            'vendor_token' => '58xx',
            'user_token' => '+33606060606',
            'recipient' => '+33606060606',
            'message' => 'hello',
            'currency' => 'EUR',
            'type' => 'phone',
        );

        $param = array(
        'amount'   => '12.20',
        'vendor_token' => '58xx',
        'user_token' => '+33606060606',
        'recipient' => '+33606060606',
        'message' => 'hello',
        'currency' => 'EUR',
        'type' => 'phone',
        );
        
        $param['signature'] = $this->makeSig($sigParam);
        $jsonParam = json_encode($param);

        $res = $client->request('POST', 'https://homologation.lydia-app.com/api/request/do.json', [
            'json' => $jsonParam
        ]);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $res,
            'body' => $res->getBody(),
            'param' => $param,
            'json' => $jsonParam
        ]); 
    }

    /**
     * @Route("/test_body", name="test_body")
     */
    public function toTestBody()
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://homologation.lydia-app.com',
            
        ]);
        $response = $client->post('/api/request/do.json');
        

        return $this->render('home/result.html.twig', [
            'response' => $response,
            'status_code' => $response->getStatusCode(),
            'body' => $response->getBody(),

        ]); 
    }
}
