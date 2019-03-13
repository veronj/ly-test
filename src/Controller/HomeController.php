<?php

namespace App\Controller;

use GuzzleHttp\Client;
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
     * @Route("/api", name="api")
     */
    public function access()
    {
        $client = new Client();

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
        $client = new Client();
        $res = $client->request('GET', 'https://api.github.com/user');
        echo $res->getStatusCode();
        // "200"
        echo $res->getHeader('content-type')[0];
        // 'application/json; charset=utf8'
        echo $res->getBody();
    }

}
