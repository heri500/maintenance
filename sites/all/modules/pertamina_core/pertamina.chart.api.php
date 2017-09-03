<?php

function total_omset_chart($dateFrom = null, $dateThru = null, $exportToPng = false){
    $omsetChart = '';
    if (!empty($dateFrom) && !empty($dateThru)) {
        $detect = mobile_detect_get_object();
        $tglAwal = date('Y-m-d', $dateFrom);
        $tglAkhir = date('Y-m-d', $dateThru);
        if ($tglAwal == $tglAkhir) {
            $omsetData['title'] = 'Omset Pertamina ' . date('d M Y', $dateFrom);
        } else {
            $omsetData['title'] = 'Omset Pertamina ' . date('d M Y', $dateFrom) . ' s/d ' . date('d M Y', $dateThru);
        }
        $dateFrom = date('Y-m-d',$dateFrom);
        $SplitDate = explode('-', $dateFrom);
        $dateFrom = mktime(5,0,0, $SplitDate[1], $SplitDate[2] - 1, $SplitDate[0]);
        $arrOmset = get_penjualan_bbm_by_date(date('Y-m-d',$dateFrom), date('Y-m-d',$dateThru));
        $ArrOmsetByBbm = array();
        $ArrModalByBbm = array();
        if (count($arrOmset)) {
            $totalOmset = 0;
            $totalModal = 0;
            for ($i = 0;$i < count($arrOmset);$i++) {
                $dataOmset = $arrOmset[$i]->field_field_harga_jual[0]['raw']['value'] * $arrOmset[$i]->field_field_jumlah_liter_jual[0]['raw']['value'];
                $dataModal = $arrOmset[$i]->field_field_harga_modal[0]['raw']['value'] * $arrOmset[$i]->field_field_jumlah_liter_jual[0]['raw']['value'] ;
                $totalOmset = $totalOmset + $dataOmset;
                $totalModal = $totalModal + $dataModal;
                $IdJenisBbm = $arrOmset[$i]->field_field_jenis_bbm_jual[0]['raw']['nid'];
                if (!isset($ArrOmsetByBbm[$IdJenisBbm])){
                    $ArrOmsetByBbm[$IdJenisBbm] = $dataOmset;
                }else{
                    $ArrOmsetByBbm[$IdJenisBbm] = $ArrOmsetByBbm[$IdJenisBbm] + $dataOmset;
                }
                if (!isset($ArrModalByBbm[$IdJenisBbm])){
                    $ArrModalByBbm[$IdJenisBbm] = $dataModal;
                }else{
                    $ArrModalByBbm[$IdJenisBbm] = $ArrModalByBbm[$IdJenisBbm] + $dataModal;
                }
            }
        }
        $ArrData = array();
        $JenisBbm = get_jenis_bbm_array_by_nid();
        foreach ($ArrOmsetByBbm as $NidBbm => $OmsetBbm) {
            $ArrData[] = array($JenisBbm[$NidBbm]->node_title, $OmsetBbm);
        }
        $omsetData['subtitle'] = 'Total Omset : Rp ' . number_format($totalOmset, 0, '.', ',');
        $omsetData['subtitle'] .= ', Modal : Rp '.number_format($totalModal, 0, '.', ',');
        $omsetData['subtitle'] .= ', Keuntungan : Rp '.number_format(($totalOmset - $totalModal), 0, '.', ',');
        if (count($ArrData)) {
            $omsetData['series'][] = (object)array(
                'name' => 'Omset',
                'data' => $ArrData,
            );
        }
        $options = omset_total_chart_options($omsetData);
        if ($exportToPng) {
            return $options;
        } else {
            if (is_object($options)) {
                // Optionally add styles or any other valid attributes, suitable for
                // drupal_attributes().
                if ($detect->isMobile()) {
                    $attributes = array('style' => array('height: 450px;'));
                } else {
                    $attributes = array('style' => array('height: 600px;'));
                }
                // Return block definition.
                $omsetChart = highcharts_render($options, $attributes);
            }
        }
    }
    return $omsetChart;
}

function omset_total_chart_options($omsetData = null, $seriesColor = null)
{
    $options = null;
    if (!empty($omsetData)) {
        $options = new stdClass();
        // Chart.
        $options->chart = (object)array(
            'renderTo' => 'container',
            'type' => 'pie',
            'backgroundColor' => 'transparent',
            'options3d' => (object)array(
                'enabled' => TRUE,
                'alpha' => 45,
            ),
        );
        // Title.
        $options->title = new stdClass();
        $options->title->text = strtoupper($omsetData['title']);

        // Sub Title
        $options->subtitle = new stdClass();
        $options->subtitle->text = strtoupper($omsetData['subtitle']);

        // Legend.
        /*$options->legend = new stdClass();
        $options->legend = (object)array(
            'align' => 'right',
            'x' => -30,
            'verticalAlign' => 'top',
            'y' => 75,
            'floating' => true,
            'backgroundColor' => 'transparent',
            'borderColor' => '#CCC',
            'borderWidth' => 1,
            'shadow' => false,
        );
        */
        // Tooltip
        /*$options->tooltip = new stdClass();
        $options->tooltip = (object)array(
            'headerFormat' => '<b>{point.x}</b><br/>',
            'pointFormat' => '{series.name}: {point.y}<br/> Total: {point.stackTotal}',
        );*/
        $options->plotOptions = new stdClass();
        $options->plotOptions->pie = new stdClass();
        $options->plotOptions->pie->depth = 45;
        $options->plotOptions->pie->innerSize = 100;
        $options->plotOptions->pie->allowPointSelect = true;
        $options->plotOptions->pie->dataLabels = new stdClass();
        $options->plotOptions->pie->dataLabels->enabled = true;
        $options->plotOptions->pie->dataLabels->format = '{point.name}: <b>Rp. {point.y:,.0f}</b>';
        // Series.
        $options->series = array();
        $options->series = $omsetData['series'];
        // Disable credits.
        $options->credits = new stdClass();
        $options->credits->enabled = FALSE;
    }
    return $options;
}