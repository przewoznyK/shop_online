<?php 
namespace App\Command;


use App\Entity\OrderProduct;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class MyCommand extends Command
{
    private $entityManager;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;

        parent::__construct();
    }
    protected static $deafultName = 'my:command';

    protected function configure()
    {
        $this->setName('my:command');
        $this->setDescription('My commandd description');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statusArray = $this->entityManager->getRepository(OrderProduct::class)->findBy(['status' => ['ready_to_pick_up','shipped']]);
        foreach ($statusArray as $status)
        {
            $checkStatus = $status->getStatus();
            
            if($checkStatus == 'shipped')
            {
                $startDelivery = $status->getStartDelivery();
                $now = new \DateTime();

                $interval = $startDelivery->diff($now);
                
                if($interval-> i >=5)
                {
                    
                    $email = (new Email())
                    ->from('symfony_project_shop@proton.me')
                    ->to($status->getEmail())
                    ->subject('Your delivery ')
                    ->html('Hi '.$status->getName().'<br>   Your delivery is ready to pickup in '.$status->getFinalLocation());
            
                    $this->mailer->send($email);
                    $status->setStatus('done');
                    $output->writeln('Change status to done: '.$status->getId());
                }
            }
            else if($checkStatus == 'ready_to_pick_up')
            {
                $email = (new Email())
                ->from('symfony_project_shop@proton.me')
                ->to($status->getEmail())
                ->subject('Your delivery ')
                ->html('Hi '.$status->getName().'<br>   Your delivery is ready to pickup in '.$status->getFinalLocation());
        
                $this->mailer->send($email);
                $status->setStatus('done');
                $output->writeln('Change status to done: '.$status->getId());
            }
            
            
        }
        $this->entityManager->flush();
        return Command::SUCCESS;    
    }
}