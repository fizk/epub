<?php

namespace Epub\Document\Navigation;

interface NavigationInterface {

    public function addNavigation(string $title, string $location = null): NavigationInterface;
}