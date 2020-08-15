<?php
// src/Controller/PhotoController.php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photos;
use App\Service\FileHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;


class PhotoController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/api/v1/albums/{id}/photos")
     * @Rest\View
     * @ParamConverter("album", converter="fos_rest.request_body")
     */
    public function createAction(Photos $photo, ConstraintViolationList $violations)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        if (count($violations)) {
            return $this->view("", Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($photo);
        $em->flush();

        return $photo;
    }
    
    /**
     * @Rest\Get("/api/v1/albums/{id}/photos/random", name="app_photos_download_rand")
     * requirements = {"id"="\d+"}
     * @Rest\View
     */
    public function downloadRandomAction(Album $album)
    {
        $this->denyAccessUnlessGranted('view', $album);

        $photo = $this->getDoctrine()->getRepository('App:Photos')->getRandomPhoto($album->getId());
        return $this->downloadActionHelper($album,$photo,1);
    }
    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Get("/api/v1/albums/{id}/photos", name="app_photo_get")
     */
    public function listAction(Album $album){
        $this->denyAccessUnlessGranted('view', $album);

        $user = $this->getUser();
        $Photos = $this->getDoctrine()->getRepository('App:Photos')->search($album->getId());
        return $Photos;

    }
  
    /**
     * @Rest\Get("/api/v1/albums/{id}/photos/{idPhoto}", name="app_photos_get")
     * requirements = {"id"="\d+", "idPhoto"="\d+"}
     * @Rest\View
     * @ParamConverter("photo", options={"id" = "idPhoto"})

     */
    public function getAction(Album $album, Photos $photo)
    {
        $this->denyAccessUnlessGranted('view', $album);

        if ($album->getId() <> $photo->getAlbumId())
        {
            return $this->view("Album and photo mismatch", Response::HTTP_BAD_REQUEST);
        }
        return $photo;
    }


    /**
     * downloadAction but without query parameters to be called internally
     */

    public function downloadActionHelper(Album $album, Photos $photo,int $thumb)
    {
        $this->denyAccessUnlessGranted('view', $album);

        if ($album->getId() <> $photo->getAlbumId())
        {
            return $this->view("Album and photo mismatch", Response::HTTP_BAD_REQUEST);
        }

        $fileHelper = new FileHelper();
        $image = $fileHelper->getPhotoFile($album,$photo,$thumb);
        
        $headers = array(
            'Content-Type'     => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.$photo->getPath().'"');
        return new Response($image, 200, $headers);

    }
     /**
     * @Rest\Get("/api/v1/albums/{id}/photos/{idPhoto}/download", name="app_photos_download")
     * requirements = {"id"="\d+", "idPhoto"="\d+"}
     * @Rest\View
     * @ParamConverter("photo", options={"id" = "idPhoto"})
     * @Rest\QueryParam(
     *     name="thumb",
     *     requirements="\d",
     *     nullable=true,
     *     default="1",
     *     description="0 to full size, 1 for thumbnail"
     * )

     */
    public function downloadAction(Album $album, Photos $photo,ParamFetcherInterface $paramFetcher)
    {
        return $this->downloadActionHelper($album,$photo,$paramFetcher->get('thumb'));
    }

    
}
