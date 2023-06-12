<?php

namespace Palzin\Symfony\Bundle\Inspectable\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Types\Type;
use Palzin\Palzin;

class InspectableSQLLogger implements SQLLogger
{
    /** @var Palzin */
    protected $palzin;

    /** @var \Palzin\Models\PerformanceModel|\Palzin\Models\Segment */
    protected $segment;

    /** @var array */
    protected $configuration;

    /** @var string */
    protected $connectionName;

    /**
     * InspectableSQLLogger constructor.
     *
     * @param Palzin $palzin
     * @param array $configuration
     * @param string $connectionName
     */
    public function __construct(Palzin $palzin, array $configuration, string $connectionName)
    {
        $this->palzin = $palzin;
        $this->configuration = $configuration;
        $this->connectionName = $connectionName;
    }

    /**
     * Logs a SQL statement.
     *
     * @param string $sql SQL statement
     * @param array<int, mixed>|array<string, mixed>|null $params Statement parameters
     * @param array<int, Type|int|string|null>|array<string, Type|int|string|null>|null $types Parameter types
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        // This check is needed as transaction is flushed in MessengerEventSubscriber
        if (!$this->palzin->hasTransaction()) {
            return;
        }

        $this->segment = $this->palzin->startSegment("doctrine:".$this->connectionName, substr($sql, 0, 50));

        $context = ['sql' => $sql];

        // Checks if option is set and is convertible to true
        if (!empty($this->configuration['query_bindings']) && $params) {
            $context['bindings'] = $params;
        }

        $this->segment->addContext('DB', $context);
    }

    /**
     * Marks the last started query segment as stopped.
     */
    public function stopQuery(): void
    {
        // This check is needed as transaction is flushed in MessengerEventSubscriber
        if (!$this->palzin->hasTransaction()) {
            return;
        }

        if (null === $this->segment) {
            throw new \LogicException('Attempt to stop a segment that has not been started');
        }

        $this->segment->end();
        $this->segment = null;
    }
}
