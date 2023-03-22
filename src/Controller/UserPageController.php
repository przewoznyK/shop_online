<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\MySecurity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
    public function showProfile(EntityManagerInterface $entityManager, int $id): Response
    {
        $userID = $entityManager->getRepository(User::class)->find($id);
        if (!$userID) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }
        return $this->render('user_page/index.html.twig',[
            'username' => $userID->getUsername(),
        ]);
    }
}
