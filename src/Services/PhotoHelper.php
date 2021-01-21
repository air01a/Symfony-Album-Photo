<?php
namespace App\Services;


use App\Entity\Album;
use App\Entity\Photos;

class PhotoHelper
{
    private $appPath;

    public function __construct() {
        $directory = $_SERVER['PHOTO_STORAGE'];
        if ($directory[0]!='/')
            $directory =dirname(__DIR__).'/../'.$directory;
        $this->appPath = $directory.'/';

    }
    //####################################################################
    // Find best x&y ratio according to initial and requested image size
    //####################################################################
    public function bestRatio($photo, $x,$y)
    {
        list($old_x, $old_y, $type, $attr)=getimagesize($photo);


        if($old_x > $old_y) 
        {
            $thumb_w    =   $x;
            $thumb_h    =   $old_y*($x/$old_x);
        }

        if($old_x < $old_y) 
        {
            $thumb_w    =   $old_x*($y/$old_y);
            $thumb_h    =   $y;
        }

        if($old_x == $old_y) 
        {
            $thumb_w    =   $x;
            $thumb_h    =   $y;
        }

        if ($thumb_w>$old_x || $thumb_h>$old_y){
            $thumb_w=$old_x;
            $thumb_h=$old_y;
        }   
        return array('x'=>$thumb_w,'y'=>$thumb_h);
    }
    ////####################################################################
    //Deprecated
    //####################################################################
    /*
    public function bestRatioMini($photo, $x,$y)
    {
        list($old_x, $old_y, $type, $attr)=getimagesize($photo);


        if($old_x > $old_y) 
        {
            $thumb_w    =   $x;
            $thumb_h    =   $old_y*($y/$old_x);
        }

        if($old_x < $old_y) 
        {
            $thumb_w    =   $old_x*($x/$old_y);
            $thumb_h    =   $y;
        }

        if($old_x == $old_y) 
        {
            $thumb_w    =   $x;
            $thumb_h    =   $y;
        }
        return array('x'=>$thumb_w,'y'=>$thumb_h);
    }
    */

    public function imageFixOrientation(&$image, $filename) {
        $image = imagerotate($image, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[@exif_read_data($filename)['Orientation'] ?: 0], 0);
    } 


    public function getPhotoFile(Album $album,Photos $photo,int $thumb,$win){
       ($thumb==1) ? $size="/320/" : $size="/800/";
       // $size="/320/";
        $photoFile = $this->appPath.$album->getPath().$size.$photo->getPath();
	if (file_exists($photoFile))
        {
            if ($thumb==1)
                $image = file_get_contents($photoFile);

            else {
                $x=1000;
                $y=700;
                if ($win) {
                    $windows=explode('x',$win);
                    if (sizeof($windows)==2)
                    {
                        $x=$windows[0];
                        $y=$windows[1];
                    }
                    
                }

                $ratio=$this->bestRatio($photoFile,$x,$y);
                // Some smartphone correct the orientation of the picture with an exiv, so manage it
                $image=$this->createThumbnail($photoFile,$ratio['x'],$ratio['y']);
            }

        } else {
            // A corriger
            $image = file_get_contents(\dirname(__DIR__).'/../public/images/notfound.jpeg');
        }
        return $image;
    }

    //####################################################################
    // Used to resize image for welcome page slideshow
    //####################################################################
    public function createDiapo($image_name,$width,$height) {
        $mime = getimagesize($image_name);
        $src_img = imagecreatefromjpeg($image_name);

        $old_x          =   imageSX($src_img);
        $old_y          =   imageSY($src_img);

        $dst_img        =   ImageCreateTrueColor($width,$height);
        $white = imagecolorallocate($dst_img, 255, 255, 255);
        imagefill($dst_img, 0, 0, $white);

        $ratio=$this->bestRatio($image_name,$width,$height);

        $x=0;$y=0;
        if ($ratio['x']<$width)
            $x=($width-$ratio['x'])/2;
        if ($ratio['y']<$height)
            $y=($height-$ratio['y'])/2;
        imagecopyresampled($dst_img,$src_img,$x,$y,0,0,$ratio['x'],$ratio['y'],$old_x,$old_y); 
        ob_start();
        imagejpeg($dst_img,NULL,95);
        $image = ob_get_clean();

        imagedestroy($dst_img); 
        imagedestroy($src_img);
        return $image;
    }

    //####################################################################
    // Create Thumbnail for album page
    //####################################################################
    public function createThumbnail($image_name,$new_width,$new_height,$quality=85)
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

        $dst_img        =   ImageCreateTrueColor($new_width,$new_height);

        imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_width,$new_height,$old_x,$old_y); 
        $this->imageFixOrientation($dst_img,$image_name);


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
            imagejpeg($dst_img,NULL,$quality);
            $image = ob_get_clean();
        }


        imagedestroy($dst_img); 
        imagedestroy($src_img);

        return $image;
    }

    
    
    private function isImage($uploadedFile){
        return true;
        if(@is_array(getimagesize($uploadedFile[0]->getPath().'/'.$uploadedFile[0]->getFileName()))){
            return true;
        } else {
            return false;
        }

    }

    public function getExif($album, $photo){
        $directory=$this->appPath.'/'.$album->getPath();
        try {
            return exif_read_data($directory.'/800/'.$photo->getPath());
        } catch (\Exception $e) {
            return null;
        }
    }


    public function getExifDate($exif) {
        if (!is_array($exif))
            return null;

        if (array_key_exists('DateTimeOriginal',$exif)){
            $dt = $exif['DateTimeOriginal'];
            $tab = explode(' ',$dt);
            $tab[0] = str_replace(':','-',$tab[0]);
            $dt = $tab[0].' '.$tab[1];
            //var_dump($dt);
            return $dt;
        }
        return null;
    }

    //####################################################################
    // Put the image in the correct directory and create thumbnail
    //####################################################################
    public function storeImage(Album $album, Photos $photo, $uploadedFile){
        $directory=$this->appPath.'/'.$album->getPath();
        $photo->setPath($uploadedFile[0]->getClientOriginalName());
        //if (!$this->isImage($uploadedFile))
          //  return -3;
        try {
            $file = $uploadedFile[0]->move($directory.'/800/', $photo->getPath());
            $ratio=$this->bestRatio($file,450,450); // Was bestRationMini
            $image = $this->createThumbnail($directory.'/800/'.$photo->getPath(),$ratio['x'],$ratio['y'],95);
            file_put_contents($directory.'/320/'.$photo->getPath(),$image);
        } catch(\Exception $e) {
            $photo->setPath(null);
            return -1;
        }
        return 1;
    }

}
