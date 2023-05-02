<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductSortFormType;
use App\Service\CartService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

class IndexController extends AbstractController
{
    public function createSortForm(Request $request)
    {


        $sortOptions = [
            'price_asc' => [
                'label' => 'Cena rosnąco',
                'sort' => 'price_asc',
                'orderBy' => 'p.price',
                'direction' => 'ASC'
            ],
            'price_desc' => [
                'label' => 'Cena malejąco',
                'sort' => 'price_desc',
                'orderBy' => 'p.price',
                'direction' => 'DESC'
            ]
        ];

        $sortBy = $request->query->get('sort_by') ?? null;
    }

    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $entityManager, SessionInterface $session, Request $request)
    {


        $sortOptions = [
            'price_asc' => [
                'label' => 'Cena rosnąco',
                'sort' => 'price_asc',
                'orderBy' => 'p.price',
                'direction' => 'ASC'
            ],
            'price_desc' => [
                'label' => 'Cena malejąco',
                'sort' => 'price_desc',
                'orderBy' => 'p.price',
                'direction' => 'DESC'
            ]
        ];

        $sortBy = $request->query->get('sort_by') ?? null;
        // My carts count
        $categories = $entityManager->getRepository(Category::class)->findAll();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('is_public', true))
            ->andWhere(Criteria::expr()->gt('quantity', 0));
        $allOffer = $entityManager->getRepository(Product::class)->matching($criteria);


        if ($allOffer) {
            foreach ($allOffer as $allOfferTakeId) {
                $allOfferId[] = $allOfferTakeId->getId();
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
        } else {
            $allOfferId = 0;
            $allOfferInfo = 0;
            $allOfferDirImages = 0;
        }
        //  $cartsCount = $cartService->getCartsCount($session);
        return $this->render('index/index.html.twig', [
            'allOfferId' => $allOfferId,
            'allOfferInfo' => $allOfferInfo,
            'allOfferDirImages' => $allOfferDirImages,
            //'cartsCount' => $cartsCount,
            'categories' => $categories,
            'actualCategory' => null,
            'sortOptions' => $sortOptions,
            'sortBy' => $sortBy
        ]);
    }

    #[Route('/carts', name: 'app_carts')]
    public function carts(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        $response->headers->set('Pragma', 'no-cache');
      
        
        $myCarts = $session->get('cartsId');
        if ($myCarts) {
            $cartsIdArray = explode('|', $myCarts);
            $cartsIdAndQuantityArray = array();
            foreach ($cartsIdArray as $cart) {
                $keyValue = explode(':', $cart);
                $cartsIdAndQuantityArray[$keyValue[0]] = $keyValue[1];
            }
            $myCartsDirImages =  array();
            $myCartsInfo = array();
            $i = 0;
            foreach (array_keys($cartsIdAndQuantityArray) as $myCartId) {
                $productUser = $entityManager->getRepository(Product::class)->find($myCartId);
                if ($productUser) {
                    $myCartsInfo[] = $productUser;
                    $myCartsInfo[$i]->quantityUser = array_values($cartsIdAndQuantityArray)[$i];
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
            $myCartsInfo = [];
            $myCartsDirImages = [];
            $cartsIdAndQuantityArray = [];
        }
        usort($myCartsInfo, function ($a, $b) {
            return strcmp($a->getUserId(), $b->getUserId());
        });
        usort($myCartsDirImages, function ($a, $b) {
            return strcmp($a['id'], $b['id']);
        });
        //sort($myCartsInfo);
        return $this->render('index/carts.html.twig', [
            'myCartsIdAndQuantityArray' => $cartsIdAndQuantityArray,
            'myCartsInfo' => $myCartsInfo,
            'myCartsDirImages' => $myCartsDirImages,
        ]);
    }

    #[Route('/category/{name}', name: 'app_category')]
    public function findWithCategory(EntityManagerInterface $entityManager, string $name, Request $request)
    {
        //$sortForm = $this->createSortForm($request);


        $sortOptions = [
            'price_asc' => [
                'label' => 'Cena rosnąco',
                'sort' => 'price_asc',
                'orderBy' => 'p.price',
                'direction' => 'ASC'
            ],
            'price_desc' => [
                'label' => 'Cena malejąco',
                'sort' => 'price_desc',
                'orderBy' => 'p.price',
                'direction' => 'DESC'
            ]
        ];


        // Take all categories
        $categories = $entityManager->getRepository(Category::class)->findAll();
        // Take one category with name category
        $value = $entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        // Take value from Search form
        $minPrice = $request->query->get('minPrice') ?? null;
        $maxPrice = $request->query->get('maxPrice') ?? null;
        $searchName = $request->query->get('searchName') ?? null;
        $sortBy = $request->query->get('sort_by') ?? null;
        // Create response from database with parametrs
        $allOffer = $entityManager->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.is_public = :isPublic')
            ->andWhere('p.category_id = :categoryId');

        if (!empty($sortOptions[$sortBy]['orderBy'])) {
            $allOffer->orderBy($sortOptions[$sortBy]['orderBy'], $sortOptions[$sortBy]['direction']);
        }

        if (!empty($searchName)) {
            $allOffer->andWhere('p.name LIKE :searchTerm')
                ->setParameter('searchTerm', $searchName . '%');
        }

        if (!empty($minPrice)) {
            $allOffer->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if (!empty($maxPrice)) {
            $allOffer->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        $allOffer = $allOffer->setParameter('isPublic', true)
            ->setParameter('categoryId', $value->getId())
            ->getQuery()
            ->getResult();

        // My carts count



        if ($allOffer) {
            foreach ($allOffer as $allOfferTakeId) {
                $allOfferId[] = $allOfferTakeId->getId();
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
        } else {
            $allOfferId = 0;
            $allOfferInfo = 0;
            $allOfferDirImages = 0;
        }
        return $this->render('index/index.html.twig', [
            'allOfferId' => $allOfferId,
            'allOfferInfo' => $allOfferInfo,
            'allOfferDirImages' => $allOfferDirImages,
            'actualCategory' => $value,
            'categories' => $categories,
            'sortOptions' => $sortOptions,
            'sortBy' => $sortBy
        ]);
    }

    #[Route('/listenig', name: 'app_listening')]
    public function findByString(Request $request, EntityManagerInterface $entityManager)
    {
        $sortOptions = [
            'price_asc' => [
                'label' => 'Cena rosnąco',
                'sort' => 'price_asc',
                'orderBy' => 'p.price',
                'direction' => 'ASC'
            ],
            'price_desc' => [
                'label' => 'Cena malejąco',
                'sort' => 'price_desc',
                'orderBy' => 'p.price',
                'direction' => 'DESC'
            ]
        ];
        $sortBy = $request->query->get('sort_by') ?? null;
        // My carts count
        $categories = $entityManager->getRepository(Category::class)->findAll();
        $minPrice = $request->query->get('minPrice') ?? null;
        $maxPrice = $request->query->get('maxPrice') ?? null;
        $searchName = $request->query->get('searchName') ?? null;
        $allOffer = $entityManager->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.is_public = :isPublic')
            ->orderBy($sortOptions[$sortBy]['orderBy'], $sortOptions[$sortBy]['direction']);

        if (!empty($searchName)) {
            $allOffer->andWhere('p.name LIKE :searchTerm')
                ->setParameter('searchTerm', $searchName . '%');
        }

        if (!empty($minPrice)) {
            $allOffer->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if (!empty($maxPrice)) {
            $allOffer->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        $allOffer = $allOffer->setParameter('isPublic', true)
            ->getQuery()
            ->getResult();

        if ($allOffer) {
            foreach ($allOffer as $allOfferTakeId) {
                $allOfferId[] = $allOfferTakeId->getId();
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
        } else {
            $allOfferId = 0;
            $allOfferInfo = 0;
            $allOfferDirImages = 0;
        }
        return $this->render('index/index.html.twig', [
            'allOfferId' => $allOfferId,
            'allOfferInfo' => $allOfferInfo,
            'allOfferDirImages' => $allOfferDirImages,
            'actualCategory' => null,
            'categories' => $categories,
            'sortOptions' => $sortOptions,
            'sortBy' => $sortBy
        ]);
    }

    #[Route('/my_sell', name: 'app_my_sell')]
    public function mySell(Request $request, EntityManagerInterface $entityManager)
    {   
        $myOrdersArray = $entityManager->getRepository(OrderProduct::class)->findBy(array('owner'=> $this->getUser()));
        
        $i=0;
        $myOrdersArray = $entityManager->getRepository(OrderProduct::class)->findBy(array('buyer'=> $this->getUser()));

        foreach ($myOrdersArray as $element)
        {
         $count = 0;
           if(str_contains($element->getProduct(),'|'))
           {
                $elementExplodeArray = explode('|', $element->getProduct());
                foreach($elementExplodeArray as $elementExplode)
                {
                    $element->dir[$count] = $elementExplode;
                    $count++;
                }
                
           }
           else
           {    
                $element->dir[0] = $element->getProduct();
           }                
           $i++; 
        }
        foreach($myOrdersArray as $element)
        {
            $count=0;
            foreach($element->dir as $direction)
            {
                $direction = explode(':', $direction);
                $product = $entityManager->getRepository(Product::class)->find($direction[0]);
                $element->productId[$count] = $product->getId();
                unset($element->dir[$count]);

              
     
                $dir = scandir('users_data/' . $element->getOwner()->getId() . '/products/' .  $product->getImagesDir());
                foreach ($dir as $file) {
           
                    {
                        if ($file != '.' && $file != '..') {  
                            
                        $element->dir[$product->getImagesDir()] = $file;
                        break;
                    }
                    }
                }
                
                $count++;
            }
        }
       

        $myOrdersArrayPending = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'pending') {
                $myOrdersArrayPending[] = $element;
             
                unset($myOrdersArray[$key]);
            }
        }
        
        $myOrdersArrayProgressing = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'progressing') {
                $myOrdersArrayProgressing[] = $element;
             
                unset($myOrdersArray[$key]);
            }
        }

        $myOrdersArrayShipped= array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'shipped') {
                $myOrdersArrayShipped[] = $element;
             
                unset($myOrdersArray[$key]);
            }
        }

             $myOrdersArrayWaitingForFeedback= array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'ready_to_pick_up' || $element->getStatus() == 'email_sent') {
                $myOrdersArrayWaitingForFeedback[] = $element;
             
                unset($myOrdersArray[$key]);
            }
        }
        $myOrdersArrayDone = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getFeedback() == 'ok') {
                $myOrdersArrayDone[] = $element;       
                unset($myOrdersArray[$key]);
            }
        }
        
        $myOrdersArrayProblem = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'problem') {
                $myOrdersArrayProblem[] = $element; 
                unset($myOrdersArray[$key]);
            }
        }

        return $this->render('index/my_sell.html.twig',[
            'myOrdersArray' => $myOrdersArray,
            'myOrdersArrayPending' => $myOrdersArrayPending,
            'myOrdersArrayProgressing' => $myOrdersArrayProgressing,
            'myOrdersArrayShipped' => $myOrdersArrayShipped,
            'myOrdersArrayWaitingForFeedback' => $myOrdersArrayWaitingForFeedback,
            'myOrdersArrayDone' => $myOrdersArrayDone,
            'myOrdersArrayProblem' => $myOrdersArrayProblem,
        ]);
    }

    #[Route('/my_orders', name: 'app_my_orders')]
    public function myOrders(Request $request, EntityManagerInterface $entityManager)
    {   

        $i=0;
        $myOrdersArray = $entityManager->getRepository(OrderProduct::class)->findBy(array('buyer'=> $this->getUser()));

        foreach ($myOrdersArray as $element)
        {
         $count = 0;
           if(str_contains($element->getProduct(),'|'))
           {
                $elementExplodeArray = explode('|', $element->getProduct());
                foreach($elementExplodeArray as $elementExplode)
                {
                    $element->dir[$count] = $elementExplode;
                    $count++;
                }
                
           }
           else
           {    
                $element->dir[0] = $element->getProduct();
           }                
           $i++; 
        }
        foreach($myOrdersArray as $element)
        {
            $count=0;
            foreach($element->dir as $direction)
            {
                $direction = explode(':', $direction);
                $product = $entityManager->getRepository(Product::class)->find($direction[0]);
                $element->productId[$count] = $product->getId();
                unset($element->dir[$count]);

              
     
                $dir = scandir('users_data/' . $element->getOwner()->getId() . '/products/' .  $product->getImagesDir());
                foreach ($dir as $file) {
           
                    {
                        if ($file != '.' && $file != '..') {  
                            
                        $element->dir[$product->getImagesDir()] = $file;
                        break;
                    }
                    }
                }
                
                $count++;
            }
        }

        usort($myOrdersArray, function ($a, $b) {
            $status_order = [
                "pending" => 0,
                "progressing" => 1,
                "shipped" => 2,
                "ready_to_pick_up" => 3,
                "email_sent" => 4,
                "done" => 5
            ];
            $a_val = $status_order[$a->getStatus()] ?? 9999; // Domyślna wartość dla nieznanych statusów
            $b_val = $status_order[$b->getStatus()] ?? 9999;
            return $a_val - $b_val;
        });

        $myOrdersArrayDone = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getFeedback() == 'ok') {
                $myOrdersArrayDone[] = $element;       
                unset($myOrdersArray[$key]);
            }
        }

        $myOrdersArrayProblem = array(); 
        foreach($myOrdersArray as $key => $element) {
            if($element->getStatus() == 'problem') {
                $myOrdersArrayProblem[] = $element; 
                unset($myOrdersArray[$key]);
            }
        }

        return $this->render('index/my_orders.html.twig',[
            'myOrdersArray' => $myOrdersArray,
            'myOrdersArrayDone' => $myOrdersArrayDone,
            'myOrdersArrayProblem' => $myOrdersArrayProblem,
        ]);
    }
    #[Route('/my_wallet', name: 'app_my_wallet')]
    public function myWallet(Request $request, EntityManagerInterface $entityManager)
    {

        return $this->render('index/my_wallet.html.twig',[

        ]);
    }
}
