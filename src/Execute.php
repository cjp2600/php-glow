<?php

namespace Glow;

use Spiral\Goridge\Exceptions\RPCException;
use Spiral\Goridge\RPC;

/**
 * Class Execute
 */
final class Execute
{
    /**
     * execute glow rpc method name
     * @var string
     */
    protected $executeMethod = 'glow.Execute';

    /** @var RPC */
    private $rpc;

    /**
     * @param RPC $rpc
     */
    public function __construct(RPC $rpc)
    {
        $this->rpc = $rpc;
    }

    /**
     * @param Query $query
     * @return string
     */
    public function execute(Query $query):string
    {
        try {
           return $this->rpc->call($this->executeMethod, $query->prepare());
        } catch (RPCException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

}
