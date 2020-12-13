<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\LazyLoader\Generator;

use Laminas\Code\Generator\ClassGenerator as LaminasClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

class ClassGenerator extends LaminasClassGenerator implements ClassGeneratorInterface
{
    public function modifyMethods(array $methods) : void
    {
        /** @var MethodGenerator $method */
        foreach ($methods as $method) {
            if ($method->getName() != '__construct') {
                $this->buildMethodBody($method);
            } else {
                $this->modifyProxyConstructor($method);
            }
        }
    }

    public function addProxy() : void
    {
        $this->addProxyMethods();
        $this->addProxyParameters();
    }

    public function addProxyParameters() : void
    {
        $this->addProperty('____parameters', null, PropertyGenerator::FLAG_PRIVATE);
        $this->addProperty('____isInstantiated', false, PropertyGenerator::FLAG_PRIVATE);
    }

    public function modifyProxyConstructor(MethodGenerator $method) : void
    {
        $method->setBody('$this->____parameters = func_get_args();');
    }

    public function addProxyMethods() : void
    {
        $this->addMethod(
            '____initConstructor',
            [],
            MethodGenerator::FLAG_PRIVATE, '
if (!$this->____isInstantiated) {
    $this->____isInstantiated = true;
    parent::__construct(...$this->____parameters);    
}
            ',
            null
        );
    }

    public function buildMethodBody(MethodGenerator $method) : void
    {
        $hasReturnType = 'void' !== (string) $method->getReturnType() &&
            ('' === (string) $method->getReturnType() || !empty((string) $method->getReturnType()));

        $newBody = '
$this->____initConstructor(); 
' . ($hasReturnType ? 'return ' : '') . 'parent::' . $method->getName() . '(';

        foreach ($method->getParameters() as $parameter) {
            $newBody .= '$' . $parameter->getName() . ', ';
        }
        $newBody = rtrim($newBody, ', ');
        $newBody .= ');';

        $method->setBody($newBody);
    }
}
