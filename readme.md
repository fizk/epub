# Epub3
PHP library to convert text files (HTML, Markdown etc...) into a *.epu3 file.

## Theory.
Converting a set of files on a hard-drive (or where ever they are) can be considered a three step process

1. Iterate over and collect the files that make up the Epub.
2. Format each file to comply with the Epub3 standard.
3. Concatenate files and convert to a *.epub3 file.

Step **1** and **3** are always the same. Step 2 is specific to each use-case. This sets up processes and interfaces so you don't need to worry about fetching and compiling files, you only need to worry about extracting and formatting.

## Practice.
Converting files in nested directories into an Epub book look like this:

```php
use Epub\Epub3;
use Epub\Storage\StorageZip;
use Epub\Resource\RecursiveDirectory;
use Epub\Document\Package;

$package = new Package(uniqid() , 'Title', new DateTime());
$iterator = new RecursiveDirectory(realpath('./path/to/file'));
$storage = new StorageZip('./where/to/store.epub');
$formatter = new SimpleFormatter();

$epub = (new Epub3('Title'))
    ->setPackage($package)
    ->setCoverImage(file_get_contents('./path/to/cover.jpg'),'image/jpeg', 'jpg')
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

### Iterate over files.
This repo comes with `Epub\Resource\RecursiveDirectory`, it's job is to iterate over folders and nested folders on a hard-drive and provide and files fond to the **Formatter**. It extends [`RecursiveIterator`](https://www.php.net/manual/en/class.recursiveiteratoriterator.php), so if there is a need to source material from other places beside the hard-drive, another implementation can be substituted for `RecursiveDirectory`.

### Format Files.
The content of each file that the `RecursiveIterator` finds is passed through the **Formatter**.

```php
interface FormatterInterface {

    public function setWorkspace(ContainerInterface $workspace);

    public function formatChapterTitle(string $title): string;

    public function formatPageTitle(string $title, ?string $content): string;

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?DOMDocument;

    public function pageTemplate(ResourceInterface $page, string $content): ?DOMDocument;
}
```

A very simple implementation could look something look this:
```php
class SimpleFormatter {

    private ContainerInterface $workspace;

    public function setWorkspace(ContainerInterface $workspace) {
        $this->workspace = $workspace;
    }

    public function formatChapterTitle(string $title): string {
        return str_replace('_', ' ', $title);
    }

    public function formatPageTitle(string $title, ?string $content): string {
        return str_replace('_', ' ', $title);
    }

    public function chapterTemplate(ResourceInterface $chapter, RecursiveIterator $children): ?DOMDocument {
        $dom = new DOMDocument();
        $headline = $dom->createElement('h1', $chapter->getName());
        $list = $dom->createElement('ul');
        foreach($children as $child) {
            $list->appendChild(
                $dom->createElement('li', $child->getName())
            );
        }
        $dom->appendChild($headline);
        $dom->appendChild($list);

        return $dom;
    }

    public function pageTemplate(ResourceInterface $page, string $content): ?DOMDocument {
        $dom = new DOMDocument();
        $dom->loadXML($content);

        $elements = $page->getElementsByTagName('img');
        foreach($elements as $element) {
            $src = $element->getAttribute('src');
            $ext = pathinfo($src)['extension'];

            $img = $this->getImage('https://www.some-domain.org/' . $src);
            if ($img && $this->extMap[$ext] !== null) {
                $imagePath = $this->workspace->addResource($img, $this->extMap[$ext], $ext);
                $element->setAttribute('src', $imagePath);
            }
        }

        return $dom;
    }
}
```

### Storing files.
This repo comes with `Epub\Storage\StorageZip` which concats all files into on *.epub3 file. It also comes with `Epub\Storage\StorageFilesystem` which will just save the formatted files back to the hard-drive. It's useful for debugging and understanding that the **Formatter** is doing.