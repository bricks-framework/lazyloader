<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\LazyLoader;

interface LazyLoaderInterface
{
    public function get(string $class, array $arguments = []) : object;
}