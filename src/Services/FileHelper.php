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

    public function compress($source, $destination, $quality) {

        $info = \getimagesize($source);
    
        if ($info['mime'] == 'image/jpeg') 
            $image = \imagecreatefromjpeg($source);
    
        elseif ($info['mime'] == 'image/gif') 
            $image = imagecreatefromgif($source);
    
        elseif ($info['mime'] == 'image/png') 
            $image = imagecreatefrompng($source);
        $thumb = imagescale( $image, 320 ); 

        \imagejpeg($thumb, $destination, $quality);
    
        return $destination;
    }

    public function storeFile(Album $album, Photos $photo, $uploadedFile){
        $directory=$this->appPath.'/'.$album->getPath();
        $photo->setPath($uploadedFile[0]->getClientOriginalName());
        try {
            $file = $uploadedFile[0]->move($directory.'/800/', $photo->getPath());
            $this->compress($directory.'/800/'.$photo->getPath(),$directory.'/320/'.$photo->getPath(),75);
        } catch(\Exception $e) {
            $photo->setPath(null);
        }
    }

    public function getRandomDirectory() {
        do { 
            $directory = bin2hex(random_bytes(8));
        }while (is_dir($this->appPath.'/'.$directory));
        return $directory;
    }

    public function prepareDirectory($directory) {
        mkdir($this->appPath.'/'.$directory);
        mkdir($this->appPath.'/'.$directory.'/320');
        mkdir($this->appPath.'/'.$directory.'/800');
    }

    public function deleteDir($directory) {
        try {
            $baseDir=$this->appPath.'/'.$directory;

            $files = scandir($baseDir.'/800');
            foreach($files as $file)
                unlink($baseDir.'/800/'.$file);

            $files = scandir($baseDir.'/320');
            foreach($files as $file)
                unlink($baseDir.'/320/'.$file);
            
            $files = scandir($baseDir.'/');
            foreach($files as $file)
                unlink($baseDir.'/'.$file);
            rmdir($baseDir.'/320');
            rmdir($baseDir.'/800');
            rmdir($baseDir.'/');
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }
}