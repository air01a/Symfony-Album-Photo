<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends AbstractController
{
	/**
     * Matches /
     *
     *
     * @Route("/registration", name="registration")
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {
        $em=$this->getDoctrine()->getManager();
        if ($em->getRepository('App:User')->findAll()!=null)
            throw $this->createAccessDeniedException('Application already configured');

        // creates a task object and initializes some data for this example
        $user = new User();

        
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class)
            ->add('Password', PasswordType::class)

            ->add('save', SubmitType::class, ['label' => 'Create User'])
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword($user,$user->getPassword()));
            $user->setRole(json_encode(['ROLE_USER','ROLE_ADMIN']));
            $em->persist($user);

            $user = new User();
            $user->setUsername('ANONYMOUS');
            $user->setIdPriv(0);
            $user->setPassword($passwordEncoder->encodePassword($user,bin2hex(random_bytes(25))));
            $em->persist($user);


            $em->flush();
            return $this->redirectToRoute('app_login');
        }


	    return $this->render('registration.html.twig', [
            'form' => $form->createView(),
        ]);
        // ...
    }
}
