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

class UserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options){
		$builder
			->add('emailAddress', EmailType::class, ['label' => 'Email Address:'])
			->add('firstName', TextType::class, ['label' => 'First Name:'])
			->add('lastName', TextType::class, ['label' => 'Last Name:'])
			->add('plainPassword', PasswordType::class, ['label' => 'Password:']);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Users::class,
		]);
	}
}
?>