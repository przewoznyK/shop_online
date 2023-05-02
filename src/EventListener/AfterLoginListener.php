<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AfterLoginListener
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var $session SessionInterface */
        $session = new Session();
        /** @var $myUser User */
        $myUser = $event->getAuthenticationToken()->getUser();
        if($myUser->getDeletedAt())
        {
            $session->set('account_deleted', 'This account was deleted');

            throw new DisabledException('Account deleted');
            
        }
       
        $myCarts = $myUser->getCarts();
        if ($myCarts) {
            $myCartsId = explode('|', $myCarts);
            $session->set('cartsId', $myCarts);
            $session->set('cartsCount', count($myCartsId));
        }
        $session->set('wallet', $myUser->getWallet());
        $session->set('userId', $myUser->getId());
    }
}


