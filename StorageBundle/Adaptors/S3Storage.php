<?php

namespace Bluesquare\StorageBundle\Adaptors;

use Aws\S3\S3Client;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Interface de manipulation du stockage S3
 * Usage: $storage = new S3Storage('my_storage_name', $config);
 * @author Maxime Renou
 */
class S3Storage
{
    const MODE_PRIVATE = 'private';
    const MODE_PUBLIC = 'public-read';

    protected $client;
    protected $bucket;
    protected $region;

    private $required_config_field = ['bucket', 'region', 'endpoint', 'credientials'];

    public function __construct ($storage_name, $config)
    {
        // TODO: on check la présence de "bucket", "region", "endpoint", "credentials" ("key", "secret") dans $config

        if ($this->configIsNotNormed($config))
            throw new Exception("Error from config file :(");
//        if (isset($config['bucket']) && isset($config))

        $this->config = $config;
        $this->bucket = $config['bucket'];

        $this->client = new S3Client([
            'version'  => isset($config['version']) ? $config['version'] : 'latest',
            'region'   => $config['region'],
            'endpoint' => $config['endpoint'],
            'credentials' => [
                'key'    => $config['credentials']['key'],
                'secret' => $config['credentials']['secret']
            ]
        ]);
    }

    private function configIsNotNormed($config)
    {
        dump($config); die;
        dump(array_diff($this->required_config_field, $config)); die;
        return (false);
//        return ()
    }

    protected function getPrefix($prefix = null)
    {
        $ret = '';
        if (isset($this->config['path'])) {
            $ret = trim($this->config['path'], '/').'/';
        }
        if (!is_null($prefix)) {
            $ret = ltrim($prefix, '/');
        }
        return $ret;
    }

    public function index($prefix = null)
    {
        return ($this->client->listObjectsV2([
            'Bucket' => $this->bucket,
            'Prefix' => $this->getPrefix($prefix)
        ]));
    }

    /**
     * Permet d'obtenir l'URL vers une ressource S3
     * Cette ressource n'est accessible que si le fichier a été stocké en mode public
     * @param $target_path
     * @return string
     */
    public function url ($target_path)
    {
        return rtrim($this->config['endpoint'], '/').'/'.$this->getPrefix().ltrim($target_path, '/');
    }

    /**
     * Permet de stocker un fichier dans S3
     * @param $source_path
     * @param $target_path
     * @param string $permissions
     * @return \Aws\Result
     */
    public function store ($source_path, $target_path, $permissions = self::MODE_PRIVATE)
    {
        return $this->client->putObject([
            'Bucket' => $this->bucket,
            'Path'   => $this->getPrefix().$target_path,
            'Key'    => $this->getPrefix().$target_path,
            'Body'   => file_get_contents($source_path),
            'ACL'    => $permissions
        ]);
    }

    /**
     * Permet de récupérer un fichier dans S3 pour le stocker en local
     * @param $distant_path
     * @param $local_path
     */
    public function retrieve ($distant_path, $local_path)
    {
        $file_stream = fopen($local_path, 'w');

        $aws_stream = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $this->getPrefix().$distant_path,
            'Path'   => $this->getPrefix().$distant_path,
        ])->get('Body')->detach();

        stream_copy_to_stream($aws_stream, $file_stream);
        fclose($file_stream);
    }

    /**
     * Permet de stream un fichier stocké dans S3
     * @param $distant_path
     * @param $target_stream
     */
    public function stream ($distant_path, $target_stream)
    {
        $aws_stream = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $this->getPrefix().$distant_path,
            'Path'   => $this->getPrefix().$distant_path,
        ])->get('Body')->detach();

        stream_copy_to_stream($aws_stream, $target_stream);
    }

    /**
     * Permet de supprimer un fichier stocké dans S3
     * @param $distant_path
     */
    public function delete ($distant_path)
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Path'   => $this->getPrefix().$distant_path,
            'Key'    => $this->getPrefix().$distant_path,
        ]);
    }
}
