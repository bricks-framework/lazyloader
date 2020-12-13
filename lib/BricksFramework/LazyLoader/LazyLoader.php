<?php

namespace BricksFramework\LazyLoader;

use BricksFramework\LazyLoader\ProxyGenerator\ProxyGenerator;
use Composer\Autoload\ClassLoader as ClassLoader;
use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use Laminas\Filter\Word\SeparatorToSeparator;

class LazyLoader implements LazyLoaderInterface
{
    /** @var string */
    protected $compileDir;

    /** @var ClassLoader */
    protected $autoloader;

    protected $md5sums = [];

    public function __construct(ClassLoader $autoloader, string $compileDir) {
        $this->autoloader = $autoloader;
        $this->compileDir = $compileDir . DIRECTORY_SEPARATOR . 'BricksCompile' . DIRECTORY_SEPARATOR . 'LazyLoader';
    }

    public function get($class, array $arguments = []) : object
    {
        if (!$this->hasClassFile($class) || $this->needUpdate($class)) {
            $this->createFile($class);
        }

        $filepath = $this->getFilePath($class) . '.php';
        $this->autoloader->addClassMap([
            'BricksCompile\\LazyLoader\\' . $class => $filepath
        ]);

        return $this->instantiate($class, $arguments);
    }

    protected function needUpdate(string $class) : bool
    {
        $this->getMd5Sums();
        $file = $this->autoloader->findFile($class);
        $filemd5 = md5_file($file);
        return $this->md5sums[$file] ?? '' != $filemd5;
    }

    protected function getMd5Sums() : array
    {
        if (!$this->md5sums) {
            $file = $this->getMd5SumFile();
            if (file_exists($file)) {
                $this->md5sums = require $file;
            }
        }
        return $this->md5sums;
    }

    protected function addMd5Sum(string $filepath, string $md5sum) : void
    {
        $this->getMd5Sums();
        $this->md5sums[$filepath] = $md5sum;
        $md5file = $this->getMd5SumFile();
        $config = new Config($this->md5sums);
        $writer = new PhpArray();
        $writer->toFile($md5file, $config);
    }

    protected function getMd5SumFile() : string
    {
        return $this->compileDir . DIRECTORY_SEPARATOR . 'md5sums.php';
    }

    protected function hasClassFile(string $class) : bool
    {
        $proxyFile = $this->getFilePath($class) . '.php';
        return file_exists($proxyFile);
    }

    protected function getFilePath(string $class) : string
    {
        $filter = new SeparatorToSeparator('\\', DIRECTORY_SEPARATOR);
        return $this->compileDir . DIRECTORY_SEPARATOR . $filter->filter($class);
    }

    protected function instantiate(string $class, array $arguments = []) : object
    {
        $newClass = '\\BricksCompile\\LazyLoader\\' . $class;
        return new $newClass(...$arguments);
    }

    /**
     * @throws \ReflectionException
     */
    protected function createFile(string $class) : void
    {
        $proxyGenerator = new ProxyGenerator($class);
        $proxyGenerator->modify();
        $classContent = $proxyGenerator->generate();

        $filepath = $this->getFilePath($class) . '.php';
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0775, true);
        }
        file_put_contents($filepath, '<?php ' . "\n" . $classContent);

        $originalFile = $this->autoloader->findFile($class);
        $this->addMd5Sum($originalFile, md5_file($originalFile));
    }
}
