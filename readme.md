# Epub

A simple collection of classes to convert html documents to a epub3 file.

## What is this for?
Let's say you have a some html file in a structure like this, and you want to bundle into a *.epub document:
```
  Book
   +--- Volume I
   |     + --- Chaper 1
   |     |       +--- page 1.html
   |     |       +--- page 2.html
   |     |       `--- page 3.html
   |     + --- Chaper 2
   |     |       +--- page 1.html
   |     |       `--- page 2.html
   |     ` --- Chaper 3
   |              `--- page 1.html
   `--- Volume II
         + --- Chaper 1
                 +--- page 1.html
                 +--- page 2.html
                 `--- page 3.html
```

you can convert it into an Epub book by running
```php
<?php

use Epub\Epub3;
use Epub\Storage\StorageZip;
use Epub\Resource\RecursiveDirectory;
use Epub\Formatter\FormatterInterface;

class YourOwnFormatter implements FormatterInterface {
    // ...
}

$iterator = new RecursiveDirectory(realpath('/path/to/Book'));
$storage = new StorageZip('/path/to/book.epub');
$formatter = new YourOwnFormatter();

$epub = (new Epub3('Title of the book'))
    ->setCoverPage('<!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta charset="utf-8"/>
            </head>
            <body>
                <h1>I am the cover</h1>
            </body>
        </html>
    ')
    ->setStorage($storage)
    ->setFormatter($formatter)
    ->save($iterator);
```

### What's an iterator/resource.
Currently the use case for this repo is to recursively iterate over a directory structure and use all files found as content and all directories as chaper names. If the is another way to collect data (say from a database or a network call), the
[RecursiveIterator](https://www.php.net/manual/en/class.recursiveiterator.php) interface can be implemented.

Because PHP doesn't have generics, there is no way to force RecursiveIterator to contain a specific type. Just be aware that the script is expecting the children of your iterator to be of type `Epub\Resource\ResourceInterface`.

This repo contains `Epub\Resource\RecursiveDirectory` which runs thru directories on disk.

This repo also ships with a test resource type `Epub\Resource\RecursiveMemory` and a corresponding `Epub\Resource\ResourceMemory`. They are mostly for testing and allow for creating resources out of an `array`
```php
$resource = new ResourceMemory('Volume 1', null, [
    new ResourceMemory('Chapter 1', null, [
        new ResourceMemory('page 1.1.html', '...html...'),
        new ResourceMemory('page 1.2.html', '...html...'),
    ]),
    new ResourceMemory('Chapter 2', null, [
        new ResourceMemory('page 2.1.html', '...html...'),
    ]),
]);

$iterator = new RecursiveMemory($resource);
```

### What is storage?
Storage is where the artifacts of this script go. Currently there are three implementations

1. Epub\Storage\StorageMemory
2. Epub\Storage\StorageFilesystem
3. Epub\Storage\StorageZip

**StorageMemory** is mostly for testing, it holds are artifacts in an `array`. **StorageFilesystem** will write results to disk. **StorageZip** will create a zip archive on disk. Because epub books are zip archive with a different extension, this storage class will create an actual Epub book, if you provide the *.epub extension.

### What's formatter?
Here is where your custom code lives. If the html files need any kind of reformatting, cleaning or validating, here is where that logic lives.

```php
namespace Epub\Formatter;

use Epub\ContainerInterface;
use Epub\Resource\ResourceInterface;
use RecursiveIterator;

interface FormatterInterface {

    public function formatChapterTitle(string $title): string;

    public function formatPageTitle(string $title): string;

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?string;

    public function pageTemplate(ResourceInterface $page, string $content): ?string;

    public function setWorkspace(ContainerInterface $workspace);
}
```

#### `formatChapterTitle`
Will be sent a name of a directory. If, for example you are using numbers as the beginning of your directories to keep them sorted but don't want that as your chapter title, your logic might look something like this:
```php
public function formatChapterTitle(string $title): string {
    // name of directory | 10 - The Chapter Name
    preg_match('/(([0-9]*)( - ))?(.*)/', $title, $match);

    // Chapter name returned | ▶ The Chapter Name
    return "▶ {$match[4]}";
}
```

#### `formatPageTitle`
Similar to `formatChapterTitle` but is called on a file's name rather than directory name.

#### `chapterTemplate`
This method is called before each chapter page is saved, it is a good way to have consistent wrapper around all page content.
This example wraps content in well structured XHTML and add title to each page and a list of content for this chapter
```php
public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): string {
    $list = implode("\n", array_map(function ($item) {
        return "<li>{$this->formatPageTitle($item->getName())}</li>";
    }, \iterator_to_array($children)));

    return "<!DOCTYPE html>\n".
    "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
    "    <head>\n".
    "        <meta charset=\"utf-8\"/>\n".
    "        <title>{$this->formatChapterTitle($chapter->getName())}</title>\n".
    "    </head>\n".
    "    <body>\n".
    "        <h1>{$this->formatChapterTitle($chapter->getName())}</h1>\n".
    "        <hr />\n".
    "        <ol>{$list}</ol>\n".
    "    </body>\n".
    "</html>";
}
```

#### `pageTemplate`
Same as `chapterTemplate` but for a content page:
```php
    public function pageTemplate(ResourceInterface $page, string $content): string {
        return "<!DOCTYPE html>\n".
        "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
        "    <head>\n".
        "        <meta charset=\"utf-8\"/>\n".
        "        <title>{$this->formatPageTitle($page->getFilename())}</title>\n".
        "    </head>\n".
        "    <body>\n".
        "        {$this->format($content)}\n".
        "    </body>\n".
        "</html>";
    }
```
It is a good idea to split the actual formatting of the content into its own method that would look something like this: It allows for cleaning and validating of the content

```php
private function format(string $content): string {
    $dom = new DomDocument();
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_NOEMPTYTAG);
    $xpath = new DOMXPath($dom);

    // ... remove unwanted elements

    return $dom->saveXML();
}
```

#### `setWorkspace`
This is a dependency injection method so that the formatter has access to the container. This is useful for if you want to register any additional resources like images. the `format` method is a good place to use it.

In this example, all img tags are queried, the actual image is downloaded, it's converted into black-and-white (e-readers are only black-and-white, so the color info will only make the overall book bigger with no additional benefits), the resource is stored and the img tag is updated with the new image name.
```php
private function format(string $content): string {
    $dom = new DomDocument();
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_NOEMPTYTAG);

    $extMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    $imagic = new Imagick();

    foreach($dom->getElementsByTagName('img') as $element) {
        $src = $element->getAttribute('src');
        $ext = pathinfo($src)['extension'];

        $resource = file_get_content($src);

        $imagic->readImageBlob($resource);
        $imagic->setImageType(Imagick::IMGTYPE_GRAYSCALE);
        $imagePath = $this->workspace->addResource($imagic, $extMap[$ext], $ext); //<---
        $element->setAttribute('src', $imagePath);
    }

    return $dom->saveXML();
}
```

You could also regiser a stylesheet in this method and then add a reference to it in the `pageTemplate` method
```php
private string $stylesheet;

public function setWorkspace(ContainerInterface $workspace) {
    $this->stylesheet = $workspace->addResource('...css...', 'text/css', 'css');
}

public function pageTemplate(ResourceInterface $page, string $content): string {
    return "<!DOCTYPE html>\n".
    "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
    "    <head>\n".
    "        <meta charset=\"utf-8\"/>\n".
    "        <title>{$this->formatPageTitle($page->getFilename())}</title>\n".
    "        <link rel="stylesheet" type="text/css" href="{this->stylesheet}" />\n".
    "    </head>\n".
    "    <body>\n".
    "        {$this->format($content)}\n".
    "    </body>\n".
    "</html>";
}
```

This repo contains `Epub\Formatter\BlankFormatter` which does almost nothing but pass data. It is mostly there for testing.
