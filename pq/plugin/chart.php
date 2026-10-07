<?php
/**
 * =========================================================
 * PQ Chart Module
 * FILENAME  : /pq/plugin/chart.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */
class PQChart {
    protected $data = [];
    protected $type = 'bar';
    protected $config = [
        'x' => '',
        'y' => [],
        'title' => '',
        'height' => 300,
        'legend' => true
    ];

    public static function bar($data = []) {
        $inst = new self();
        $inst->type = 'bar';
        $inst->data = $data;
        return $inst;
    }

    public static function line($data = []) {
        $inst = new self();
        $inst->type = 'line';
        $inst->data = $data;
        return $inst;
    }

    public static function pie($data = []) {
        $inst = new self();
        $inst->type = 'pie';
        $inst->data = $data;
        return $inst;
    }

    public function x($field) {
        $this->config['x'] = $field;
        return $this;
    }

    public function y($fields) {
        $this->config['y'] = is_array($fields) ? $fields : [$fields];
        return $this;
    }

    public function title($title) {
        $this->config['title'] = $title;
        return $this;
    }

    public function height($height) {
        $this->config['height'] = (int)$height;
        return $this;
    }

    public function legend($show = true) {
        $this->config['legend'] = (bool)$show;
        return $this;
    }

    public function render() {
        $canvas_id = 'pq_chart_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $x_field = $this->config['x'];
        $y_fields = $this->config['y'];

        $labels = [];
        $datasets_map = [];

        // 기본 색상 팔레트
        $colors = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(153, 102, 255, 0.7)'
        ];

        $c_idx = 0;
        foreach ($y_fields as $yf) {
            $datasets_map[$yf] = [
                'label' => $yf,
                'data' => [],
                'backgroundColor' => $colors[$c_idx % count($colors)],
                'borderColor' => str_replace('0.7', '1', $colors[$c_idx % count($colors)]),
                'borderWidth' => 1
            ];
            $c_idx++;
        }

        foreach ($this->data as $row) {
            $row_arr = (array)$row;
            if (isset($row_arr[$x_field])) {
                $labels[] = $row_arr[$x_field];
            }
            foreach ($y_fields as $yf) {
                if (isset($row_arr[$yf])) {
                    $datasets_map[$yf]['data'][] = (float)$row_arr[$yf];
                }
            }
        }

        $js_labels = json_encode(array_values($labels), JSON_UNESCAPED_UNICODE);
        $js_datasets = json_encode(array_values($datasets_map), JSON_UNESCAPED_UNICODE);
        $js_title = json_encode($this->config['title'], JSON_UNESCAPED_UNICODE);
        $js_legend = $this->config['legend'] ? 'true' : 'false';

        // HTML & JS 스크립트 출력
        echo "<div style='position:relative; width:100%; height:{$this->config['height']}px;'>";
        echo "  <canvas id='{$canvas_id}'></canvas>";
        echo "</div>";

        echo "<script>
        (function() {
            function initChart() {
                var el = document.getElementById('{$canvas_id}');
                if (!el) return;
                var ctx = el.getContext('2d');
                if (window['inst_{$canvas_id}']) {
                    window['inst_{$canvas_id}'].destroy();
                }
                window['inst_{$canvas_id}'] = new Chart(ctx, {
                    type: '{$this->type}',
                    data: {
                        labels: {$js_labels},
                        datasets: {$js_datasets}
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: { display: Boolean({$js_title}), text: {$js_title} },
                            legend: { display: {$js_legend} }
                        }
                    }
                });
            }

            if (typeof Chart === 'undefined') {
                var script = document.createElement('script');
                script.src = '/assets/chart/chart.js';
                script.onload = initChart;
                document.head.appendChild(script);
            } else {
                initChart();
            }
        })();
        </script>";
    }
}
?>