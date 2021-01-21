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
        return $archive;
       // return $this->deleteZipTask($path);

    }


    public function zipAll(){

        $folderToZip = $this->folderToZip();
        foreach($folderToZip as $folder) {
            $this->zip($folder);
        }
    }
}
