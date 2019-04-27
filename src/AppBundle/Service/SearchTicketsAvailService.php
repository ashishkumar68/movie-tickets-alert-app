<?php

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

class SearchTicketsAvailService
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
     * Function to check if the div element is present with text in HTML or not.
     *
     * @param string $content
     * @param string $searchString
     *
     * @return bool
     */
    public function checkCinemaPresenceInHTML(string $content, string $searchString): bool
    {
        $html = html_entity_decode($content, ENT_HTML5, 'UTF-8');
        $crawler = new Crawler($html);

        $nodes = $crawler->filterXPath('//div[text()="'.$searchString.'"]');

        if (!empty($nodes) && !empty($nodes->first())) {
            return true;
        }

        return false;
    }
}