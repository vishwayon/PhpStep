<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Parses text for various Lexer elements
 * @author girish
 */
class Parser {
    
    /**
     * Parses a worksheet cell and returns an instance of the CellNode
     * @param Worksheet $ws         The Worksheet
     * @param Cell $cell            The Worksheet Cell to be parsed
     * @return PhpStep\CellNode     Returns a CellNode
     */
    public static function parse(Worksheet $ws, Cell $cell) : CellNode {
        $cn = new CellNode();
        $cn->cellText = $cell->getValue() ?? '';
        $matches = [];
        if (preg_match_all(Lexer::REGEX_FIELD, $cn->cellText, $matches)) {
            foreach($matches[0] as $key => $match) {
                $token = new Tokens\FieldToken();
                $token->text = $match;
                $token->attrs[] = $matches[1][$key];
                $cn->tokens[] = $token;
            }
        } elseif (preg_match(Lexer::REGEX_EACH, $cn->cellText, $matches)) {
            $token = new Tokens\EachToken();
            $token->text = $matches[0];
            $token->attrs[] = $matches[1];
            if (isset($matches[2])) {
                $token->attrs[] = intval($matches[2]);
            }
            $token->rowCells = self::parseEachRowField($ws, $cell->getRow(), $token);
            $cn->tokens[] = $token;
        } elseif (preg_match(Lexer::REGEX_EACH_COL, $cn->cellText, $matches)) {
            $token = new Tokens\EachColToken();
            $token->text = $matches[0];
            $token->attrs[] = $matches[1];
            if (isset($matches[2])) {
                $token->attrs[] = intval($matches[2]);
            }
            $token->colCells = self::parseEachColField($ws, $cell->getRow(), \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cell->getColumn()), $token);
            $cn->tokens[] = $token;
        } else {
            $token = new Tokens\TextToken();
            $cn->tokens[] = $token;
        }
        return $cn;
    }
    
    /**
     * Sub-procedure to parse eachRow
     * @param Worksheet $ws             The Worksheet
     * @param int $rowIndex             The current row index
     * @param Tokens\EachToken $ptoken  The parent Each{row} Token
     * @return array                    Returns a collection of CellNodes
     */
    private static function parseEachRowField(Worksheet $ws, int $rowIndex, Tokens\EachToken $ptoken) : array {
        // The fields for binding each would always be listed in the next row
        $row = $rowIndex + 1;
        $hCol = Coordinate::columnIndexFromString($ws->getHighestColumn());
        // Create prop range
        $rowCells = [];
        for($cc = 1; $cc <= $hCol; $cc++) {
            $cCell = $ws->getCellByColumnAndRow($cc, $row);
            $cNode = self::parse($ws, $cCell);
            $rowCells[$cc] = $cNode;
        }
        return $rowCells;
    }
    
    /**
     * Sub-procedure to parse eachCol
     * @param Worksheet $ws                         The Worksheet
     * @param int $rowIndex                         The Current Row Index
     * @param int $colIndex                         The Current Column Index
     * @param \PhpStep\Tokens\EachColToken $ptoken  The parent token representing EachCol
     * @return array                                Returns an array of CellNodes
     */
    private static function parseEachColField(Worksheet $ws, int $rowIndex, int $colIndex, Tokens\EachColToken $ptoken) : array {
        // The fields for binding each would always be listed in the next row
        $col = $colIndex + 1;
        $hCol = $colIndex + $ptoken->colSpan();
        // Create prop range
        $colCells = [];
        for($cc = 1; $cc <= $hCol; $cc++) {
            $cCell = $ws->getCellByColumnAndRow($rowIndex, $cc);
            $cNode = self::parse($ws, $cCell);
            $colCells[$cc] = $cNode;
        }
        return $colCells;
    }
}
