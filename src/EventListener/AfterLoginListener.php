<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AfterLoginListener
{

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var $session SessionInterface */

        $session = new Session();
        /** @var $myUser User */
        
        $myUser = $event->getAuthenticationToken()->getUser();
        $myCarts = $myUser->getCarts();
        if ($myCarts) {
            $myCartsId = explode(',', $myCarts);
            $session->set('cartsId', $myCarts);
          $session->set('cartsCount', count($myCartsId));
           echo $session->get('cartsCount');
           echo $session->get('cartsId');
        }
    }
}



// namespace App\EventListener;

// use Symfony\Component\HttpFoundation\Session\SessionInterface;
// use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
// use Symfony\Component\Security\Http\SecurityEvents;

// class AfterLoginListener
// {
//     public function onAfterLoginListener(InteractiveLoginEvent $event)
//     {
//         die('brawo');
//     }
//     private $session;
    
//     public function __construct(SessionInterface $session)
//     {
//         $this->session = $session;
//     }
    
//     public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
//     {
//         // Do something after login
//         $this->session->set('cartsId', 999);
        
//     }
 //}



















// namespace App\EventListener;

// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// use Symfony\Component\HttpFoundation\Session\SessionInterface;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
// use Symfony\Component\Security\Http\SecurityEvents;

// /**
//  * @service
//  */
// class AfterLoginListener implements EventSubscriberInterface
// {
//     private $session;
//     public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
//     {
//         $this->session = $session;
//     }

//     public static function getSubscribedEvents()
//     {
//         return [
//             InteractiveLoginEvent::class => 'onSecurityInteractiveLogin'
//         ];
//     }

//     public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
//     {
//         /** @var $myUser User */
//         $myUser = $event->getAuthenticationToken()->getUser();
//         $myCarts = $myUser->getCarts();
//                 if ($myCarts) {
//             $myCartsId = explode(',', $myCarts);
            
//             $this->session->set('cartsId', $myCarts);
//             $this->session->set('cartsCount', count($myCartsId));
//             error_log($this->session->get('cartsId'));
//         }
//     }
//     // public function onLogin(InteractiveLoginEvent $event, SessionInterface $session)
//     // {

//     //     // Kod, który zostanie wykonany po zalogowaniu użytkownika.

//     //     /** @var $myUser User */
//     //     $myUser = $this->getUser();
//     //     $myCarts = $myUser->getCarts();

//     // }
// }