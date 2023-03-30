<?php

namespace App\Controller;

use App\Entity\ProductReview;
use App\Entity\User;
use App\Entity\Product;
use DateTime;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionController extends AbstractController
{
    public function addValueToSession(Request $request, SessionInterface $session, EntityManagerInterface $entity): JsonResponse
    {

        $cartsCount = $session->get('cartsCount', 0) + 1;
        $session->set('cartsCount', $cartsCount);

        $productId = $request->request->get('productId');
        $quantity = $request->request->get('quantity');
        $cartsId = $session->get('cartsId', '');

        if ($cartsId === '') {
            $cartsId = $productId . ':' . $quantity;
        } else {
            $cartsId .= '|' . $productId . ':' . $quantity;
        }

        $session->set('cartsId', $cartsId);

        /** @var $myUser User */
        $myUser = $this->getUser();
        if ($myUser) {
            $carts = $myUser && $myUser->getCarts() ? $myUser->getCarts() . '|' . $productId . ':' . $quantity : $productId . ':' . $quantity;

            $myUser->setCarts($carts);
            $entity->persist($myUser);
            $entity->flush();
        }
        // $this->addFlash('success', 'Product successfully add to cart!');



        return new JsonResponse(['cartsCount' => $cartsCount]);
    }

    public function removeCart(Request $request, SessionInterface $session, EntityManagerInterface $entity): JsonResponse
    {
        // Change session cartsCount value
        $cartsCount = $session->get('cartsCount', 0) - 1;
        $session->set('cartsCount', $cartsCount);
        // Take data from order
        $productId = $request->request->get('productId');
        $quantity = $request->request->get('quantity');
        // Take all carts id from user session
        $cartsId = $session->get('cartsId');
        // Make array with id and quantity [id:value, id:value]
        $cartsIdArray = explode('|', $cartsId);
        // Make new array [id => value, id, value]
        $cartsIdAndQuantityArray = array();
        foreach ($cartsIdArray as $cart) {
            $keyValue = explode(':', $cart);

            $cartsIdAndQuantityArray[$keyValue[0]] = $keyValue[1];
        }

        dump($cartsIdAndQuantityArray);
        // Delete data product
        foreach ($cartsIdAndQuantityArray as $key => $value) {
            if ($key == $productId) {
                unset($cartsIdAndQuantityArray[$key]);
                break;
            }
        }

        $cartsIdAfterRemove = '';

        foreach ($cartsIdAndQuantityArray as $key => $value) {
            $cartsIdAfterRemove .= $key . ':' . $value . '|';
        }

        $cartsIdAfterRemove = rtrim($cartsIdAfterRemove, '|');
        $session->set('cartsId', $cartsIdAfterRemove);
        /** @var $myUser User */
        $myUser = $this->getUser();
        if ($myUser) {
            $myUser->setCarts($session->get('cartsId'));
            $entity->persist($myUser);
            $entity->flush();
        }
        return new JsonResponse(['cartsCount' => $cartsCount]);
    }

    public function addReviewsProduct(Request $request, EntityManagerInterface $entityManager)
    {

        $user = $this->getUser();
        $productId = $request->request->get('productId');
        $product = $entityManager->find(Product::class, $productId);


        $comment = $request->request->get('comment');
        $productId = $request->request->get('productId');
        $rating = $request->request->get('rating');
        $time = new DateTime();

        $newReview = new ProductReview();
        $newReview->setAuthor($user);
        $newReview->setProduct($product);
        $newReview->setRating($rating);
        $newReview->setComment($comment);
        $newReview->setCreatedAt($time);
        $newReview->setUpvote(0);
        $newReview->setDownvote(0);

        $entityManager->persist($newReview);
        $entityManager->flush();

        $CommentsAndRatingArray = $entityManager->getRepository(ProductReview::class)->findBy(['product' => $productId]);
        return new JsonResponse(['CommentsAndRatingArray' => $CommentsAndRatingArray]);
    }

    public function upVoteProductReview(Request $request, EntityManagerInterface $entityManager)
    {
        /** @var $myUser User */
        $myUser = $this->getUser();
        $offDownBool = false;
        $offUpBool = false;
        $offValue = 0;
        // Take data from ajax
        $reviewId = $request->request->get('reviewId');
        $type = $request->request->get('type');
        // Take this review
        $review = $entityManager->find(ProductReview::class, $reviewId);
        $currentClass = $request->request->get('currentClass');
        // If user add Like
        if (($type == 'upVote') && ($currentClass == 'vote')) {
            $newValue = $review->getUpVote() + 1;
            $review->setUpVote($newValue);
            $myUserVote = $myUser->getUpVoteReviews();
            if ($myUserVote) {
                $myUser->setUpVoteReviews($myUserVote . '|' . $reviewId);
            } else {
                $myUser->setUpVoteReviews($reviewId);
            }
            // Switch vote, undo dislike
            $myUserSwitch = $myUser->getDownVoteReviews();
            $myUserSwitch = explode('|', $myUserSwitch);
            if (in_array($reviewId, $myUserSwitch)) {
                $myUserSwitch = array_diff($myUserSwitch, array($reviewId));
                $myUserSwitch = implode('|', $myUserSwitch);
                $myUser->setDownVoteReviews($myUserSwitch);
                $offDownBool = true;
                $offValue = $review->getDownVote() - 1;
                $review->setDownVote($offValue);
            }
            // If user undo like
        } else if (($type == 'upVote') && ($currentClass == 'undoVote')) {
            $newValue = $review->getUpVote() - 1;
            $review->setUpVote($newValue);
            $myUserVote = $myUser->getUpVoteReviews();
            $undoVote = explode('|', $myUserVote);

            $newUndoVote = array_diff($undoVote, array($reviewId));
            $newUndoVote = implode('|', $newUndoVote);
            $myUser->setUpVoteReviews($newUndoVote);


            
        }

        // If user add dislike
        if (($type == 'downVote') && ($currentClass == 'vote')) {
            $newValue = $review->getDownVote() + 1;
            $review->setDownVote($newValue);
            $myUserVote = $myUser->getDownVoteReviews();
            if ($myUserVote) {
                $myUser->setDownVoteReviews($myUserVote . '|' . $reviewId);
            } else {
                $myUser->setDownVoteReviews($reviewId);
            }
            // Switch vote, undo like
            $myUserSwitch = $myUser->getUpVoteReviews();

            $myUserSwitch = explode('|', $myUserSwitch);
            if (in_array($reviewId, $myUserSwitch)) {
                $myUserSwitch = array_diff($myUserSwitch, array($reviewId));
                $myUserSwitch = implode('|', $myUserSwitch);
                $myUser->setUpVoteReviews($myUserSwitch);
                $offUpBool = true;
                $offValue = $review->getUpVote() - 1;
                $review->setUpVote($offValue);
            }
            // If user undo dislike
        } else if (($type == 'downVote') && ($currentClass == 'undoVote')) {


            $newValue = $review->getDownVote() - 1;
            $review->setDownVote($newValue);
            $myUserVote = $myUser->getDownVoteReviews();
            $undoVote = explode('|', $myUserVote);
            $newUndoVote = array_diff($undoVote, array($reviewId));
            $newUndoVote = implode('|', $newUndoVote);
            $myUser->setDownVoteReviews($newUndoVote);
        }

        // Check 


        $entityManager->persist($myUser);
        $entityManager->flush();
        $entityManager->persist($review);
        $entityManager->flush();
        return new JsonResponse(['success' => 1, 'newValue' => $newValue, 'type' => $type, 'offUpBool' => $offUpBool, 'offDownBool' => $offDownBool, 'offValue' => $offValue]);
    }
}
