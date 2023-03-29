<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function getGlobalVariables()
    {
        return [
            // 'cartItemCount' => $this->cartService->getCartsArray(),
        ];
    }
}
