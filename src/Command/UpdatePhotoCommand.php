<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Album;
use App\Entity\Photos;
use App\Services\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\ArrayInput;
use App\Services\StatisticHelper;


class UpdatePhotoCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-photo';
    private $em;
    private $fileHelper;
    private $stat;


    public function __construct(EntityManagerInterface $em,FileHelper $fileHelper, StatisticHelper $stat)
    {
        $this->em=$em;
        $this->fileHelper=$fileHelper;
        $this->stat = $stat;
        parent::__construct();

    }

    protected function configure()
    {
        // ...
        $this
            ->setDescription('Update photos [exif, tags]')
            ->setHelp('Update photos [exif, tags, stats]')
            /*->setDefinition(
                new InputDefinition([
                    new InputOption('exif', '-E',InputOption::VALUE_OPTIONAL,"Update Exif",false),
                    new InputOption('tag', '-T',InputOption::VALUE_OPTIONAL,'Update tag',false),
                    new InputOption('stat', '-s',InputOption::VALUE_OPTIONAL,'Show focal information',false)
                ])
             )*/
             ->addOption('exif', 'E',InputOption::VALUE_OPTIONAL,"Update Exif",true)
             ->addOption('tag', 'T',InputOption::VALUE_OPTIONAL,'Update tag',true)
             ->addOption('stat', 's',InputOption::VALUE_OPTIONAL,'Show focal information',true);
    }

    protected function setExif(InputInterface $input, OutputInterface $output){
        $output->writeln("--------- Set Exif --------");

        $photos = $this->em->getRepository('App:Photos')->findBy(['exif'=>null],['albumId'=>'ASC']);
        $album = new Album();
        foreach($photos as $photo) {
            if ($album->getId()!=$photo->getAlbumId())
                $album=$this->em->getRepository('App:Album')->findOneBy(['id'=>$photo->getAlbumId()]);
            if ($album==null) {
                $output->writeln("Found photo without album:".$photo->getPath()." => Deleting");
                $this->em->remove($photo);
                $album = new Album();
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


    protected function setTag(InputInterface $input, OutputInterface $output){
        $output->writeln("---------  Set Tag  --------");
        $output->writeln("Not yet implemented, get tag from AWS Rekognition");
        return Command::SUCCESS;
    }

    protected function getStat(InputInterface $input, OutputInterface $output){
        $output->writeln("--------- Get Stat --------");
        $stat=$this->stat->getStat();
        $total=$stat["total"];
        unset($stat["total"]);
        arsort($stat);
        foreach($stat as $focale=>$percent)
            $output->writeln($focale." mm : ".strval(round($percent/$total,2)*100).' %');
        return Command::SUCCESS;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ... put here the code to run in your command

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        if($input->getOption('exif')==null)
            return $this->setExif($input,$output);

        if($input->getOption('tag')==null)
            return $this->setTag($input,$output);

        if($input->getOption('stat')==null)
            return $this->getStat($input,$output);


        $args = $this->getNativeDefinition()->getArguments();
        $this->getNativeDefinition()->setArguments($args);
        $helpCommand = $this->getApplication()->get('help');
        $helpCommand->run(new ArrayInput(['command_name' => $this->getName()]), $output);
        return Command::FAILURE;
       // return $help->run($input, $output);
        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return 
    }
}