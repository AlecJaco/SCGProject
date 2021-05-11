<?php
namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Security\Core\Security;

class CreateUserType extends AbstractType
{
	//Used to get current user, so we know what roles they can assign
	private $security;

	public function __construct(Security $security)
	{
	    $this->security = $security;
	}

	//Build create user form
	public function buildForm(FormBuilderInterface $builder, array $options){
		/*
		* Get list of roles that this user should be able to assign, based on their security level
		* They should be able to assign every role that is under their level
		*/
		$user = $this->security->getUser();

		$roles = ['Customer' => 'ROLE_ROLE'];
		if(in_array("ROLE_ADMIN",$user->getRoles())){
			$roles['Manager'] = 'ROLE_MANAGER';
			$roles['Owner'] = 'ROLE_OWNER';
		}
		else if(in_array("ROLE_OWNER",$user->getRoles())){
			$roles['Manager'] = 'ROLE_MANAGER';
		}

		$builder
			->add('emailAddress', EmailType::class, ['label' => 'Email Address:'])
			->add('firstName', TextType::class, ['label' => 'First Name:'])
			->add('lastName', TextType::class, ['label' => 'Last Name:'])
			->add('plainPassword', PasswordType::class, ['label' => 'Password:'])
			->add('roles', ChoiceType::class, [
				'choices' => $roles,
				'label' => 'Role'
			]);

		//Transform selected role into the array form that the Users entity class expects
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                     // transform the array to a string
                     return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                     // transform the string back to an array
                     return [$rolesString];
                }
        ));
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Users::class,
		]);
	}
}
?>