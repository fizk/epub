<?php

use PHPUnit\Framework\TestCase;
use Epub\Document\Navigation\XHTMLNavigation;

class NavigationTest extends TestCase {

    public function testSubMenu() {
        $navigation =  new XHTMLNavigation();
        $subnav = $navigation->addNavigation('text');
        $subnav->addNavigation('sub1');
        $subnav->addNavigation('sub2');

        $expected = '<?xml version="1.0"?>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                <head>
                    <meta charset="utf-8"/>
                </head>
                <body>
                    <nav epub:type="toc">
                        <ol>
                            <li>
                                <span>text</span>
                                <ol>
                                    <li><span>sub1</span></li>
                                    <li><span>sub2</span></li>
                                </ol>
                            </li>
                        </ol>
                    </nav>
                </body>
            </html>
        ';
        $actual = (string) $navigation;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testSubMenuLinks() {
        $navigation =  new XHTMLNavigation();
        $subnav = $navigation->addNavigation('text');
        $subnav->addNavigation('sub1', 'path/1');
        $subnav->addNavigation('sub2', 'path/2');

        $expected = '<?xml version="1.0"?>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                <head>
                    <meta charset="utf-8"/>
                </head>
                <body>
                    <nav epub:type="toc">
                        <ol>
                            <li>
                                <span>text</span>
                                <ol>
                                    <li><a href="path/1">sub1</a></li>
                                    <li><a href="path/2">sub2</a></li>
                                </ol>
                            </li>
                        </ol>
                    </nav>
                </body>
            </html>
        ';
        $actual = (string) $navigation;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testMenu() {
        $navigation =  new XHTMLNavigation();
        $subnav = $navigation->addNavigation('text');

        $expected = '<?xml version="1.0"?>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                <head>
                    <meta charset="utf-8"/>
                </head>
                <body>
                    <nav epub:type="toc">
                        <ol>
                            <li><span>text</span></li>
                        </ol>
                    </nav>
                </body>
            </html>
        ';
        $actual = (string) $navigation;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testSiblings() {
        $navigation =  new XHTMLNavigation();
        $navigation->addNavigation('text 1');
        $navigation->addNavigation('text 2');

        $expected = '<?xml version="1.0"?>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                <head>
                    <meta charset="utf-8"/>
                </head>
                <body>
                    <nav epub:type="toc">
                        <ol>
                            <li><span>text 1</span></li>
                            <li><span>text 2</span></li>
                        </ol>
                    </nav>
                </body>
            </html>
        ';
        $actual = (string) $navigation;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }
}