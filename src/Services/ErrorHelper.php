<?php
namespace App\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorHelper
{
    private $ERROR=array(
        -3=>"This service only accepts image",
        -100=>"Error during file copy on disk"
    );


    public function manageError($error){
        if ($error>0)
            return ;
        if (isset($ERROR[$error]))
            $strError=$ERROR[$error];
        else
            $strError="Unknown error";
        $returnCode=400;
        if ($error<-99)
            $returnCode=500;

        throw new HttpException($returnCode, json_encode(array('error'=>$error,'error_str'=>$strError)));
    }



}