<?php

namespace App\CrawlObserver;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

class CategoryCrawlObserver extends CrawlObserver
{

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        // TODO: Implement crawled() method.
        $html = $response->getBody();
        logger($html);
        $crawler = new Crawler($html);

        foreach ($crawler as $domElement) {
            //var_dump($domElement->nodeName);
            logger($domElement->nodeName);
        }
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        // TODO: Implement crawlFailed() method.
        logger("crawlFailed");
    }
}
