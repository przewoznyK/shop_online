<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionController extends AbstractController
{
    public function addValueToSession(Request $request, SessionInterface $session, EntityManagerInterface $entity): JsonResponse
    {

        $cartsCount = $session->get('cartsCount', 0) + 1;
        $session->set('cartsCount', $cartsCount);

        $productId = $request->request->get('productId');
        $quantity = $request->request->get('quantity');
        $cartsId = $session->get('cartsId', '');

        if ($cartsId === '') {
            $cartsId = $productId . ':' . $quantity;
        } else {
            $cartsId .= '|' . $productId . ':' . $quantity;
        }

        $session->set('cartsId', $cartsId);

        /** @var $myUser User */
        $myUser = $this->getUser();
        if ($myUser) {
            $carts = $myUser && $myUser->getCarts() ? $myUser->getCarts() . '|' . $productId . ':' . $quantity : $productId . ':' . $quantity;

            $myUser->setCarts($carts);
            $entity->persist($myUser);
            $entity->flush();
        }
        // $this->addFlash('success', 'Product successfully add to cart!');



        return new JsonResponse(['cartsCount' => $cartsCount]);
    }

    public function removeCart(Request $request, SessionInterface $session, EntityManagerInterface $entity): JsonResponse
    {
        // Change session cartsCount value
        $cartsCount = $session->get('cartsCount', 0) - 1;
        $session->set('cartsCount', $cartsCount);
        // Take data from order
        $productId = $request->request->get('productId');
        $quantity = $request->request->get('quantity');
        // Take all carts id from user session
        $cartsId = $session->get('cartsId');
        // Make array with id and quantity [id:value, id:value]
        $cartsIdArray = explode('|', $cartsId);
        // Make new array [id => value, id, value]
        $cartsIdAndQuantityArray = array();
        foreach ($cartsIdArray as $cart) {
            $keyValue = explode(':', $cart);

            $cartsIdAndQuantityArray[$keyValue[0]] = $keyValue[1];
        }
       
        dump($cartsIdAndQuantityArray);
        // Delete data product
        foreach ($cartsIdAndQuantityArray as $key => $value) {
            if ($key == $productId) {
                unset($cartsIdAndQuantityArray[$key]);
                break;
            }
        }

        $cartsIdAfterRemove = '';
       
        foreach ($cartsIdAndQuantityArray as $key => $value) {
            $cartsIdAfterRemove .= $key . ':' . $value . '|';
        }

        $cartsIdAfterRemove = rtrim($cartsIdAfterRemove, '|');
        $session->set('cartsId', $cartsIdAfterRemove);
        /** @var $myUser User */
        $myUser = $this->getUser();
        if ($myUser) {
            $myUser->setCarts($session->get('cartsId'));
            $entity->persist($myUser);
            $entity->flush();
        }
        return new JsonResponse(['cartsCount' => $cartsCount]);
    }
}
