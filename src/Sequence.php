<?php

namespace Glow;

/**
 * Class Sequence
 * @package Glow
 */
final class Sequence implements Builder
{
    /** @var string */
    const Parallel = 'parallel';

    /** @var string */
    const Sync = 'sync';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $jobs;

    /**
     * @return string
     */
    protected function getType(): string
    {
        if (empty($this->type)) {
            $this->setType(self::Sync);
        }

        return $this->type;
    }

    /**
     * @param string $type
     * @return Sequence
     */
    protected function setType(string $type): Sequence
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return $this
     */
    public function parallel(): Sequence
    {
        $this->setType(self::Parallel);

        return $this;
    }

    /**
     * @return $this
     */
    public function sync(): Sequence
    {
        $this->setType(self::Sync);

        return $this;
    }

    /**
     * @param Builder ...$jobs
     * @return Sequence
     */
    public function jobs(Builder ...$jobs): Sequence
    {
        foreach ($jobs as $job) {
            $this->jobs[] = $job->build();
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getJobs(): array
    {
        return $this->jobs;
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
        $response['type'] = $this->getType();
        $response['jobs'] = $this->getJobs();

        return $response;
    }

}