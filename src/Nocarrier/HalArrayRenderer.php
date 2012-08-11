<?php
/**
 * This file is part of the Hal library
 *
 * (c) Ben Longden <ben@nocarrier.co.uk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Nocarrier
 */
namespace Nocarrier;

/**
 * HalArrayRenderer
 * 
 * @uses HalRenderer
 * @package Nocarrier
 * @author Ben Longden <ben@nocarrier.co.uk>
 */
class HalArrayRenderer implements HalRenderer
{
    /**
     * render
     *
     * @param Hal $resource
     * @param bool $pretty
     * @return string
     */
    public function render(Hal $resource, $pretty = true)
    {
        $options = 0;

        return $this->arrayForArray($resource, $pretty);
    }

    /**
     * Return an array (compatible with the hal+json format) representing associated links
     *
     * @param mixed $uri
     * @param array $links
     * @return array
     */
    protected function linksForArray($uri, $links)
    {
        $data = array('self' => array('href' => $uri));

        foreach($links as $rel => $links) {
            if (count($links) === 1) {
                $data[$rel] = array('href' => $links[0]['uri']);
                if (!is_null($links[0]['title'])) {
                    $data[$rel]['title'] = $links[0]['title'];
                }
                foreach ($links[0]['attributes'] as $attribute => $value) {
                    $data[$rel][$attribute] = $value;
                }
            } else {
                $data[$rel] = array();
                foreach ($links as $link) {
                    $item = array('href' => $link['uri']);
                    if (!is_null($link['title'])) {
                        $item['title'] = $link['title'];
                    }
                    foreach ($link['attributes'] as $attribute => $value) {
                        $item[$attribute] = $value;
                    }
                    $data[$rel][] = $item;
                }
            }
        }

        return $data;
    }

    /**
     * Return an array (compatible with the hal+json format) representing associated resources
     *
     * @param mixed $resources
     * @return array
     */
    protected function resourcesForArray($resources)
    {
        $data = array();

        foreach ($resources as $resource) {
            $data[] = $this->arrayForArray($resource);
        }

        return $data;
    }

    /**
     * Return an array (compatible with the hal+json format) representing the
     * complete response
     *
     * @param Hal $resource
     * @return array
     */
    protected function arrayForArray(Hal $resource)
    {
        $data = $resource->getData();
        $data['_links'] = $this->linksForArray($resource->getUri(), $resource->getLinks());

        foreach($resource->getResources() as $rel => $resources) {
            $data['_embedded'][$rel] = $this->resourcesForArray($resources);
        }

        return $data;
    }

}
