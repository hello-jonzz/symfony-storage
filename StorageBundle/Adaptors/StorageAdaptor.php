<?php

namespace Bluesquare\StorageBundle\Adaptors;

interface StorageAdaptor
{
    public function index();
    public function mode($name);
    public function store($source_path, $target_path);
    public function retrieve($distant_path, $local_path);
    public function stream($distant_path, $target_stream);
    public function delete($distant_path);
}