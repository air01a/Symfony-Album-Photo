<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

use Symfony\Component\Security\Core\User\UserInterface;
/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_8D93D649F85E0677", columns={"username"})})
 * @ORM\Entity
 */
class User implements UserInterface, \Serializable
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
     * @ORM\Column(name="username", type="string", length=180, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="text", length=0, nullable=false)
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     * @exclude
     */
    private $password;



    private $apiKey='';


    public function __construct()
    {
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }

    public function getId()
    {
        return $this->id;
    }

    public function setApiKey($api){
        $this->apiKey = $api;
    }

    public function getApiKey(){
        return $this->apiKey;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function setRole($role)
    {
        $this->roles=$role;
    }


    public function getRole()
    {
        return $this->roles;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        $data = json_decode($this->roles, true);
        if ($data==null)
            return array("USER_ROLE");
        if (!in_array("USER_ROLE",$data))
            array_push($data,"USER_ROLE");
        return $data;
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->apiKey
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->apiKey
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

}
