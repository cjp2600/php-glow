<?php

namespace Glow;

use Spiral\Goridge\Exceptions\RPCException;

/**
 * Class Response
 * @package App\Services
 */
final class Response
{
    /** @var array */
    private $source;

    /** @var array */
    private $response;

    /**
     * Response constructor.
     * @param array $source
     */
    public function __construct(array $source)
    {
        $this->source = $source;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRawResponse(string $id): string
    {
        if (key_exists('jobs', $this->source)) {
            return $this->source['jobs'][mb_strtolower($id)];
        }
        throw new Exception("job $id not exist");
    }

    /**
     * @param string $id
     * @param bool $assoc
     * @return mixed
     */
    public function getResponse(string $id, bool $assoc = false)
    {
        if (empty($this->response[$id])) {
            try {
                $this->response[$id] = json_decode($this->getRawResponse($id), $assoc);
            } catch (RPCException $e) {
                throw new Exception($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $this->response[$id];
    }
}