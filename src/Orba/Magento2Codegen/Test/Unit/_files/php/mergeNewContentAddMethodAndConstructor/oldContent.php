<?php

namespace Namespace1;

use Namespace1\Tester\Php as PhpTester;

class Class1
{
    /**
     * @var PhpTester
     */
    private $phpTester;

    public function __construct(PhpTester $phpTester)
    {
        $this->phpTester = $phpTester;
    }

    public function getPhpTester(): PhpTester
    {
        return $this->phpTester;
    }
}