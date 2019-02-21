<?php

namespace Bluesquare\StorageBundle;

use Aws\S3\S3Client;
use Bluesquare\StorageBundle\Adaptors\S3Storage;
use Bluesquare\StorageBundle\Exceptions\InvalidFileException;
use Bluesquare\StorageBundle\Exceptions\MimeTypeException;
use Bluesquare\StorageBundle\Exceptions\MissingStorageAnnotation;
use Bluesquare\StorageBundle\Exceptions\UnknownStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Bluesquare\StorageBundle\Annotations\Storage as StorageAnnotation;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Interface de manipulation des stockages préconfigurés
 * Usage par injection
 */
class Storage
{
    private $config_storage = [];

    public function __construct(array $user_config = [])
    {
        dump($user_config); die;
        $this->config_storage = $user_config;
    }

    public function get($storage_name)
    {
        if (array_key_exists($storage_name, $this->config_storage))
        {
            $config = $this->config_storage[$storage_name];

            switch ($config['type'])
            {
                case 's3': return (new S3Storage($storage_name, $config));
            }
        }

        return (null);
    }

    public function store($entity, $attribute, $file)
    {
        $reflection = new \ReflectionProperty($entity, $attribute);
        $reader = new AnnotationReader();
        $annotations = $reader->getPropertyAnnotations($reflection);

        $storage_annotation = null;

        foreach ($annotations as $annotation)
        {
            if ($annotation instanceof StorageAnnotation) {
                $storage_annotation = $annotation;
            }
        }

        if (is_null($storage_annotation))
        {
            throw new MissingStorageAnnotation("Missing Storage annotation for $attribute in ".get_class($entity));
        }

        $file_hash = hash('sha256', time().$attribute.uniqid());
        $storage = $this->get($storage_annotation->name);

        if (is_null($storage_annotation))
        {
            throw new UnknownStorage("Unknown storage for $attribute in ".get_class($entity));
        }

        $prefix = is_null($storage_annotation->prefix) || empty($storage_annotation->prefix) ? '' : trim($storage_annotation->prefix, '/').'/';

        $mode = $storage->mode($storage_annotation->mode);

        if ($file instanceof UploadedFile) {
            if (!is_null($storage_annotation->mime)) {
                $valid = true;
                if (count(explode('/', $storage_annotation->mime)) > 1) {
                    $valid = strtolower($storage_annotation->mime) == $file->getMimeType();
                }
                else {
                    $valid = strtolower($storage_annotation->mime) == explode('/', $file->getMimeType())[0];
                }
                if (!$valid) {
                    throw new MimeTypeException("Invalid mime type");
                }
            }
            $storage->store($file->getRealPath(), "$prefix$file_hash", $mode);
        }
        elseif (is_string($file) && file_exists($file)) {
            $storage->store($file, "$prefix$file_hash", $mode);
        }
        else {
            throw new InvalidFileException("Invalid file arguement");
        }

        $camel = lcfirst(Container::camelize($attribute));

        $entity->{"set$camel"}($file_hash);

        return $file_hash;
    }
}
