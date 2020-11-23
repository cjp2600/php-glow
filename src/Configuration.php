<?php

namespace Glow;

/**
 * Class Configuration
 * @package Glow
 */
class Configuration
{
    /**
     * Roadrunner RPC address
     * @var string
     */
    private $address;

    /**
     * Roadrunner RPC port
     * @var int
     */
    private $port;

    /**
     * Set is debug query on RR CLI
     * @var bool
     */
    private $debug;

    /**
     * Configuration constructor.
     * @param string $address
     * @param int $port
     */
    public function __construct(string $address = '127.0.0.1', int $port = 6001)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
}