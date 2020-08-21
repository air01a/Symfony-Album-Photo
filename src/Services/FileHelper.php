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
        $this->appPath = $directory.'/';

    }


    public function getPhotoFile(Album $album,Photos $photo,int $thumb){
       ($thumb==1) ? $size="/320/" : $size="/800/";
       // $size="/320/";
        $photoFile = $this->appPath.$album->getPath().$size.$photo->getPath();
	if (file_exists($photoFile))
        {
            if ($thumb==1)
                $image = file_get_contents($photoFile);
            else
                $image=$this->createThumbnail($photoFile,1800,1800);

        } else {
            // A corriger
            $image = file_get_contents(\dirname(__DIR__).'/../public/images/diapo/20140530_154227.jpg');
        }
        return $image;
    }

    public function createThumbnail($image_name,$new_width,$new_height)
    {

        $mime = getimagesize($image_name);

        if($mime['mime']=='image/png') { 
            $src_img = imagecreatefrompng($image_name);
        }
        if($mime['mime']=='image/jpg' || $mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
            $src_img = imagecreatefromjpeg($image_name);
        }   

        $old_x          =   imageSX($src_img);
        $old_y          =   imageSY($src_img);

        if($old_x < $old_y) 
        {
            $thumb_w    =   $new_width;
            $thumb_h    =   $old_y*($new_height/$old_x);
        }

        if($old_x > $old_y) 
        {
            $thumb_w    =   $old_x*($new_width/$old_y);
            $thumb_h    =   $new_height;
        }

        if($old_x == $old_y) 
        {
            $thumb_w    =   $new_width;
            $thumb_h    =   $new_height;
        }

        $dst_img        =   ImageCreateTrueColor($thumb_w,$thumb_h);

        imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 


        // New save location
       // $new_thumb_loc = $moveToDir . $image_name;
        $image=null;
        if($mime['mime']=='image/png') {
          //  $result = imagepng($dst_img,$destination_name,8);
            ob_start();
            imagepng($dst_img,NULL,8);
            $image = ob_get_clean();
        }
        
        if($mime['mime']=='image/jpg' || $mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
        //    $result = imagejpeg($dst_img,$destination_name,100);
            ob_start();
            imagejpeg($dst_img,NULL,100);
            $image = ob_get_clean();
        }


        imagedestroy($dst_img); 
        imagedestroy($src_img);

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
        imagesetinterpolation($image,IMG_SINC);
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
            $image = $this->createThumbnail($directory.'/800/'.$photo->getPath(),250,250);
            file_put_contents($directory.'/320/'.$photo->getPath(),$image);
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
