<?php
// src/Controller/PhotoController.php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photos;
use App\Services\FileHelper;
use App\Services\PhotoHelper;
use App\Services\ErrorHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Psr\Log\LoggerInterface;


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
        $em = $this->getDoctrine()->getManager();
        $count = $em->getRepository('App:Photos')->isPhotoUniq($album,$photo);
        if ($count<2)
            $fileHelper->deletePhoto($album,$photo);

        $em->remove($photo);
        $em->flush();
    }

    /**
     * @Rest\Post("/api/v1/albums/{id}/photos/{idPhoto}")
     * requirements = {"id"="\d+", "idPhoto"="\d+"}
     * @ParamConverter("photo", options={"id" = "idPhoto"})
     * @Rest\View
     */
    public function uploadAction(Album $album, Photos $photo, PhotoHelper $photoHelper,Request $request,ErrorHelper $errorManager,LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $uploadedFile = $request->files;

        $logger->info('Objet reçu : ' . json_encode($request));

        $logger->error("Uploading photo  to album ");

        if ($uploadedFile==null){
            return false;
        }

        $logger->info('Objet reçu : ' . json_encode($uploadedFile));


        $logger->error("Uploading photo  to album 2");

        $em = $this->getDoctrine()->getManager();

        $logger->error("Uploading photo  to album 3");
        $error=$photoHelper->storeImage($album, $photo, $uploadedFile->get('file'));
        $logger->error("out");

        if ($error>=0){
            $exif=$photoHelper->getExif($album,$photo);
            if ($exif) {
                $photo->setExif(json_encode($exif,1));
                $photo->setDateTime($photoHelper->getExifDate($exif));
            }
        }

        if ($photo->getPath()==null)
            $em->remove($photo);
        else {
            // Put photo in the right position
            $orderBy = $album->getSorter();
            $order = $this->getDoctrine()->getRepository('App:Photos')->getOrder($album->getId(),$photo->getDateTime(), $orderBy);
            if ($order) {
             //   var_dump($order);
                $this->getDoctrine()->getRepository('App:Photos')->updateOrder($album->getId(),$order);  
                $photo->setOrderInAlbum($order);
            }   

            $em->persist($photo);
        }



        $em->flush();
        return $errorManager->sendResponse($photo,$error);
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
    public function downloadRandomAction(Album $album,PhotoHelper $photoHelper,ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('view', $album);

        $photo = $this->getDoctrine()->getRepository('App:Photos')->getRandomPhoto($album->getId());


        return $this->downloadActionHelper($album,$photo,$paramFetcher,$photoHelper);
    }
    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Get("/api/v1/albums/{id}/photos", name="app_photo_get")
     */
    public function listAction(Album $album){
        $this->denyAccessUnlessGranted('view', $album);

        $user = $this->getUser();
        $Photos = $this->getDoctrine()->getRepository('App:Photos')->search($album->getId(),$album->getSorter());
        return $Photos;

    }


        
    /**
     * @Rest\Post("/publicapi/albums/{id}/photos", name="app_public_album_photo_list",requirements = {"id"="\d+"})
     *     
     * @Rest\View
     */
    public function getPhotosPublic(Album $album, Request $request, FileHelper $fileHelper)
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$album->getPublic() || !$token || $album->getIdPub()!=$token) {
            throw $this->createNotFoundException('Album non trouvé');


        }
        //return ($album->getIdPub());
        //if ($album->getIdPub)
        $Photos = $this->getDoctrine()->getRepository('App:Photos')->search($album->getId(),$album->getSorter());
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

    public function downloadActionHelper(Album $album, ?Photos $photo,ParamFetcherInterface $paramFetcher,PhotoHelper $photoHelper, $public=false)
    {
        if (!$public)
            $this->denyAccessUnlessGranted('view', $album);


        if ($photo==null)
        {
            $image = $photoHelper->getImageNotFound();
            $photo = new Photos;
            $photo->setPath('notfound.jpg');
        } else {

            if ($album->getId() <> $photo->getAlbumId())
            {
                return $this->view("Album and photo mismatch", Response::HTTP_BAD_REQUEST);
            }

            $image = $photoHelper->getPhotoFile($album,$photo,$paramFetcher->get('thumb'),$paramFetcher->get('size'));
        }    

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
    public function downloadAction(Album $album, Photos $photo,ParamFetcherInterface $paramFetcher,PhotoHelper $photoHelper)
    {
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$photoHelper);
    }


    /**
     * @Rest\Post("/publicapi/albums/{id}/photos/{idPhoto}/download", name="app_photos_download_public")
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
    public function downloadActionPublic(Album $album, Photos $photo, Request $request,ParamFetcherInterface $paramFetcher,PhotoHelper $photoHelper)
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$album->getPublic() || !$token || $album->getIdPub()!=$token) {
            throw $this->createNotFoundException('Album non trouvé');


        }
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$photoHelper, true);
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
    public function downloadbyhashAction(Album $album, Photos $photo,PhotoHelper $photoHelper,ParamFetcherInterface $paramFetcher)
    {
        $token = $paramFetcher->get('token');
        if ($token!=null)
        {
            $this->getUser()->setApiKey($token);
        }
        return $this->downloadActionHelper($album,$photo,$paramFetcher,$photoHelper);
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
    public function partialUpdateAction(Album $album, Photos $photo, Request $request,LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('edit', $album);

        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent());
        $em->persist($photo->setParameters($data));
        $em->flush();
        return $photo;
    }
 
    

}
