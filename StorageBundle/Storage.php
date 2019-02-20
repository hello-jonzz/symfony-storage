<?php

namespace Bluesquare\StorageBundle;

use Aws\S3\S3Client;
use Bluesquare\StorageBundle\Adaptors\S3Storage;

/**
 * Interface de manipulation des stockages préconfigurés
 * Usage par injection
 */
class Storage
{
    private $config_storage = [];

    public function __construct(array $user_config = [])
    {
        $this->config_storage = $user_config;
    }

    public function get($storage_name)
    {
        if (array_key_exists($storage_name, $this->config_storage))
        {
            $config = $this->config_storage[$storage_name];

            dump($config); die;

            switch ($config['type'])
            {
                case 's3': return (new S3Storage($storage_name, $config));
            }

        }

        return (null);
    }
}
