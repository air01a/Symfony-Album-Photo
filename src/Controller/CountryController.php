<?php
// src/Controller/CountryController.php
namespace App\Controller;

use App\Entity\Album;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Request\ParamFetcherInterface;
use App\Repository\AlbumRepository;
use App\Representation\Albums;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

class CountryController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/api/v1/country")
     * @Rest\View
     */
    public function getAction(){
        $user = $this->getUser();

        $country = $this->getDoctrine()->getRepository('App:Album')->getCountry($user->getId());
        $result = array();
        foreach($country as $c)
            $result[$c["country"]]=$c["count"];
        return $result;
    }

    /**
     * @Rest\Get("/api/v1/country/{country}")
     *     requirements = {"country"="[A-Z][A-Z]"}

     * @Rest\View
     */
    public function getAlbumFromCountry($country){
        $user = $this->getUser();

        $results = $this->getDoctrine()->getRepository('App:Album')->getAlbumFromCountry($country,$user->getId());
       
        return $results;
    }

    
}
