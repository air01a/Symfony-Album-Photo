<?php
// src/Controller/PhotoController.php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photos;
use App\Services\FileHelper;
use App\Services\ErrorHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;


class PhotoController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/api/v1/albums/{id}/photos")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("photo", converter="fos_rest.request_body")
     */
    public function createAction(Album $album, Photos $photo, ConstraintViolationList $violations)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($photo);
        $em->flush();

        return $photo;
    }

    /**
     * @Rest\Delete("/api/v1/albums/{id}/photos/{idPhoto}")
     * requirements = {"id"="\d+", "idPhoto"="\d+"}
     * @ParamConverter("photo", options={"id" = "idPhoto"})
     * @Rest\View
     */
    public function deleteAction(Album $album, Photos $photo, FileHelper $fileHelper)
    {   
        $fileHelper->deletePhoto($album,$photo);
        $em = $this->getDoctrine()->getManager();
        $em->remove($photo);
        $em->flush();
    }

    /**
     * @Rest\Post("/api/v1/albums/{id}/photos/{idPhoto}")
     * requirements = {"id"="\d+", "idPhoto"="\d+"}
     * @ParamConverter("photo", options={"id" = "idPhoto"})
     * @Rest\View
     */
    public function uploadAction(Album $album, Photos $photo, FileHelper $fileHelper,Request $request,ErrorHelper $errorManager)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $uploadedFile = $request->files;
        if ($uploadedFile==null){
            return false;
        }
        $em = $this->getDoctrine()->getManager();


        $error=$fileHelper->storeImage($album, $photo, $uploadedFile->get('file'));
        $errorManager->manageError($error);
        if ($photo->getPath()==null)
            $em->remove($photo);
        else     
            $em->persist($photo);
        $em->flush();
        return $photo;
    }
    
    /**
     * @Rest\Get("/api/v1/albums/{id}/photos/random", name="app_photos_download_rand")
     * requirements = {"id"="\d+"}
     * @Rest\View
     * @Rest\QueryParam(
     *     name="thumb",
     *     requirements="\d",
     *     nullable=true,
     *     default="1",
     *     description="0 to full size, 1 for thumbnail"
     * )
     * 
     * @Rest\QueryParam(
     *     name="size",
     *     requirements="[0-9]*x[0-9]*",
     *     nullable=true,
     *     default="1200x700",
     *     description="size of image"
     * )
     */
    public function downloadRandomAction(Album $album,FileHelper $fileHelper,ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('view', $album);

        $photo = $this->getDoctrine()->getRepository('App:Photos')->getRandomPhoto($album->getId());
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$fileHelper);
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

    public function downloadActionHelper(Album $album, Photos $photo,ParamFetcherInterface $paramFetcher,FileHelper $fileHelper)
    {
        $this->denyAccessUnlessGranted('view', $album);

        if ($album->getId() <> $photo->getAlbumId())
        {
            return $this->view("Album and photo mismatch", Response::HTTP_BAD_REQUEST);
        }

        $image = $fileHelper->getPhotoFile($album,$photo,$paramFetcher->get('thumb'),$paramFetcher->get('size'));
        
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
     * @Rest\QueryParam(
     *     name="size",
     *     requirements="[0-9]*x[0-9]*",
     *     nullable=true,
     *     default="1200x700",
     *     description="size of image"
     * )

     */
    public function downloadAction(Album $album, Photos $photo,ParamFetcherInterface $paramFetcher,FileHelper $fileHelper)
    {
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$fileHelper);
    }

         /**
     * @Rest\Get("/downloadbyhash/{id}/photos/{idPhoto}", name="app_photos_downloadbyhash")
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
     * @Rest\QueryParam(
     *     name="token",
     *     requirements="[A-Za-z0-9]*",
     *     nullable=true,
     *     default="",
     *     description="token"
     * )
     * 
     * @Rest\QueryParam(
     *     name="size",
     *     requirements="[0-9]*x[0-9]*",
     *     nullable=true,
     *     default="1200x700",
     *     description="size of image"
     * )
     */
    public function downloadbyhashAction(Album $album, Photos $photo,FileHelper $fileHelper,ParamFetcherInterface $paramFetcher)
    {
        $token = $paramFetcher->get('token');
        if ($token!=null)
        {
            $this->getUser()->setApiKey($token);
        }
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$fileHelper);
    }


    /**
     * @Rest\View(StatusCode = 202)
     * @Rest\Patch(
     *     path = "/api/v1/albums/{id}/photos/orders",
     *     name = "app_photo_orders",
     *     requirements = {"id"="\d+"}
     *  )
     */
    public function orderAction(Album $album, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $em = $this->getDoctrine()->getManager();
        $photos = $em->getRepository('App:Photos');

        $data = json_decode($request->getContent());
        $order=1;
        foreach($data as $photoId){
            $photo = $photos->findOneBy(['id'=>$photoId]);
            if ($photo->getAlbumId()!=$album->getId())
                var_dump("Photo & album mismatch");
            else {
                $photo->setOrderInAlbum($order);
                $em->persist($photo);
                $order+=1;
            }
        }
        
        $em->flush();
        return $this->listAction($album);
    }

    /**
     * @Rest\View(StatusCode = 201)
     * @Rest\Patch(
     *     path = "/api/v1/albums/{id}/photos/{idPhoto}",
     *     name = "app_photo_partupdate",
     *     requirements = {"id"="\d+", "idPhoto"="\d+"}
     *  )
     *  @ParamConverter("photo", options={"id" = "idPhoto"})
     * 
     */
    public function partialUpdateAction(Album $album, Photos $photo, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent());
        $em->persist($photo->setParameters($data));
        $em->flush();
        return $photo;
    }
 
    

}
