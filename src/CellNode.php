<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep;

/**
 * CellNode represents a parsed cell in the worksheet
 * @author girish
 */
class CellNode {
    public string $cellText = '';
    
    /**
     * Contains collection of Tokens
     * @var Tokens\AbstractToken[]
     */
    public array $tokens = [];
}
