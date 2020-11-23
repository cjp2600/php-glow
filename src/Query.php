<?php

namespace Glow;

use Spiral\Goridge\Exceptions\RPCException;

/**
 * Class Query
 * @package App\Services
 */
final class Query
{
    /** @var array */
    private $source;

    public function __construct(array $source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    function prepare(): string
    {
        try {

            $jsonObj = json_encode($this->source);

            if ($jsonObj === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('encode json error');
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $jsonObj;
    }

}