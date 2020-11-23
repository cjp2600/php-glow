<?php

namespace Glow;

/**
 * Class Job
 *
 * @package Glow
 */
final class Job implements Builder
{
    /**
     * Allowed methods
     *
     * @var array
     */
    public static $methods = [
        'get' => false,
        'head' => false,
        'post' => true,
        'put' => true,
        'patch' => true,
        'delete' => true,
        'options' => false,
    ];

    /**
     * Allowed types
     *
     * @var string[]
     */
    public static $types = [
        'string',
        'int',
        'int32',
        'int64',
        'float64',
    ];

    /**
     * The Glow request id.
     *
     * @var string
     */
    private $id;

    /**
     * The HTTP method to use.
     *
     * @var string
     */
    private $method;

    /**
     * The URL the request is sent to.
     *
     * @var string
     */
    private $url;

    /**
     * The headers sent with the request.
     *
     * @var array
     */
    private $headers = array();

    /**
     * The cookies sent with the request.
     *
     * @var array
     */
    private $cookies = array();

    /**
     * POST data sent with the request.
     *
     * @var array
     */
    private $data = array();

    /**
     * @var int|mixed
     */
    private $encoding;

    /**
     * @var array
     */
    private $var;

    /**
     * Set the HTTP method of the request.
     *
     * @param string $method
     * @return Job
     */
    public function setMethod(string $method): Job
    {
        $method = strtolower($method);

        if (!array_key_exists($method, static::$methods)) {
            throw new \InvalidArgumentException("Method [$method] not a valid HTTP method.");
        }

        if ($this->data && !static::$methods[$method]) {
            throw new \LogicException('Request has POST data, but tried changing HTTP method to one that does not allow POST data');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Get the HTTP method of the request.
     *
     * @return string
     */
    protected function getMethod(): string
    {
        if (strlen($this->method) == 0) {
            $this->method = 'get';
        }
        return $this->method;
    }

    /**
     * Set id.
     *
     * @param string $id
     * @return Job
     */
    public function setId(string $id): Job
    {
        $this->id = mb_strtolower($id);

        return $this;
    }

    /**
     * Get id.
     *
     * @return Job
     */
    protected function getId(): string
    {
        if (strlen($this->id) > 0) {
            return $this->id;
        }

        return $this->url;
    }

    /**
     * Set the URL of the request.
     *
     * @param string $url
     * @return Job
     */
    public function setUrl(string $url): Job
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the Variables of the request.
     *
     * @param string $name
     * @param string $JPath
     * @param string $type = 'string'
     * @return Job
     */
    public function setVariable(string $name, string $JPath, string $type = 'string'): Job
    {
        if (!in_array($type, static::$types)) {
            throw new \InvalidArgumentException("Type [$type] not a valid.");
        }

        $this->var[] = [
            'name' => $name,
            'type' => $type,
            'jPath' => $JPath,
        ];

        return $this;
    }

    /**
     * Get the Variables of the request.
     *
     * @return array
     */
    protected function getVar(): array
    {
        if (!is_array($this->var)) {
            return [];
        }
        return $this->var;
    }

    /**
     * Get the URL of the request.
     *
     * @return Job
     */
    protected function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set a specific header to be sent with the request.
     *
     * @param string $key Can also be a string in "foo: bar" format
     * @param mixed $value
     * @param bool $preserveCase
     * @return Job
     */
    public function setHeader(string $key, $value = null, $preserveCase = false): Job
    {
        if ($value === null) {
            list($key, $value) = explode(':', $value, 2);
        }

        if (!$preserveCase) {
            $key = strtolower($key);
        }

        $key = trim($key);
        $this->headers[$key] = trim($value);

        return $this;
    }

    /**
     * Get a specific header from the request.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getHeader(string $key): string
    {
        $key = strtolower($key);

        return isset($this->headers[$key]) ? $this->headers[$key] : "";
    }

    /**
     * Get the headers to be sent with the request.
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        if (!is_array($this->headers)) {
            return [];
        }
        return $this->headers;
    }

    /**
     * Set a cookie.
     *
     * @param string $key
     * @param string $value
     * @return Job
     */
    public function setCookie(string $key, string $value): Job
    {
        $this->cookies[$key] = $value;
        $this->updateCookieHeader();

        return $this;
    }

    /**
     * Replace the request's cookies.
     *
     * @param array $cookies
     * @return Job
     */
    public function setCookies(array $cookies): Job
    {
        $this->cookies = $cookies;
        $this->updateCookieHeader();

        return $this;
    }

    /**
     * Read the request cookies and set the cookie header.
     *
     * @return void
     */
    private function updateCookieHeader()
    {
        $strings = [];

        foreach ($this->cookies as $key => $value) {
            $strings[] = "{$key}={$value}";
        }

        $this->setHeader('cookie', implode('; ', $strings));
    }

    /**
     * Get a specific cookie from the request.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getCookie(string $key): string
    {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : "";
    }

    /**
     * Get all the request's cookies.
     *
     * @return string[]
     */
    protected function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Set the POST data to be sent with the request.
     * Only Json format supported!
     *
     * @param array $data
     * @return Job
     */
    public function setData(array $data): Job
    {
        if ($data && !static::$methods[$this->method]) {
            throw new \InvalidArgumentException("HTTP method [$this->method] does not allow POST data.");
        }

        $this->data = $data;

        // Only Json format supported!
        $this->setHeader('Content-Type', 'application/json');

        return $this;
    }

    /**
     * Get the POST data to be sent with the request.
     *
     * @return mixed
     */
    protected function getData(): array
    {
        if (!is_array($this->data)) {
            return [];
        }
        return $this->data;
    }

    /**
     * Get the current encoding which will be used on the POST data
     *
     * @return int  a Request::ENCODING_* constant
     */
    protected function getEncoding(): int
    {
        return $this->encoding;
    }

    /**
     * Get variablle 
     * 
     * @return string
     */
    public function var(string $variableName): string
    {
        return `$` . $variableName;
    }

    /**
     * @param string $token
     * @param string $prefix
     * @return $this
     */
    public function setAuth(string $token, string $prefix = 'Bearer '): Job
    {
        $this->setHeader('Authorization', $prefix . $token);
        return $this;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return $this->build();
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $response = [];

        // build job request
        $response['id'] = $this->getId();
        $response['method'] = $this->getMethod();
        $response['url'] = $this->getUrl();

        if (count($this->getData()) > 0) {
            $response['body'] = $this->getData();
        }

        if (count($this->getHeaders()) > 0) {
            $response['header'] = $this->getHeaders();
        }

        if (count($this->getVar()) > 0) {
            $response['var'] = $this->getVar();
        }

        return $response;
    }

}