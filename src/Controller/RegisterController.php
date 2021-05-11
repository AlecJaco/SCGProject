<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\Users;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
	/**
	* @Route("/register", name="view register")
	* Register page. Allows users to signup for an account
	*/
	public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
	{
		//If logged in already, then go to orders
        if ($this->getUser()) 
        {
             return $this->redirectToRoute('orders');
        }

		//Create the register form
		$user = new Users();
		$form = $this->createForm(UserType::class, $user);

		//When user posts the register form, this section will handle it
		$form->handleRequest($request);

		//Potential errors
		$passwordError = "";
		$emailFound = "";

		if($form->isSubmitted() && $form->isValid())
		{
			//Make sure password passes all constraints
			$passwordError = $user->checkPassword();
			if($passwordError == "")
			{
				//Hash password
				$user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
				//Add normal user role
				$user->addRole("ROLE_USER");
				
				//Check if this email already exists in the database
				$query =
					'
					SELECT 
						COUNT(*) as count
					FROM 
						users
					WHERE
						email_address = :email
					';
				$entityManager = $this->getDoctrine()->getManager();
				$statement = $entityManager->getConnection()->prepare($query);
				$statement->bindValue('email',$user->getEmailAddress());
				$statement->execute();
				$rows = intval($statement->fetch()['count']);
				//Email address not found, create user
				if($rows == 0)
				{
					//Save user by adding them to the DB
					$entityManager->persist($user);
					$entityManager->flush();
            		
            		//Automatically logs user in and creates session on register
            		$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
			        $this->container->get('security.token_storage')->setToken($token);
			        $this->container->get('session')->set('_security_main', serialize($token));

					return $this->redirectToRoute('orders');
				}
				else
				{
					$emailFound = "This email address is already taken, please enter a new one";
				}
			}
		}

        return $this->render('register/index.html.twig', [
        		'form' => $form->createView(),
        		'passwordError' => $passwordError,
        		'emailFound' => $emailFound,
        		'page' => 'register'
        	]);
	}
}
