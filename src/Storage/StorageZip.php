<?php

namespace Epub\Storage;
use Epub\Storage\StorageInterface;
use ZipArchive;

class StorageZip implements StorageInterface
{
    private ZipArchive $zip;

    public function __construct(string $name)
    {
        file_put_contents($name, \base64_decode($this->initial()));
        $written = file_put_contents('php://memory', \base64_decode($this->initial()));

        $this->zip = new ZipArchive();
        $this->zip->open($name, ZipArchive::CREATE);
    }

    public function createContainer(string $path): bool
    {
        return true;
    }

    public function createResource(string $path, string $content): bool
    {
        if ($path === 'mimetype') return true;
        $this->zip->addFromString($path, $content);
        return true;
    }

    private function initial(): string
    {
        return 'UEsDBAoAAAAAAFk6/lBvYassFAAAABQAAAAIAAAAbWltZXR5cGVhcHBsaWN'.
                'hdGlvbi9lcHViK3ppcFBLAQIeAwoAAAAAAFk6/lBvYassFAAAABQAAAAIA'.
                'AAAAAAAAAAAAACkgQAAAABtaW1ldHlwZVBLBQYAAAAAAQABADYAAAA6AAAAAAA=';
    }
}