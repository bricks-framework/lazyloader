<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\LazyLoader\ProxyGenerator;

use BricksFramework\LazyLoader\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;

class ProxyGenerator implements ProxyGeneratorInterface
{
    /** @var string */
    protected $class;

    /** @var ClassGenerator */
    protected $classGenerator;

    public function __construct(string $class)
    {
        $this->class = $class;
        $this->classGenerator = ClassGenerator::fromReflection(new \Laminas\Code\Reflection\ClassReflection($class));
    }

    public function getClassGenerator() : ClassGenerator
    {
        return $this->classGenerator;
    }

    public function getClass() : string
    {
        return $this->class;
    }

    public function modify() : void
    {
        $this->modifyNamespace();
        $this->modifyFinal();
        $this->modifyExtendClass();
        $this->modifyImplements();
        $this->modifyTraits();
        $this->modifyContstants();
        $this->modifyProperties();
        $this->modifyMethods();
        $this->addProxy();
    }

    public function modifyNamespace() : void
    {
        $this->getClassGenerator()
            ->setNamespaceName('BricksCompile\\LazyLoader\\' . $this->getClassGenerator()->getNamespaceName());
    }

    public function modifyFinal() : void
    {
        $this->getClassGenerator()
            ->setFinal(true);
    }

    public function modifyExtendClass() : void
    {
        $this->getClassGenerator()
            ->setExtendedClass($this->getClass());
    }

    public function modifyImplements() : void
    {
        $this->getClassGenerator()
            ->setImplementedInterfaces(['\BricksFramework\LazyLoader\LazyLoaderProxyInterface']);
    }

    public function modifyTraits() : void
    {
        $classGenerator = $this->getClassGenerator();
        foreach ($classGenerator->getTraits() as $trait) {
            $classGenerator->removeTrait($trait);
        }
        foreach ($classGenerator->getTraitAliases() as $alias) {
            $classGenerator->removeUseAlias($alias);
        }
    }

    public function modifyContstants() : void
    {
        $classGenerator = $this->getClassGenerator();
        foreach ($classGenerator->getConstants() as $constant) {
            $classGenerator->removeConstant($constant->getName());
        }
    }

    public function modifyProperties() : void
    {
        $classGenerator = $this->getClassGenerator();
        foreach ($classGenerator->getProperties() as $property) {
            $classGenerator->removeProperty($property->getName());
        }
    }

    public function modifyMethods() : void
    {
        $classGenerator = $this->getClassGenerator();
        $methods = [];
        foreach ($classGenerator->getMethods() as $method) {
            if ($method->getVisibility() != MethodGenerator::VISIBILITY_PUBLIC) {
                $classGenerator->removeMethod($method->getName());
            } else {
                $methods[] = $method;
            }
        }

        $classGenerator->modifyMethods($methods);
    }

    public function addProxy() : void
    {
        $this->getClassGenerator()
            ->addProxy();
    }

    public function generate() : string
    {
        return $this->getClassGenerator()->generate();
    }
}
