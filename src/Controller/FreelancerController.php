<?php

namespace App\Controller;

use App\Entity\Freelancer;
use App\Repository\FreelancerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class FreelancerController extends AbstractController
{
    #[Route('/freelancers', name: 'app_freelancers')]
    public function index(FreelancerRepository $freelancerRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $categoryParam = $request->query->get('category');
        $categoryId = ($categoryParam !== null && $categoryParam !== '') ? (int) $categoryParam : null;
        $category = $categoryId ? $categoryRepository->find($categoryId) : null;

        $query = $freelancerRepository->findFiltered($category);

        $freelancers = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('freelancer/index.html.twig', [
            'freelancers' => $freelancers,
            'categories' => $categoryRepository->findAll(),
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