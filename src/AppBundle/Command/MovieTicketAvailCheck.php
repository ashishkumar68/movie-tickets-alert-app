<?php

namespace AppBundle\Command;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MovieTicketAvailCheck extends ContainerAwareCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $maxValue;

    protected function configure()
    {
        $this->setName('app:check-for-movie-tickets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->logger->info('======Ticket Check Job Started======');
            $this->logger->info('======Checking if max count is reached======');

            $counterService = $this->container->get('app.counter_service');

            if ($this->maxValue === $counterService->getAlertCountValue()) {
                $this->logger->info('=======Max Alert Value is reached quitting Job=======');

                return 1;
            }

            $uri = $this->container->getParameter('hit_url');
            $this->logger->info('Hitting URL:'. $uri);

            $response = $this->container
                ->get('app.thirdparty_call_service')
                ->makeCurlToThirdParty($uri)
            ;

            if (200 !== $response['code']) {
                $this->logger->error('Invalid Response retrieved from third Party Server, quitting Job.');

                return 1;
            }

            $isPresent = $this->container->get('app.search_ticket_avail_service')
                ->checkCinemaPresenceInHTML(
                    $response['content'],
                    $this->container->getParameter('cinema_name')
                )
            ;

            if (false === $isPresent) {
                $this->logger->info('Tickets are not available Yet, quitting Job');

                return 1;
            }

            $sentStatus = $this->container->get('app.email_notifier_service')
                ->sendEmail(
                    'Hurray! Your Tickets are here',
                    $this->container->getParameter('recipients')
                )
            ;

            if (false === $sentStatus['status']) {
                $this->logger->info('Failed to Send Email, quitting Job');

                return 1;
            }

            $this->logger->info('Email has been sent, Updating Alert Count Now');
            $counterService->updateAlertCountInFile();

            $this->logger->info('Updated Email Count, Done!!!');
        } catch (\Exception $e) {
            $this->logger->error($this->getName(). ' Command Failed due to Error:'. $e->getMessage());

            return 1;
        }
    }

    public function setServiceContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setCronLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setMaxValue(int $maxValue)
    {
        $this->maxValue = $maxValue;
    }
}