<?php

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class CounterService
{
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;

    /**
     * @var integer
     */
    private $maxValue;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $fileName;

    /**
     * ThirdPartyCallService constructor.
     *
     * @param ContainerInterface $container
     * @param integer $maxValue
     * @param string $filePath
     * @param LoggerInterface $logger
     * @param string $fileName
     */
    public function __construct(
        ContainerInterface $container,
        int $maxValue,
        string $filePath,
        LoggerInterface $logger,
        string $fileName
    )
    {
        $this->serviceContainer = $container;
        $this->maxValue         = $maxValue;
        $this->filePath         = $filePath;
        $this->logger           = $logger;
        $this->fileName         = $fileName;
    }

    /**
     * Function to update the alert counter in file if
     * the alert count has not reached maximum value and return the current Value.
     *
     *
     * @return int
     */
    public function updateAlertCountInFile(): int
    {
        $dir = $this->serviceContainer->get('kernel')->getProjectDir().$this->filePath;
        $absoluteFile = $dir.$this->fileName;
        $fs = new Filesystem();
        $fs->mkdir($dir);

        if (!$fs->exists($absoluteFile)) {
            $fs->dumpFile($absoluteFile, json_encode(['alert_count' => 0]));
        }

        $count = json_decode(file_get_contents($absoluteFile), TRUE);

        if (!isset($count['alert_count']) || !is_integer($count['alert_count'])) {
            $this->logger->error('Invalid Data found in incremental File.');
            $fs->dumpFile($absoluteFile, json_encode(['alert_count' => 0]));
        }

        $newValue = $count['alert_count'] + 1;
        $fs->dumpFile($absoluteFile, json_encode(['alert_count' => $newValue]));

        return $newValue;
    }

    /**
     * Function to return current value of Alert Counter.
     *
     * @return int
     */
    public function getAlertCountValue(): int
    {
        $absoluteFile = $this->serviceContainer->get('kernel')->getProjectDir().$this->filePath.$this->fileName;

        if (!is_file($absoluteFile)) {
            return 0;
        }

        $currentValue = json_decode(file_get_contents($absoluteFile), TRUE);

        return (isset($currentValue['alert_count']) && is_integer($currentValue['alert_count']))
            ? $currentValue['alert_count'] : 0
        ;
    }
}