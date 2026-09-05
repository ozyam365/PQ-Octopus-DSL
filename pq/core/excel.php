<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/file.php 
 * COMPONENT : PQ excel 
 * =========================================================
 */


if (!class_exists('XLSXWriter')) {
	include_once PQ_DIR ."/assets/xlsx/xlsxwriter.php"; 
}
if (!class_exists('SimpleXLSX')) {
	include_once PQ_DIR ."/assets/xlsx/SimpleXLSX.php";
}
class PQ_Excel {
    private $writer;
    private $maps = [];
    private $data = [];
    private $sheetName = 'Sheet1'; // 기본 시트명

    public function make() {
        $this->writer = new XLSXWriter();
        return $this;
    }

    // 1 [추가] 시트 이름 지정 - "excel.make().sheet('매출현황')"
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
                $db_val = $row[$key] ?? 0;
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

    // 2 upload() 기능 보강
    public function upload($filePath) {
        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx) {
            return $xlsx->rows();
        } else {
            return ['error' => SimpleXLSX::parseError()];
        }
    }
}
?>