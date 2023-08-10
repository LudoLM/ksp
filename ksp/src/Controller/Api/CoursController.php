<?php

namespace App\Controller\Api;

use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Routes(path: "/", name:"api")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository $coursRepository
    )
    {

    }

    #[Route('api/getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(): Response
    {
        return $this->json($this->coursRepository->findAll());
    }

}