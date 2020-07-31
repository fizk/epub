<?php

namespace Epub\Resource;

interface ResourceInterface {

    public function getContent() /*:mixed */;

    public function getName(): string;
}