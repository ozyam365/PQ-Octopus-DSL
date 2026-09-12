<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/excel.php 
 * COMPONENT : PQ Excel Core
 * =========================================================
 */

// [ENGINE CORE] Auto-load external XLSX libraries
if (!class_exists('XLSXWriter')) {
    include_once PQ_DIR . "/assets/xlsx/xlsxwriter.php"; 
}
if (!class_exists('SimpleXLSX')) {
    include_once PQ_DIR . "/assets/xlsx/SimpleXLSX.php";
}

class PQ_Excel {
    private $writer;
    private $maps = [];
    private $sheetName = 'Sheet1'; // [CUSTOMIZE] Default sheet name

    // [ENGINE CORE] Excel Generator Pipeline
    public function make() {
        $this->writer = new XLSXWriter();
        return $this;
    }

    public function sheet($name) {
        $this->sheetName = $name ? $name : 'Sheet1';
        return $this;
    }

    public function map(array $rules) {
        $this->maps = $rules;
        return $this;
    }

    public function autoHeader() {
        $header = [];
        foreach($this->maps as $arr) {
            $header[is_array($arr) ? $arr[0] : $arr] = 'string';
        }
        $this->writer->writeSheetHeader($this->sheetName, $header);
        return $this;
    }

    public function data(array $rows) {
        foreach($rows as $row) {
            $displayRow = [];
            foreach($this->maps as $key => $arr) {
                // Fallback to empty string if database value is null or missing
                $db_val = $row[$key] ?? ''; 
                $displayRow[] = isset($arr[$db_val]) ? (string)$arr[$db_val] : (string)$db_val;
            }
            $this->writer->writeSheetRow($this->sheetName, $displayRow);
        }
        return $this;
    }

    public function download($filename = "PQ_Export.xlsx") {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        $this->writer->writeToStdOut();
        exit;
    }

    // [ENGINE CORE] Excel Reader Pipeline
    public function upload($filePath) {
        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx) {
            return $xlsx->rows();
        } else {
            return ['error' => SimpleXLSX::parseError()];
        }
    }
}

// [ENGINE CORE] Singleton Bridge & DSL Wrapper
function excel() {
    static $instance = null;
    if ($instance === null) {
        $instance = new PQ_Excel();
    }
    return $instance;
}
?>