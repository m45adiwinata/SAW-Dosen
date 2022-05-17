<?php
    function getAttribute($arr, $key) {
        $max = -INF;
        $min = INF;
        $sum = 0;
        foreach ($arr as $value) {
            if ($max < $value[$key]) {
                $max = $value[$key];
            }
            if ($min > $value[$key]) {
                $min = $value[$key];
            }
            $sum += $value[$key];
        }

        return array(
            'max' => $max,
            'min' => $min,
            'sum' => $sum
        );
    }

    function sortData($data) {
        for ($i=0; $i < count($data)-1; $i++) { 
            for ($j=$i+1; $j < count($data); $j++) { 
                // echo $data[$i]['n_bobot']." : ".$data[$j]['n_bobot']."<br>";
                if ($data[$i]['n_bobot'] < $data[$j]['n_bobot']) {
                    $temp = $data[$i];
                    $data[$i] = $data[$j];
                    $data[$j] = $temp;
                }
            }
        }
        return $data;
    }
