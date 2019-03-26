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
        "vendor_token" => "58385365be57f651843810",
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
        $callSig = md5(implode("&", $sig)."58385365c0157470110435");

        return $callSig;
    }

    /**
     * @Route("/register", name="register")
     */
    public function register()
    {
        $client = new httpClient();
        $param = array(
            'vendor_token' => '58385365be57f651843810',
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
            'vendor_token' => '58385365be57f651843810',
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '+33606060606',
            'password' => 'password',
            'provider_token' => '58385365be57f651843810'
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
            'vendor_token' => '58385365be57f651843810',
            'user_token' => '+33606060606',
            'recipient' => '+33606060606',
            'message' => 'hello',
            'currency' => 'EUR',
            'type' => 'phone',
        );

        $param = array(
        'amount'   => '12.20',
        'vendor_token' => '58385365be57f651843810',
        'user_token' => '+33606060606',
        'recipient' => '+33606060606',
        'message' => 'hello',
        'currency' => 'EUR',
        'type' => 'phone',
        );
        
        $param['signature'] = $this->makeSig($sigParam);
        $jsonParam = json_encode($param);


        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/request/do.json', [
            'multipart' => [
                [
                    'name'     => 'amount',
                    'contents' => '12.20'
                ],
                [
                    'name'     => 'vendor_token',
                    'contents' => '58385365be57f651843810'
                ],

                [
                    'name'     => 'recipient',
                    'contents' => '+33677985915'
                ],
                [
                    'name'     => 'currency',
                    'contents' => 'EUR'
                ],
                [
                    'name'     => 'type',
                    'contents' => 'phone'
                ],
            ]
        ]);

        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),

        ]);
    }

    /**
     * @Route("/request_list", name="request_list")
     */
    public function request_list()
    {
        $client = new httpClient();
        $now = new \DateTime();
        $date = $now->format('Y-m-d H');
        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/payment/list.json', [
            'multipart' => [
                [
                    'name'     => 'vendor_token',
                    'contents' => '58385365be57f651843810'
                ],
                [
                    'name'     => 'user_token',
                    'contents' => '+33677985915'
                ],
                [
                    'name'     => 'startDate',
                    'contents' => '2018-01-01 00:00:00'
                ],
                [
                    'name'     => 'endDate',
                    'contents' => '2019-03-15 16:44:34'
                ],
            ]
        ]);





        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),
            'date' => $now

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

    /**
     * @Route("/search", name="search")
     */
    public function search()
    {
        $client = new httpClient();

        $param = array(
            'vendor_token' => '58385365be57f651843810',
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '0606060606',
            'password' => 'password',
        );


        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/auth/register.json', [
            'multipart' => [
                [
                    'name'     => 'vendor_token',
                    'contents' => '58385365be57f651843810'
                ],
                [
                    'name'     => 'firstname',
                    'contents' => 'jb'
                ],
                [
                    'name'     => 'lastname',
                    'contents' => 'test'
                ],
                [
                    'name'     => 'email',
                    'contents' => 'user@email.com'
                ],
                [
                    'name'     => 'phone',
                    'contents' => '0606060606'
                ],
                [
                    'name'     => 'password',
                    'contents' => 'password'
                ],

            ]
        ]);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),

        ]);
    }

    /**
     * @Route("/check", name="check")
     */
    public function check()
    {
        $client = new httpClient();





        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/auth/vendortoken.json', [
            'multipart' => [
                [
                    'name'     => 'vendor_token',
                    'contents' => '58385365be57f651843810'
                ],
                [
                    'name'     => 'vendor_id',
                    'contents' => '58385365c0157470110435'
                ],
            ]
        ]);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),

        ]);
    }

    /**
     * @Route("/auth", name="auth")
     */
    public function auth()
    {
        $client = new httpClient();

        $param = array(
            'vendor_token' => '58385365be57f651843810',
            'firstname' => 'jb',
            'lastname' => 'test',
            'email'   => 'user@email.com',
            'phone' => '0606060606',
            'password' => 'password',
        );


        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/auth/login.json', [
            'multipart' => [

                [
                    'name'     => 'phone',
                    'contents' => '0606060606'
                ],
                [
                    'name'     => 'password',
                    'contents' => 'password'
                ],

            ]
        ]);

        //return new Response();
        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),

        ]);
    }

    /**
     * @Route("/payment_ini", name="payment_ini")
     */
    public function payment_init()
    {
        $client = new httpClient();

        $param = array(
            'provider_token' => '58385365be57f651843810',
            'amount' => '12.20',
            'payer_info' => 'toto@email.com',
            'recipient'   => 'pikachu@email.com',
            'currency' => 'EUR',

        );

        $signature = $this->makeSig($param);



        $response = $client->request('POST', 'https://homologation.lydia-app.com/api/payment/init.json', [
            'multipart' => [
                [
                    'name'     => 'amount',
                    'contents' => '12.20'
                ],
                [
                    'name'     => 'vendor_token',
                    'contents' => '556728ae65bd6264713182'
                ],
                [
                    'name'     => 'payer_info',
                    'contents' => 'toto@email.com'
                ],
                [
                    'name'     => 'recipient',
                    'contents' => 'pikachu@email.com'
                ],
                [
                    'name'     => 'currency',
                    'contents' => 'EUR'
                ],
                [
                    'name'     => 'signature',
                    'contents' => $signature
                ],
            ]
        ]);

        return $this->render('home/result.html.twig', [
            'response' => $response,
            'body' => $response->getBody()->read(1024),

        ]);
    }
}
