<?php

namespace Zacksleo\NeweggSdk;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['api'] = function ($pimple) {
            return new Api($pimple->getConfig()['key'], $pimple->getConfig()['secret'], $pimple->getConfig()['seller_id']);
        };
    }
}
