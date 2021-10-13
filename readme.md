# Epub3
PHP library to convert text files (HTML, Markdown etc...) into a *.epu3 file.

## Theory.
Converting a set of files on a hard-drive (or where ever they are) can be considered a three step process

1. Iterate over, and collect the files that make up the Epub.
2. Format each file to comply with the Epub3 standard.
3. Concatenate files and convert to a *.epub3 file.

Step **1** and **3** are always the same. Step 2 is specific to each use-case. This sets up processes and interfaces so you don't need to worry about fetching and compiling files, you only need to worry about extracting and formatting.

## Examples

### Simple Markdown example
Let's say you have a directory containing a few Markdown documents that you would like to convert into an Epub book

```
projects
|-- index.php
|-- cover.jpg
`-- documents
    |-- chapter-1
    |   |   file1.md
    |   `-- file2.md
    |-- chapter-2
    |   |   file1.md
    |   `-- file2.md
    `-- chapter-3
    `-- file1.md
```
The first thing to do would be to implement a **Formatter** for these Markdown files. To create a formatter, implement the `FormatterInterface`.

```php
interface FormatterInterface
{
    public function setWorkspace(ContainerInterface $workspace);

    public function formatChapterTitle(ResourceInterface $resource): string;

    public function formatPageTitle(ResourceInterface $resource): string;

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument;

    public function pageTemplate(ResourceInterface $resource): ?DOMDocument;
}
```

We can take a shortcut and extends the `BlankFormatter` and only overwrite the `pageTemplate` method

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $resource): ?DOMDocument
    {
        $htmlString = $markdown->format($resource->getContent());
        $dom = new DOMDocument();
        $dom->loadXML($htmlString);
        return $dom;
    }
}
```

In your `index.php` file setup the boilerplate code.

```php
use Epub\Epub3;
use Epub\Storage\StorageZip;
use Epub\Resource\RecursiveDirectory;
use Epub\Document\Package;

// Setup Package. It is the thing that describes the meta-data of the book
//  like author, title and creation date.
$package = new Package(uniqid() , 'Title', new DateTime());

// Setup a recursive iterator that will traverse and find all the markdown files
$iterator = new RecursiveDirectory(realpath(__DIR__ .'/documents'));

// Setup storage. It will reseive all document and store them in a *.epub file
$storage = new StorageZip(__DIR__ .'/store.epub');

// Setup the Formatter, it will know hoe to change Markdown files in to XHTML files.
$formatter = new MarkdownFormatter();

// Run the Epub3 generator.
(new Epub3('Title'))
    ->setPackage($package)
    ->setCoverImage(file_get_contents(__DIR__ .'/cover.jpg'),'image/jpeg', 'jpg')
    ->setCoverPage('<!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta charset="utf-8"/>
            </head>
            <body>
                <h1>Title page</h1>
            </body>
        </html>
    ')
    ->setStorage($storage)
    ->setFormatter($formatter)
    ->save($iterator);
```

Run this code and you will end up with an Epub file in the project directory.

### Example with a chapter page.
The example above doesn't create a dedicated page for each chapter. For that we need to implement the `chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument` method.

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $resource): ?DOMDocument
    {
        // ... same as above
    }

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument
    {
        $dom = new DOMDocument();
        $htmlElement = $dom->createElement('html');
        $bodyElement = $dom->createElement('body');
        $headerElement = $dom->createElement('h1', $resource->getName());

        $dom->appendChild($htmlElement);
        $htmlElement->appendChild($bodyElement);
        $bodyElement->appendChild($headerElement);

        return $dom;
    }
}
```

Maybe we want to take it a step further and list all the pages in the chapter on the chapter title page. We can do that like this:

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $resource): ?DOMDocument
    {
        // ... same as above
    }

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument
    {
        $dom = new DOMDocument();
        $htmlElement = $dom->createElement('html');
        $bodyElement = $dom->createElement('body');
        $headerElement = $dom->createElement('h1', $resource->getName());
        $listElement = $dom->createElement('ol');

        foreach($children as $child) {

            $listItemElement = $dom->createElement('li', $child->getName());
            $listElement->appendChild($listItemElement);
        }

        $dom->appendChild($htmlElement);
        $htmlElement->appendChild($bodyElement);
        $bodyElement->appendChild($headerElement);
        $bodyElement->appendChild($listElement);

        return $dom;
    }
}
```

If this chapter page needs to have its table-of-content items being clickable, we need to use the `encodeContentUri` method that we have access to through the `$this->workspace` property.

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $resource): ?DOMDocument
    {
        // ... same as above
    }

    public function chapterTemplate(ResourceInterface $resource, RecursiveIterator $children): ?DOMDocument
    {
        $dom = new DOMDocument();
        $htmlElement = $dom->createElement('html');
        $bodyElement = $dom->createElement('body');
        $headerElement = $dom->createElement('h1', $resource->getName());
        $listElement = $dom->createElement('ol');

        foreach($children as $child) {

            $linkElement = $dom->createElement('a', $child->getName());
            $linkElement->setAttribute('href', $this->workspace->encodeContentUri($child));

            $listItemElement = $dom->createElement('li');
            $listItemElement->appendChild($linkElement);
            $listElement->appendChild($listItemElement);
        }

        $dom->appendChild($htmlElement);
        $htmlElement->appendChild($bodyElement);
        $bodyElement->appendChild($headerElement);
        $bodyElement->appendChild($listElement);

        return $dom;
    }
}
```

### Custom chapter and pages names.
Up to this point we have been using the same names of the files and folder for chapter and pages names. That might not be ideal as these are the names used in the TOC. Let's fix it.

For the chapter names we are just going to use the number included in the folder name. For the page name, we are doing to peek inside the Markdown document and extract the first top-level header element.

```php
class MarkdownFormatter extends BlankFormatter
{
    public function formatChapterTitle(ResourceInterface $resource): string
    {
        preg_match('/[0-9]+/', $resource->getName(), $match);
        return $match[0];
    }

    public function formatPageTitle(ResourceInterface $resource): string
    {
        $dom = new DOMDocument();
        $dom->loadHTML($markdown->parse($resource->getContent()));

        return $dom->getElementsByTagName('h1')->item->nodeValue;
    }
}
```

### Custom chapter page with summary.
For this example we are going to give each chapter its custom name. We are also going to include a little summary in each of the chapter pages. For this we will include a `chapter.md` in each directory that will hold the name of the chapter and the summary. The `chapter.md` file will look something like this:

```markdown
# Name of a chaper

This is a descriptino of the chapter.
```

...and including this new documents looks like this:

```
projects
|-- index.php
|-- cover.jpg
`-- documents
    |-- chapter-1
    |   |   chapter.md <---- name and description of chapter-1
    |   |   file1.md
    |   `-- file2.md
    |-- chapter-2
    |   |   chapter.md <---- name and description of chapter-2
    |   |   file1.md
    |   `-- file2.md
    `-- chapter-3
        |   chapter.md <---- name and description of chapter-3
        `-- file1.md
```

Next we have to extend the `RecursiveDirectory` class so it will ignore `chapter.md` the file files

```php
class ExtendedRecursiveDirectory extends RecursiveDirectory
{
    public function __construct($directory)
    {
        $this->currentFileObject = new SplFileInfo($directory);
        $this->children = array_values(array_map(function ($item) use ($directory) {
            return new class($directory . '/' . $item) extends SplFileInfo implements ResourceInterface
            {
                public function getContent()
                {
                    return \file_get_contents($this->getRealPath());
                }

                public function getName(): string
                {
                    return $this->getFilename();
                }

                public function getPath(): string
                {
                    return $this->getRealPath();
                }
            };
        }, array_diff(scandir($this->currentFileObject->getRealPath()), array('..', '.', '.DS_Store', 'chapter.md'))));
    }

    public function getChildren(): RecursiveIterator
    {
        return new ExtendedRecursiveDirectory($this->children[$this->index]->getRealPath());
    }
}
```

Then, in our Formatter we reach into the `markdown.md` when we are in a chapter/directory.
```php
class MarkdownFormatter extends BlankFormatter
{
    public function formatChapterTitle(ResourceInterface $chapter): string
    {
        $file = \file_get_contents($chapter->getPath() . '/markdown.md');

        $dom = new DOMDocument();
        $dom->loadHTML($file);
        $documentTitle = $dom->getElementsByTagName('h1')->item(0)->nodeValue;
        return mb_convert_encoding(trim(htmlspecialchars($documentTitle)), 'UTF-8');
    }

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?DOMDocument
    {
        $file = \file_get_contents($chapter->getPath() . '/markdown.md');

        $dom = new DOMDocument();
        $dom->loadHTML($file);

        $chapterDom = new DOMDocument();

        $htmlElement = $chapterDom->createElement('html');
        $bodyElement = $chapterDom->createElement('body');
        $headerElement = $chapterDom->createElement('h1', $dom->getElementsByTagName('h1')->item(0)->nodeValue);
        $bodyElement->appendChild($headerElement);

        foreach($dom->getElementsByTagName('p') as $element) {
            $paragraphElement = $dom->importNode($element, true);
            $bodyElement->appendChild($paragraphElement);
        }

        return $chapterDom;
    }
}
```

## The `RecursiveDirectory`
Last example touched briefly on the `RecursiveDirectory` class. It is responsible for iteratively traversing a directory structure. It is actually implementing the [RecursiveIteratorIterator](https://www.php.net/manual/en/class.recursiveiteratoriterator.php) and as such, a different `RecursiveIteratorIterator` can be implemented that traverses a **database**, an external service via TCP/IP just to name a few examples.

This repo actually also contains another Iterator: `RecursiveMemory` which is mostly used for unit-testing but also implements `RecursiveIteratorIterator`.

## The `StorageZip`
All of the example above have used `StorageZip` as its storage class. This class is responsible for taking all the resources and zipping them up into an Epub file. This class implements the `StorageInterface` interface.

```php
interface StorageInterface
{
    public function createContainer(string $path): bool;

    public function createResource(string $path, string $content): bool;
}
```

This repo also comes with a `StorageFilesystem` class that writes all the resources back to disk. This is useful for debugging. This repos also comes with a `StorageMemory` which is mostly used for unit-testing.

While this code is written for running as a CLI program, one could imagine it being used in a web-server setting. IN which case the `StorageZip` class would have to be rewritten so doesn't store the ZIP file onto disk, but puts it in the output buffer.

## Adding resources
Many Epubs contain images and other media that needs to be embedded into the final product. That that we have the `addResource($content, string $mimetype, string $extension): string;` method.

In this example, I'm fetching all `<img />` tags in a document, converting them to a low-res black'n'white JPEG image, adding it as a resource, receiving back a resource URL that use to update the existing `<img />` tag.

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $page): ?DOMDocument
    {
        $dom = new DOMDocument();
        $dom->loadHTML($page->getContent());

        $imagick = new Imagick();

        foreach($dom->getElementsByTagName('img') as $imageElement) {
            $imagick->readImageBlob(\file_get_contents($imageElement->getAttribute('src')));
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageType(Imagick::IMGTYPE_GRAYSCALEMATTE);
            $imagick->setImageCompressionQuality(35);

            $imagePath = $this->workspace->addResource($imagick->getImageBlob(), 'image/jpeg', 'jpg');
            $imageElement->setAttribute('src', $imagePath);

        }
        return $dom;
    }
}
```

## XHTML namespace.
Because Epubs are a collection of XHTML documents, it is a good idea to add that namespace to the `<html>` tag

```php
class MarkdownFormatter extends BlankFormatter
{
    public function pageTemplate(ResourceInterface $page): ?DOMDocument
    {
        $dom = new DOMDocument('1.0', 'utf-8');

        $html = $dom->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xml:lang', 'en');
        $html->setAttribute('lang', 'en');

        // format and convert the document

        return $dom;
    }
}
```
I have omitted this in the examples to make the code shorter, but I always to this to all documents.

## Epub meta-data and Package.
If you want to add more details to the Epub's meta-data, this is how you would do that

```php
$package = new Package(uniqid(), 'Title of epub', new DateTime());
$package->addMetadata(new MetadataDescription("Short description"));
$package->addMetadata(new MetadataAuthor('Autor name', 'Autor name, sorted by'));
$package->addMetadata(new MetadataPublisher('Publisher'));
$package->addMetadata(new MetadataPublishDate(new DateTime('2013-12-31')));
$package->addMetadata(new MetadataCover());

$epub = (new Epub3('Title of epub'))->setPackage($package);
```