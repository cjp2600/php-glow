<?php

namespace Glow;

use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;

/**
 * Class Glow
 *
 * @package App\Services
 */
final class Glow implements Builder
{
    /**
     * execute
     * @var Execute
     */
    protected $execute;

    /**
     * @var array
     */
    private $sequence;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * Glow constructor.
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $relay = new SocketRelay($config->getAddress(), $config->getPort());
        $rpc = new RPC($relay);

        $this->config = $config;

        $this->execute = new Execute($rpc);
    }

    /**
     * @param Builder $seq
     * @return array
     */
    public function sequenceSwitcher(Builder $seq): array
    {
        switch (get_class($seq)) {
            case Job::class:
                return $this->sync($seq)->build();
            default:
                return $seq->build();
        }
    }

    /**
     * @param Builder ...$sequence
     * @return Glow
     */
    public function sequences(Builder ...$sequence): Glow
    {

        foreach ($sequence as $seq) {
            $this->sequence[] = $this->sequenceSwitcher($seq);
        }

        return $this;
    }

    /**
     * @param Builder ...$jobs
     * @return Sequence
     */
    public function sync(Builder ...$jobs): Sequence
    {
        return (new Sequence())->sync()->jobs(...$jobs);
    }

    /**
     * @param Builder ...$jobs
     * @return Sequence
     */
    public function parallel(Builder ...$jobs): Sequence
    {
        return (new Sequence())->parallel()->jobs(...$jobs);
    }

    /**
     * @param string $id
     * @return Job
     */
    public function request(string $id): Job
    {
        return (new Job())->setId($id);
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * @return array
     */
    protected function getSequence(): array
    {
        return $this->sequence;
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
        $response['sequence'] = $this->getSequence();
        $response['options'] = $this->getOptions();

        return $response;
    }

    /**
     * @return array
     */
    private function getOptions(): array 
    {
        return [
            "debug" => $this->config->isDebug()
        ];
    }

    /**
     * @return Response
     */
    public function execute(): Response
    {
        $source = $this->prepare($this->execute->execute(new Query($this->build())));

        return new Response($source);
    }

    /**
     * @param string $source
     * @return array
     */
    private function prepare(string $source): array
    {
        try {
            $jsonObj = json_decode($source, true);

            if ($jsonObj === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('decode json error');
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $jsonObj;
    }

}