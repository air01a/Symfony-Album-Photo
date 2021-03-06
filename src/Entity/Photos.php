<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation as Serializer;

/**
 * Photos
 *
 * @ORM\Table(name="photos", indexes={@ORM\Index(name="album_id", columns={"album_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 */
class Photos
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     * @exclude
     */
    private $path;

    /**
     * @var int
     *
     * @ORM\Column(name="album_id", type="integer", nullable=false)
     *      
     * @ORM\ManyToOne(targetEntity="App\Entity\Album")
     * @ORM\JoinColumn=(name="album_id", referencedColumnName"id")
     */
    
    private $albumId = '0';



    /**
     * @var int
     *
     * @ORM\Column(name="order_in_album", type="integer", nullable=true)
     *      
     */
    private $orderInAlbum=1000;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", length=16777215, nullable=true)
     */
    private $commentaire;


    /**
     * @var string
     *
     * @ORM\Column(name="exif", type="text", length=16777215, nullable=true)
     */
    private $exif;

    /**
     * @var datetime
     *
     * @ORM\Column(name="date_time", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'U'>")
     */
    private $dateTime;


    public function getDateTime(){
        return $this->dateTime;
    }

    public function setDateTime($date){
        try {
            $this->dateTime=new \DateTime($date);
        } catch (\Exception $e) {
            $this->dateTime=null;
        }
    }

    public function getExif() {
        return $this->exif;
    }

    public function setExif($exif) {
        $this->exif=$exif;
    }

    public function getAlbumId() {
        return $this->albumId;
    }

    public function setAlbumId($id) {
        $this->albumId=$id;
    }

    public function getPath() {
        return $this->path;
    }
    
    public function setPath($path) {
        $this->path=$path;
    }

    public function getCommentaire() {
        return $this->commentaire;
    }
    
    public function setCommentaire($comment) {
        $this->commentaire=$comment;
    }


    public function setParameters($params) {
        foreach ($params as $k => $p) {
            if (!is_null($p)) { // here is the if statement
                //$key = Inflector::camelize($k);
                $key = $k;
                if (property_exists($this, $key)) {
                    $this->{'set' . ucfirst($key)}($p);
                }
            }
        }
        return $this;
    }

    public function getOrderInAlbum()
    {
        return $this->orderInAlbum;
    }

    public function setOrderInAlbum($order)
    {
        $this->orderInAlbum=$order;
    }
}
