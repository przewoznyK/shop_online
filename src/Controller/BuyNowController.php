<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Form\OrderProductFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BuyNowController extends AbstractController
{

    #[Route('/buy_now', name: 'app_buy_now')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {

        // Take all data from form
        $allData = $request->request->all();
        $productIdAndQuantityArray = array();
        $count = 0;
        foreach ($allData['quantity'] as $key => $value) {
            $newItem = array('product_id' => $allData['product_id'][$count], 'quantity' => $value);
            $productIdAndQuantityArray[] = $newItem;
            $count++;
        }

        // Splitting into smaller arrays id and quantity and next loop for display images products

               
            if ($productIdAndQuantityArray) {
                foreach ($productIdAndQuantityArray as $allOfferTakeId) {
                    $allOfferId[] = $allOfferTakeId['product_id'];
                }
                $allOfferDirImages =  array();
                $allOfferInfo = array();
                $i = 0;
                foreach ($allOfferId as $myCartId) {
                    $productUser = $entityManager->getRepository(Product::class)->find($myCartId);
                    $allOfferInfo[] = $productUser;

                    $allOfferDirImages[$i]['dir'] = $productUser->getImagesDir();
                    $owner = $productUser->getUser();
                    foreach ($allOfferDirImages[$i] as $images) {
                        $dir = scandir('users_data/' . $owner->getId() . '/products/' . $images);
                        foreach ($dir as $file) {
                            if ($file != '.' && $file != '..') {
                                $allOfferDirImages[$i]['images'][] = $file;
                            }
                        }
                    }
                    $allOfferDirImages[$i]['id'] = $owner->getId();

                    $i++;
                }
            } 
        
       // dd($allOfferDirImages);
        $newOrder = new OrderProduct();
        $form_create_order_product = $this->createForm(OrderProductFormType::class, $newOrder);
        
        $form_create_order_product->handleRequest($request);
        if($form_create_order_product->isSubmitted() && $form_create_order_product->isValid())
        {
         
        }

        return $this->render('buy_now/buyNow.html.twig', [
            'controller_name' => 'BuyNowController',
           // 'id' => $productId,
            'productIdAndQuantityArray' => $productIdAndQuantityArray,
            'allOfferInfo' => $allOfferInfo,
            'allOfferDirImages' => $allOfferDirImages,
            'OrderProductFormType' => $form_create_order_product->createView(),
        ]);
    }
}
