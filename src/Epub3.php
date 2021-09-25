<?php

namespace Epub;

use Epub\ContainerInterface;
use Epub\Document\Navigation\NavigationInterface;
use Epub\Document\Navigation\XHTMLNavigation;
use Epub\Document\Package;
use Epub\Formatter\FormatterInterface;
use Epub\Storage\StorageInterface;
use Epub\Storage\StorageMemory;
use Epub\Formatter\BlankFormatter;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use RecursiveIterator;
use DateTime;

class Epub3 implements ContainerInterface {

    private NavigationInterface $navigation;
    private Package $package;
    private FormatterInterface $formatter;
    private StorageInterface $storage;
    private LoggerInterface $logger;
    private ?string $coverPage = null;
    private ?array $coverImage = null;

    public function __construct(string $title)
    {
        $this->setPackage(new Package($this->generateUUID(), $title, new DateTime()));
        $this->setNavigation(new XHTMLNavigation());
        $this->setStorage(new StorageMemory());
        $this->setFormatter(new BlankFormatter());

        $this->logger = new Logger('epub3');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setNavigation(NavigationInterface $navigation): self
    {
        $this->navigation = $navigation;
        return $this;
    }

    public function setPackage(Package $package): self
    {
        $this->package = $package;
        return $this;
    }

    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;
        $formatter->setWorkspace($this);

        return $this;
    }

    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    public function setCoverPage(string $content): self
    {
        $this->coverPage = $content;
        return $this;
    }

    public function setCoverImage($resource, $mediaType, $extension): self
    {
        $this->coverImage = [
            'resource' => $resource,
            'mediaType' => $mediaType,
            'extension' => $extension,
        ];
        return $this;
    }

    public function addResource($content, string $mediaType, string $extension): string
    {
        $id = 'resource'.\md5($content);
        $this->package->addManifest("resources/{$id}.{$extension}", $id, $mediaType);
        $this->storage->createResource("EPUB/resources/{$id}.{$extension}", $content);

        return "../resources/{$id}.{$extension}";
    }

    public function save(RecursiveIterator $iterator): void
    {
        $this->logger->info("Starting process with formatter : " . get_class($this->formatter));
        $this->storage->createResource('mimetype', 'application/epub+zip');

        $this->storage->createContainer('META-INF');
        $this->storage->createContainer('EPUB');
        $this->storage->createContainer('EPUB/resources');
        $this->storage->createContainer('EPUB/content');

        $this->storage->createResource('META-INF/container.xml', '<?xml version="1.0"?>'. "\n" .
            '<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">'. "\n" .
            '<rootfiles>'. "\n" .
            '    <rootfile full-path="EPUB/package.opf" media-type="application/oebps-package+xml" />'. "\n" .
            '</rootfiles>'. "\n" .
            '</container>');

        if ($this->coverPage) {
            $this->storage->createResource('EPUB/content/cover-page.xhtml', (string) $this->coverPage);
            $this->package
                ->addManifest('content/cover-page.xhtml', 'cover-page', 'application/xhtml+xml')
                ->addSpine('cover-page')
            ;
        }

        if ($this->coverImage) {
            $id = 'cover'.\md5($this->coverImage['resource']);
            $this->storage
                ->createResource("EPUB/resources/{$id}.{$this->coverImage['extension']}", $this->coverImage['resource']);
            $this->package
                ->addManifest("resources/{$id}.{$this->coverImage['extension']}", 'cover', $this->coverImage['mediaType'], ['cover-image']);
        }

        $this->package
            ->addManifest('toc.xhtml', 'toc-xhtml', 'application/xhtml+xml', ['nav'])
            ->addSpine('toc-xhtml');

        $this->iterate($iterator, $this);

        $this->storage->createResource('EPUB/toc.xhtml', (string) $this->navigation);
        $this->storage->createResource('EPUB/package.opf', (string) $this->package);

        $this->logger->info("Process done");
    }

    public function encodeContentUri(string $name): string
    {
        $fileName = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        return"content/". rawurlencode($fileName).".xhtml";
    }

    private function addPage(string $title, ?string $content = null, ?NavigationInterface $navItem = null): NavigationInterface
    {
        $contentLocation = $this->encodeContentUri($title);

        if ($content) {
            $this->storage->createResource("EPUB/{$contentLocation}", $content);
            $this->package->addManifest($contentLocation, \md5($contentLocation));
            $this->package->addSpine(\md5($contentLocation));
        }

        $returnNavItem = ($navItem)
            ? $returnNavItem = $navItem->addNavigation($title, $contentLocation)
            : $returnNavItem = $this->navigation->addNavigation($title, $contentLocation);

        return $returnNavItem;
    }

    private function iterate(RecursiveIterator $iterator, Epub3 $workspace, NavigationInterface $navItem = null)
    {
        foreach ($iterator as $value) {
            if ($iterator->hasChildren()) {
                try {
                    $chapterTemplate = $this->formatter->chapterTemplate(clone $value, clone $iterator->getChildren());
                    $subNavItem = $workspace->addPage(
                        $this->formatter->formatChapterTitle($value->getName()),
                        $chapterTemplate ? $chapterTemplate->saveXML() : null,
                        $navItem
                    );
                    $this->iterate($iterator->getChildren(), $workspace, $subNavItem);
                    continue;
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage() .' '. $value->getName(), $e->getTrace());
                    exit(1);
                }
                $this->logger->debug("Processed {$value->getName()}");
            }
            try {
                $pageTemplate = $this->formatter->pageTemplate(clone $value, $value->getContent());
                $workspace->addPage(
                    $this->formatter->formatPageTitle($value->getName(), $value->getContent()),
                    $pageTemplate ? $pageTemplate->saveXML() : null,
                    $navItem
                );
                $this->logger->debug("Processed {$value->getName()}");
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage() . ' ' . $value->getName(), $e->getTrace());
            }

        }
    }

    private function generateUUID(): string
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}