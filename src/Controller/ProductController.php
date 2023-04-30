<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\ProductReview;
use App\Form\AddProductFormType;
use App\Form\AddReviewFormType;
use App\Form\DeliveryFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/user/create_product', name: 'app_create_product')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // $category = new Category();
        // $category->setName('Computer Peripherals');
        // $product = new Product();
        // $entityManager->persist($category);
        // $entityManager->persist($product);
        // $entityManager->flush();
        /** @var $user User */
        $user = $this->getUser();
        $product = new Product();

        // Create product form
        $form_create_product = $this->createForm(AddProductFormType::class, $product, [
            'image_required' => true,
            'validation_groups' => ['create'],
        ]);
        $form_create_product->handleRequest($request);
        if ($user) {

            $id = $user->getId();
            if ($form_create_product->isSubmitted() && $form_create_product->isValid()) {

                $product->setName(
                    $form_create_product->get('name')->getData()
                );
                $product->setDescription(
                    $form_create_product->get('description')->getData()
                );
                $product->setPrice(
                    $form_create_product->get('price')->getData()
                );
                $product->setCategory(
                    $form_create_product->get('category')->getData()
                );
                $product->setIsPublic(
                    $form_create_product->get('is_public')->getData()
                );
                $nameCatalog = $form_create_product->get('name')->getData() . uniqid();
                $directionCatalog = 'users_data/' . $id . '/products/' . $nameCatalog;
                $filesystem = new Filesystem();
                $filesystem->mkdir($directionCatalog);
                $product->setImagesDir($nameCatalog);

                $files = $form_create_product->get('my_files')->getData();
                foreach ($files as $file) {
                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    $file->move(
                        $directionCatalog,
                        $filename
                    );
                }

                $user = $entityManager->find(User::class, $id);
                $product->setCreatedAt(new \DateTime());
                $product->setUpdatedAt(new \DateTime());
                $product->setUser($user);

                $product->setQuantity(
                    $form_create_product->get('quantity')->getData()
                );
                $product->setIsDeleted(false);
                $entityManager->persist($product);
                $entityManager->flush();
                $this->addFlash('success', 'Product added successfully!');
                $id = $product->getId();


                $url = 'add_delivery/' . $id;
                return new RedirectResponse($url);
            }
        }

        $delivery = new Delivery();
        $form_create_delivery = $this->createForm(DeliveryFormType::class, $delivery);


        return $this->render('product/index.html.twig', [
            'AddProductFormType' => $form_create_product->createView(),
            'AddDeliveryFormType' => $form_create_delivery->createView(),
        ]);
    }

    #[Route('/user/edit_product/{id}', name: 'app_edit_product')]
    public function editProduct(EntityManagerInterface $entityManager, Request $request, int $id): Response
    { {

            /** @var $user User */
            $user = $this->getUser();
            $imagesName = [];
            $product = $entityManager->getRepository(Product::class)->findOneBy(array('id' => $id));
            $userOwnerProduct = $product->getUser();

            $myProductBool = false;

            $form = $this->createForm(
                AddProductFormType::class,
                $product,
                [
                    'image_required' => false,
                    'validation_groups' => ['edit'],
                ]
            );
            $form->handleRequest($request);

            $productImagesDirection = $product->getImagesDir();
            $dir = scandir('users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection);
            foreach ($dir as $file) {
                if ($file != '.' && $file != '..') {
                    $imagesName[] = $file;
                }
            }

            $id = $user->getId();
            if ($form->isSubmitted() && $form->isValid()) {

                $product->setName(
                    $form->get('name')->getData()
                );
                $product->setDescription(
                    $form->get('description')->getData()
                );
                $product->setPrice(
                    $form->get('price')->getData()
                );
                $product->setCategory(
                    $form->get('category')->getData()
                );
                $product->setIsPublic(
                    $form->get('is_public')->getData()
                );
                $directionCatalog = 'users_data/' . $user->getId() . '/products/' . $product->getImagesDir();

                $files = $form->get('my_files')->getData();
                foreach ($files as $file) {
                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    $file->move(
                        $directionCatalog,
                        $filename
                    );
                }

                $product->setUpdatedAt(new \DateTime());

                $entityManager->persist($product);
                $entityManager->flush();

                return new RedirectResponse('/user/add_delivery/'.$product->getId());
                
            }
            return $this->render('product/edit_product.html.twig', [
                'product' => $product,
                'imagesName' => $imagesName,
                'userOwnerProduct' => $userOwnerProduct,
                'myProductBool' => $myProductBool,
                'AddProductFormType' => $form->createView(),
            ]);
        }
    }


    #[Route('/user/add_delivery/{id}', name: 'app_user_add_delivery')]
    public function addDelivery(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $deliveryArray = $entityManager->getRepository(Delivery::class)->findBy(array('product' => $id));
       
        $deliveryTypeArray = [];
        $deliveryPersonalPickupLocationArray = [];
        foreach ($deliveryArray as $element) {
            $deliveryTypeArray[] = $element->getType();
            if($element->getPersonalPickup())
            {
                $deliveryPersonalPickupLocationArray[] = $element->getPersonalPickup();
            }
        }
    
        $product = $entityManager->find(Product::class, $id);
        $time = new DateTime();
        $formData = $request->request->all();
        $count = 0;
        $addLocationArray = [];
        if ($formData) {
            $oldDeliveryArray = $entityManager->getRepository(Delivery::class)->findBy(['product' => $product]);
            foreach ($oldDeliveryArray as $delivery) {
                $entityManager->remove($delivery);
            }
           
            $entityManager->flush();

            foreach ($formData as $key => $data) {
                dump($formData);
            
                if($key == 'my_array') {echo 'arrayklucz';}
                if($key == 'parcel_locker') {echo 'parcel_locker';}
                
                if ($key == 'my_array') {
          
                    if($data[0])
                    {
                   
                    // $array = json_decode($data[0], true);
                    // $explodedArray = explode(',', $array[0]);
                    // print_r($explodedArray);
                        
                        $addLocationArray = explode(',', $data[0]);
                        $addLocationArray = str_replace(['"', '[', ']'], '', $addLocationArray);
    
                        foreach($addLocationArray as $addLocation)
                        {
                            $newDelivery = new Delivery();
                            $count++;
                            
                            $newDelivery->setType('personal_pickup');
                            $newDelivery->setPersonalPickup($addLocation);
                            $newDelivery->setProduct($product);
                            $newDelivery->setPrice(0);
                            $newDelivery->setDeliveryTime($time);
            
                            $entityManager->persist($newDelivery);
                            $entityManager->flush();
                        }
                    }

                    

                    
                } 
                else if($data !== 'personal_pickup'){
                    $newDelivery = new Delivery();
                    $newDelivery->setType($data);
                    $newDelivery->setPersonalPickup('');

                    $newDelivery->setProduct($product);
                    $newDelivery->setPrice(0);
                    $newDelivery->setDeliveryTime($time);

                    $entityManager->persist($newDelivery);
                    $entityManager->flush();
                }
              

                
             }
             return new RedirectResponse('/user');
        }

        return $this->render('product/add_delivery.html.twig', [
            'id' => $id,
            'deliveryTypeArray' => $deliveryTypeArray,
            'deliveryPersonalPickupLocationArray' => $deliveryPersonalPickupLocationArray
        ]);
    }



    #[Route('/check_product/{id}', name: 'app_check_product')]
    public function checkProduct(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $productNotFoundBool = false;
        $imagesName = [];
        $product = $entityManager->getRepository(Product::class)->findOneBy(array('id' => $id));
        
        if($product)
        {

        $userOwnerProduct = $product->getUser();
        /** @var $myUser User */
        $myUser = $this->getUser();

        $CommentsAndRatingArray = $entityManager->getRepository(ProductReview::class)->findBy(['product' => $id]);
        $myProductBool = false;
        // if User is login
        if ($myUser) {
                // Checking whether the product belongs to this user 
                if ($userOwnerProduct->getId() == $myUser->getId()) {
                    $myProductBool = true;
                    
                }
            foreach ($CommentsAndRatingArray as $key => $productReview) {
                if (is_int($key)) { // upewnij się, że to jest obiekt ProductReview
                    $upVotesCheck = $myUser->getUpVoteReviews();
                    $downVotesCheck = $myUser->getDownVoteReviews();
                    $productReview->upVotesCheck = $upVotesCheck;
                    $productReview->downVotesCheck = $downVotesCheck;
                    $CommentsAndRatingArray[$key] = $productReview;
                }

            }
        } else {

            foreach ($CommentsAndRatingArray as $key => $productReview) {
                if (is_int($key)) { // upewnij się, że to jest obiekt ProductReview
                    $upVotesCheck = 0;
                    $downVotesCheck = 0;
                    $productReview->upVotesCheck = $upVotesCheck;
                    $productReview->downVotesCheck = $downVotesCheck;
                    $CommentsAndRatingArray[$key] = $productReview;
                }
            }
        }


        // Display product images
        $productImagesDirection = $product->getImagesDir();
        $dir = scandir('users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection);
        foreach ($dir as $file) {
            if ($file != '.' && $file != '..') {
                $imagesName[] = $file;
            }
        }
        }
        else
        {
            $product['id'] = 0; 
            $userOwnerProduct = 0;
            $CommentsAndRatingArray = 0;
            $myUser = 0;
            $productNotFoundBool = true;
        }

        $myProductBool = false;

        return $this->render('product/check_product.html.twig', [
            'product' => $product,
            'imagesName' => $imagesName,
            'userOwnerProduct' => $userOwnerProduct,
            'myProductBool' => $myProductBool,
            'CommentsAndRatingArray' => $CommentsAndRatingArray,
            'myUser' => $myUser,
            'productNotFoundBool' => $productNotFoundBool

        ]);
    }

    #[Route('/user/image_remove/{dir}/{image}', name: 'app_user_image_remove')]
    public function productImageRemove(EntityManagerInterface $entityManager, Request $request, string $dir, string $image): Response
    {
        /** @var $user User */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');
        $fileManager = new Filesystem();
        $fileManager->remove('users_data/' . $user->getId() . '/products/' . $dir . '/' . $image);


        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/user/remove_product/{id}', name: 'app_user_remove_product')]
    public function removeProduct(EntityManagerInterface $entityManager, Request $request, string $id): Response
    {
        /** @var $user User */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');
        $filesystem = new Filesystem();
        $finder = new Finder();
        $product = $entityManager->getRepository(Product::class)->findOneBy(array('id' => $id));
        if ($product) {
            $dir = 'users_data/' . $user->getId() . '/products/' . $product->getImagesDir();

            if ($filesystem->exists($dir)) {
                $files = $finder->in($dir)->ignoreDotFiles(false)->files();

                foreach ($files as $file) {
                    $filesystem->remove($file->getRealPath());
                }

                $filesystem->remove($dir);
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
