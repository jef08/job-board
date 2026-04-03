<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Listing;
use App\Form\ListingFormType;
use App\Repository\ListingRepository;

final class ListingController extends AbstractController
{
    #[Route('/listing', name: 'app_listing')]
    public function index(ListingRepository $listingRepository): Response
    {
        $listings = $listingRepository->findAll();

        return $this->render('listing/index.html.twig', [
            'listings' => $listings,
        ]);
    }

    #[Route('/listing/new', name: 'app_listing_new')]
    public function new(Request $request, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        $listing = new Listing();

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setUser($this->getUser());
            $em->persist($listing);
            $em->flush();

            return $this->redirectToRoute('company_dashboard');
        }

        return $this->render('listing/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
