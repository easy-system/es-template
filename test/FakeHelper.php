<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Template\Test;

use LogicException;

class FakeHelper
{
    public function setOptions()
    {
        throw new LogicException(sprintf('The "%s" is stub.', __METHOD__));
    }

    public function __invoke()
    {
        throw new LogicException(sprintf('The "%s" is stub.', __METHOD__));
    }
}
