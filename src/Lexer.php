<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep;

/**
 * Contains list of constants used for Parsing Tokens
 * @author girish
 */
class Lexer {
    const REGEX_FIELD = '/\$[Ff]\{(\w{1,}|_)\}/'; // Field with pattern $F{field_name}
    const REGEX_EACH = '/\$Each\{(\w{1,}|_),?\s{0,}(\d)?\}/';    // Each Row with pattern $Each{array [, rows]}
    const REGEX_EACH_COL = '/\$EachCol\{(\w{1,}|_),?\s{0,}(\d)?\}/';    // Each Row with pattern $EachCol{array [, cols]}
}
