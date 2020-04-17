<?php


namespace Kikwik\IpTraceableListenerBundle\Tests;


use Gedmo\IpTraceable\IpTraceableListener;
use Kikwik\IpTraceableListenerBundle\EventSubscriber\IpTraceableSubscriber;
use Kikwik\IpTraceableListenerBundle\KikwikIpTraceableListenerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;

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
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('gedmo.ip_traceable.ip_traceable_listener'),'Container has gedmo.ip_traceable.ip_traceable_listener');
        $service = $container->get('gedmo.ip_traceable.ip_traceable_listener');
        $this->assertInstanceOf(IpTraceableListener::class, $service, 'Service gedmo.ip_traceable.ip_traceable_listener is instance of IpTraceableListener');

        $this->assertTrue($container->has('kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber'),'Conteiner has kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber');
        $service = $container->get('kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber');
        $this->assertInstanceOf(IpTraceableSubscriber::class, $service,'Service kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber is instance of IpTraceSubscriber');
    }
}