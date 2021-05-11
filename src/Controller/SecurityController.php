<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="login")
     * Login page. Allows users to sign in to their account and access the order functionality
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //Already signed in, redirect to the orders page
        if ($this->getUser()) 
        {
             return $this->redirectToRoute('orders');
        }

        //If there is an error logging in, then get the authentication error to pass to twig
        $error = $authenticationUtils->getLastAuthenticationError();
        
        //Gets the last username for this user, to make logging in easier
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', 
            [
                'last_username' => $lastUsername, 
                'error' => $error,
                'page' => 'login'
            ]);
    }

    /**
     * @Route("/logout", name="logout")
     * Logout page. Allows the user to end their current session and be re-directed to the login page
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
