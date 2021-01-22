<?php
namespace App\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\View
 * */
class ErrorHelper
{
    private $ERROR=array(
        -3=>"This service only accepts image",
        -100=>"Error during file copy on disk",
        -101=>"Directory creation failed",
        -102=>"Error creating picture on disk" 
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


    public function sendResponse($response,$error) {
        if ($error>=0)
            return $response;
        
        if (isset($this->ERROR[$error]))
            $strError=$this->ERROR[$error];
        else
            $strError="Unknown error";
        $returnCode=400;
        if ($error<-99)
            $returnCode=500;
        return new Response(json_encode(array('error'=>$error,'error_str'=>$strError)), $returnCode);

    }



}