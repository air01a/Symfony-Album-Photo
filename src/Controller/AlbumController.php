<?php
namespace App\Controller;

use App\Entity\Album;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Request\ParamFetcherInterface;
use App\Representation\Albums;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

class AlbumController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/api/v1/albums")
     * @Rest\View
     * @ParamConverter("album", converter="fos_rest.request_body")
     */
    public function createAction(Album $album, ConstraintViolationList $violations)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        if (count($violations)) {
            return $this->view("error", Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

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
     */
    public function listAction(ParamFetcherInterface $paramFetcher){
        $user = $this->getUser();
        $pager = $this->getDoctrine()->getRepository('App:Album')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('page'),
            $user->getId()
        );
        

        return new Albums($pager);

    }


    
    /**
     * @Rest\Get("/api/v1/albums/{id}", name="app_album_list")
     *     name = "app_album_get",
     *     requirements = {"id"="\d+"}
     * @Rest\View
     */
    public function getAction(Album $album)
    {
        $this->denyAccessUnlessGranted('view', $album);

        return $album;
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
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $album->setCommentaire($newAlbum->getCommentaire());
        $album->setCountry($newAlbum->getCountry());
        $album->setDate($newAlbum->getDate());
        $album->setName($newAlbum->getName());
        $album->setPath($newAlbum->getPath());
        $album->setYoutube($newAlbum->getYoutube());
        $album->setVideo($newAlbum->getVideo());
        $album->setPublic($newAlbum->getPublic());
        $album->setZip($newAlbum->getZip());
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
