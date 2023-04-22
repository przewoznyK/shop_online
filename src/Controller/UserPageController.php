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

class UserPageController extends AbstractController
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
    public function showProfile(EntityManagerInterface $entityManager, int $id, Request $request, SessionInterface $session): Response
    {
        $myPageBool = false;
        $user = $entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }
        /** @var $user User */
        $myUser = $this->getUser();
        if ($myUser) {
            $myId = $myUser->getId();
            if ($user->getId() == $myId) {
                $myPageBool = true;
            }
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
            if($newAvatar)
            {
                       $directionAvatar = 'users_data/' . $user->getId() . '/avatar/';
            $filename = 'avatar.jpg';
            $newAvatar->move(
                $directionAvatar,
                $filename
            );
            }
     
        }

        //Send my offerts products data
        $productsUser = $entityManager->getRepository(Product::class)->findBy(array('user_id' => $id, 'is_deleted' => false));

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
                if ($productUser && !$productUser->getIsDeleted()) {
                    $myCartsInfo[] = $productUser;
                    $myCartsDirImages[$i]['dir'] = $productUser->getImagesDir();

                    $owner = $productUser->getUser();

                    foreach ($myCartsDirImages[$i] as $images) {
                        $dir = scandir('users_data/' . $owner->getId() . '/products/' . $images);
                        foreach ($dir as $file) {
                            if ($file != '.' && $file != '..') {
                                $myCartsDirImages[$i]['images'][] = $file;
                            }
                        }
                    }
                    $myCartsDirImages[$i]['id'] = $owner->getId();

                    $i++;
                }
            }
        } else {
            $myCartsId = 0;
            $myCartsInfo = 0;
            $myCartsDirImages = 0;
        }

        return $this->render('user_page/profile.html.twig', [
            'username' => $user->getUsername(),
            'description' => $user->getDescription(),
            'userId' => $user->getId(),
            'productUser' => $productsUser,
            'files' => $productImagesDirection,
            'editProfile' => $form->createView(),
            'myPageBool' => $myPageBool,
            'myCardsId' => $myCartsId,
            'myCartsInfo' => $myCartsInfo,
            'myCartsDirImages' => $myCartsDirImages,
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
                    $cartsId =  $session->get('cartsId') . ',' . $carts;
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
    #[Route('/delete_account', name: 'app_delete_account')]
    public function deleteAccount(EntityManagerInterface $entityManager)
    {
        /** @var $myUser User */
        $myUser = $this->getUser();

        $productArray = $entityManager->getRepository(Product::class)->findBy(['user' =>$myUser]);
        foreach ($productArray as $product)
        {
            $product->setIsDeleted(true);
            $product->setIsPublic(false);
            $entityManager->persist($product);
        }
        $now = new \DateTimeImmutable();
        $myUser->setDeletedAt($now);
        $entityManager->persist($myUser);
        $entityManager->flush();
        session_destroy();
        return $this->render('user_page/account_successfully_deleted.html.twig');
    }
}
