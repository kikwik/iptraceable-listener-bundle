<?php


namespace Kikwik\IpTraceableListenerBundle\Tests;


use Gedmo\IpTraceable\IpTraceableListener;
use Kikwik\IpTraceableListenerBundle\EventSubscriber\IpTraceableSubscriber;
use Kikwik\IpTraceableListenerBundle\KikwikIpTraceableListenerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

    private function initKernel()
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

        return $kernel;
    }

    public function testInitBundle()
    {
        $kernel = $this->initKernel();
        $container = $this->getContainer();

        // Test if you services exists
        $services = [
            'gedmo.ip_traceable.ip_traceable_listener' => IpTraceableListener::class,
            'kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber' => IpTraceableSubscriber::class,
        ];
        foreach($services as $serviceId => $serviceClass)
        {
            $this->assertTrue($container->has($serviceId),'Container should have '.$serviceId);
            $service = $container->get($serviceId);
            $this->assertInstanceOf($serviceClass, $service, 'Service '.$serviceId.' should be an instance of '.$serviceClass);
        }
    }

    public function testSetIp()
    {
        $kernel = $this->initKernel();
        $container = $this->getContainer();

        $listener = $container->get('gedmo.ip_traceable.ip_traceable_listener');
        $subscriber = $container->get('kikwik.ip_traceable_listener.event_subscriber.ip_traceable_subscriber');

        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $requestMock->method('getClientIp')->willReturn('127.0.0.2');

        $requestEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\RequestEvent')->setConstructorArgs([$kernel,$requestMock,HttpKernelInterface::MASTER_REQUEST])->getMock();
        $requestEventMock->method('getRequest')->willReturn($requestMock);

        $subscriber->onKernelRequest($requestEventMock);
        $this->assertEquals('127.0.0.2',$listener->getFieldValue(null,null,null),'IpTraceableSubscriber should copy user IP from Request to IpTraceableListener');
    }
}