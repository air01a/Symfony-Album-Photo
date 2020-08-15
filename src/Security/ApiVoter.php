<?php
// src/Security/ApiVoter.php
namespace App\Security;

use App\Entity\Album;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use App\Service\TokenManager;


class ApiVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof Album) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Album $album */
        $album = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($album, $user);
            case self::EDIT:
                return $this->canEdit($album, $user);
        }

        throw new \LogicException('Voter only support VIEW/EDIT');
    }

    private function canView(Album $album, User $user)
    {

        foreach($album->getRights() as $right)
            if ($right->getUserId()==$user->getId())
            return true;
        
        return false;
    }

    private function canEdit(Album $album, User $user)
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) 
            return true;

        return false;    
    }


}