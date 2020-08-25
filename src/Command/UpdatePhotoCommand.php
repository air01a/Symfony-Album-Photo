<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Album;
use App\Entity\Photos;
use App\Services\FileHelper;
use Doctrine\ORM\EntityManagerInterface;

class UpdatePhotoCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-photo';

    public function __construct(EntityManagerInterface $em,FileHelper $fileHelper)
    {
        $this->em=$em;
        $this->fileHelper=$fileHelper;
        parent::__construct();

    }

    protected function configure()
    {
        // ...
        $this
            ->setDescription('Update photos [exif, tags]')
            ->setHelp('Update photos [exif, tags]\nbin/console app:update-photo -E / -T\n-E = set Exif when empty\n-T = set tag when empty ');
        
    }

    protected function setExif(InputInterface $input, OutputInterface $output){
        $photos = $this->em->getRepository('App:Photos')->findBy(['exif'=>null],['albumId'=>'ASC']);
        $album = new Album();
        foreach($photos as $photo) {
            if ($album->getId()!=$photo->getAlbumId())
                $album=$this->em->getRepository('App:Album')->findOneBy(['id'=>$photo->getAlbumId()]);
            if ($album==null) {
                $output->writeln("Found photo without album:".$photo->getPath()." => Deleting");
                $this->em->remove($photo);
            } else {    
                $exif=$this->fileHelper->getExif($album,$photo);
                $output->write("Found photo ".$photo->getPath());

                if ($exif) {
                    $output->write(" =>Setting Exif");

                    $photo->setExif(json_encode($exif,1));
                    $photo->setDateTime($this->fileHelper->getExifDate($exif));
                    $this->em->persist($photo);
                } else {
                    $output->write(" => Exiv Null");
                }
                $output->writeln("");
            }
            $this->em->flush();
        }
        return Command::SUCCESS;


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ... put here the code to run in your command

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return $this->setExif($input,$output);

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}