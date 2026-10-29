<?php
/**
 * =========================================================
 * PQ Excel File Processing Core Module
 * FILENAME  : /pq/core/excel.php
 * UPDATE :  2026-10-30 AM 12:11
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
				$db_val = $row[$key] ?? '';

				// 1. For header mapping arrays where $arr is not a value conversion lookup array (e.g., ['1'=>'Use', '0'=>'Unused']),
				// actually use $db_val
				if (is_array($arr) && isset($arr[$db_val])) {
					$val = $arr[$db_val];
				} else {
					$val = $db_val;
				}

				// 2. Numeric data (including 0) is kept as primitive types (int/float) without conversion to strings
				if (is_numeric($val) && !is_string($val)) {
					$displayRow[] = $val;
				} else {
					$displayRow[] = (string)$val;
				}
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