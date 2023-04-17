<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\User;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Form\OrderProductFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BuyNowController extends AbstractController
{

    #[Route('/buy_now', name: 'app_buy_now')]
    public function index(EntityManagerInterface $entityManager, SessionInterface $session, Request $request): Response
    {
        $newOrder = new OrderProduct();
        $form_create_order_product = $this->createForm(OrderProductFormType::class, $newOrder);

        $form_create_order_product->handleRequest($request);
        if ($form_create_order_product->isSubmitted() && $form_create_order_product->isValid()) {
            // Request data
            $allData = $request->request->all();
            $ownerId = $request->request->get('ownerId');
            $deliveryType = $request->request->get('delivery-type-checkbox');
            $owner = $entityManager->find(User::class, $ownerId);
            $summary = $request->request->get('summary');
            $paymentMethod = $request->request->get('payment-method');
            $time = new DateTime();
            $idAndQuantityArray = $allData['id-and-quantity'];

            // Delete carts from order
            /** @var $myUser User */
            $myUser=$this->getUser();
            $myCarts=$myUser->getCarts();
            $myCartsExplode = explode('|', $myCarts);
            foreach($myCartsExplode as $element) {
                $explodedElement = explode(':', $element);
                if (count($explodedElement) == 2) {
                    $newCarts[$explodedElement[0]] = $explodedElement[1];
                }
            }

            foreach($idAndQuantityArray as $element)
            {
                $deleteFromCarts = explode(':', $element);
                if (count($deleteFromCarts) == 2) {
                    $deleteCarts[$deleteFromCarts[0]] = $deleteFromCarts[1];
                }
            }
            foreach($deleteCarts as $key => $element) {
                unset($newCarts[$key]);
            }

            // Make final form to database
            $newCartsFinal = [];
            foreach($newCarts as $key => $value)
            {
                $newCartsFinal[] = $key . ':' . $value;
            }


            

            $newCartsFinal = implode('|', $newCartsFinal);
            // Change session value
            $session->set('cartsId', $newCartsFinal);
            $session->set('cartsCount', count($newCarts));
            $productsOrder = implode("|",$idAndQuantityArray);
            //Add data to newOrder
            $newOrder->setOwner($owner);
            $newOrder->setBuyer($this->getUser());
            $newOrder->setName(
                $form_create_order_product->get('name')->getData()
            );
            $newOrder->setLastName(
                $form_create_order_product->get('last_name')->getData()
            );
            $newOrder->setEmail(
                $form_create_order_product->get('email')->getData()
            );
            $newOrder->setPhoneNumber(
                $form_create_order_product->get('phone_number')->getData()
            );
            $newOrder->setComment(
                $form_create_order_product->get('comment')->getData()
            );
            $newOrder->setDeliveryType($deliveryType);
            $newOrder->setFinalLocation(
                $form_create_order_product->get('final_location')->getData()
            );
            $newOrder->setAddress(
                $form_create_order_product->get('address')->getData()
            );
            $newOrder->setPrice($summary);
            $newOrder->setProduct($productsOrder);
            $newOrder->setCreateIn($time);
            $myUser->setCarts($newCartsFinal);
            $newOrder->setPaymentMethod($paymentMethod);
            $newOrder->setStatus('pending');
            if($paymentMethod=='My wallet'){
                $newOrder->setIspaid(true);
                $myUser->setWallet($myUser->getWallet()-$summary);
            }
            else{$newOrder->setIspaid(false);}
            $entityManager->persist($newOrder);
            $entityManager->flush();
            $this->addFlash('success', 'Product ordered!');
            return new RedirectResponse('/user');
        }
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
