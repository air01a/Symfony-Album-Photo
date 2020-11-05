<?php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Right;
use App\Services\FileHelper;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Request\ParamFetcherInterface;
use App\Representation\Albums;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileRespons;

class AlbumController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/api/v1/albums")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("album", converter="fos_rest.request_body")
     */
    public function createAction(Album $album, FileHelper $fileHelper, ConstraintViolationList $violations)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $right = new Right();
        $right->setUser($this->getUser());
        $right->setAlbum($album);

        $album->setPath($fileHelper->getRandomDirectory());
        $fileHelper->prepareDirectory($album->getPath());
        $em->persist($album);
        $em->persist($right);
        $em->flush();

        return $album;
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Get("/api/v1/albums", name="app_album_get")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]*",
     *     nullable=true,
     *     default="",
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="desc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="30",
     *     description="Max number of movies per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="[a-zA-Z0-9=]*",
     *     default="",
     *     description="The pagination offset"
     * ) 
     * @Rest\QueryParam(
     *     name="admin",
     *     requirements="\d",
     *     default=0,
     *     description="do not check for right"
     * ) 
     */
    public function listAction(ParamFetcherInterface $paramFetcher){
        $user = $this->getUser();

        $pager = $this->getDoctrine()->getRepository('App:Album')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('page'),
            $user->getId(),
            $paramFetcher->get('admin')
        );

        $albums = new Albums($pager);
        return $albums;

    }
    
    /**
     * @Rest\Get("/api/v1/albums/{id}", name="app_album_list")
     *     name = "app_album_get",
     *     requirements = {"id"="\d+"}
     * @Rest\View
     */
    public function getAction(Album $album,FileHelper $fileHelper)
    {
        $this->denyAccessUnlessGranted('view', $album);
        $album->hasToBeZipped=$fileHelper->hasToBeZipped($album->getPath());
        return $album;
    }

    /**
     * @Rest\Delete("/api/v1/albums/{id}", name="app_album_delete")
     *     name = "app_album_delete",
     *     requirements = {"id"="\d+"}
     * @Rest\View
     */
    public function deleteAction(Album $album,FileHelper $fileHelper)
    {
        $this->denyAccessUnlessGranted('edit', $album);
        $em=$this->getDoctrine()->getManager();

        $photos = $this->getDoctrine()->getRepository('App:Photos')->search($album->getId());
        foreach($photos as $photo){
            $em->remove($photo);
        }

        foreach($album->getRights() as $right)
            $em->remove($right);


        if (strlen($album->getPath())>5)
            if (!$fileHelper->deleteDir($album->getPath())) 
                return 'error="deleting file"';
        $em->remove($album);
        $em->flush();
    }



   /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Get(
     *     path = "/api/v1/albums/{id}/zip",
     *     name = "app_album_download",
     *     requirements = {"id"="\d+"}
     * )
     */
    
    public function downloadAction(Album $album,FileHelper $fileHelper)
    {
        $this->denyAccessUnlessGranted('view', $album);

        
        $zip = $fileHelper->zip($album->getPath());

        
        //$headers = array(
        //    'Content-Type'     => 'application/zip',
        //    'Content-Disposition' => 'inline; filename="photos'.strval($album->getId()).'.zip"');
        //return new Response($zip, 200, $headers);
        return new BinaryFileResponse($zip);
    }



   /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Get(
     *     path = "/albums/{id}/zip",
     *     name = "app_album_download_auth",
     *     requirements = {"id"="\d+"}
     * )
     */
    
    public function downloadActionNoJWT(Album $album,FileHelper $fileHelper)
    {

        return $this->downloadAction($album, $fileHelper);

    }



    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/api/v1/albums/{id}",
     *     name = "app_album_update",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newAlbum", converter="fos_rest.request_body")
     */
    public function updateAction(Album $album, Album $newAlbum, ConstraintViolationList $violations)
    {

        $this->denyAccessUnlessGranted('edit', $album);

        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $album->setCommentaire($newAlbum->getCommentaire());
        $album->setCountry($newAlbum->getCountry());
        $album->setDate($newAlbum->getDate());
        $album->setName($newAlbum->getName());
        $album->setYoutube($newAlbum->getYoutube());
        $album->setVideo($newAlbum->getVideo());
        $album->setPublic($newAlbum->getPublic());
        $album->setIdPub($newAlbum->getIdPub());
        $this->getDoctrine()->getManager()->flush();
        return $album;
    }


    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Patch(
     *     path = "/api/v1/albums/{id}",
     *     name = "app_album_partupdate",
     *     requirements = {"id"="\d+"}
     * )
     */
    public function partialUpdateAction(Album $album, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $em = $this->getDoctrine()->getManager();
        $this->getDoctrine()->getManager()->flush();
        $data = json_decode($request->getContent());
        $em->persist($album->setParameters($data));
        $em->flush();
        return $album;
    }

}
