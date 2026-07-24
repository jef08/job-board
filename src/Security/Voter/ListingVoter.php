<?php

namespace App\Security\Voter;

use App\Entity\Listing;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ListingVoter extends Voter
{
    public const EDIT = 'LISTING_EDIT';
    public const VIEW = 'LISTING_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Listing;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        /** @var Listing $listing */
        $listing = $subject;

        switch ($attribute) {
            case self::EDIT:
                $company = $user->getCompany();

                if ($company === null) {
                    $vote?->addReason('Only companies can edit listings.');
                    return false;
                }

                return $listing->getCompany() === $company;

            case self::VIEW:
                // anyone can view a listing — public route
                return true;
        }

        return false;
    }
}
