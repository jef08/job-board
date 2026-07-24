<?php

namespace App\Security\Voter;

use App\Entity\Application;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ApplicationVoter extends Voter
{
    public const MANAGE = 'APPLICATION_MANAGE';
    public const VIEW = 'APPLICATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::MANAGE, self::VIEW])
            && $subject instanceof Application;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');
            return false;
        }

        /** @var Application $application */
        $application = $subject;

        $owningCompany = $application->getListing()->getCompany();
        $applyingFreelancer = $application->getFreelancer();

        switch ($attribute) {
            case self::MANAGE:
                // Only the company that owns the listing can accept/reject
                $userCompany = $user->getCompany();

                if ($userCompany === null) {
                    $vote?->addReason('Only companies can manage applications.');
                    return false;
                }

                return $owningCompany === $userCompany;

            case self::VIEW:
                // Either the applying freelancer, or the owning company
                $userCompany = $user->getCompany();
                $userFreelancer = $user->getFreelancer();

                if ($userCompany !== null && $owningCompany === $userCompany) {
                    return true;
                }

                if ($userFreelancer !== null && $applyingFreelancer === $userFreelancer) {
                    return true;
                }

                return false;
        }

        return false;
    }
}