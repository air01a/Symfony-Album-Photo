<?php
namespace App\Services;


use App\Entity\Album;
use App\Entity\Photos;

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

    private function isImage($uploadedFile){
        return true;
        if(@is_array(getimagesize($uploadedFile[0]->getPath().'/'.$uploadedFile[0]->getFileName()))){
            return true;
        } else {
            return false;
        }

    }

    public function storeImage(Album $album, Photos $photo, $uploadedFile){
        $directory=$this->appPath.'/'.$album->getPath();
        $photo->setPath($uploadedFile[0]->getClientOriginalName());
        //if (!$this->isImage($uploadedFile))
          //  return -3;
        try {
            $file = $uploadedFile[0]->move($directory.'/800/', $photo->getPath());
            $this->compress($directory.'/800/'.$photo->getPath(),$directory.'/320/'.$photo->getPath(),75);
        } catch(\Exception $e) {
            var_dump($e->getMessage());
            $photo->setPath(null);
            return -1;
        }
        return 1;
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

    private function deleteFiles($directory) {
        $files = scandir($directory);
        foreach($files as $file)
            if (is_file($directory.'/'.$file))
                unlink($directory.'/'.$file);
    }

    public function deleteDir($directory) {
        try {
            $baseDir=$this->appPath.'/'.$directory;
            $this->deleteFiles($baseDir.'/800');
            $this->deleteFiles($baseDir.'/320');
            $this->deleteFiles($baseDir.'/');
            rmdir($baseDir.'/320');
            rmdir($baseDir.'/800');
            rmdir($baseDir.'/');
            return true;
        } catch(\Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    public function deletePhoto(Album $album,Photos $photo){
        try {
            $baseDir=$this->appPath.'/'.$album->getPath();
            unlink($baseDir.'/320/'.$photo->getPath());
            unlink($baseDir.'/800/'.$photo->getPath());
            return true;
        } catch (\Exception $e) {
            var_dump($e->getMessage()); 
            return false;
        }
    }



    public function hasToBeZipped($path){
        if (!is_file($this->appPath.'/zipperTask.json'))
            return false;
        
        $file = file_get_contents($this->appPath.'/zipperTask.json');
        $task = json_decode($file, true);
        if (in_array($path,$task['toZip']))
            return true;
        return false;
    }



    public function folderToZip(){
        if (!is_file($this->appPath.'/zipperTask.json'))
            return null;
        
        $file = file_get_contents($this->appPath.'/zipperTask.json');
        $task = json_decode($file, true);
        return $task['toZip'];
    }



    public function addZipTask($path) {

        try {
            if (is_file($this->appPath.'/zipperTask.json'))
            {
                $file = file_get_contents($this->appPath.'/zipperTask.json');
                $task = json_decode($file, true);
            } else {
                $task=array("toZip"=>[]);
            }

            if (!in_array($path,$task['toZip']))
                array_push($task['toZip'],$path);
            $jsonData = json_encode($task);
            file_put_contents($this->appPath.'/zipperTask.json',$jsonData);
            return true;
        
        } catch (\Exception $e) {
             return false;
        }
    }


    public function deleteZipTask($path) {

        try {
            if (is_file($this->appPath.'/zipperTask.json'))
            {
                $file = file_get_contents($this->appPath.'/zipperTask.json');
                $task = json_decode($file, true);
            } else {
                return true;
            }

            $index = array_search($path,$task['toZip']);
            if ($index!==false)
                unset($task['toZip'][$index]);

            $jsonData = json_encode($task);
            file_put_contents($this->appPath.'/zipperTask.json',$jsonData);
            return true;
        
        } catch (\Exception $e) {
             return false;
        }
    }

    public function zip($path){

        $archive = $this->appPath.'/'.$path.'/'.$path.'.zip';
        $zip = new \ZipArchive();
        if ($zip->open($archive, \ZipArchive::CREATE)!==TRUE) {
            return "Cannot create archive";
        }
        $rep=$this->appPath.'/'.$path.'/800/';
        $files = scandir($rep);
        foreach($files as $file) {
            if (is_file($rep.$file))
                $zip->addFile($rep.$file,$file);
        }
        
        $zip->close();
        return file_get_contents($archive);
       // return $this->deleteZipTask($path);

    }


    public function zipAll(){

        $folderToZip = $this->folderToZip();
        foreach($folderToZip as $folder) {
            $this->zip($folder);
        }
    }
}