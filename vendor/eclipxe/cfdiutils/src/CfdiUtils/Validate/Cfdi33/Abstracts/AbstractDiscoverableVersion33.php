<?php

namespace CfdiUtils\Validate\Cfdi33\Abstracts;

use CfdiUtils\Validate\Contracts\DiscoverableCreateInterface;
use CfdiUtils\Validate\Contracts\ValidatorInterface;

abstract class AbstractDiscoverableVersion33 extends AbstractVersion33 implements DiscoverableCreateInterface
{
    final public function __construct()
    {
    }

    public static function createDiscovered(): ValidatorInterface
    {
        return new static();
    }
}
