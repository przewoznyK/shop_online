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
    public function index(EntityManagerInterface $entityManager, Request $request, Filesystem $filesystem): Response
    {
        /** @var $myUser User */
        $myUser = $this->getUser();
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $id = $myUser->getId();
        $product = new Product();

        // Create product form
        $form_create_product = $this->createForm(AddProductFormType::class, $product);
        $form_create_product->handleRequest($request);

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
                $nameCatalog = str_replace(' ' , '_',$form_create_product->get('name')->getData() . uniqid());
                $directionCatalog = 'users_data/' . $id . '/products';
                $files = scandir($directionCatalog.'/temp');
                if (count($files) <= 3) {
                    $filesystem->copy('tools/no-image.png', $directionCatalog.'/temp/no-image.png');
                    $filesystem->copy('tools/no-image.png', $directionCatalog.'/temp/main/no-image.png');
                }
                $filesystem->rename($directionCatalog.'/temp', $directionCatalog.'/'.$nameCatalog);
                $product->setImagesDir($nameCatalog);

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

            $nameCatalog = 'temp';
            $directionCatalog = 'users_data/' . $id . '/products/' . $nameCatalog;
    
            if ($filesystem->exists($directionCatalog)) {
                $filesystem->remove($directionCatalog);
            }
                    $filesystem->mkdir($directionCatalog);
                    $filesystem->mkdir($directionCatalog.'/main');
        return $this->render('product/create_product.html.twig', [
            'AddProductFormType' => $form_create_product->createView(),
        ]);
    }

    #[Route('/user/edit_product/{id}', name: 'app_edit_product')]
    public function editProduct(EntityManagerInterface $entityManager, Request $request, int $id): Response
    { 
        {
            /** @var $myUser User */
            $myUser = $this->getUser();
            $imagesName = [];
            $mainImageName;
            $product = $entityManager->getRepository(Product::class)->findOneBy(array('id' => $id));
            $userOwnerProduct = $product->getUser();
          
            if (!$this->getUser() ) {
                return $this->redirectToRoute('app_login');
            }
            if ($userOwnerProduct->getId() != $myUser->getId()) {
                return $this->redirectToRoute('app_index');
            }

            $myProductBool = false;

            $form = $this->createForm(AddProductFormType::class, $product);
            $form->handleRequest($request);

            $productImagesDirection = $product->getImagesDir();
            $dir = scandir('users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection);
            foreach ($dir as $file) {
                $filePath = 'users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection . '/' . $file;
                if ($file != '.' && $file != '..' && is_file($filePath)) {
                    $imagesName[] = $file;
                }
            }
            $dir = scandir('users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection.'/main');
            foreach ($dir as $file) {
                $filePath = 'users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection . '/' . $file;
                if ($file != '.' && $file != '..' && is_file($filePath)) {
                    $mainImageName = $file;
                }
            }
            $id = $myUser->getId();
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
                $product->setUpdatedAt(new \DateTime());

                $entityManager->persist($product);
                $entityManager->flush();

                return new RedirectResponse('/user/add_delivery/'.$product->getId());
                
            }
            return $this->render('product/edit_product.html.twig', [
                'product' => $product,
                'imagesName' => $imagesName,
                'mainImageName' => $mainImageName,
                'userOwnerProduct' => $userOwnerProduct,
                'myProductBool' => $myProductBool,
                'AddProductFormType' => $form->createView(),
            ]);
        }
    }


    #[Route('/user/add_delivery/{id}', name: 'app_user_add_delivery')]
    public function addDelivery(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        /** @var $myUser User */
        $myUser = $this->getUser();
        $product = $entityManager->find(Product::class, $id);

        $userOwnerProduct = $product->getUser();

        if (!$this->getUser() ) {
            return $this->redirectToRoute('app_login');
        }
        if ($userOwnerProduct->getId() != $myUser->getId()) {
            return $this->redirectToRoute('app_index');
        }

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
                
                if ($key == 'my_array') {
          
                    if($data[0])
                    {
                        
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
                if (is_int($key)) {
                    $upVotesCheck = $myUser->getUpVoteReviews();
                    $downVotesCheck = $myUser->getDownVoteReviews();
                    $productReview->upVotesCheck = $upVotesCheck;
                    $productReview->downVotesCheck = $downVotesCheck;
                    $CommentsAndRatingArray[$key] = $productReview;
                }

            }
        } else {

            foreach ($CommentsAndRatingArray as $key => $productReview) {
                if (is_int($key)) { 
                    $upVotesCheck = 0;
                    $downVotesCheck = 0;
                    $productReview->upVotesCheck = $upVotesCheck;
                    $productReview->downVotesCheck = $downVotesCheck;
                    $CommentsAndRatingArray[$key] = $productReview;
                }
            }
        }
        // Take delivery options
        $deliveryArray = $entityManager->getRepository(Delivery::class)->findBy(['product' => $product->getId()]);
        

        // Display product images
        $productImagesDirection = $product->getImagesDir();
       
        $dir = scandir('users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection);
        foreach ($dir as $file) {
            $filePath = 'users_data/' . $userOwnerProduct->getId() . '/products/' . $productImagesDirection . '/' . $file;
            if ($file != '.' && $file != '..' && is_file($filePath)) {
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
            $deliveryArray = 0;
        }
        
        $myProductBool = false;

        return $this->render('product/check_product.html.twig', [
            'product' => $product,
            'imagesName' => $imagesName,
            'userOwnerProduct' => $userOwnerProduct,
            'myProductBool' => $myProductBool,
            'CommentsAndRatingArray' => $CommentsAndRatingArray,
            'myUser' => $myUser,
            'productNotFoundBool' => $productNotFoundBool,
            'deliveryArray' => $deliveryArray

        ]);
    }

    #[Route('/user/image_remove/{dir}/{image}', name: 'app_user_image_remove')]
    public function productImageRemove(Request $request, string $dir, string $image): Response
    {
        try{
                    /** @var $user User */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');
        $fileManager = new Filesystem();
        $fileManager->remove('users_data/' . $user->getId() . '/products/' . $dir . '/' . $image);


        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
        } catch(\Exception $e) {
            $this->addFlash('error', 'Wystąpił błąd. Spróbuj ponownie później.');
            return $this->redirectToRoute('app_index');        }

    }
}
