<?php
/**
 * FacebookPages.php
 *
 * @package Providers
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace SimpleLifestream\Providers;

/**
 * A provider for Facebook Pages
 */
class FacebookPages extends Adapter
{
    /** inline {@inheritdoc} */
    protected $url = 'http://www.facebook.com/feeds/page.php?id=%s&format=json';

    /** inline {@inheritdoc} */
    public function getApiData()
    {
        $response = $this->http->fetch($this->getApiUrl());
        $response = json_decode($response, true);

        if (!empty($response['entries']))
            return array_map(array($this, 'filterResponse'), $response['entries']);

        return null;
    }

    /** inline {@inheritdoc} */
    protected function filterResponse(array $value = array())
    {
        if (trim($value['title']) !== '')
            $text = $value['title'];
        else if (strlen(strip_tags($value['content'])) > 80)
            $text = substr(strip_tags($value['content']), 0, 80) . '...';
        else
            $text = $value['alternate'];

        return array(
            'service'  => 'facebookpages',
            'type'     => 'link',
            'resource' => $value['author']['name'],
            'stamp'    => (int) strtotime($value['published']),
            'url'      => $value['alternate'],
            'text'     => $text
        );
    }
}

?>
