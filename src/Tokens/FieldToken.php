<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep\Tokens;

/**
 * An instance of Field Token
 * @author girish
 */
class FieldToken extends AbstractToken {
    public function field() : string {
        return $this->attrs[0];
    }
}
