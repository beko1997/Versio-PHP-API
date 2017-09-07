<?php
namespace BekoDesign\versioAPI;

use BekoDesign\versioAPI\Contracts\IClient;
use BekoDesign\versioAPI\Models\Category;
use Http\Discovery\HttpClientDiscovery;
use Http\Message\Authentication;
use Http\Message\Authentication\BasicAuth;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;


class Client implements IClient
{
    const VERSIO_SCHEMA = 'https';
    const VERSIO_HOST = 'www.versio.nl';
    const VERSIO_URL_LIVE = '/api/v1/';
    const VERSIO_URL_TEST = '/testapi/v1/';

    protected $username;
    private $password;

    protected $path;
    protected $host;

    /**
     * @var Authentication
     */
    private $authentication = null;

    /**
     * @var \Http\Client\HttpAsyncClient
     */
    private $httpAsyncClient;

    /**
     * @var \Http\Client\HttpClient
     */
    private $httpClient;

    /**
     * Client constructor.
     *
     * @param $username string Versio Username
     * @param $password string Versio Password
     * @param string $path
     * @param string $host
     * @internal param string $api_url
     */
    public function __construct($username, $password, $path = Client::VERSIO_URL_LIVE, $host = Client::VERSIO_HOST)
    {
        $this->username = $username;
        $this->password = $password;
        $this->path = $path;
        $this->host = $host;

        $this->authentication = new BasicAuth($this->username, $this->password);
        $this->httpAsyncClient = HttpAsyncClientDiscovery::find();
        $this->httpClient = HttpClientDiscovery::find();
    }

    /**
     * @return Category
     */
    public function categories() : Category {
        return new Category($this);
    }

    /**
     * @param $request RequestInterface
     * @return ResponseInterface
     */
    public function sendRequest($request) : ResponseInterface {
        return $this
            ->httpClient
            ->sendRequest(
                $this->parseRequest($request)
            );

    }

    /**
     * @param $request RequestInterface
     * @return Promise
     */
    public function sendRequestAsync($request) : Promise {

        return $this
            ->httpAsyncClient
            ->sendAsyncRequest(
                $this->parseRequest($request)
            );

    }

    /**
     * @param $request RequestInterface
     * @return RequestInterface
     */
    private function parseRequest(RequestInterface $request) : RequestInterface {
        return $this->authentication
            ->authenticate(
                $request->withUri(
                    $this->pasrseUri($request->getUri())
                )
            );
    }

    /**
     * @param UriInterface $uri
     * @return UriInterface
     */
    private function pasrseUri(UriInterface $uri) : UriInterface {
        return $uri
            ->withScheme(Client::VERSIO_SCHEMA)
            ->withHost($this->host)
            ->withPath($this->path .  $uri->getPath());
    }


}