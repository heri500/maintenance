<?php

/**
 * @return array
 */
function get_jenis_bbm_array(){
    $ArrayJenisBbm = array();
    if (function_exists('create_array_from_view')){
        $ArrayJenisBbm = create_array_from_view('data_jenis_bbm');
    }
    return $ArrayJenisBbm;
}

/**
 * @return array
 */
function get_jenis_bbm_array_by_nid(){
    $ArrayJenisBbm = get_jenis_bbm_array();
    $NewArray = array();
    for ($i = 0;$i < count($ArrayJenisBbm);$i++){
        $NewArray[$ArrayJenisBbm[$i]->nid] = $ArrayJenisBbm[$i];
    }
    return $NewArray;
}

/**
 * @param null $date1
 * @param null $date2
 * @return array
 */
function get_penjualan_bbm_by_date($date1 = null, $date2 = null){
    $ArrayPenjualan = array();
    if (!empty($date1) && !empty($date2)){
        if (function_exists('create_array_from_view')) {
            $filter = array();
            $filter[0]['filtername'] = 'field_tanggal_penjualan_value';
            $filter[0]['filtervalue'] = array(
                'min' => $date1,
                'max' => $date2,
            );
            $ArrayPenjualan = create_array_from_view('data_penjualan', null, $filter);
        }
    }else{
        if (function_exists('create_array_from_view')) {
            $ArrayPenjualan = create_array_from_view('data_penjualan');
        }
    }
    return $ArrayPenjualan;
}

/**
 * @param null $report_type
 * @param null $tahun
 * @param null $bulan
 * @return string
 */
function ikhwan_chart_total_omset($report_type = null, $tahun = null, $bulan = null){
    global $user;
    if (empty($report_type)){
        $report_type = 'monthly';
    }
    date_default_timezone_set('Asia/Jakarta');
    $module_path = drupal_get_path('module','pertamina_core');
    $csspath = $module_path.'/css/custom-style.css';
    drupal_add_css($csspath);
    $jspath = $module_path.'/js/graphictotalomset.js';
    drupal_add_js($jspath,array('scope' => 'footer', 'weight' => 5));
    drupal_set_title('Graphic Total Omset Pertamina');
    $yearBeforeButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-warning'),
                'id' => 'year-before',
                'name' => 'year-before',
            ),
            '#button_type' => 'button',
            '#value' => date('Y') - 1,
        )
    );
    drupal_add_js(
        array(
            'report_type' => $report_type,
        )
        , 'setting');
    include_date_picker_function();
    $yearBeforeButton = theme('button', $yearBeforeButtonVar);
    $yearBeforeButton .= '<input type="hidden" id="year_before" name="year_before" value="'.(date('Y') - 1).'">';
    $currYearButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-warning'),
                'id' => 'curr-year',
                'name' => 'curr-year',
            ),
            '#button_type' => 'button',
            '#value' => date('Y'),
        )
    );
    $currYearButton = theme('button', $currYearButtonVar);
    $currYearButton .= '<input type="hidden" id="curr_year" name="curr_year" value="'.date('Y').'">';
    $monthBeforeButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-primary'),
                'id' => 'month-before',
                'name' => 'month-before',
            ),
            '#button_type' => 'button',
            '#value' => date('F', mktime(0,0,0,date('n') -1,1,date('Y'))),
        )
    );
    $monthBeforeButton = theme('button', $monthBeforeButtonVar);
    $monthBefore = date('Y', mktime(0,0,0,date('n') - 1, 1, date('Y'))).'_'.date('n', mktime(0,0,0,date('n') - 1, 1, date('Y')));
    $monthBeforeButton .= '<input type="hidden" id="month_before" name="month_before" value="'.$monthBefore.'">';
    $currMonthButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-primary'),
                'id' => 'curr-month',
                'name' => 'curr-month',
            ),
            '#button_type' => 'button',
            '#value' => date('F'),
        )
    );
    $currMonthButton = theme('button', $currMonthButtonVar);
    $currMonthButton .= '<input type="hidden" id="curr_month" name="curr_month" value="'.date('Y').'_'.date('n').'">';
    $weeklyButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-success'),
                'id' => 'weekly-report',
                'name' => 'weekly-report',
            ),
            '#button_type' => 'button',
            '#value' => 'week',
        )
    );
    $weeklyButton = theme('button', $weeklyButtonVar);
    $dailyButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-danger'),
                'id' => 'daily-report',
                'name' => 'daily-report',
            ),
            '#button_type' => 'button',
            '#value' => 'today',
        )
    );
    $dailyButton = theme('button', $dailyButtonVar);
    $tglawal = 0;
    $tglakhir = 0;
    if (isset($_GET['tglawal']) && isset($_GET['tglakhir'])){
        $xD1 = explode('-',$_GET['tglawal']);
        $xD2 = explode('-',$_GET['tglakhir']);
        $tglawal = mktime(5, 0, 0, (int)$xD1[1], (int)$xD1[2], (int)$xD1[0]);
        $tglakhir = mktime(23, 0, 0, (int)$xD2[1], (int)$xD2[2], (int)$xD2[0]);
    }else {
        if ($report_type == 'monthly') {
            if (is_null($tahun) || $tahun === 0) {
                $tahun = date('Y');
            }
            if (is_null($bulan) || $bulan === 0) {
                $bulan = date('n');
            }
            if ($tahun == date('Y') && $bulan == date('n')) {
                $tglawal = mktime(5, 0, 0, $bulan, 1, $tahun);
                $tglakhir = get_latest_tgl_penjualan();
            } else {
                $tglawal = mktime(5, 0, 0, $bulan, 1, $tahun);
                $lastDay = get_last_day($bulan, $tahun);
                $tglakhir = mktime(23, 0, 0, $bulan, $lastDay, $tahun);
            }
        } else if (empty($report_type) || $report_type == 'weekly') {
            $tglawal = mktime(5, 0, 0, date('n'), (date('d') - 6), date('Y'));
            $tglakhir = get_latest_tgl_penjualan();
        } else if ($report_type == 'yearly') {
            if (is_null($tahun) || $tahun === 0) {
                $tahun = date('Y');
            }
            if ($tahun == date('Y')) {
                $tglawal = mktime(5, 0, 0, 1, 1, $tahun);
                $tglakhir = get_latest_tgl_penjualan();
            } else if ($tahun < date('Y')) {
                $tglawal = mktime(5, 0, 0, 1, 1, $tahun);
                $tglakhir = mktime(23, 0, 0, 12, 31, $tahun);
            }
        } else if ($report_type == 'daily') {
            $jamSekarang = date('G');
            if ($jamSekarang < 5){
                $tglawal = mktime(5, 0, 0, date('n'), (int)date('d') - 1, date('Y'));
            }else{
                $tglawal = mktime(5, 0, 0, date('n'), date('d'), date('Y'));
            }
            $tglakhir = get_latest_tgl_penjualan();
            $jamTanggalAkhir = date('G',$tglakhir);
            if ($tglakhir < $tglawal){
                if ($jamTanggalAkhir < 5){
                    $tglawal = mktime(5, 0, 0, date('n'), date('d') - 1, date('Y'));
                }else{
                    $tglakhir = mktime(23, 0, 0, date('n'), date('d'), date('Y'));
                }
            }
        }
    }
    $chartView = '<div class="col-md-12 main-chart">';
    $chartView .= total_omset_chart($tglawal,$tglakhir);
    $chartView .= '</div>';
    $viewButtonVar = array(
        'element' => array(
            '#attributes' => array(
                'class' => array('btn-success'),
                'id' => 'view-report',
                'name' => 'view-report',
            ),
            '#button_type' => 'button',
            '#value' => 'View',
        )
    );
    $viewButton = theme('button', $viewButtonVar);
    $periodeStart = '<div clasa="col-md-12"><input type="text" id="start_date" name="start_date" value="'.date('Y-m-d', $tglawal).'" class="input-date form-control form-text datepicker">';
    $periodeStart .= '<input type="text" id="end_date" name="end_date" value="'.date('Y-m-d', $tglakhir).'" class="input-date form-control form-text datepicker"></div>';
    $periodeStart .= $viewButton;
    return $yearBeforeButton.$currYearButton.$monthBeforeButton.$currMonthButton.$weeklyButton.$dailyButton.$periodeStart.$chartView;
}

/**
 * @return int
 */
function get_latest_tgl_penjualan(){
    $result = time();
    if (function_exists('create_array_from_view')) {
        $ArrayPenjualanAkhir = create_array_from_view('data_penjualan_akhir');
        $result = $ArrayPenjualanAkhir[0]->field_field_tanggal_penjualan[0]['raw']['value'];
    }
    return $result;
}

/**
 * @param null $month
 * @param null $year
 * @return string
 */
function input_penjualan_pertamina($month = null, $year = null){
    $module_path = drupal_get_path('module','pertamina_core');
    $csspath = $module_path.'/css/custom-style.css';
    drupal_add_css($csspath);
    if (!empty($month) && !empty($year)){
        $variables['month'] = $month;
        $variables['year'] = $year;
        $form = drupal_get_form('penjualan_bbm_form',$variables);
        $formPendapatan = drupal_render($form);
    }else{
        $month = date('n');
        $year = date('Y');
        $variables['month'] = $month;
        $variables['year'] = $year;
        $variables['alamat'] = 'inputpenjualan';
        $form = drupal_get_form('filter_periode_form',$variables);
        $formPendapatan = drupal_render($form);
    }
    return $formPendapatan;
}

function summary_penjualan_bbm_by_date($dateFrom = null, $dateThru = null){
    if (!empty($dateFrom) && !empty($dateThru)) {
        $SplitDate = explode('-', $dateFrom);
        $dateFrom = mktime(5,0,0, $SplitDate[1], $SplitDate[2] - 1, $SplitDate[0]);
        $dateFrom = date('Y-m-d',$dateFrom);
        $arrOmset = get_penjualan_bbm_by_date($dateFrom, $dateThru);
        $ArrOmsetByBbm = array();
        $ArrModalByBbm = array();
        if (count($arrOmset)) {
            $totalOmset = 0;
            $totalModal = 0;
            for ($i = 0; $i < count($arrOmset); $i++) {
                $dataOmset = $arrOmset[$i]->field_field_harga_jual[0]['raw']['value'] * $arrOmset[$i]->field_field_jumlah_liter_jual[0]['raw']['value'];
                $dataModal = $arrOmset[$i]->field_field_harga_modal[0]['raw']['value'] * $arrOmset[$i]->field_field_jumlah_liter_jual[0]['raw']['value'];
                $totalOmset = $totalOmset + $dataOmset;
                $totalModal = $totalModal + $dataModal;
                $IdJenisBbm = $arrOmset[$i]->field_field_jenis_bbm_jual[0]['raw']['nid'];
                $TglJual = $arrOmset[$i]->field_field_tanggal_penjualan[0]['raw']['value'];
                if (!isset($ArrOmsetByBbm[$TglJual])) {
                    $ArrOmsetByBbm[$TglJual][$IdJenisBbm] = $dataOmset;
                } else {
                    if (!isset($ArrOmsetByBbm[$TglJual][$IdJenisBbm])){
                        $ArrOmsetByBbm[$TglJual][$IdJenisBbm] = $dataOmset;
                    }else {
                        $ArrOmsetByBbm[$TglJual][$IdJenisBbm] = $ArrOmsetByBbm[$TglJual][$IdJenisBbm] + $dataOmset;
                    }
                }
                if (!isset($ArrModalByBbm[$TglJual])) {
                    $ArrModalByBbm[$TglJual][$IdJenisBbm] = $dataModal;
                } else {
                    if (!isset($ArrModalByBbm[$TglJual][$IdJenisBbm])) {
                        $ArrModalByBbm[$TglJual][$IdJenisBbm] = $dataModal;
                    }else{
                        $ArrModalByBbm[$TglJual][$IdJenisBbm] = $ArrModalByBbm[$TglJual][$IdJenisBbm] + $dataModal;
                    }
                }
            }
        }
    }
    return array('omset' => $ArrOmsetByBbm, 'modal' => $ArrModalByBbm);
}

function create_laporan_penjualan_harian($dateFrom = null, $dateThru = null){
    $LaporanPenjualan = '';
    if (!empty($dateFrom) && !empty($dateThru)){
        $SummaryPenjualan = summary_penjualan_bbm_by_date($dateFrom,$dateThru);
        $JenisBbm = get_jenis_bbm_array_by_nid();
        if (count($SummaryPenjualan)){
            if (isset($SummaryPenjualan['omset']) && count($SummaryPenjualan['omset'])){
                foreach ($SummaryPenjualan['omset'] as $IntTglJual => $OmsetBbm){
                    $LaporanPenjualan .= 'Laporan Omset Pom Bensin GISBH';
                    $LaporanPenjualan .= '<br>';
                    $LaporanPenjualan .= 'Tanggal : '.date('j',$IntTglJual).' '.month_array(date('n',$IntTglJual) - 1).' '.date('Y',$IntTglJual);
                    $LaporanPenjualan .= '<br>';
                    if (count($OmsetBbm)){
                        $TotalModal = 0;
                        $TotalOmset = 0;
                        foreach ($OmsetBbm as $NidBbm => $Omset){
                            $LaporanPenjualan .= '<b>Omset '.$JenisBbm[$NidBbm]->node_title.' : Rp. '.number_format($Omset,0,',','.').'</b>';
                            $LaporanPenjualan .= '<br>';
                            $TotalOmset = $TotalOmset + $Omset;
                            $Modal = isset($SummaryPenjualan['modal']) && isset($SummaryPenjualan['modal'][$IntTglJual]) && isset($SummaryPenjualan['modal'][$IntTglJual][$NidBbm]) ? $SummaryPenjualan['modal'][$IntTglJual][$NidBbm] : 0;
                            $TotalModal = $TotalModal + $Modal;
                        }
                        $LaporanPenjualan .= '<b>TOTAL OMSET : Rp. '.number_format($TotalOmset,0,',','.').'</b>';
                        $LaporanPenjualan .= '<br>';
                        $LaporanPenjualan .= '<b>TOTAL MODAL : Rp. '.number_format($TotalModal,0,',','.').'</b>';
                        $LaporanPenjualan .= '<br>';
                        $LaporanPenjualan .= '<b>TOTAL KEUNTUNGAN : Rp. '.number_format($TotalOmset - $TotalModal,0,',','.').'</b>';
                        $LaporanPenjualan .= '<br>';
                        $LaporanPenjualan .= '<br>';
                    }
                }
            }
        }
    }
    return $LaporanPenjualan;
}

function get_overhead_harian($bulan = null, $tahun = null){
    $OverheadHarian =  9225264;
    if (!empty($bulan) && !empty($tahun)){

    }
    return $OverheadHarian;
}