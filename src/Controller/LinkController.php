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
        $dir = \dirname(__DIR__).'/../templates/';
        if (is_file($dir.'/link/link.html'))
            $content=file_get_contents($dir.'/link/link.html');
        else
            $content=file_get_contents($dir.'/link/link.html.default');


        return $this->render('/link/link.html.twig', [
            'current'=>'link',
            'content'=>$content
        ]);
    }
}

