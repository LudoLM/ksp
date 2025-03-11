<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    // catch all routes and render the vue app except for the login, logout  and register routes
    #[Route(path: "/{route}", name:"vue_pages",requirements: ["route" => "^(?).*$"])]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('app.html.twig', ['user' => $this->getUser()]);
    }

}