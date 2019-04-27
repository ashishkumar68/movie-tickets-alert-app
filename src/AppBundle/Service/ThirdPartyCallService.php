<?php

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ThirdPartyCallService
{
    /**
     * @var ContainerInterface
     */
    private $serviceContainer;

    /**
     * ThirdPartyCallService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Function to Make cURL request to URI.
     *
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param string $payload
     *
     * @return string
     * @throws \Exception
     */
    public function makeCurlToThirdParty(
        string $url,
        string $method = 'GET',
        array $headers = [],
        string $payload = ''
    ): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);

        return ['content' => $result, 'code' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE)];
    }
}