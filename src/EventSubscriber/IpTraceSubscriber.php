<?php

namespace Kikwik\IpTraceableListenerBundle\EventSubscriber;

use Gedmo\IpTraceable\IpTraceableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class IpTraceSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Gedmo\IpTraceable\IpTraceableListener
     */
    private $ipTraceableListener;

    public function __construct(IpTraceableListener $ipTraceableListener)
    {
        $this->ipTraceableListener = $ipTraceableListener;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (null === $request) {
            return;
        }

        // If you use a cache like Varnish, you may want to set a proxy to Request::getClientIp() method
        // $request->setTrustedProxies(array('127.0.0.1'));

        // $ip = $_SERVER['REMOTE_ADDR'];
        $ip = $request->getClientIp();

        if (null !== $ip) {
            $this->ipTraceableListener->setIpValue($ip);
        }
    }


    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

}