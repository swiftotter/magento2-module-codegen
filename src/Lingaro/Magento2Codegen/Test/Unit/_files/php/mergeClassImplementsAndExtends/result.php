<?php

/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */
namespace Namespace1;

use Namespace1\Extends1;
use Namespace1\Interface2;
use Namespace3\Interface1;
class Class1 extends Extends1 implements Interface2, Interface1
{
    public function getSomething1(): string
    {
        return '';
    }
    public function implement1(): float
    {
        return 0.0;
    }
}
