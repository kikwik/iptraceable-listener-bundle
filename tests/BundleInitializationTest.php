<?php


namespace Kikwik\IpTraceableListenerBundle\Tests;


use Gedmo\IpTraceable\IpTraceableListener;
use Kikwik\IpTraceableListenerBundle\EventSubscriber\IpTraceableSubscriber;
use Kikwik\IpTraceableListenerBundle\KikwikIpTraceableListenerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return KikwikIpTraceableListenerBundle::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->addCompilerPass(new PublicServicePass('|gedmo.ip_traceable.ip_traceable_listener|'));
        $this->addCompilerPass(new PublicServicePass('|kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber|'));
    }


    public function testInitBundle()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config.yml');

        // Add some other bundles we depend on
        $kernel->addBundle(FrameworkBundle::class);
        $kernel->addBundle(StofDoctrineExtensionsBundle::class);

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $services = [
            'gedmo.ip_traceable.ip_traceable_listener' => IpTraceableListener::class,
            'kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber' => IpTraceableSubscriber::class,
        ];
        foreach($services as $serviceId => $serviceClass)
        {
            $this->assertTrue($container->has($serviceId),'Container has '.$serviceId);
            $service = $container->get($serviceId);
            $this->assertInstanceOf($serviceClass, $service, 'Service '.$serviceId.' is instance of '.$serviceClass);
        }
    }
}