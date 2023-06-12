<?php


namespace Palzin\Symfony\Bundle\DependencyInjection;

use Palzin\Palzin;
use Palzin\Symfony\Bundle\Inspectable\Twig\InspectableTwigExtension;
use Palzin\Symfony\Bundle\Listeners\ConsoleEventsSubscriber;
use Palzin\Symfony\Bundle\Listeners\KernelEventsSubscriber;
use Palzin\Symfony\Bundle\Listeners\MessengerEventsSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class PalzinExtension extends Extension
{
    /**
     * Current version of the bundle.
     */
    const VERSION = '23.03.22';

    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException|\Exception When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('palzin.configuration.definition', $config);

        /*
         * Palzin configuration
         */
        $palzinConfigDefinition = new Definition(\Palzin\Configuration::class, [$config['ingestion_key']]);
        $palzinConfigDefinition->setPublic(false);
        $palzinConfigDefinition->addMethodCall('setEnabled', [$config['enabled']]);
        $palzinConfigDefinition->addMethodCall('setUrl', [$config['url']]);
        $palzinConfigDefinition->addMethodCall('setTransport', [$config['transport']]);
        $palzinConfigDefinition->addMethodCall('setVersion', [self::VERSION]);

        $container->setDefinition(\Palzin\Configuration::class, $palzinConfigDefinition);

        /*
         * Palzin service itself
         */
        $palzinDefinition = new Definition(Palzin::class, [$palzinConfigDefinition]);
        $palzinDefinition->setPublic(true);
        $container->setDefinition(Palzin::class, $palzinDefinition);

        /*
         * Kernel events subscriber: request, response etc.
         */
        $kernelEventsSubscriberDefinition = new Definition(KernelEventsSubscriber::class, [
            new Reference(Palzin::class),
            new Reference(RouterInterface::class),
            new Reference(Security::class),
            $config['ignore_routes']
        ]);
        $kernelEventsSubscriberDefinition->setPublic(false)->addTag('kernel.event_subscriber');
        $container->setDefinition(KernelEventsSubscriber::class, $kernelEventsSubscriberDefinition);

        /*
         * Connect the messenger event subscriber
         */
        if (interface_exists(MessageBusInterface::class) && true === $config['messenger']) {
            $messengerEventsSubscriber = new Definition(MessengerEventsSubscriber::class, [
                new Reference(Palzin::class)
            ]);

            $messengerEventsSubscriber->setPublic(false)->addTag('kernel.event_subscriber');
            $container->setDefinition(MessengerEventsSubscriber::class, $messengerEventsSubscriber);
        }

        /*
         * Console events subscriber
         */
        $consoleEventsSubscriberDefinition = new Definition(ConsoleEventsSubscriber::class, [
            new Reference(Palzin::class),
            $config['ignore_commands'],
        ]);

        $consoleEventsSubscriberDefinition->setPublic(false)->addTag('kernel.event_subscriber');
        $container->setDefinition(ConsoleEventsSubscriber::class, $consoleEventsSubscriberDefinition);

        /*
         * Twig
         */
        if (true === $config['templates']) {
            $inspectableTwigExtensionDefinition = new Definition(InspectableTwigExtension::class, [
                new Reference(Palzin::class),
            ]);

            $inspectableTwigExtensionDefinition->addTag('twig.extension');
            $container->setDefinition(InspectableTwigExtension::class, $inspectableTwigExtensionDefinition);
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
