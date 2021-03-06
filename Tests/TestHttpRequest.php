<?php
/**
 * TestHttpRequest.php
 *
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestHttpRequest extends PHPUnit_Framework_TestCase
{
    public function testInvalidUrl()
    {
        $this->setExpectedException('Exception');

        $http = new \SimpleLifestream\HttpRequest(array());
        $http->fetch('this is an invalid url');
    }

    public function testInvalidUrl2()
    {
        $this->setExpectedException('Exception');

        if (!ini_get('allow_url_fopen'))
        {
            $this->markTestIncomplete('Could not test file_get_contents wrapper, allow_url_fopen is closed');
            return ;
        }

        $http = new \SimpleLifestream\HttpRequest(array('prefer_curl' => false));
        $http->fetch('this is an invalid url');
    }

    public function testFetchCurl()
    {
        $http = new \SimpleLifestream\HttpRequest(array());
        $response = $http->fetch('http://httpbin.org/user-agent');
        $response = json_decode($response, true);

        $this->assertEquals('Mozilla/5.0 PHP/SimpleLifestream', $response['user-agent']);

        $config = array(
            'curl' => array(
                CURLOPT_USERAGENT => 'PHP/Morcilla',
            )
        );

        $http = new \SimpleLifestream\HttpRequest($config);
        $response = $http->fetch('http://httpbin.org/user-agent');
        $response = json_decode($response, true);

        $this->assertEquals('PHP/Morcilla', $response['user-agent']);

        $config = array(
            'curl' => array(
                CURLOPT_FOLLOWLOCATION => 0,
                CURLOPT_USERAGENT => 'PHP/Morcilla 2',
            ),
            'force_redirects' => true
        );

        $http = new \SimpleLifestream\HttpRequest($config);
        $response = $http->fetch('http://httpbin.org/relative-redirect/2');
        $response = json_decode($response, true);

        $this->assertEquals('http://httpbin.org/get', $response['url']);

        $response = $http->fetch('http://httpbin.org/redirect-to?url=' . urlencode('http://httpbin.org/user-agent'));
        $response = json_decode($response, true);

        $this->assertEquals('PHP/Morcilla 2', $response['user-agent']);

        $config = array(
            'curl' => array(
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_USERAGENT => 'PHP/Morcilla 3',
            ),
        );

        $http = new \SimpleLifestream\HttpRequest($config);
        $response = $http->fetch('http://httpbin.org/relative-redirect/2');
        $response = json_decode($response, true);

        $this->assertEquals('http://httpbin.org/get', $response['url']);

        $response = $http->fetch('http://httpbin.org/redirect-to?url=' . urlencode('http://httpbin.org/user-agent'));
        $response = json_decode($response, true);

        $this->assertEquals('PHP/Morcilla 3', $response['user-agent']);
    }

    public function testFetchFileGetcontents()
    {
        if (!ini_get('allow_url_fopen'))
        {
            $this->markTestIncomplete('Could not test file_get_contents wrapper, allow_url_fopen is closed');
            return ;
        }

        $config = array(
            'prefer_curl' => false,
            'fopen' => array(
                'user_agent' => 'PHP/FGC Morcilla'
            )
        );

        $http = new \SimpleLifestream\HttpRequest($config);
        $response = $http->fetch('http://httpbin.org/user-agent');
        $response = json_decode($response, true);

        $this->assertEquals('PHP/FGC Morcilla', $response['user-agent']);

        $http = new \SimpleLifestream\HttpRequest($config);
        $response = $http->fetch('http://httpbin.org/relative-redirect/2');
        $response = json_decode($response, true);

        $this->assertEquals('http://httpbin.org/get', $response['url']);

        $response = $http->fetch('http://httpbin.org/redirect-to?url=' . urlencode('http://httpbin.org/user-agent'));
        $response = json_decode($response, true);

        $this->assertEquals('PHP/FGC Morcilla', $response['user-agent']);
    }
}

?>
