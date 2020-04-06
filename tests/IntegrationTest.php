<?php

namespace Kikwik\IpTraceableListenerBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Gedmo\IpTraceable\IpTraceableListener;
use Kikwik\IpTraceableListenerBundle\EventSubscriber\IpTraceSubscriber;
use Kikwik\IpTraceableListenerBundle\KikwikIpTraceableListenerBundle;
use PHPUnit\Framework\TestCase;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class IntegrationTest extends TestCase
{
    private $kernel;

    protected function tearDown() : void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->kernel->getCacheDir());

        parent::tearDown();
    }

    public function testServiceWiring()
    {
        $this->kernel = new KikwikIpTraceableTestingKernel();
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $traceableListener = $container->get('gedmo.ip_traceable.ip_traceable_listener');
        $this->assertInstanceOf(IpTraceableListener::class, $traceableListener);

        $traceableSubscriber = $container->get('kikwik.ip_traceable_listener.event_subscriber.ip_trace_subscriber');
        $this->assertInstanceOf(IpTraceSubscriber::class, $traceableSubscriber);
    }
}

class KikwikIpTraceableTestingKernel extends Kernel
{
    public function __construct()
    {
        parent::__construct('test',true);
    }

    public function registerBundles()
    {
        return [
            new StofDoctrineExtensionsBundle(),
            new KikwikIpTraceableListenerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->register('annotation_reader', AnnotationReader::class);
        });
    }

    public function getCacheDir()
    {
        return parent::getCacheDir().'/'.spl_object_hash($this);
    }
}