<?php

namespace App\Controller;

use App\Entity\Freelancer;
use App\Repository\FreelancerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FreelancerController extends AbstractController
{
    #[Route('/freelancers', name: 'app_freelancers')]
    public function index(FreelancerRepository $freelancerRepository): Response
    {
        $freelancers = $freelancerRepository->findAll();

        return $this->render('freelancer/index.html.twig', [
            'freelancers' => $freelancers,
        ]);
    }

    #[Route('/freelancer/{id}', name: 'app_freelancer_show')]
    public function show(Freelancer $freelancer): Response
    {
        return $this->render('freelancer/show.html.twig', [
            'freelancer' => $freelancer,
        ]);
    }
}