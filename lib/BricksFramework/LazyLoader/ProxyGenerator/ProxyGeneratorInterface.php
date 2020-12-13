<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\LazyLoader\ProxyGenerator;

use BricksFramework\LazyLoader\Generator\ClassGenerator;

interface ProxyGeneratorInterface
{
    public function getClassGenerator() : ClassGenerator;
    public function getClass() : string;
    public function modify() : void;
    public function modifyNamespace() : void;
    public function modifyFinal() : void;
    public function modifyExtendClass() : void;
    public function modifyTraits() : void;
    public function modifyContstants() : void;
    public function modifyProperties() : void;
    public function modifyMethods() : void;
    public function addProxy() : void;
    public function generate() : string;
}
