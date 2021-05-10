<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersListController extends AbstractController
{
    /**
	* @Route("/users-list", name="users-list")
	*/
    public function usersList(): Response
    {
		$this->denyAccessUnlessGranted('ROLE_MANAGER');

		//Construct the 'WHERE' section of the query to only get users who are below our privelege level
		$user = $this->getUser();
		$roles = [];
		if(in_array('ROLE_ADMIN',$user->getRoles())){
			$roles[] = 'u.roles LIKE \'%ROLE_OWNER%\'';
			$roles[] = 'u.roles LIKE \'%ROLE_MANAGER%\'';
			$roles[] = 'u.roles LIKE \'%ROLE_USER%\'';
		}
		if(in_array('ROLE_OWNER',$user->getRoles())){
			$roles[] = 'u.roles LIKE \'%ROLE_MANAGER%\'';
			$roles[] = 'u.roles LIKE \'%ROLE_USER%\'';
		}
		if(in_array('ROLE_MANAGER',$user->getRoles())){
			$roles[] = 'u.roles LIKE \'%ROLE_USER%\'';
		}
		$whereQuery = count($roles) > 0 ? "WHERE ".implode(" OR ",$roles): "";

    	//Get all information for users we are allowed to view
		$query =
			'
			SELECT 
				u.user_id, u.first_name, u.last_name, u.email_address,u.roles,
					(SELECT 
						COUNT(*) 
					FROM 
						orders o 
					WHERE 
						o.user_id = u.user_id
					) as orders,
					(SELECT 
						COUNT(*) 
					FROM 
						cards c 
					INNER JOIN 
						orders o on o.order_id = c.order_id 
					WHERE o.user_id = u.user_id
					) as cards
			FROM 
				users u
			'.$whereQuery.'
			ORDER BY
				u.user_id
			';
		$entityManager = $this->getDoctrine()->getManager();
		$statement = $entityManager->getConnection()->prepare($query);
		$statement->execute();
		$users = $statement->fetchAll();

        return $this->render('users_list/index.html.twig', [
        		'page' => 'users-list',
        		'users' => $users
        	]);
    }
}
