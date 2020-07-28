<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep\Tokens;

/**
 * An instance of Each[row]
 * @author girish
 */
class EachToken extends AbstractToken {
    /** @var array   A collection of CellNodes for each row cell */
    public array $rowCells = [];
    
    /** Returns the property name (Field to be bound)
     * @return string */
    public function prop() : string {
        return $this->attrs[0];
    }
    
    /** Returns the row Span. Represents the number of rows that are part of template
     * @return int  */
    public function rowSpan() : int {
        if (isset($this->attrs[1])) {
            return $this->attrs[1];
        } else {
            return 1;
        }
    }
    
}
