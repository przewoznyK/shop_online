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
    #[Route('/buy/now', name: 'app_buy_now')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $productId = 80;
        $product = $entityManager->find(Product::class, $productId);
        
        $type = 'curier';

        $price = 10;
        $time = new DateTime();

        $addDelivery = new Delivery();
        $addDelivery->setProduct($product);
        $addDelivery->setType($type);

        $addDelivery->setPrice($price);
        $addDelivery->setDeliveryTime($time);

        $entityManager->persist($addDelivery);
        $entityManager->flush();
        return $this->render('buy_now/buyNow.html.twig', [
            'controller_name' => 'BuyNowController',
        ]);
    }
}
