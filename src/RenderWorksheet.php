<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license MIT
 */

namespace PhpStep;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use PhpStep\base\PatternType;

/**
 * RenderWorksheet puts together the Xlsx template worksheet and the json data source
 * This class applies the Json data to the worksheet
 * .
 * All changes are reflected in the original template file. Make sure that you 
 * have created a copy of the original file before submission or use the writer after
 * applying template and save the file with a new name
 * 
 * @author girish
 */
class RenderWorksheet {
    /** @deprecated since 0.3
     * Contains a collection of regex patterns to search
     * @var array
     */
    private $patterns = [
        'field' => '/\$F\{(\w{1,}|_)\}/',  // Field with pattern $F{field_name}
        'each' => '/\$Each\{\S{1,}\}/' // Each with pattern $Each{array_name}
    ];
    
    /**
     * @deprecated since 0.3
     * @param Worksheet $worksheet
     * @param type $model
     */
    public function applyDataV1(Worksheet $worksheet, $model) {
        $hRow = $worksheet->getHighestRow();
        $hCol = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
        for ($row = 1; $row <= $hRow; $row++) {
            for ($col = 1; $col <= $hCol; $col++) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                $ptype = $this->parsePattern($worksheet, $cell);
                if($ptype->getType() == PatternType::PATTERN_TYPE_FIELD) {
                    $this->setCellDataV1($cell, $ptype, $model);
                } elseif ($ptype->getType() == PatternType::PATTERN_TYPE_EACH) {
                    //Store Each Row marker
                    $eachRowMarker = $row; 
                    $row++;
                    if (property_exists($model, $ptype->propName)) {
                        $prop = $ptype->propName;
                        $childItems = $model->$prop;
                        foreach($childItems as $itm) {
                            // insert row in sheet
                            $worksheet->insertNewRowBefore($row, 1);
                            foreach($ptype->tranInfo as $cc => $cptype) {
                                $cell = $worksheet->getCellByColumnAndRow($cc, $row);
                                $this->setCellDataV1($cell, $cptype, (object)$itm);
                                // Copy cell styles to inserted row
                                $worksheet->duplicateStyle($worksheet->getStyleByColumnAndRow($cc, $row+1), Coordinate::stringFromColumnIndex($cc).$row);
                            }
                            $row++;
                        }
                        // Remove row->field markers
                        $worksheet->removeRow($eachRowMarker);
                        $worksheet->removeRow($row-1);
                        $row--;
                    }
                }
                $hRow = $worksheet->getHighestRow();
            }
        }
    }
    
    /**
     * Applies data to the requested worksheet
     * Data should always be a model with accessible properties
     * 
     * @param Worksheet $worksheet      The worksheet template
     * @param mixed $data               A data structure/model that contains properties to be applied to the worksheet
     */
    public function applyData(Worksheet $ws, $model) {
        $hRow = $ws->getHighestRow();
        $hCol = Coordinate::columnIndexFromString($ws->getHighestColumn());
        for ($row = 1; $row <= $hRow; $row++) {
            for ($col = 1; $col <= $hCol; $col++) {
                $cell = $ws->getCellByColumnAndRow($col, $row);
                $cn = Parser::parse($ws, $cell);
                if ($cn->tokens[0] instanceof Tokens\FieldToken) {
                    $this->setCellData($cell, $cn, $model);
                } elseif ($cn->tokens[0] instanceof Tokens\EachToken) {
                    //Store Each Row marker
                    $eachRowMarker = $row; 
                    $row++;
                    $token = $cn->tokens[0];
                    if (property_exists($model, $token->prop())) {
                        $prop = $token->prop();
                        $childItems = $model->$prop;
                        foreach($childItems as $itm) {
                            // insert row in sheet
                            $ws->insertNewRowBefore($row, 1);
                            foreach($token->rowCells as $cc => $cptype) {
                                $cell = $ws->getCellByColumnAndRow($cc, $row);
                                $this->setCellData($cell, $cptype, (object)$itm);
                                // Copy cell styles to inserted row
                                $ws->duplicateStyle($ws->getStyleByColumnAndRow($cc, $row+1), Coordinate::stringFromColumnIndex($cc).$row);
                            }
                            $row++;
                        }
                        // Remove row->field markers
                        $ws->removeRow($row);
                        $ws->removeRow($eachRowMarker);
                        $row--;
                    }
                } elseif ($cn->tokens[0] instanceof Tokens\EachColToken) {
                    // blank text
                }
                $hRow = $ws->getHighestRow();
            }
        }
    }
    
    /**
     * Sets the value for a Cell based on the CellNode
     * @param Cell $cell                The target cell in the worksheet
     * @param \PhpStep\CellNode $cn     The compiled CellNode
     * @param type $model               The Data Model
     */
    private function setCellData(Cell $cell, CellNode $cn, $model) {
        $fields = [];
        foreach($cn->tokens as $token) {
            if ($token instanceof Tokens\FieldToken && property_exists($model, $token->field())) {
                $prop = $token->field();
                $fields[$token->text] = $model->$prop;
            }
        }
        $cell->setValue(strtr($cn->cellText, $fields));
        if (defined('PHPSTEP_TEST') && constant('PHPSTEP_TEST'))
            echo $cn->cellText != '' ? $cn->cellText . PHP_EOL : '';
    }
    
    /** @deprecated since 0.3
     * Returns the patternType from the cell
     * @param Cell $cell
     * @return string
     */
    private function parsePattern(Worksheet $worksheet, Cell $cell): PatternType {
        $val = $cell->getValue();
        $pType = new PatternType(PatternType::PATTERN_TYPE_NONE);
        if (preg_match($this->patterns['field'], $val, $matched)) { 
            $pType = new PatternType(PatternType::PATTERN_TYPE_FIELD);
            $pType->propName = strtr($matched[0], [
                    '$F{' => '', '}' => ''
                ]);
            return $pType;
        } elseif (preg_match($this->patterns['each'], $val, $matched)) {
            $pType = new PatternType(PatternType::PATTERN_TYPE_EACH);
            $eachOpts = explode(",", strtr($matched[0], [
                    '$Each{' => '', '}' => ''
                ]));
            $pType->propName = strtr($matched[0], [
                    '$Each{' => '', '}' => ''
                ]);
            $pType->tranInfo = $this->buildTranInfo($worksheet, $cell->getRow());
        } elseif ($val !== null && isset($val)) {
            $pType->currentValue = $val;
        }
        return $pType;
    }
    
    /**
     * @deprecated since 0.3
     * @param Cell $cell
     * @param PatternType $ptype
     * @param type $model
     */
    private function setCellDataV1(Cell $cell, PatternType $ptype, $model) {
        if ($ptype->getType() != PatternType::PATTERN_TYPE_NONE && property_exists($model, $ptype->propName)) {
            $prop = $ptype->propName;
            $cell->setValue($model->$prop);
        } elseif ($ptype->getType() == PatternType::PATTERN_TYPE_COPY) { // Set the value as is
            $cell->setValue($ptype->currentValue);
        }
    }
    
    /**
     * @deprecated since 0.3
     * @param Worksheet $worksheet
     * @param int $eachRowMarker
     * @return array
     */
    private function buildTranInfo(Worksheet $worksheet, int $eachRowMarker): array {
        // The fields for binding each would always be listed in the next row
        $row = $eachRowMarker + 1;
        $hCol = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
        // Create prop range
        $propRange = [];
        for($cc = 1; $cc <= $hCol; $cc++) {
            $cCell = $worksheet->getCellByColumnAndRow($cc, $row);
            $ptype = $this->parsePattern($worksheet, $cCell);
            if ($ptype->getType() == PatternType::PATTERN_TYPE_NONE) {
                // Since this is an Array, we set the cell value to be copied
                $ptype->setType(PatternType::PATTERN_TYPE_COPY);
            }
            $propRange[$cc] = $ptype;
        }
        return $propRange;
    }
    
}
