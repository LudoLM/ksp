<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/home', name: 'app_home')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('app.html.twig');
    }

}