<?php


namespace Palzin\Symfony\Bundle\Listeners;

use Palzin\Palzin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @var Palzin
     */
    protected $palzin;

    /**
     * ConsoleEventsSubscriber constructor.
     *
     * @param Palzin $palzin
     */
    public function __construct(Palzin $palzin)
    {
        $this->palzin = $palzin;
    }

    /**
     * @uses onWorkerMessageFailed
     * @uses onWorkerMessageHandled
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onWorkerMessageFailed',
            WorkerMessageHandledEvent::class => 'onWorkerMessageHandled',
        ];
    }

    /**
     * Handle worker fail.
     *
     * @param WorkerMessageFailedEvent $event
     * @throws \Exception
     */
    public function onWorkerMessageFailed(WorkerMessageFailedEvent $event)
    {
        if (! $this->palzin->isRecording()) {
            return;
        }

        // reportException will create a transaction if it doesn't exists.
        $this->palzin->reportException($event->getThrowable());
        $this->palzin->currentTransaction()->setResult('error');
        $this->palzin->flush();
    }

    /**
     * MessageHandled.
     *
     * @param WorkerMessageHandledEvent $event
     * @throws \Exception
     */
    public function onWorkerMessageHandled(WorkerMessageHandledEvent $event)
    {
        if (!$this->palzin->hasTransaction()) {
            return;
        }

        $processedByStamps = $event->getEnvelope()->all(HandledStamp::class);
        $processedBy = [];

        /** @var HandledStamp $handlerStamp */
        foreach ($processedByStamps as $handlerStamp) {
            $processedBy[] = $handlerStamp->getHandlerName();
        }

        $this->palzin->currentTransaction()
            ->addContext('Handlers', $processedBy)
            ->addContext('Envelope', \serialize($event->getEnvelope()));

        $this->palzin->flush();
    }
}
