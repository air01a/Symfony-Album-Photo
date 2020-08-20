<?php
// src/Controller/UserController.php
namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractFOSRestController
{
 
    /**
     * @Rest\Get("/api/v1/users")
     * @Rest\View
     * 
     */
    public function getAction() {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('GET OUT!');
        $users = $this->getDoctrine()->getRepository('App:User')->findAll(); 
   
        
        return $users;  

    }

    /**
     * @Rest\Get("/api/v1/users/{id}")
     * requirements = {"id"="\d+"}
     * @Rest\View
     * 
     */
    public function getOneUserAction(User $user) {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('Need to be admin!');
        return $user;  
    }


    /**
     * @Rest\Patch("/api/v1/users/{id}")
     * requirements = {"id"="\d+"}
     * @Rest\View
     * 
     */
    public function modifyUser(User $user, Request $request,UserPasswordEncoderInterface $passwordEncoder) {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('Need to be admin');
        
        $data = json_decode($request->getContent());
        
        if (isset($data->password) && strlen($data->password)>2) {
            $newPassword=$passwordEncoder->encodePassword($user,$data->password);

            $user->setPassword($newPassword);
        }
        if (isset($data->username) && strlen($data->username)>2)
            $user->setUsername($data->username);
        if (isset($data->isAdmin) && $data->isAdmin)
            $user->setRole(json_encode(['ROLE_USER','ROLE_ADMIN']));
        else
            $user->setRole(json_encode(['ROLE_USER']));

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $user;
        
    }


    /**
     * @Rest\Post("/api/v1/users")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     */
    function createUser(User $user,UserPasswordEncoderInterface $passwordEncoder)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('Need to be admin');
        
        $user->setPassword($passwordEncoder->encodePassword($user,bin2hex(random_bytes(28))));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }

     /**
     * @Rest\Delete("/api/v1/users/{id}")
     * requirements = {"id"="\d+"}
     * @Rest\View(StatusCode = 201)
     */
    function deleteUser(User $user){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('Need to be admin');

        if ($user->getId()==$this->getUser()->getId())
           throw $this->createAccessDeniedException('Cannot delete current user');
        
           $em=$this->getDoctrine()->getManager();

        $rights = $em->getRepository('App:Right')->findBy(['user'=>$user->getId()]);
        foreach($rights as $right)
            $em->remove($right);
        $em->remove($user);
        $em->flush();

        
    }

}
