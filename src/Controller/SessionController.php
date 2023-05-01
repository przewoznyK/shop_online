<?php

namespace App\Controller;

use App\Entity\ProductReview;
use App\Entity\Product;
use DateTime;
use App\Entity\Delivery;
use App\Entity\OrderProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    public function deleteProduct(Request $request, EntityManagerInterface $entityManager)
    {
        $productId = $request->request->get('id');
        $product = $entityManager->getRepository(Product::class)->find($productId);
        $product->setIsDeleted(true);
        $product->setIsPublic(false);
        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse(['id' => $productId]);
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

        $myUser = $this->getUser();
        $productId = $request->request->get('productId');
        $product = $entityManager->find(Product::class, $productId);


        $comment = $request->request->get('comment');
        $productId = $request->request->get('productId');
        $rating = $request->request->get('rating');
        $time = new DateTime();

        $newReview = new ProductReview();
        $newReview->setAuthor($myUser);
        $newReview->setProduct($product);
        $newReview->setRating($rating);
        $newReview->setComment($comment);
        $newReview->setCreatedAt($time);
        $newReview->setUpvote(0);
        $newReview->setDownvote(0);

        $entityManager->persist($newReview);
        $entityManager->flush();

        /** @var $myUser User */
        $myUser = $this->getUser();

        $CommentsAndRatingArray = $entityManager->getRepository(ProductReview::class)->findBy(['product' => $productId]);
        foreach ($CommentsAndRatingArray as $key => $productReview) {
            if (is_int($key)) { // upewnij się, że to jest obiekt ProductReview
                $upVotesCheck = $myUser->getUpVoteReviews();
                $downVotesCheck = $myUser->getDownVoteReviews();
                $productReview->upVotesCheck = $upVotesCheck;
                $productReview->downVotesCheck = $downVotesCheck;
                $CommentsAndRatingArray[$key] = $productReview;
            }
        }

        $html = '';
        foreach ($CommentsAndRatingArray as $commentsAndRating) {
            $html .= '
      <section style="background-color: #e7effd;" class="reviewFull"  data="' . $commentsAndRating->getId() . '">
        <div class="container my-5 py-5 text-dark">
          <div class="row d-flex justify-content-center">
            <div class="col-md-11 col-lg-9 col-xl-7">
              <div class="d-flex flex-start mb-4">
                <img class="rounded-circle shadow-1-strong me-3"
                src="/users_data/' . $commentsAndRating->getAuthor()->getId() . '/avatar/avatar.jpg" alt="avatar" width="65"
                  height="65" />
                <div class="card w-100">
                  <div class="card-body p-4">
                    <div class="">
                      <h5>' . $commentsAndRating->getAuthor()->getUsername() . ' '; 
                        $html .="<input type='hidden' class='rating-count' value='".$commentsAndRating->getRating()."'>";

                        for ($i=0; $i<$commentsAndRating->getRating(); $i++)
                        {
                            $html .= '<i class="fas fa-star" style="color: #f8c50d;"></i>';
                        }
                      $html.= '</h5>';
            if ($commentsAndRating->getAuthor()->getId() == $myUser->getId()) {
                $html .= "<h5><i data-comment-id='" . $commentsAndRating->getId() . "' class='deleteReview fa-sharp fa-solid fa-trash position-absolute top-0 end-0 m-3 btn'></i></h5>";
            }
            $html .= ' <p class="small">' . $commentsAndRating->getCreatedAt()->format('d-m-Y H:i:s') . '</p>
                      <p>
                      ' . $commentsAndRating->getComment() . '
                      </p>

                      <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                        ';
            // if (in_array( $commentsAndRating->getId(),$commentsAndRating->upVotesCheck))
            if ((in_array($commentsAndRating->getId(), (explode('|', $commentsAndRating->upVotesCheck))))) {
                // tablica zawiera wartość 1 dla tego obiektu ProductReview

                $html .= '<i class="undoVote fas fa-thumbs-up me-1 btn" style="color: blue;" data-comment-id="' . $commentsAndRating->getId() . '" data-type="upVote">' . $commentsAndRating->getUpVote() . '</i>';
            } else {
                $html .= '<i class="vote fas fa-thumbs-up me-1 btn"  data-comment-id="' . $commentsAndRating->getId() . '" data-type="upVote">' . $commentsAndRating->getUpVote() . '</i>';
            }
            if ((in_array($commentsAndRating->getId(), (explode('|', $commentsAndRating->downVotesCheck))))) {
                $html .= '<i class="undoVote fas fa-thumbs-down me-1 btn" style="color: red;" data-comment-id="' . $commentsAndRating->getId() . '" data-type="downVote">' . $commentsAndRating->getDownVote() . '</i>';
            } else {
                $html .= '<i class="vote fas fa-thumbs-down me-1 btn"  data-comment-id="' . $commentsAndRating->getId() . '" data-type="downVote">' . $commentsAndRating->getDownVote() . '</i>';
            }
            $html .= '
                        </div>
                        <a href="#!" class=""><i class="link-muted fas fa-reply me-1"></i> Reply</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    ';
        }

        $CommentsAndRatingArray = $entityManager->getRepository(ProductReview::class)->findBy(['product' => $productId]);
        return new JsonResponse(['CommentsAndRatingArray' => $CommentsAndRatingArray, 'html' => $html]);
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



    public function removeReviewsProduct(Request $request, EntityManagerInterface $entityManager)
    {
        $reviewId = $request->request->get('reviewId');
        $reviewToDelete = $entityManager->getRepository(ProductReview::class)->find($reviewId);
        $entityManager->remove($reviewToDelete);
        $entityManager->flush();
        return new JsonResponse(['success' => 1, 'reviewId' => $reviewId]);
    }

    public function addDelivery(Request $request, EntityManagerInterface $entityManager)
    {
        $productId = $request->request->get('productId');
        $type = $request->request->get('type');
        $location = $request->request->get('location');
        $price = $request->request->get('price');
        $product = $entityManager->find(Product::class, $productId);
        $time = new DateTime();

        $addDelivery = new Delivery();
        $addDelivery->setProduct($product);
        $addDelivery->setType($type);
        $addDelivery->setPersonalPickup($location);
        $addDelivery->setPrice($price);
        $addDelivery->setDeliveryTime($time);

        $entityManager->persist($addDelivery);
        $entityManager->flush();
        $id = $addDelivery->getId();

        return new JsonResponse(['id' => $id, 'type' => $type, 'location' => $location, 'price' => $price]);
    }

    public function removeDelivery(Request $request, EntityManagerInterface $entityManager)
    {
        $deliveryId = $request->request->get('deliveryId');
        $deliveryDelete = $entityManager->getRepository(Delivery::class)->find($deliveryId);
        $entityManager->remove($deliveryDelete);
        $entityManager->flush();
        return new JsonResponse(['dziala' => $deliveryId]);
    }

    public function orderFromBuyers(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $orderId = $request->request->get('id');
        $orderStatus = $request->request->get('status');
        $orderType = $request->request->get('type');
        $order = $entityManager->getRepository(OrderProduct::class)->find($orderId);
        $done = false;
         $newStatus = '11';
        // $newText = '111';
        if ($orderStatus == 'pending') {
            $newStatus = 'progressing';

            if($orderType == 'personal_pickup')
            {
                $newText = 'Ready to pick up';
            }
            else
            {
                $newText = 'Shipped';
            }
        }
        if ($orderStatus == 'progressing')
        {
            if($orderType == 'personal_pickup')
            {
                $newStatus = 'ready_to_pick_up';
                $newText = 'Waiting for pickup';
            }
            else
            {
                $time = new DateTime();
                $order->setStartDelivery($time);
                $newStatus = 'shipped';
                $newText = 'Durning shipment';
                
            }
            // Change product quantity value
            $orderProducts = $order->getProduct();
            $orderProductExplode = explode('|', $orderProducts);
            foreach($orderProductExplode as $element)
            {
                $elementExplode = explode(':', $element);
                $product = $entityManager->getRepository(Product::class)->find($elementExplode[0]);
                $productQuantity = $product->getQuantity();
                $productQuantityChange = $productQuantity - $elementExplode[1];
                $product->setQuantity($productQuantityChange);
                $entityManager->persist($product);
                $entityManager->flush();
            }

            /** @var $myUser User */
            $myUser=$this->getUser();
            $myUser->setWallet($myUser->getWallet()+$order->getPrice());
            $done = true;
            $entityManager->persist($myUser);
            $session->set('wallet', $myUser->getWallet());
        }
        $order->setStatus($newStatus);
        $entityManager->persist($order);
        $entityManager->flush();
        return new JsonResponse(['id' => $orderStatus, 'newStatus' => $newStatus, 'newText' => $newText, 'done' => $done]);
    }

    public function myOrders(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $orderId = $request->request->get('id');
        $orderToDelete = $entityManager->getRepository(OrderProduct::class)->find($orderId);
        if($orderToDelete->getIsPaid())
        {
            /** @var $myUser User */
            $myUser = $this->getUser();
            $myUser->setWallet($myUser->getWallet()+$orderToDelete->getPrice());
            $session->set('wallet', $myUser->getWallet());
        }
        $entityManager->remove($orderToDelete);
        $entityManager->flush();
        return new JsonResponse(['id' => $orderId]);

    }


}
