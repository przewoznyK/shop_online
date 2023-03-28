<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Form\AddProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
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
        $form = $this->createForm(AddProductFormType::class, $product);
        $form->handleRequest($request);
        if ($user) {
            $id = $user->getId();
            if ($form->isSubmitted() && $form->isValid()) {
                $product->setName(
                    $form->get('category')->getData()
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

                $product->setCreatedAt(new \DateTime());
                $product->setUpdatedAt(new \DateTime());
                $product->setUserId($id);
                $product->setCategoryId(1);
                $entityManager->persist($product);
                $entityManager->flush();
            }
        }


        return $this->render('product/index.html.twig', [
            'AddProductFormType' => $form->createView(),
        ]);
    }
}
