<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;

class CartService
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getCartsCount(SessionInterface $session): int
    {
        //  dump($session->get('cartsId'));

        //  die;
        /** @var $myUser User */
        $myUser = $this->security->getUser();
        if ($myUser) {
            $myCarts = $myUser->getCarts();
            $myCartsArray = explode(',', $myCarts);
    
            return count($myCartsArray);
        }
        else
        {
            
            if($session->has('cartsCount'))
                {
                   return $session->get('cartsCount');
                }
        }
        return 0;
    }
}
