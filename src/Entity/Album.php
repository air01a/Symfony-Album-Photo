<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * Album
 *
 * @ORM\Table(name="album", indexes={@ORM\Index(name="name", columns={"name", "path"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\AlbumRepository")
 * 
 */
class Album
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $date = '0000-00-00';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60, nullable=false, options={"default"=""})
     * @Assert\NotBlank
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", length=16777215, nullable=false)
     */
    private $commentaire;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Right", mappedBy="album")
     * @exclude
     */
    private $rights;



    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean", nullable=true)
     */
    private $public = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_pub", type="string", length=64, nullable=true)
     * 
     */
    private $idPub = '';


    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=true, options={"default"="FR"})
     */
    private $country = 'FR';

    /**
     * @var string|null
     *
     * @ORM\Column(name="youtube", type="text", length=65535, nullable=true, options={"default"=""})
     */
    private $youtube = '';

    /**
     * @var string
     *
     * @ORM\Column(name="sorter", type="string", length=10, nullable=false, options={"default"="path"})
     */
    private $sorter = 'path';



    public function getId() {
        return $this->id;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = new \DateTime($date);
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }


    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getCommentaire() {
        return $this->commentaire;
    } 


    public function setCommentaire($comment) {
        $this->commentaire = $comment;
    } 

    public function getPublic() {
        return $this->public;
    } 


    public function setPublic($public) {
        $this->public = $public;
    } 

    public function getVideo() {
        return $this->video;
    } 


    public function setVideo($video) {
        $this->video = $video;
    } 

    public function getYoutube() {
        return $this->youtube;
    } 


    public function setYoutube($youtube) {
        $this->youtube = $youtube;
    } 

    public function getCountry() {
        return $this->country;
    } 


    public function setCountry($country) {
        $this->country = $country;
    } 


    public function getIdPub() {
        return $this->idPub;
    } 


    public function setIdPub($idPub) {
        $this->idPub = $idPub;
    } 



    public function setParameters($params) {
        foreach ($params as $k => $p) {
            if (!is_null($p)) { // here is the if statement
              
            //    $key = Inflector::camelize($k);
                $key = $k;
                if (property_exists($this, $key)) {
                    $this->{'set' . ucfirst($key)}($p);
                }
            }
        }
        return $this;
    }

    public function getRights(){
        return $this->rights;
    }

    public function setSorter($sorter){
        $this->sorter=$sorter;
    }

    public function getSorter(){
        return $this->sorter;
    }


}
    
    