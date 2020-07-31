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
use RecursiveIterator;
use DateTime;

class Epub3 implements ContainerInterface {

    private NavigationInterface $navigation;
    private Package $package;
    private FormatterInterface $formatter;
    private StorageInterface $storage;
    private ?string $coverPage = null;

    public function __construct(string $title) {
        $this->setPackage(new Package($this->generateUUID(), $title, new DateTime()));
        $this->setNavigation(new XHTMLNavigation());
        $this->setStorage(new StorageMemory());
        $this->setFormatter(new BlankFormatter());
    }

    public function setNavigation(NavigationInterface $navigation): self {
        $this->navigation = $navigation;
        return $this;
    }

    public function setPackage(Package $package): self {
        $this->package = $package;
        return $this;
    }

    public function setFormatter(FormatterInterface $formatter): self {
        $this->formatter = $formatter;
        $formatter->setWorkspace($this);

        return $this;
    }

    public function setStorage(StorageInterface $storage): self {
        $this->storage = $storage;
        return $this;
    }

    public function setCoverPage(string $content): self {
        $this->coverPage = $content;

        return $this;
    }

    public function setCoverImage($resource): self {

        return $this;
    }

    public function addResource($content, string $mimetype, string $extension): string {
        $id = 'resource'.\md5($content);
        $this->package->addManifest("resources/{$id}.{$extension}", $id, $mimetype);
        $this->storage->createResource("EPUB/resources/{$id}.{$extension}", $content);

        return "../resources/{$id}.{$extension}";
    }

    public function save(RecursiveIterator $iterator): void {
        $this->storage->createResource('mimetype', 'application/epub+zip');

        $this->storage->createContainer('META-INF');
        $this->storage->createContainer('EPUB');
        $this->storage->createContainer('EPUB/images');
        $this->storage->createContainer('EPUB/content');

        //     ->addManifest('content/styles.css', 'css', 'text/css')
        //     ->addManifest('images/cover.jpg', 'cover-image', 'image/jpeg')

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

        $this->package
            ->addManifest('toc.xhtml', 'toc-xhtml', 'application/xhtml+xml', ['nav'])
            ->addSpine('toc-xhtml');

        $this->iterate($iterator, $this);

        $this->storage->createResource('EPUB/toc.xhtml', (string) $this->navigation);
        $this->storage->createResource('EPUB/package.opf', (string) $this->package);
    }

    private function addPage(string $title, ?string $content = null, ?NavigationInterface $navItem = null): NavigationInterface {
        $contentLocation = null;

        if ($content) {
            $id = 'item'.md5($title.$content);
            $contentLocation = "content/{$id}.xhtml";
            $this->storage->createResource("EPUB/{$contentLocation}", $content);
            $this->package->addManifest($contentLocation, $id);
            $this->package->addSpine($id);
        }

        $returnNavItem = ($navItem)
            ? $returnNavItem = $navItem->addNavigation($title, $contentLocation)
            : $returnNavItem = $this->navigation->addNavigation($title, $contentLocation);

        return $returnNavItem;
    }

    private function iterate(RecursiveIterator $iterator, Epub3 $workspace, NavigationInterface $navItem = null) {
        foreach ($iterator as $value) {
            if ($iterator->hasChildren()) {
                try {
                    $subNavItem = $workspace->addPage(
                        $this->formatter->formatChapterTitle($value->getName()),
                        $this->formatter->chapterTemplate(clone $value, clone $iterator->getChildren()),
                        $navItem
                    );
                    $this->iterate($iterator->getChildren(), $workspace, $subNavItem);
                    continue;
                } catch (\Throwable $e) {
                    print_r([
                        $e->getMessage(),
                        $value->getName(),
                        $e->getTraceAsString(),
                    ]);
                    exit(1);
                }

            }
            try {
                $workspace->addPage(
                    $this->formatter->formatPageTitle($value->getName()),
                    $this->formatter->pageTemplate(clone $value, $value->getContent()),
                    $navItem
                );
            } catch (\Throwable $e) {
                print_r([
                    $e->getMessage(),
                    $value->getName(),
                    $e->getTraceAsString(),
                ]);
            }

        }
    }

    private function generateUUID(): string {
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