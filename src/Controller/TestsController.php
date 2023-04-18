<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestsController extends AbstractController
{
    #[Route('/tests', name: 'app_tests')]
    public function sendEmail(): Response
    {

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
