<?php

namespace Palzin\Symfony\Bundle\Command;

use Palzin\Palzin;
use Palzin\Configuration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PalzinTestCommand extends Command
{
    /**
     * The default command name.
     *
     * @var string|null
     */
    protected static $defaultName = 'palzin:test';

    /**
     * The default command description.
     *
     * @var string|null
     */
    protected static $defaultDescription = 'Send data to your Palzin Monitor (APM) dashboard.';

    /**
     * @var Palzin
     */
    protected $palzin;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Palzin\Configuration
     */
    protected $configuration;

    /**
     * PalzinTestCommand constructor.
     *
     * @param Palzin $palzin
     * @param LoggerInterface $logger
     * @param Configuration $configuration
     */
    public function __construct(Palzin $palzin, LoggerInterface $logger, Configuration $configuration)
    {
        parent::__construct();

        $this->palzin = $palzin;
        $this->logger = $logger;
        $this->configuration = $configuration;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
        ;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->palzin->isRecording()) {
            $io->warning('Palzin is not enabled');

            return Command::FAILURE;
        }

        $io->block("I'm testing your Palzin integration.", 'INFO', 'fg=green', ' ', true);

        // Test proc_open function availability
        try {
            proc_open("", [], $pipes);
        } catch (\Throwable $exception) {
            $io->warning("❌ proc_open function disabled.");

            return Command::FAILURE;
        }

        // Check Palzin API key
        $this->palzin->addSegment(function ($segment) use ($io) {
            usleep(10 * 1000);

            !empty($this->configuration->getIngestionKey())
                ? $io->text('✅ Palzin Monitor (APM) ingestion key installed.')
                : $io->warning('❌ Palzin Monitor (APM) ingestion key not specified. Make sure you specify the PALZIN_APM_INGESTION_KEY in your .env file.');

            $segment->addContext('example payload', ['key' => $this->configuration->getIngestionKey()]);
        }, 'test', 'Check Palzin Monitor (APM) Ingestion key');

        // Check Palzin is enabled
        $this->palzin->addSegment(function ($segment) use ($io) {
            usleep(10 * 1000);

            $this->configuration->isEnabled()
                ? $io->text('✅ Palzin Monitor (APM) is enabled.')
                : $io->warning('❌ Palzin Monitor (APM) is actually disabled, turn to true the `enable` field of the `palzin-apm` config file.');


            $segment->addContext('another payload', ['enable' => $this->configuration->isEnabled()]);
        }, 'test', 'Check if Palzin Monitor (APM) is enabled');

        // Check CURL
        $this->palzin->addSegment(function ($segment) use ($io) {
            usleep(10 * 1000);

            function_exists('curl_version')
                ? $io->text('✅ CURL extension is enabled.')
                : $io->warning('❌ CURL is actually disabled so your app could not be able to send data to Palzin.');

            $segment->addContext('another payload', ['foo' => 'bar']);
        }, 'test', 'Check CURL extension');

        // Report Exception
        $this->palzin->reportException(new \Exception('First Exception detected using Palzin Monitor (APM)'));
        // End the transaction
        $this->palzin->currentTransaction()
            ->setResult('success')
            ->end();

        // Logs will be reported in the transaction context.
        $this->logger->debug("In this section, you can access the log entries that were created throughout the transaction.");

        /*
         * Loading demo data
         */
        $io->text('Loading demo data...');

        foreach ([1, 2, 3, 4, 5, 6] as $minutes) {
            $this->palzin->startTransaction("Other transactions")
                ->start(microtime(true) - 60*$minutes)
                ->setResult('success')
                ->end(rand(100, 200));

            $this->logger->debug("In this section, you can access the log entries that were created throughout the transaction.");
        }

        $io->success('Done!');

        return 0;
    }
}
