<?php
// src/Controller/SlideShowController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SlideShowController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/", name="slideshow")
     */
    public function slideshow() 
    {
        $DIRDIAPO='images/diapo/';
        $images=scandir($DIRDIAPO);
        shuffle($images);
        $diapo=array();
        $i=0;

        $dir = \dirname(__DIR__).'/../templates/';
        if (is_file($dir.'slideshow/slideshow.html'))
            $content=file_get_contents($dir.'slideshow/slideshow.html');
        else
            $content=file_get_contents($dir.'slideshow/slideshow.html.default');


        foreach ($images as $file) 
            if (is_file($DIRDIAPO.$file) && strtoupper(pathinfo($file, PATHINFO_EXTENSION))=='JPG') {
                array_push($diapo,array('file'=>$DIRDIAPO.$file,'id'=>'wows1_'.$i));
                $i++;
            }

        return $this->render('slideshow/slideshow.html.twig', [
            'diapo' => $diapo,
            'current'=>'slideshow',
            'content'=>$content
        ]);
    }
}

