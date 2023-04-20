<?php

namespace App\Command;


use App\Entity\OrderProduct;
use App\Entity\Product;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MyCommand extends Command
{
    private $entityManager;
    private $mailer;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->params = $params;

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
        $productsInfoArray = [];
        $url = $this->params->get('app.url');
        $mainDir = $this->params->get('app.mainDir');
        $orderArray = $this->entityManager->getRepository(OrderProduct::class)->findBy(['status' => ['ready_to_pick_up', 'shipped']]);
        foreach ($orderArray as $order) {
            $checkStatus = $order->getStatus();
            $token = $order->getToken();

            $delivered = false;
            $startDelivery = $order->getStartDelivery();
            $now = new \DateTime();
            $feedbackUrl = $url . '/feedback_product/' . $token;
            if($startDelivery)
            {
                $interval = $startDelivery->diff($now);
                if($interval->i >= 5 ) $delivered = true;
            }

            if ($delivered  || $order->getStatus = 'ready_to_pick_up') {
                $priceDetailsArray = [];
                $priceDetails = $order->getPriceDetails();
                $priceDetailsExplode = explode('+', $priceDetails);
                foreach ($priceDetailsExplode as $element) {
                    $elementExplode = explode('*', $element);
                    $priceDetailsArray[] = $elementExplode;
                }

                $myProducts = $order->getProduct();
                $productsIdArray = explode('|', $myProducts);
                $productsIdAndQuantityArray = array();
                foreach ($productsIdArray as $cart) {
                    $keyValue = explode(':', $cart);
                    $productsIdAndQuantityArray[$keyValue[0]] = $keyValue[1];
                    $myProductsDirImages =  array();
                    $myProductsInfo = array();
                    $i = 0;
                    foreach (array_keys($productsIdAndQuantityArray) as $myCartId) {
                        $productUser = $this->entityManager->getRepository(Product::class)->find($myCartId);
                        if ($productUser) {
                            $myProductsInfo[] = $productUser;
                            $myProductsInfo[$i]->quantityUser = array_values($productsIdAndQuantityArray)[$i];
                            $myProductsDirImages[$i]['dir'] = $productUser->getImagesDir();
                            foreach ($myProductsDirImages[$i] as $images) {
                                $owner = $productUser->getUser();
                                $folder = $mainDir . '/users_data/' . $owner->getId() . '/products/' . $images;
                                $dirForEmail = $url . '/users_data/' . $owner->getId() . '/products/' . $images;
                                $dir = opendir($folder);
                                $oneTime = true;
                                if ($oneTime) {
                                    while ($file = readdir($dir)) {
                                        if ($file != '.' && $file != '..') {
                                            $myProductsDirImages[$i]['images'][] = $file;
                                            $productsInfoArray[$i]['images'] = $dirForEmail . '/' . $file;
                                            $productsInfoArray[$i]['productPrice'] = $priceDetailsArray[$i][0];
                                            $productsInfoArray[$i]['productQuantity'] = $priceDetailsArray[$i][1];

                                            //  $productsInfoArray
                                            $oneTime = false;
                                        }
                                    }
                                    closedir($dir);
                                }
                            }
                            $myProductsDirImages[$i]['id'] = $owner->getId();
                            $output->writeln($myProductsDirImages[$i]['id'] = $owner->getId());
                            $i++;
                        }
                    }


                    $email = (new TemplatedEmail())
                        ->from('symfony_project_shop@proton.me')
                        ->to($order->getEmail())
                        ->subject('Your delivery ')
                        ->htmlTemplate('html_templates/email_template.html.twig')
                        ->context([
                            'productsInfoArray' => $productsInfoArray,
                            'orderArray' => $orderArray,
                            'feedbackUrl' => $feedbackUrl
                        ]);
                    $this->mailer->send($email);
                    $order->setStatus('done');
                    $output->writeln('Change status to done: ' . $order->getId());
                }
            } 
        }
        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
