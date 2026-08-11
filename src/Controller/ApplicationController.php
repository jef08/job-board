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
use App\Enum\ApplicationStatus;
use App\Enum\ListingStatus;
use App\Repository\ApplicationRepository;
use App\Security\Voter\ApplicationVoter;

final class ApplicationController extends AbstractController
{
    #[Route('/application', name: 'app_application')]
    public function index(): Response
    {
        return $this->render('application/index.html.twig', [
            'controller_name' => 'ApplicationController',
        ]);
    }

    #[Route('/application/{id}', name: 'app_application_show')]
    public function show(Application $application): Response
    {
        $this->denyAccessUnlessGranted(ApplicationVoter::VIEW, $application);

        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/listing/{id}/apply', name: 'app_listing_apply')]
    public function apply(Listing $listing, Request $request, EntityManagerInterface $em, ApplicationRepository $applicationRepository): Response {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        if ($listing->getStatus() !== ListingStatus::Open) {
            throw $this->createAccessDeniedException('This listing is closed.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $existingApplication = $applicationRepository->findOneBy([
            'freelancer' => $user->getFreelancer(),
            'listing' => $listing,
        ]);

        if($existingApplication) {
            $this->addFlash('error', 'You have already applied to this job');
            return $this->redirectToRoute('app_listing_show', [
                'slug' => $listing->getSlug(),
            ]);
        }

        $application = new Application();

        $form = $this->createForm(ApplicationFormType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setFreelancer($user->getFreelancer());
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

    #[Route('/application/{id}/accept', name: 'app_application_accept', methods: ['POST'])]
    public function accept(Application $application, EntityManagerInterface $em, Request $request): Response {
    
        $this->denyAccessUnlessGranted(ApplicationVoter::MANAGE, $application);

        if (!$this->isCsrfTokenValid('accept-application-' . $application->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $application->setStatus(ApplicationStatus::Accepted);

        $em->flush();

        return $this->redirectToRoute('app_listing_applicants', [
            'id' => $application->getListing()->getId(),
        ]);
    }

    #[Route('/application/{id}/reject', name: 'app_application_reject', methods: ['POST'])]
    public function reject(Application $application, EntityManagerInterface $em, Request $request): Response {

        $this->denyAccessUnlessGranted(ApplicationVoter::MANAGE, $application);

        if (!$this->isCsrfTokenValid('reject-application-' . $application->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $application->setStatus(ApplicationStatus::Rejected);
        $em->flush();

        return $this->redirectToRoute('app_listing_applicants', [
            'id' => $application->getListing()->getId(),
        ]);
    }

    #[Route('/application/{id}/withdraw', name: 'app_application_withdraw', methods: ['POST'])]
    public function withdraw(Application $application, EntityManagerInterface $em, Request $request): Response {
        $this->denyAccessUnlessGranted(ApplicationVoter::WITHDRAW, $application);

        if (!$this->isCsrfTokenValid('withdraw-application-' . $application->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $application->setStatus(ApplicationStatus::Withdrawn);
        $em->flush();

        $this->addFlash('success', 'Application withdrawn.');
        return $this->redirectToRoute('app_freelancer_applications');
    }
}
