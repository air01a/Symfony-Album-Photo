<?php
namespace App\Service;



class TokenManager
{

    public function __construct() {
        if (!isset($_SESSION["APITOKEN"]))
            $_SESSION["APITOKEN"]=array("23EAAAAAE3232"=>["idObject"=>1,"expiration"=>time()+3600]);
        else 
            foreach($_SESSION["APITOKEN"] as $key=>$token)
                if ($token['expiration']<time())
                    unset($_SESSION["APITOKEN"][$key]);
    }


    public function getToken($token) {
        if (isset($_SESSION["APITOKEN"][$token])) 
            return $_SESSION["APITOKEN"][$token];
        return null;
    }

    public function createToken($type,$id){
        $idToken = bin2hex(random_bytes(20));
        if ($type!="ALBUM" && $type!="USER")
            return null;
        $token = array("type" =>$type, "idObject" => $id, "expiration"=>time()+3600 );
        $_SESSION["APITOKEN"][$idToken]=$token;
        return $idToken;
    }
}