<?php

namespace Bluesquare\StorageBundle;

use Bluesquare\StorageBundle\DependencyInjection\StorageExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StorageBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        dump($container); die;
    }

    public function getContainerExtension()
    {
        if (null === $this->extension)
            $this->extension = new StorageExtension();
        return $this->extension;
    }
}
