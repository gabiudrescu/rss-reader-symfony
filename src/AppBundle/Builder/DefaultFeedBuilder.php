<?php

namespace AppBundle\Builder;

use AppBundle\Model\Feed;
use AppBundle\Model\FeedInterface;
use AppBundle\Model\FeedItem;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Class DefaultFeedBuilder
 * @package AppBundle\Builder
 * @author  Mardari Dorel <mardari.dorua@gmail.com>
 */
class DefaultFeedBuilder implements FeedBuilderInterface
{
    /** @var FeedInterface */
    private $feed;

    /** @var \SimpleXMLElement */
    private $feedXml;

    /** @var string */
    private $uri;

    /** @var string */
    private $suffix;

    /**
     * DefaultFeedBuilder constructor.
     *
     * @param string $uri
     * @param string $suffix
     */
    public function __construct($uri, $suffix)
    {
        $this->uri = $uri;
        $this->suffix = $suffix;
    }

    /**
     * @inheritdoc
     */
    public function createFeed()
    {
        $title = (string) $this->getFeedXML()->channel->title;
        $link = (string) $this->getFeedXML()->channel->link;

        $this->feed = new Feed($title, $link);
    }

    /**
     * @inheritdoc
     */
    public function createFeedItem(\SimpleXMLElement $item)
    {
        $feedItemTitle = (string) $item->title;
        $feedItemDate = new \DateTime((string) $item->pubDate);
        $feedItemDescription = (string) $item->description;
        $feedItemImage = '';

        $feedItem = new FeedItem($feedItemTitle, $feedItemDate, $feedItemDescription, $feedItemImage);

        $this->getFeed()->addItem($feedItem);
    }

    /**
     * @inheritdoc
     */
    public function fetchFeed(HandlerStack $stack)
    {
        $client = new Client([
            'base_uri' => $this->uri,
            'headers' => ['Accept' => 'application/xml'],
            'handler' => $stack,
        ]);

        $response = $client->get($this->suffix);

        $this->feedXml = new \SimpleXMLElement($response->getBody()->getContents());
    }

    /**
     * @inheritdoc
     */
    public function getFeed(): FeedInterface
    {
        return $this->feed;
    }

    /**
     * @inheritdoc
     */
    public function getFeedXML(): \SimpleXMLElement
    {
        if (!$this->feedXml) {
            throw new \Exception(sprintf(
                'Please make sure you have fetched the feed xml. Builder: %s',
                get_class($this)
            ));
        }

        return $this->feedXml;
    }
}
