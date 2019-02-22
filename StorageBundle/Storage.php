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
//        dump($user_config); die;
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

    protected function getStorageAnnotation($entity, $attribute)
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

        return $storage_annotation;
    }

    public function getStorageFor($entity, $attribute)
    {
        $annotation = $this->getStorageAnnotation($entity, $attribute);
        return $annotation->name;
    }

    public function url($entity, $attribute)
    {
        $annotation = $this->getStorageAnnotation($entity, $attribute);
        $storage = $this->get($annotation->name);
        $prefix = is_null($annotation->prefix) || empty($annotation->prefix) ? '' : trim($annotation->prefix, '/').'/';
        $camel = ucfirst(Container::camelize($attribute));
        return $storage->url("$prefix{$entity->{"get".$camel}()}");
    }

    public function delete($entity, $attribute)
    {
        $annotation = $this->getStorageAnnotation($entity, $attribute);
        $storage = $this->get($annotation->name);
        $prefix = is_null($annotation->prefix) || empty($annotation->prefix) ? '' : trim($annotation->prefix, '/').'/';
        $camel = ucfirst(Container::camelize($attribute));
        return $storage->delete("$prefix{$entity->{"get".$camel}()}");
    }

    public function retrieve($entity, $attribute, $local_path)
    {
        $annotation = $this->getStorageAnnotation($entity, $attribute);
        $storage = $this->get($annotation->name);
        $prefix = is_null($annotation->prefix) || empty($annotation->prefix) ? '' : trim($annotation->prefix, '/').'/';
        $camel = ucfirst(Container::camelize($attribute));
        return $storage->retrieve("$prefix{$entity->{"get".$camel}()}", $local_path);
    }

    public function stream($entity, $attribute, $target_stream)
    {
        $annotation = $this->getStorageAnnotation($entity, $attribute);
        $storage = $this->get($annotation->name);
        $prefix = is_null($annotation->prefix) || empty($annotation->prefix) ? '' : trim($annotation->prefix, '/').'/';
        $camel = ucfirst(Container::camelize($attribute));
        return $storage->stream("$prefix{$entity->{"get".$camel}()}", $target_stream);
    }

    public function store($entity, $attribute, $file)
    {
        $storage_annotation = $this->getStorageAnnotation($entity, $attribute);
        $file_hash = hash('sha256', time().$attribute.uniqid());
        $storage = $this->get($storage_annotation->name);

        if (is_null($storage))
        {
            throw new UnknownStorage("Unknown storage {$storage_annotation->name} for $attribute in ".get_class($entity));
        }

        $prefix = is_null($storage_annotation->prefix) || empty($storage_annotation->prefix) ? '' : trim($storage_annotation->prefix, '/').'/';
        $mode = $storage->mode($storage_annotation->mode);
        $camel = ucfirst(Container::camelize($attribute));

        if ($file instanceof UploadedFile) {
            $file_hash .= strlen($file->getClientOriginalExtension()) > 0 ? '.'.$file->getClientOriginalExtension() : '';

            if (!is_null($storage_annotation->mime))
            {
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
            $previous_file_hash = $entity->{"get$camel"}();
            if (!is_null($previous_file_hash) && !empty($previous_file_hash)) {
                $storage->delete("$prefix$previous_file_hash");
            }
        }
        elseif (is_string($file) && file_exists($file)) {
            $storage->store($file, "$prefix$file_hash", $mode);
        }
        else {
            throw new InvalidFileException("Invalid file argument");
        }

        $entity->{"set$camel"}($file_hash);

        return $file_hash;
    }
}
