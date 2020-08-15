<?php
// src/Controller/LinkController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LinkController extends AbstractController
{
     /**
     * Matches /
     *
     * @Route("/link", name="getLink")
     */
    public function link() 
    {
       
        return $this->render('link.html.twig', [
            'current'=>'link'
        ]);
    }
}

