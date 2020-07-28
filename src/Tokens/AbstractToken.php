<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep\Tokens;

/**
 * Abstract Base Class for all Tokens
 * @author girish
 */
class AbstractToken {
    public string $text = '';
    public array $attrs = [];
}
