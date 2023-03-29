<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\EditProfileFormType;
use App\Security\MySecurity;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserPageController extends BaseController
{
    #[Route('/user', name: 'app_user')]


    public function index(): Response
    {
        /** @var $user User */
        $user = $this->getUser();
        $id = $user->getId();
        $url = 'user/' . $id;
        return new RedirectResponse($url);
    }

    #[Route('/user/{id}', name: 'app_user_profile')]
    public function showProfile(EntityManagerInterface $entityManager, int $id, Request $request, SessionInterface $session, CartService $cartService): Response
    {

        // $cartToRemove = $request->request->get('productId');
        // $quantity = $request->request->get('quantity');

        // $cartsId = $session->get('cartsId');
        // $session->set('cartsId', '1:5|2:4|3:4');
        // $cartsId = $session->get('cartsId');
        
        //     $cartsIdArray = explode('|', $cartsId);
       
        // $result = array();
        // foreach ($cartsIdArray as $cart) {
        //   $keyValue = explode(':', $cart);
          
        //   $result[$keyValue[0]] = $keyValue[1];
        // }
        // dump($result);
        // foreach ($result as $key => $value) {
        //     if ($key == '1' && $value == '5') {
        //         unset($result[$key]);
        //     }
        // }
        
        // $cartsIdAfterRemove = '';
        // //$session->set('cartsId', implode(',', $result));
        // foreach ($result as $key => $value) {
        //     $cartsIdAfterRemove .= $key . ':' . $value . '|';
        // }
        // dump($cartsIdAfterRemove);
        // /** @var $myUser User */
        // // $myUser = $this->getUser();
        // // if ($myUser) {
        // //     $myUser->setCarts($session->get('cartsId'));
        // //     $entity->persist($myUser);
        // //     $entity->flush();
        // // }
        // //  dump($result);
        // die;



        // $string = "5:1|2:5|1:5";
        // $pairs = explode('|', $string);
        
        // $result = array();
        // foreach ($pairs as $pair) {
        //   $keyValue = explode(':', $pair);
        //   $result[$keyValue[0]] = $keyValue[1];
        // }


        // // if (($key = array_search($result, $values)) !== false) {
        // //     unset($values[$key]);
        // //     $cartsCount = $session->get('cartsCount', 0) - 1;
        // //     $session->set('cartsCount', $cartsCount);
        // // }

        // foreach ($result as $key => $value) {
        //     if ($key == '1' && $value == '5') {
        //         unset($result[$key]);
        //     }
        // }
        // dump($result);
        // die;
        /** @var $user User */
        $myUser = $this->getUser();
        $myId = $myUser->getId();

        $myPageBool = false;
        $user = $entityManager->getRepository(User::class)->find($id);
        if ($user->getID() == $myId) {
            $myPageBool = true;
        }

        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        $form = $this->createForm(EditProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user->setDescription(
                $form->get('description')->getData()
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $newAvatar = $form->get('avatar')->getData();
            $directionAvatar = 'users_data/' . $user->getId() . '/avatar/';
            $filename = 'avatar.jpg';
            $newAvatar->move(
                $directionAvatar,
                $filename
            );
        }

        //Send my offerts products data
        $productsUser = $entityManager->getRepository(Product::class)->findBy(array('user_id' => $id));

        $productImagesDirection  = array();
        $i = 0;
        foreach ($productsUser as $product) {
            $productImagesDirection[$i]['dir'] = $product->getImagesDir();
            foreach ($productImagesDirection[$i] as $images) {
                $dir = scandir('users_data/' . $user->getId() . '/products/' . $images);
                foreach ($dir as $file) {
                    if ($file != '.' && $file != '..') {
                        $productImagesDirection[$i]['images'][] = $file;
                    }
                }
            }
            $i++;
        }

        //Send my cart product data
        
        $myCarts = $session->get('cartsId');
       
        if ($myCarts) {
            $myCartsId = explode(',', $myCarts);
            $myCartsDirImages =  array();
            $myCartsInfo = array();
            $i = 0;
    
            foreach ($myCartsId as $myCartId) {
                $productUser = $entityManager->getRepository(Product::class)->find($myCartId);
                if($productUser)
                {
     $myCartsInfo[] = $productUser;
                $myCartsDirImages[$i]['dir'] = $productUser->getImagesDir();
                $ownerId = $productUser->getUserId();
                $owner = $entityManager->getRepository(User::class)->find($ownerId);
                $ownerUsername = $owner->getUsername();

                foreach ($myCartsDirImages[$i] as $images) {
                    $dir = scandir('users_data/' . $owner->getId() . '/products/' . $images);
                    foreach ($dir as $file) {
                        if ($file != '.' && $file != '..') {
                            $myCartsDirImages[$i]['images'][] = $file;
                        }
                    }
                }
                $myCartsDirImages[$i]['id'] = $ownerId;

                $i++;
                }
           
            }
        } else {
            $myCartsId = 0;
            $myCartsInfo = 0;
            $myCartsDirImages = 0;
        }
        // $i = 0;
        // $files = $productImagesDirection;
        // foreach ($productImagesDirection as $images) {
        //     $dir = scandir('users_data/' . $userID->getUsername() . '/products/' . $images);
        //     foreach ($dir as $file) {
        //         if ($file != '.' && $file != '..') {
        //             $files[$i][] = $file;
        //             $i++;
        //         }
        //     }
        // }
        // dump($files);
        $cartsCount = $cartService->getCartsCount($session);
        return $this->render('user_page/index.html.twig', [
            'username' => $user->getUsername(),
            'userId' => $user->getId(),
            'productUser' => $productsUser,
            'files' => $productImagesDirection,
            'editProfile' => $form->createView(),
            'myPageBool' => $myPageBool,
            'myCardsId' => $myCartsId,
            'myCartsInfo' => $myCartsInfo,
            'myCartsDirImages' => $myCartsDirImages,
            'cartsCount' => $cartsCount
        ]);
    }
    #[Route('/user/{id}/{productId}', name: 'app_user_profile_cart')]
    public function addCart(Request $request, int $id, $productId, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        if ($request->getMethod() == 'POST') {
            $someParametr = $request->request->get('addCart');
            /** @var $myUser User */
            $myUser = $this->getUser();
            if ($myUser) {
                if ($myUser->getCarts()) {
                    $carts = $myUser->getCarts() . ',' . $productId;
                } else {
                    $carts = $productId;
                }

                $myUser->setCarts($carts);
                $entityManager->persist($myUser);
                $entityManager->flush();

                $this->addFlash('success', 'Product successfully add to cart!');
                return new RedirectResponse('/check_product/' . $productId);
            } else {
                $carts = $productId;
                if ($session->has('cartsCount')) {
                    $cartsCount = $session->get('cartsCount') + 1;
                    $session->set('cartsCount', $cartsCount);
                    $cartsId =  $session->get('cartsId').','.$carts;
                    $session->set('cartsId', $cartsId);
                    $this->addFlash('success', 'Jest carts');


                } else {
                    $session->set('cartsCount', 1);
                    $session->set('cartsId', $carts);
                    $this->addFlash('success', 'stworzono carts');

                }
                $this->addFlash('success', 'Product successfully add to cart!');
                return new RedirectResponse('/check_product/' . $productId);
            }
        }
    }

    
}
