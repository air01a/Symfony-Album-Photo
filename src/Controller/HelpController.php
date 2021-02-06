<?php
// src/Controller/SlideShowController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\PhotoHelper;


use Symfony\Component\HttpFoundation\Request;

class HelpController extends AbstractController
{
     /**
     * Matches /help
     *
     *
     * @Route("/help", name="help")
     */
    public function slideshow() 
    {
      

        $dir = \dirname(__DIR__).'/../templates/';
        if (is_file($dir.'help/help.html'))
            $content=file_get_contents($dir.'help/help.html');
        else
            $content=file_get_contents($dir.'help/help.html.default');





        return $this->render('help/help.html.twig', [
            'current'=>'help',
            'content'=>$content
        ]);
    }

    

}


