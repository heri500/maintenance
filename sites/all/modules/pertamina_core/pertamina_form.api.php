<?php

function filter_periode_form($form, &$form_state, $variables = null){
    if (!isset($variables['content'])) {
        $variables['content'] = t('Pilih Bulan dan Tahun Penjualan, kemudian klik tombol view untuk mulai mengisi penjualan');
    }
    $monthArray = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    );
    $currDate = date('j');
    if (isset($variables['month']) && isset($variables['year'])){
        $month = $variables['month'];
        $year = $variables['year'];
    }else {
        if ($currDate < 10) {
            $intDate = mktime(7, 0, 0, (date('n') - 1), 1, date('Y'));
            $month = date('n', $intDate);
            $year = date('Y', $intDate);
        } else {
            $month = date('n');
            $year = date('Y');
        }
    }
    if (isset($variables['alamat']) && !empty($variables['alamat'])){
        $alamat = $variables['alamat'];
    }else{
        $alamat = 'graphtotalomset';
    }
    $form['#attributes'] = array('class' => array('form-inline'));
    $form['basic'] = array(
        '#type' => 'fieldset',
        '#title' => t('Form Entry Penjualan'),
        '#collapsible' => FALSE, // Added
        '#collapsed' => FALSE,  // Added
        '#attributes' => array('class' => array('form-inline')),
    );
    $form['basic']['info_content'] = array(
        '#type' => 'item',
        '#markup' => $variables['content'],
        '#prefix' => '<div>',
        '#suffix' => '</div>',
    );
    $form['basic']['month'] = array(
        '#type' => 'select',
        '#title' => t('Bulan'),
        '#default_value' => $month,
        '#options' => $monthArray,
        '#select2' => array(
            'placeholder' => 'Pilih Bulan Penjualan',
        ),
    );
    $form['basic']['year'] = array(
        '#type' => 'textfield',
        '#title' => t('Tahun'),
        '#default_value' => $year,
        '#attributes' => array('class' => array('col-number')),
    );
    $form['basic']['alamat'] = array(
        '#type' => 'hidden',
        '#default_value' => $alamat,
    );
    $form['basic']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Show'),
        '#attributes' => array('class' => array('btn-sm btn-warning'), 'style' => 'margin: -8px 10px 0 !important;'),
    );
    return $form;
}

function filter_periode_form_submit($form, &$form_state){
    $values = $form_state['values'];
    drupal_goto($values['alamat'].'/'.$values['month'].'/'.$values['year']);
}

function input_penjualan_header(){
    $JenisBbm = get_jenis_bbm_array();
    $tableHeader = array();
    $tableHeader[] = array(
        'data' => t('TANGGAL'),
        'class' => array(
            'col-input-number'
        ),
    );
    for ($i = 0;$i < count($JenisBbm);$i++){
        $tableHeader[] = array(
            'data' => t($JenisBbm[$i]->node_title),
            'class' => array(
                'col-input-number'
            ),
        );
    }
    return $tableHeader;
}

function create_penjualan_row($dataPenjualan = null, $date1 = null, $date2 = null){
    if (!empty($date1) && !empty($date2)){
        $ArrPenjualan = array();
        for ($i = 0;$i < count($dataPenjualan);$i++){
            $TglPenjualan = $dataPenjualan[$i]->field_field_tanggal_penjualan[0]['raw']['value'];
            $ShiftPenjualan = $dataPenjualan[$i]->field_field_shift_kerja[0]['raw']['value'];
            $NidBbm = $dataPenjualan[$i]->field_field_jenis_bbm_jual[0]['raw']['nid'];
            $ArrPenjualan[$NidBbm][$TglPenjualan][$ShiftPenjualan] = $dataPenjualan[$i];
        }
        $DateArray = create_date_range_array($date1,$date2,true);
        $DateArray2 = create_date_range_array($date1,$date2);
        $JenisBbm = get_jenis_bbm_array();
        $tableRow = array();
        $tableRow['#tree'] = TRUE;
        $counter = 0;
        for ($i = 0;$i < count($DateArray);$i++){
            for ($j = 0;$j < 2;$j++) {
                $rowData = array();
                $dateTitle = $DateArray2[$i].' Shift-'.($j + 1);
                $ColIdx = 'c_' . $DateArray[$i] . '_' .($j + 1);
                $rowData[$ColIdx] = array(
                    '#type' => 'item', '#title' => $dateTitle,
                    '#attributes' => array(
                        'outerclass' => array(
                            'align-left'
                        )
                    ),
                );
                for ($k = 0;$k < count($JenisBbm);$k++){
                    $ColIdx = 'bbm_' . $JenisBbm[$k]->nid .'_'.$DateArray[$i] . '_' .($j + 1);
                    $defaultValue = 0;
                    if (isset($ArrPenjualan[$JenisBbm[$k]->nid]) && isset($ArrPenjualan[$JenisBbm[$k]->nid][$DateArray[$i]])
                    && isset($ArrPenjualan[$JenisBbm[$k]->nid][$DateArray[$i]][($j + 1)])){
                        $defaultValue = $ArrPenjualan[$JenisBbm[$k]->nid][$DateArray[$i]][($j + 1)]->field_field_jumlah_liter_jual[0]['raw']['value'];
                        $NidPenjualan = $ArrPenjualan[$JenisBbm[$k]->nid][$DateArray[$i]][($j + 1)]->nid;
                        $ColIdx .= '_'.$NidPenjualan;
                    }
                    $rowData[$ColIdx] = array(
                        '#type' => 'textfield',
                        '#default_value' => $defaultValue,
                        '#attributes' => array(
                            'class' => array(
                                'align-right',
                            ),
                        ),
                    );
                }
                $tableRow['r' . ($counter)] = $rowData;
                $counter++;
            }
        }
    }
    return $tableRow;
}

function penjualan_bbm_form($form, &$form_state, $variables = null){
    if (isset($variables['month']) && !empty($variables['year'])){
        $tableHeader = input_penjualan_header();
        $day = 1;
        $dateBefore = mktime(0,0,0, $variables['month'], $day - 1, $variables['year']);
        $date1 = date('Y-m-d',$dateBefore);
        $LastDay = get_last_day($variables['month'],$variables['year']);
        $date2 = $variables['year'].'-'.str_pad($variables['month'],2,'0',STR_PAD_LEFT).'-'.$LastDay;
        $dataPenjualan = get_penjualan_bbm_by_date($date1, $date2);
        $date1 = $variables['year'].'-'.str_pad($variables['month'],2,'0',STR_PAD_LEFT).'-01';
        $tableRow = create_penjualan_row($dataPenjualan,$date1,$date2);
        $tableFooter = input_penjualan_header();
        $form['penjualan'] = array(
            '#type' => 'fieldset',
            '#title' => 'Penjualan BBM '.date('M').' '.date('Y'),
            '#collapsible' => FALSE, // Added
            '#collapsed' => FALSE,  // Added
        );
        $form['penjualan']['table'] = array(
            '#theme' => 'formtable_form_table',
            '#header' => $tableHeader,
            'rows' => $tableRow,
            '#footer' => array($tableFooter),
        );
        $form['pengeluaran']['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Save Penjualan'),
        );
    }
    return $form;
}
function penjualan_bbm_form_submit($form, &$form_state)
{
    global $user;
    if (isset($form_state['values']['rows'])) {
        $values = $form_state['values']['rows'];
    } else {
        $values = $form_state['values'];
    }
    $JenisBbm = get_jenis_bbm_array_by_nid();
    if (count($values)){
        foreach ($values as $IdxRow => $RowData){
            if (count($RowData)){
                foreach ($RowData as $IdxCol => $QtyBbm){
                    $SplitId = explode('_', $IdxCol);
                    $NidBbm = isset($SplitId[1]) && !empty($SplitId[1]) ? $SplitId[1] : 0;
                    if (!empty($NidBbm)) {
                        if (isset($JenisBbm[$NidBbm])) {
                            $HargaJual = $JenisBbm[$NidBbm]->field_field_harga_jual_latest[0]['raw']['value'];
                            $HargaBeli = $JenisBbm[$NidBbm]->field_field_harga_beli_latest[0]['raw']['value'];
                            $TanggalPenjualan = isset($SplitId[2]) && !empty($SplitId[2]) ? $SplitId[2] : 0;
                            $ShiftKe = isset($SplitId[3]) && !empty($SplitId[3]) ? $SplitId[3] : 1;
                            if (isset($SplitId[4])){
                                $NodePenjualan = node_load($SplitId[4]);
                                if (!empty($NodePenjualan)){
                                    if ($NodePenjualan->field_jumlah_liter_jual[LANGUAGE_NONE][0]['value'] != $QtyBbm){
                                        $NodePenjualan->field_jumlah_liter_jual[LANGUAGE_NONE][0]['value'] = $QtyBbm;
                                        node_save($NodePenjualan);
                                    }
                                }
                            }else{
                                global $user;
                                date_default_timezone_set($user->timezone);
                                $Tgl = date('j', $TanggalPenjualan).' '.month_array((date('n', $TanggalPenjualan) - 1)).' '.date('Y', $TanggalPenjualan);
                                $NodeTitle = 'Penjualan '.ucfirst(strtolower($JenisBbm[$NidBbm]->node_title)).' '.$Tgl.' Shift '.$ShiftKe;
                                $node = new stdClass();
                                $node->type = 'penjualan_bbm';
                                $node->created = time();
                                $node->changed = time();
                                $node->status = 1;          // Published?
                                $node->promote = 0;       // Display on front page?
                                $node->sticky = 0;          // Display top of page?
                                $node->format = 0;         // Filtered HTML?
                                $node->uid = $user->uid;	             //  Content owner uid (author)?
                                $node->name = $user->name;
                                $node->title = $NodeTitle;
                                $node->field_tanggal_penjualan[LANGUAGE_NONE][0]['value'] = $TanggalPenjualan;
                                $node->field_jenis_bbm_jual[LANGUAGE_NONE][0]['nid'] = $NidBbm;
                                $node->field_jumlah_liter_jual[LANGUAGE_NONE][0]['value'] = $QtyBbm;
                                $node->field_harga_jual[LANGUAGE_NONE][0]['value'] = $HargaJual;
                                $node->field_harga_modal[LANGUAGE_NONE][0]['value'] = $HargaBeli;
                                $node->field_shift_kerja[LANGUAGE_NONE][0]['value'] = $ShiftKe;
                                if (!empty($NidBbm) && !empty($QtyBbm)){
                                    node_save($node);
                                }
                           }
                        }
                    }
                }
            }
        }
    }
    drupal_set_message('Data penjualan berhasil disimpan...!!');
}