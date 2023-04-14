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
        // Create new array for product ID and quantity
        $productIdAndQuantityArray = array();
        $count = 0;
        $productCount = 0;
        // Add for new add variable with data
        foreach ($allData['quantity'] as $value) {
            $newItem = array('product_id' => $allData['product_id'][$count], 'quantity' => $value);
            $productIdAndQuantityArray[] = $newItem;
            $count++;
        }
        // If array have data create 2 new array with direction images and info products
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
        // Take all avalible delivery
        $deliveryArray = [];
        $count = 0;
        foreach ($productIdAndQuantityArray as $offerInfo) {
            $deliveryType = $entityManager->getRepository(Delivery::class)->findBy(array('product' => $offerInfo['product_id']));
            foreach ($deliveryType as $delivery) {
                $deliveryArray[$count]['type'] = $delivery->getType();

                $deliveryArray[$count]['personal_pickup'] = $delivery->getPersonalPickup();

                $count++;
            }
            $productCount++;
        }

        dump($deliveryArray);
        dump($productCount);
        if ($productCount > 1) {
            foreach ($deliveryArray as $delivery) {
                $deliveryTypeArray[] = $delivery['type'];
                $deliveryPersonalPickupLocation[] = $delivery['personal_pickup'];
            }
            $counts = array_count_values(array_map('json_encode', $deliveryArray));
            $duplicates = array_filter($deliveryArray, function ($value) use ($counts, $productCount) {
                return $counts[json_encode($value)] >= $productCount;
            });
            $avalibleDelivery = array_intersect_key($duplicates, array_unique(array_map('json_encode', $duplicates)));
        } else {
            $avalibleDelivery = $deliveryArray;
        }

        // $countValues = array_count_values($deliveryTypeArray);
        // dump($countValues);
        // $avalibleDelivery = array_filter($countValues, function($value){
        //     return $value > 1;
        // });
        // dd($deliveryPersonalPickupLocation);

        $newOrder = new OrderProduct();
        $form_create_order_product = $this->createForm(OrderProductFormType::class, $newOrder);

        $form_create_order_product->handleRequest($request);
        if ($form_create_order_product->isSubmitted() && $form_create_order_product->isValid()) {
        }

        return $this->render('buy_now/buyNow.html.twig', [
            'controller_name' => 'BuyNowController',
            // 'id' => $productId,
            'productIdAndQuantityArray' => $productIdAndQuantityArray,
            'allOfferInfo' => $allOfferInfo,
            'allOfferDirImages' => $allOfferDirImages,
            'avalibleDelivery' => $avalibleDelivery,
            'OrderProductFormType' => $form_create_order_product->createView(),
        ]);
    }
}
