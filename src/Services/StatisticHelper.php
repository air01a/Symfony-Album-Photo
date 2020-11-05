<?php
namespace App\Services;


use App\Entity\Album;
use App\Entity\Photos;
use App\Services\FileHelper;
use Doctrine\ORM\EntityManagerInterface;


class StatisticHelper
{

    private $fileHelper;
    private $em;

    public function __construct(FileHelper $fileHelper,EntityManagerInterface $em) {
        $this->fileHelper = $fileHelper;
        $this->em         = $em;
    }


    public function getStat() {
        $numPhoto = 0;
        $focaleTab = array();
        $photos = $this->em->getRepository('App:Photos')->findAll();
        foreach($photos as $photo) {
        
            $exif = json_decode($photo->getExif(),true);
            if ($exif!=null)
                if (array_key_exists('FocalLengthIn35mmFilm',$exif)) {
                    $focale = $exif['FocalLengthIn35mmFilm'];
                    if (array_key_exists($focale,$focaleTab))
                        $focaleTab[$focale]++;
                    else
                        $focaleTab[$focale]=1;
                    $numPhoto ++;
                }
        }
        $focaleTab["total"]=$numPhoto;
        return $focaleTab;
    }
}
