<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Listing;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ApplicationFormType;
use App\Entity\Application;
use App\Repository\ApplicationRepository;

final class ApplicationController extends AbstractController
{
    #[Route('/application', name: 'app_application')]
    public function index(): Response
    {
        return $this->render('application/index.html.twig', [
            'controller_name' => 'ApplicationController',
        ]);
    }

    #[Route('/listing/{id}/apply', name: 'app_listing_apply')]
    public function apply(Listing $listing, Request $request, EntityManagerInterface $em, ApplicationRepository $applicationRepository): Response {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        $existingApplication = $applicationRepository->findOneBy([
            'user' => $this->getUser(),
            'listing' => $listing,
        ]);

        if($existingApplication) {
            $this->addFlash('error', 'You have already applied to this job');
            return $this->redirectToRoute('app_listing_show', [
                'id' => $listing->getId(),
            ]);
        }

        $application = new Application();

        $form = $this->createForm(ApplicationFormType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setUser($this->getUser());
            $application->setListing($listing);

            $em->persist($application);
            $em->flush();

            return $this->redirectToRoute('app_listing');
        };
        return $this->render('application/apply.html.twig', [
            'form' => $form->createView(),
            'listing' => $listing,
        ]);
    }

    #[Route('/application/{id}/accept', name: 'app_application_accept')]
    public function accept(Application $application, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY', null, 'You cannot access this page');

        if ($application->getListing()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $application->setStatus(Application::STATUS_ACCEPTED);

        $em->flush();

        return $this->redirectToRoute('app_listing_applicants', [
            'id' => $application->getListing()->getId(),
        ]);
    }

    #[Route('/application/{id}/reject', name: 'app_application_reject')]
    public function reject(Application $application, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        if($application->getListing()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $application->setStatus(Application::STATUS_REJECTED);
        $em->flush();

        return $this->redirectToRoute('app_listing_applicants', [
            'id' => $application->getListing()->getId(),
        ]);
    }
}
