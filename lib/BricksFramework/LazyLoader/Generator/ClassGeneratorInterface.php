<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\LazyLoader\Generator;

use Laminas\Code\Generator\MethodGenerator;

interface ClassGeneratorInterface
{
    public function modifyMethods(array $methods) : void;
    public function addProxy() : void;
    public function addProxyParameters() : void;
    public function modifyProxyConstructor(MethodGenerator $method) : void;
    public function addProxyMethods() : void;
    public function buildMethodBody(MethodGenerator $method) : void;
}
