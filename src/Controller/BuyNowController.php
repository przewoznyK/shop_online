<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BuyNowController extends AbstractController
{
    #[Route('/buy_now/{productId}', name: 'app_buy_now')]
    public function index(EntityManagerInterface $entityManager, int $productId): Response
    {
        $deliveryArray = $entityManager->getRepository(Delivery::class)->findBy(['product' => $productId]);
        return $this->render('buy_now/buyNow.html.twig', [
            'controller_name' => 'BuyNowController',
            'id' => $productId,
            'deliveryArray' => $deliveryArray
        ]);
    }
}
