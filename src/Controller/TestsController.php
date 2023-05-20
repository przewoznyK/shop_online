<?php

namespace App\Controller;

use App\Entity\OrderProduct;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
class TestsController extends AbstractController
{
    #[Route('/tests', name: 'app_tests')]
    public function sendEmail(EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        
        $orderArray = [];
        $orderArray[0]['name'] = 'Kamil';
        $orderArray[0]['lastName'] = 'Panek';
        $orderArray[0]['finalLocation'] = 'Poznan wilda 34';
        $orderArray[0]['deliveryType'] = 'personal_pickup';
        $orderArray[0]['price'] = '200';
        
        $productsInfoArray = [];
        
        $productsInfoArray[0]['images'] = '/users_data/37/products/wszystkie dostawy645c162a7733e/645c15f0029b0.jpg';
        $productsInfoArray[0]['productPrice'] = 100;
        $productsInfoArray[0]['productQuantity'] = 2;
        $productsInfoArray[0]['productName'] = 'nazwa produktu';
        
        $feedbackUrl = 'blablabla';

        return $this->render('tests/test1.html.twig', [
            'orderArray' => $orderArray,
            'productsInfoArray' => $productsInfoArray,
            'feedbackUrl' => $feedbackUrl

        ]);
    }
}
