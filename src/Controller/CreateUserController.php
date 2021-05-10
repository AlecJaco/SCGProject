<?php

namespace App\Controller;

use App\Form\CreateUserType;
use App\Entity\Users;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserController extends AbstractController
{
	/**
	* @Route("/create-user", name="create-user")
	*/
	public function createUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->denyAccessUnlessGranted('ROLE_MANAGER');

		//Create the new user form
		$user = new Users();
		$form = $this->createForm(CreateUserType::class, $user);

		//When user posts the create user form, this section will handle it
		$form->handleRequest($request);

		//Potential errors
		$passwordError = "";
		$emailFound = "";

		//Message that should be returned when new user is created
		$successMsg = "";

		if($form->isSubmitted() && $form->isValid())
		{
			//Make sure password passes all constraints
			$passwordError = $user->checkPassword();
			if($passwordError == "")
			{
				//Hash password
				$user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));

				//Check if this user already exists in the database
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

					$successMsg = "Successfully created new user!";

					//Reset form values so that more users can be created
					unset($user);
					unset($form);
					$user = new Users();
					$form = $this->createForm(CreateUserType::class, $user);
				}
				else
				{
					$emailFound = "This email address is already taken, please enter a new one";
				}
			}
		}

        return $this->render('create_user/index.html.twig', [
        		'form' => $form->createView(),
        		'passwordError' => $passwordError,
        		'emailFound' => $emailFound,
        		'page' => 'create-user',
        		'successMsg' => $successMsg
        	]);
	}
}
