<?php


namespace Palzin\Symfony\Bundle\Listeners;

use Palzin\Palzin;
use Palzin\Models\Segment;
use Palzin\Models\Transaction;
use Throwable;

trait PalzinAwareTrait
{
    /**
     * @var Palzin
     */
    protected $palzin;

    /**
     * @var Segment[]
     */
    protected $segments = [];

    /**
     * Checks if segments can be added
     */
    protected function canAddSegments(): bool
    {
        return $this->palzin->canAddSegments();
    }

    /**
     * Checks if transaction is needed
     */
    protected function needsTransaction(): bool
    {
        return $this->palzin->needTransaction();
    }

    /**
     * Be sure to start a transaction before report the exception.
     *
     * @throws \Exception
     */
    protected function startTransaction(string $name): ?Transaction
    {
        if ($this->palzin->needTransaction()) {
            $this->palzin->startTransaction($name);
        }

        return $this->palzin->currentTransaction();
    }

    /**
     * Report unexpected error to inspection API.
     *
     * @throws \Exception
     */
    protected function notifyUnexpectedError(Throwable $throwable): void
    {
        $this->palzin->reportException($throwable, false);
    }

    protected function startSegment(string $type, string $label = null): Segment
    {
        $segment = $this->palzin->startSegment($type, $label);

        $this->segments[$label] = $segment;

        return $segment;
    }

    /**
     * Terminate the segment.
     *
     * @param string $label
     */
    protected function endSegment(string $label): void
    {
        if (!isset($this->segments[$label])) {
            return;
        }

        $this->segments[$label]->end();

        unset($this->segments[$label]);
    }
}
