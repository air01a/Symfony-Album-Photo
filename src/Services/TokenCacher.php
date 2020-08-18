<?php
namespace App\Services;


use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
class TokenCacher
{
    private $JWTManager;
    private $session;
    private $JWTEncoder;

    public function __construct(JWTTokenManagerInterface $JWTManager,JWTEncoderInterface $jwtEncoder, SessionInterface $session) {
       $this->JWTManager = $JWTManager;
       $this->session=$session;
       $this->JWTEncoder = $jwtEncoder;
    }


    public function createToken(User $user) {
        if ($this->session->has('jwt')) {
            try {
                $data = $this->JWTEncoder->decode($this->session->get('jwt'));

            } catch (JWTDecodeFailureException $e) {
                $data=null;
            }
            if (($data!=null)&&($data["exp"]>time()))
                return $this->session->get('jwt');
        }
        $token=$this->JWTManager->create($user);
        $this->session->set('jwt',$token);
        return $token;
    }
}