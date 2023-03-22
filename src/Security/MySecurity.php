<?php
namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MySecurity
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            return $user;
        }

        return null;
    }
}
