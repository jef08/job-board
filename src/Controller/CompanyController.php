<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'app_company')]
    public function index(CompanyRepository $companyRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');

        $query = $companyRepository->findFiltered($search);

        $companies = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/company/{id}', name: 'app_company_show')]
    public function show(Company $company): Response
    {
        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }
}