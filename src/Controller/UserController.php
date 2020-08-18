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
}
