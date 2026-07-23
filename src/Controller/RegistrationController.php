<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Freelancer;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $accountType = $form->get('accountType')->getData();

            if ($accountType === 'ROLE_COMPANY') {
                $user->setRoles(['ROLE_COMPANY']);
                $profile = new Company();
                $profile->setUser($user);
                $em->persist($profile);
            } else {
                $user->setRoles(['ROLE_FREELANCER']);
                $profile = new Freelancer();
                $profile->setUser($user);
                $em->persist($profile);
            }

            $em->persist($user);
            $em->flush();

            return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
