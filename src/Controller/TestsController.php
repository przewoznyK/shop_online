<?php

namespace App\Controller;

use App\Entity\OrderProduct;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestsController extends AbstractController
{
    #[Route('/tests', name: 'app_tests')]
    public function sendEmail(EntityManagerInterface $entityManager): Response
    {
        // $order= $entityManager->getRepository(OrderProduct::class)->find(23);
        // $orderProducts = $order->getProduct();
        // $orderProductExplode = explode('|', $orderProducts);
        // foreach($orderProductExplode as $element)
        // {
        //     $elementExplode = explode(':', $element);
        //     $product = $entityManager->getRepository(Product::class)->find($elementExplode[0]);
        //     $productQuantity = $product->getQuantity();
        //     $productQuantityChange = $productQuantity - $elementExplode[1];
        //     $product->setQuantity($productQuantityChange);
        //     $entityManager->persist($product);
        //     $entityManager->flush();
        // }

        // die();
        // $allData = $request->request->all();
        // foreach ($allData['product_id'] as $key => $product_id) {
        //     $result[$product_id] = array(
        //         "value" => $allData['value'][$key]
        //     );
        // }
        

        // wypisanie wszystkich danych na ekran
        return $this->render('tests/test1.html.twig', [
            

        ]);
    }
}
