<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    // catch all routes and render the vue app except for the login and logout routes
    #[Route(path: "/{route}", name:"vue_pages",requirements: ["route" => "^(?!login|logout).*$"])]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
//dd('here');
        return $this->render('app.html.twig');
    }

}