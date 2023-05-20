<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(MailerInterface $mailer, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
            
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_USER']);
            $user->setWallet(500);
            $token = uniqid('Token');
            $user->setToken($token);
            $user->setVerify(0);
            $entityManager->persist($user);
            $entityManager->flush();
            $filesystem = new Filesystem();

            $url = $params->get('app.url');
            $verify = $url . '/verify_email/' . $token;
            $email = (new TemplatedEmail())
                ->from('symfony_project_shop@proton.me')
                ->to($user->getEmail())
                ->subject('Email verification ')
                ->htmlTemplate('html_templates/email_token_template.html.twig')
                ->context([
                    'name' => $token,
                    'verify' => $verify,
                ]);
            $mailer->send($email);
            $this->addFlash('success', 'Verify your email.');

            $filesystem->copy('tools/avatar.jpg', 'users_data/'.$user->getId().'/avatar/avatar.jpg');
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify_email/{token}', name: 'app_verify_email')]
    public function verify(string $token, EntityManagerInterface $entityManager)
    {
        $verify = false;
        $user = $entityManager->getRepository(User::class)->findOneBy(['token' => $token]);
      
        if($user)
        {
            $user->setVerify(1);
            $verify = true;
        }
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->render('registration/verify_email.html.twig', [
            'verify' => $verify,
        ]);
    }
}
