<?php
namespace App\Service;


use App\Entity\Album;
use App\Entity\Photos;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileHelper
{
    private $appPath;

    public function __construct() {
        $directory = $_SERVER['PHOTO_STORAGE'];
        if ($directory[0]!='/')
            $directory =dirname(__DIR__).'/../'.$directory;
        $this->appPath = $directory;

    }


    public function getPhotoFile(Album $album,Photos $photo,int $thumb){
       ($thumb==1) ? $size="/320/" : $size="/800/";
       // $size="/320/";
        $photoFile = $this->appPath.$album->getPath().$size.$photo->getPath();
        if (file_exists($photoFile))
        {
            $image = file_get_contents($photoFile);

        } else {
            $image = file_get_contents('/home/erwan.niquet/dev/php/delr1/DelR1WebSite/public/images/diapo/20140530_154227.jpg');
        }
        return $image;
    }
}