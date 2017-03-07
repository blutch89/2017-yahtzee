<?php
namespace YahtzeeBundle\Services;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LastContact {

    private $userManager;
    private $tokenStorage;
    private $secondsNumberForDisconnected = 20;

    public function __construct(UserManagerInterface $userManager, TokenStorageInterface $tokenStorage) {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function setLastContact() {
        $user = $this->tokenStorage->getToken()->getUser();
        $user->setLastContact(new \DateTime());
        $this->userManager->updateUser($user);
    }

    public function isDisconnected($user) {
        $userTimestamp = $user->getLastContact()->getTimestamp();
        $currentTimestamp = time();

        if ($currentTimestamp > $userTimestamp + $this->secondsNumberForDisconnected) {
            return true;
        }

        return false;
    }
}