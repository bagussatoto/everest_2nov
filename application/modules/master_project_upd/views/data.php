<?php
/**
 * Created by PhpStorm.
 * User: aziz
 * Date: 8/16/2018
 * Time: 8:51 PM
 */

switch ($mode) {
    case "index":
        $scriptBottom = "";
        $arrBlacklist = array(
            "no",
        );
        $stepper = isset($_GET['step']) ? $_GET['step'] : 1;
        if (isset($_GET['step'])) {
            //            arrPrint($steps[$_GET['step']]);
        }

        $time_line = "cek";
        if (isset($allSteps)) {
            $time_line = createStateHorizontal('-1', sizeof($allSteps), $jenisTr);
        }
        //-----------------------------------
        $keterangan_notif = notifTransaksi();
        //-----------------------------------
        if (strlen($errMsg) > 0) {
            $error = "<div class='alert alert-danger-dot text-center'><span>$errMsg</span></div>";
        }
        else {
            $error = "";
        }

        $title = isset($title) ? $title : "";
        $subTitle = isset($subTitle) ? $subTitle : "";
        $arrayHistoryLabels = isset($arrayHistoryLabels) ? $arrayHistoryLabels : array();
        $arrayHistory = isset($arrayHistory) ? $arrayHistory : array();

        $p = New Layout("$title", "$subTitle", MODUL_TEMPLATE_PATH . "template/transaksi_index.html");

        $strOnprePre = "";
        $strOnprog = "";

        if (isset($arrayOnProgressToPay) && $arrayOnProgressToPay == true) {
            //            cekHere($arrayOnProgressToPay);
        }
        else {
            //region onprogress

            //            $strOnprog = "";
            //            $switchToHistory = count($steps) == $stepper ? "History" : "";
            //
            //            if (sizeof($steps) > 1) {
            //                $strOnprog .= "<ul class='nav nav-tabs'>";
            //                foreach ($steps as $tStep => $stepData) {
            //                    $isiBadge = isset($arrayOnprogressGroup[$tStep]) ? "<span class='badge bg-red'>" . sizeof($arrayOnprogressGroup[$tStep]) . "</span>" : "";
            //                    $actives = $tStep == $stepper ? "active" : "";
            //                    $trSelesai = count($steps) == $tStep ? "SELESAI<br>" : "";
            //
            //
            //                    $cssSelesai = count($steps) == $tStep ? "style='padding-top: 0;padding-bottom: 0;'" : "";
            //                    $strOnprog .= "<li class='$actives'>";
            //                    $strOnprog .= "<a $cssSelesai class='nav-link btn' onclick=\"location.href='" . base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "?step=$tStep';\">";
            //                    $strOnprog .= "<span class='text-uppercase text-bold'>$trSelesai" . $stepData['label'] . "</span>  $isiBadge </a>";
            //                    $strOnprog .= "</li>";
            //                }
            //                $strOnprog .= "</ul>";
            //
            //                $strOnprog .= "<div class='clearfix'>&nbsp;</div>";
            //            }
            //
            //
            //            if (isset($arrayOnprogressGroup[$stepper]) && sizeof($arrayOnprogressGroup[$stepper]) > 0) {
            //                $arrayOnProgress = $arrayOnprogressGroup[$stepper];
            //                $arrayOnprogressMarking = (isset($arrayOnprogressGroupPartialMark[$stepper]) && (sizeof($arrayOnprogressGroupPartialMark[$stepper]) > 0)) ? $arrayOnprogressGroupPartialMark[$stepper] : "";
            //            }
            //            else {
            //                $arrayOnProgress = array();
            //                $arrayOnprogressMarking = array();
            //            }
            //            if (sizeof($arrayOnProgress) > 0) {
            ////                $strOnprog .= "<div class='table-responsive'>";
            //                $strOnprog .= "<table id='arrayOnProgress$switchToHistory' class='table stripe compact nowarp order-column table-condensed table-bordered no-padding' style='border:solid red 0px;'>";
            //                $strOnprog .= "<thead>";
            //
            //                if (count($steps) == $stepper) {
            //
            //                }
            //                else {
            //                    $strOnprog .= "<tr class='text-uppercase' line=" . __LINE__ . ">";
            //                    if (sizeof($arrayProgressLabels) > 0) {
            //                        $strOnprog .= "<th class=''>No.</th>";
            //                        foreach ($arrayProgressLabels as $key => $label) {
            //                            $strOnprog .= "<th class=''>";
            //                            if (is_array($label)) {
            //                                $strOnprog .= isset($label['label']) ? $label['label'] : "-";
            //                            }
            //                            else {
            //                                $strOnprog .= $label;
            //                            }
            //                            $strOnprog .= "</th>";
            //                        }
            //                    }
            //                    $strOnprog .= "</tr>";
            //                }
            //
            //                $strOnprog .= "</thead>";
            //                $strOnprog .= "<tbody>";
            //
            //                if (count($steps) == $stepper) {
            //
            //                }
            //                else {
            //                    $no = 0;
            //                    foreach ($arrayOnProgress as $key => $val) {
            //                        //----------------------
            //                        $background_color = isset($arrayOnprogressMarking[$key]['style']) ? $arrayOnprogressMarking[$key]['style'] : "";
            //
            //
            //                        $no++;
            //                        $strOnprog .= "<tr line=" . __LINE__ . " style='$background_color'>";
            //                        $strOnprog .= "<td>$no</td>";
            //                        if (sizeof($arrayProgressLabels) > 0) {
            //                            foreach ($arrayProgressLabels as $key => $label) {
            //                                $strOnprog .= "<td>";
            //                                $strOnprog .= $val[$key];
            //                                $strOnprog .= "</td>";
            //                            }
            //                        }
            //                        $strOnprog .= "</tr>";
            //                    }
            //                }
            //
            //
            //                $strOnprog .= "</tbody>";
            //
            //
            //                if (isset($sumFooter) && sizeof($sumFooter) > 0) {
            //                    $strOnprog .= "<tfoot>";
            //                    $strOnprog .= "<tr line=" . __LINE__ . ">";
            //
            //                    if (count($steps) == $stepper) {
            //
            //                    }
            //                    else {
            //                        if (sizeof($arrayProgressLabels) > 0) {
            //                            $strOnprog .= "<td>-</td>";
            //                            foreach ($arrayProgressLabels as $key => $label) {
            //                                $strOnprog .= "<td>";
            //                                if (isset($sumFooter) && isset($sumFooter[$key])) {
            //                                    $strOnprog .= $sumFooter[$key];
            //                                }
            //                                else {
            //                                    $strOnprog .= "-";
            //                                }
            //                                $strOnprog .= "</td>";
            //                            }
            //                        }
            //                    }
            //
            //                    $strOnprog .= "</tr>";
            //                    $strOnprog .= "</tfoot>";
            //                }
            //
            //                $strOnprog .= "</table>";
            //                $strOnprogFooter = "<a class='btn btn-default' href='" . base_url() . $this->uri->segment(1) . "/viewIncomplete/" . $jenisTr . "'><span class='glyphicon glyphicon-time'></span> complete list ...</a>";
            //            }
            //            else {
            //                $stepName = isset($steps[$stepper]['label']) ? $steps[$stepper]['label'] : "";
            //                $strOnprog .= "- $stepName item you specified has no entry -";
            //                $strOnprogFooter = "";
            //            }
            //
            //endregion
        }


        //region onprogressView Doank
        $strOnprogView = "";
        //        if (is_array($arrayOnProgressView) && sizeof($arrayOnProgressView) > 0) {
        //            $strOnprogView .= "<table class='table table-condensed table-bordered no-padding'>";
        //            $strOnprogView .= "<tr bgcolor='#f0f0f0'>";
        //            $strOnprogView .= "<td class='text-muted'>No.</td>";
        //            if (sizeof($stepHistoryFields) > 0) {
        //                foreach ($stepHistoryFields as $key => $label) {
        //                    $strOnprogView .= "<td class='text-muted'>";
        //                    if (is_array($label)) {
        //                        $strOnprogView .= isset($label['label']) ? $label['label'] : "-";
        //                    }
        //                    else {
        //                        $strOnprogView .= $label;
        //                    }
        //                    $strOnprogView .= "</td>";
        //                }
        //            }
        //            $strOnprogView .= "</tr>";
        //            $noOPV = 0;
        //            foreach ($arrayOnProgressView as $key => $val) {
        //                $strOnprogView .= "<tr line=" . __LINE__ . ">";
        //                if (sizeof($stepHistoryFields) > 0) {
        //                    $noOPV++;
        //                    $strOnprogView .= "<td class='text-right'>$noOPV</td>";
        //                    foreach ($stepHistoryFields as $key => $label) {
        //                        $strOnprogView .= "<td>";
        //                        $strOnprogView .= isset($val[$key]) ? $val[$key] : "";
        //                        $strOnprogView .= "</td>";
        //                    }
        //                }
        //                $strOnprogView .= "</tr>";
        //            }
        //            $strOnprogView .= "</table>";
        ////            $strOnprogFooter = "<a class='btn btn-default' href='" . base_url() . $this->uri->segment(1) . "/viewIncomplete/" . $jenisTr . "'><span class='glyphicon glyphicon-time'></span> complete list ...</a>";
        //
        //            $onpropDisplayView = "block";
        //        }
        //        else {
        //            $strOnprogView .= "-the item you specified has no entry-";
        //            $strOnprogFooter = "";
        //            $onpropDisplayView = "none";
        //        }

        //endregion


        $strHist = "";
        //        if ((isset($arrayHistoryLabelsDt)) && (sizeof($arrayHistoryLabelsDt) > 0)) {
        //            $labelColumnDt = "\n";
        //            $sortColumnDef = "";
        //            foreach ($arrayHistoryLabelsDt as $key => $label) {
        //                $sortable = in_array($key, $availDbTable) ? "true" : "false";
        //                $sortColumnDef .= $key == reset($availDbTable) ? "order: [[ " . key($availDbTable) . ", 'asc' ]]," : "";
        //                if (is_array($label)) {
        //                    $labelx = isset($label['label']) ? strtoupper($label['label']) : "-";
        //                    $labelColumnDt .= "{ field: '$key', data: '$key', title: '$labelx', sortable: $sortable },\n";
        //                }
        //                else {
        //                    $labelColumnDt .= "{ field: '$key', data: '$key', title: '" . strtoupper($label) . "', sortable: $sortable },\n";
        //                }
        //            }
        //        }

        //region histories
        if (sizeof($arrayHistory) > 0) {
            $strHist .= "<table id='arrayHistory' class='table dataTable compact display table-bordered table-condensed no-padding'>";

            $strHist .= "<thead>";
            $strHist .= "<tr line=" . __LINE__ . ">";
            if (sizeof($arrayHistoryLabels) > 0) {
                foreach ($arrayHistoryLabels as $key => $label) {
                    $strHist .= "<td class='text-muted'>";
                    if (is_array($label)) {
                        $strHist .= isset($label['label']) ? $label['label'] : "-";
                    }
                    else {
                        $strHist .= $label;
                    }
                    $strHist .= "</td>";
                }
            }
            $strHist .= "</tr>";
            $strHist .= "</thead>";
            $strHist .= "<tbody>";

            foreach ($arrayHistory as $key => $val) {
                $strHist .= "<tr line=" . __LINE__ . ">";
                if (sizeof($arrayHistoryLabels) > 0) {
                    foreach ($arrayHistoryLabels as $key => $label) {
                        $strHist .= "<td>";
                        $strHist .= $val[$key];
                        $strHist .= "</td>";
                    }
                }
                $strHist .= "</tr>";
            }
            $strHist .= "</tbody>";

            $strHist .= "</table>";

            $strHistFooter = "<a class='btn btn-default' href='" . base_url() . $this->uri->segment(1) . "/viewHistory/" . $jenisTr . "'><span class='glyphicon glyphicon-time'></span> complete histories ...</a>";
        }
        else {
            $strHist = "-the item you specified has no entry-";
            $strHistFooter = "";
        }
        //endregion

        $strRecap = "";
        //region recap

        //        if (sizeof($arrayRecap) > 0) {
        //            $strRecap .= "<div class='table-responsive'>";
        //            $strRecap .= "<table id='arrayRecap' class='table table-condensed table-bordered no-padding'>";
        //
        //            $strRecap .= "<thead>";
        //            $strRecap .= "<tr line=" . __LINE__ . ">";
        //            if (sizeof($arrayRecapLabels) > 0) {
        //                foreach ($arrayRecapLabels as $key => $label) {
        //                    $strRecap .= "<td class='text-muted'>";
        //                    $strRecap .= $label;
        //                    $strRecap .= "</td>";
        //                }
        //            }
        //            $strRecap .= "</tr>";
        //            $strRecap .= "</thead>";
        //            $strRecap .= "<tbody>";
        //            foreach ($arrayRecap as $key => $val) {
        //                $strRecap .= "<tr line=" . __LINE__ . ">";
        //                if (sizeof($arrayRecapLabels) > 0) {
        //                    foreach ($arrayRecapLabels as $key => $label) {
        //                        $strRecap .= "<td>";
        //                        $strRecap .= $val[$key];
        //                        $strRecap .= "</td>";
        //                    }
        //                }
        //                $strRecap .= "</tr>";
        //            }
        //            $strRecap .= "</tbody>";
        //
        //            $strRecap .= "</table>";
        //            $strRecap .= "</div>";
        //
        //            $strRecapFooter = "<a class='btn btn-default' href='" . base_url() . "ActivityReport/viewMonthly/" . $jenisTr . "'><span class='glyphicon glyphicon-time'></span> complete $title reports ...</a>";
        //        }
        //        else {
        //            $strRecap = "-the item you specified has no entry-";
        //            $strRecapFooter = "";
        //        }

        //endregion

        $pakai_ini = 0;
        if ($pakai_ini == 1) {

            $scriptBottom .= "

        <script>
    $(document).ready( function () {
        if( $('table#arrayOnProgressHistory') ){

            var startDate = localStorage.getItem('startDate');
            var endDate = localStorage.getItem('endDate');

            var getStartDate = function (){
                return localStorage.getItem('startDate')
            }
            var getEndDate = function (){
                return localStorage.getItem('endDate');
            }
            $.fn.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                if (typeof startdate === 'undefined' || typeof enddate === 'undefined') {
                    return true;
                }
                var coldate = moment(aData[3], 'DD-MM-YYYY');
                console.log('coldate', coldate)
                var valid = true;
                if (coldate.isValid()) {
                    if (enddate.isBefore(coldate)) {
                        console.log('enddate before coldate', enddate)
                        valid = false;
                    }
                    if (coldate.isBefore(startdate)) {
                        console.log('coldate before startdate', startdate)
                        valid = false;
                    }
                }
                else {
                    valid = false;
                }
                return valid;
            });
            arrayOnProgressHistory = $('table#arrayOnProgressHistory').DataTable( {
                $sortColumnDef
                dom: ' l <\"dataTables_filter_custom\"> f r t i p ',
                processing: true,
                serverSide: true,
                ajax: {
                    type: 'GET',
                    url: '" . base_url() . $this->uri->segment(1) . "/viewHistoryApi/$jenisTr/" . $steps[$stepper]['target'] . "?xxx=1&debuger=1',
                    datatype: 'json',
                    data: function (data) {
                        if(undefined!=$('.custom_daterange').val() && ''!=$('.custom_daterange').val()){
                            console.log('range date ada isinya');
                            console.log( $('.custom_daterange').val() );
                            var startDate = $('.custom_daterange').val().split(' to ')[0];
                            var endDate = $('.custom_daterange').val().split(' to ')[1];
                            data.startDate = startDate;
                            data.endDate = endDate;
                            $('#close_btn').removeClass('hidden');
                        }else{
                            console.log('date range kosong, data diabaikan');
                            $('#close_btn').addClass('hidden');
                        }
                    }
                },
                columns: [$labelColumnDt],
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {
                            dt.ajax.reload();
                        }
                    }
                ]
            } );

            $('.dataTables_filter_custom').html(\"<div class='input-group'><input type='text' placeholder='Filter Range Tanggal' class='form-control custom_daterange' style='margin-left: 25px;'><span id='close_btn' class='input-group-btn hidden'><button class='btn btn-danger' type='button' style='margin-left: 30px;'><i class='glyphicon glyphicon-remove'></i></button></span></div>\").css('margin-left','25');

            if( $('#arrayOnProgressHistory').length ) { new $.fn.dataTable.FixedHeader( arrayOnProgressHistory ); }

            top.$('.dataTables_scrollBody').floatingScroll();

            $( \".dataTables_scrollBody\" ).scroll(function() {
                setTimeout( function(){
                    $($.fn.dataTable.tables(true)).DataTable().fixedHeader.adjust();
                }, 400);
            });

            $('.custom_daterange').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 last days': [moment().subtract(6, 'days'), moment()],
                    '30 last days': [moment().subtract(29, 'days'), moment()],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                autoUpdateInput: false,
                opens:'left',
                locale: {
                    cancelLabel: 'Clear',
                    format: 'DD-MMM-YYYY'
                }
            });

           $('.custom_daterange', this).on('apply.daterangepicker', function (ev, picker) {
               ev.preventDefault();
               $(this).val(picker.startDate.format('DD-MMM-YYYY') + ' to ' + picker.endDate.format('DD-MMM-YYYY'));
               arrayOnProgressHistory.draw();
           });

           $('.custom_daterange', this).on('cancel.daterangepicker', function (ev, picker) {
               ev.preventDefault();
               $(this).val('');
               arrayOnProgressHistory.draw();
           });

            $('#close_btn').on('click', function () {
                $('#close_btn').addClass('hidden');
                $('.custom_daterange').val('');
                arrayOnProgressHistory.draw();
            });

        }
    });
</script>\n

        <style>
            .dataTables_wrapper .dataTables_processing {
                position: absolute;
                top: 30%;
                left: 50%;
                width: 30%;
                height: 60px;
                margin-left: -20%;
                margin-top: -50px;
                padding-top: 10px;
                text-align: center;
                font-size: 2em;
                background: lightgreen;
            }
        </style>
        <script>
            $(document).ready( function(){
                var arrayOnprePre;
                var arrayOnProgress;
                var arrayHistory;
                var arrayRecap;

                arrayRecap = $('#arrayRecap').DataTable({
                    pageLength: -1,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],
                    scrollY: 'calc(100%-400)',
                    scrollX: true,
                    scrollCollapse: false,
                    buttons: [
                        {
                            extend: 'print',
                            footer: true
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                });

                arrayHistory = $('#arrayHistory').DataTable({
                    pageLength: -1,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],
                    buttons: [
                        {
                            extend: 'print',
                            footer: true
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                });

                if( $('#arrayHistory').length ) { new $.fn.dataTable.FixedHeader( arrayHistory ); }

                arrayOnProgress = $('#arrayOnProgress').DataTable({
                    pageLength: -1,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],
                    buttons: [
                        {
                            extend: 'print',
                            footer: true
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                });

                if( $('#arrayOnProgress').length ) { new $.fn.dataTable.FixedHeader( arrayOnProgress ); }
                top.$('.dataTables_scrollBody').floatingScroll();

                $( \".dataTables_scrollBody\" ).scroll(function() {
                    console.log('sekeroll');
                    setTimeout( function(){
                        $($.fn.dataTable.tables(true)).DataTable().fixedHeader.adjust();
                    }, 400);
                });

                arrayOnprePre = $('#arrayOnprePre').DataTable({
                    pageLength: -1,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, 'All'] ],
                    scrollY: 'calc(100%-400px)',
                    scrollX: true,
                    scrollCollapse: false,
                    fixedColumns:   {
                        leftColumns: 3,
                        rightColumns: 1
                    },
                    buttons: [
                        {
                            extend: 'print',
                            footer: true
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                });

                $( \".box-body\" ).scroll(function() {
                    console.log('sekeroll');
                    setTimeout( function(){
                        $($.fn.dataTable.tables(true)).DataTable().fixedHeader.adjust();
                    }, 400);
                });

                top.$('.box-body').floatingScroll();

                $(\".DTFC_RightBodyLiner\").css(\"overflow\", \"hidden\");
                $(\".DTFC_RightWrapper\").css(\"right\", \"0px\");
                $(\".DTFC_RightWrapper\").css(\"width\", \"fit-content\");
                $(\".DTFC_RightFootWrapper\").hide();

            });
        </script>";

        }


        $scriptBottom .= isset($scriptBottomCtr) ? $scriptBottomCtr : "";


        $str_group = isset($_GET['gr']) ? "?gr=" . $_GET['gr'] : "";
        $p->addTags(
            array(
                "error_msg" => isset($error) ? $error : "",
                "jenisTr" => (isset($jenisTr) ? $jenisTr : "") . $str_group,
                "trName" => isset($trName) ? $trName : "",
                "alt_display" => isset($altDisplay) ? $altDisplay : "",
                "prop_display" => isset($propDisplay) ? $propDisplay : "",

                "menu_left" => callMenuLeft(),
                "trans_menu" => callTransMenu(),
                "float_menu_atas" => callFloatMenu('atas'),
                "float_menu_bawah" => callFloatMenu(),
                "menu_taskbar" => callMenuTaskbar(),
                "btn_back" => callBackNav(),

                "prePre_title" => isset($prePreTitle) ? $prePreTitle : "",
                "prePre_content" => isset($strOnprePre) ? $strOnprePre : "",
                "prePre_footer" => isset($strOnprePreFooter) ? $strOnprePreFooter : "",

                "onprogress_title" => isset($onprogressTitle) ? $onprogressTitle : "",
                "onprogress_content" => isset($strOnprog) ? $strOnprog : "",
                "onprogress_footer" => isset($strOnprogFooter) ? $strOnprogFooter : "",

                "onprogressView_title" => isset($onprogressViewTitle) ? $onprogressViewTitle : "",
                "onprogressView_subtitle" => isset($onprogressViewSubTitle) ? $onprogressViewSubTitle : "",
                "onprogressView_content" => isset($strOnprogView) ? $strOnprogView : "",
                "onprop_display_view" => isset($onpropDisplayView) ? $onpropDisplayView : "",

                "onprop_payment_view" => isset($arrayOnProgressPaymentView) ? $arrayOnProgressPaymentView : "none",

                "add_link" => isset($addLinkStr) ? $addLinkStr : "",
                "history_title" => isset($historyTitle) ? $historyTitle : "",
                "history_content" => isset($strHist) ? $strHist : "",
                "history_footer" => isset($strHistFooter) ? $strHistFooter : "",
                "recap_title" => isset($recapTitle) ? $recapTitle : "",
                "recap_content" => isset($strRecap) ? $strRecap : "",
                "recap_footer" => isset($strRecapFooter) ? $strRecapFooter : "",
                "profile_name" => isset($this->session->login['nama']) ? $this->session->login['nama'] : "",
                "newTrTarget" => isset($addLink['link']) ? $addLink['link'] . $str_group : "javascript:void(0)",
                "newTrDisp" => isset($addLink['link']) ? "inline-table" : "none",
                "scriptBottom" => isset($scriptBottom) ? $scriptBottom : "",
                "index_active" => "class='active'",
                "time_line" => $time_line,
                "keterangan_notif" => isset($keterangan_notif) ? $keterangan_notif : "",

            )
        );

        $p->render();

        break;
    case "master_project":

        $tabTitle = (isset($produkNama) && trim($produkNama) !== "") ? trim($produkNama) : "Master Project";
        $p = New Layout($tabTitle, "Project Manager", MODUL_TEMPLATE_PATH . "template/data_index.html");
        $targetResult = isset($result) ? "&result=$result" : "&result=result";

        if (isset($errMsg) && strlen($errMsg) > 0) {
            $error = "<div class='alert alert-danger-dot text-center'><span>$errMsg</span></div>";
        }
        else {
            $error = "";
        }

        $strMain = "";
        //region BOM
        if (sizeof($produk_fase) > 0) {
            $bomTitle = "<div>";
            $bomTitle .= "<h3 id='untuk_reload_iframe'><u>Rencana Kerja <b>( $produkNama )</b></u></h3>";
            $bomTitle .= "</div>";
        }
        else {
            $bomTitle = "<div>";
            $bomTitle .= "<h2 class='text-red text-bold'><u class='blink'>Silahkan Buat Rencana Kerja </b></u></h2>";
            $bomTitle .= "</div>";
        }

        //region master timwork
        $timWork = "";
        $timWork .= "<div class='container_timWork'>";
        $timWork .= "<div class='box-header'>";
        $timWork .= "<h3 id=''><b><u>Anggota</u></b></h3>";
        $timWork .= "</div>";
        $timWork .= "<div class='box-body no-padding'>";
        $timWork .= "<form class='form' name='timwork' id='timwork' method='post' target='result' action='$addFaseProdukKomposisiTimLink?mode=timwork'>";
        $timWork .= "<table class='table dataTable compact display table-bordered table-condensed'>";
        $timWork .= "<thead>";
        $timWork .= "<tr>";
        $timWork .= "<td>No</td>";
        foreach ($masterTimworkLabel as $hField => $hLabel) {
            $timWork .= "<td>$hLabel</td>";
        }
        $timWork .= "<td>Action</td>";
        $timWork .= "</tr>";

        $timWork .= "</thead>";
        $timWork .= "<tbody>";
        //bagaian data relasi komposisi

        $i = 0;
        if (count($masterTimwork) > 0) {
            foreach ($masterTimwork as $tID => $masterTimwork) {
                $labelName = $masterTimwork["employee_nama"];
                $modalLink = $previewLink . "MdlTimWorkProject/";
                $timWork .= "<tr>";
                $i++;
                $timWork .= "<td>$i</td>";
                foreach ($masterTimworkLabel as $hField => $hLabel) {
                    $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
                    $val = isset($masterTimwork[$transformKey]) ? $masterTimwork[$transformKey] : "";
                    $timWork .= "<td>" . formatField($hField, $val) . "</td>";
                }
                //tambah logic crud
                $btn = "<div>";
                if (isset($allowedAccess["employee_id"]["update"]) && $allowedAccess["employee_id"]["update"] == true) {
                    $btn .= "<button type='button' title='edit' class='btn btn-xs btn-flat btn-warning' onclick=\"showModal('" . $previewLink . "MdlTimWorkProject/$tID/$hField','edit $labelName')\"><span class='fa fa-fw fa-edit'></span></button>";
                }
                if (isset($allowedAccess["employee_id"]["delete"]) && $allowedAccess["employee_id"]["delete"] == true) {
                    $btn .= "<button cx_tmpnamamaterial='$labelName' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='TimWorkProject' type='button' title='clik untuk menghapus realasi teamwork' class='btn btn-xs btn-flat btn-danger delTimWork'><i class='fa fa-fw fa-eraser'></i></button>";
                }
                $btn .= "</div>";
                $timWork .= "<td class='text-center'>$btn</td>";
                $timWork .= "</tr>";
            }
        }

        //untuk tambah komponen
        //akses list untuk nambaha data
        $timWork .= "<tr>";
        $timWork .= "<td></td>";
        if (isset($allowedAccess["employee_id"]["create"]) && $allowedAccess["employee_id"]["create"] == true) {
            foreach ($masterTimworkLabel as $hField => $hLabel) {
                if (isset($produk_fase_komposisiEditable[$hField])) {
                    if (isset($relEmployee[$hField])) {
                        $strTim = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp_0').load('$selector" . "$produkID?mode=timwork&key=$hField&value='+encodeURI(this.value)+''); \">";
                        $strTim .= "<option value='0'>==PILIH==</option>";
                        $ic = 0;
                        foreach ($relEmployee[$hField] as $datas) {
                            $ic++;
                            $selected = isset($newData["timwork"][$produkID][$hField]) && $newData["timwork"][$produkID][$hField] == $datas['id'] ? "selected" : "";
                            $strTim .= "<option $selected value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
                        }
                        $strTim .= "</select>";
                    }
                    else {
                        $value = isset($newData["timwork"][$produkID][$hField]) ? $newData["timwork"][$produkID][$hField] : "";
                        $strTim = "<input class='form-control form-control-sm' type='text' value='$value' onblur=\"$('#input_temp_0').load('$selector" . "$produkID?mode=timwork&key=$hField&value='+encodeURI(this.value)+'');\">";
                    }
                }
                else {
                    $value = isset($newData["timwork"][$produkID][$hField]) ? $newData["timwork"][$produkID][$hField] : "";
                    $strTim = formatField($hField, $value);
                }
                $timWork .= "<td>";
                $timWork .= $strTim;
                $timWork .= "</td>";
            }
            //logic button sini allowed add
            $btnAddTimwork = "<button onclick=\"document.getElementById('timwork').submit();\" type='button' title='simpan tim kerja' class='btn btn-sm btn-success'><i class='fa fa-plus'></i> tambah</button>";
            $timWork .= "<td class='text-center'>$btnAddTimwork</td>";
            $timWork .= "</tr>";
        }

        $timWork .= "</tbody>";
        $timWork .= "</table>";
        $timWork .= "</form>";
        $timWork .= "</div>";
        $timWork .= "</div>";
        $timWork .= "<div id='input_temp_0'></div>";

        $timWork .= "<script>
            function reload_timework(){
                $('.container_timWork').load('$modulClassLink/showTeamWork/$produkID');
            }
            function init_delTimWork(){
                $('.delTimWork').off();
                $('.delTimWork').on('click', function(){
                    var cx_tmpnamamaterial = $(this).attr('cx_tmpnamamaterial');
                    var cx_deletelink = $(this).attr('cx_deletelink');
                    var cx_tid = $(this).attr('cx_tid');
                    var cx_mdl = $(this).attr('cx_mdl');
                    swal({
                        title: 'Apakah kamu yakin untuk menghapus?',
                        html: \"<span class='text-bold text-red text-uppercase'>\"+cx_tmpnamamaterial+\"</span> akan dihapus, data yg telah di hapus tidak bisa dikembalikan.\",
                        type: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'setuju & lanjutkan!'
                    })
                    .then(function(){
                        swal('proses menghapus<br>mohon tunggu sebentar');
                        swal.enableLoading();
                        $.ajax({
                            url: cx_deletelink + '' + cx_mdl + '/' + cx_tid,
                            success: function(res){
                                arrDatas = JSON.parse(res)
                                if(arrDatas.status){
                                    setTimeout(function(){
                                        swal('SUKSES', 'data berhasil dihapus', 'success');
                                        top.reload_timework();\n
                                        top.reloadSelectpicker();\n
                                    },1000)
                                }
                            }
                        });
                    })
                    .catch(function(reason){
                        //alert(\"The alert was dismissed by the user: \"+reason);
                    });
                })
            }
            init_delTimWork();
        </script>";

        //endregion

        //region masterproject
        $project = "";
        $lock = 0;
        $no_kontrak = 0;
        $nilai_proyek = "";
        $batas_waktu = "";
        $transaksi_no = "";
        $project_start = 0;

        if (count($masterProject) > 0) {
            $project .= "<h3 class='box-title'>Project</h3>";
            $project .= "<div class='box-body no-padding'>";
            $project .= "<table class='table dataTable compact display table-bordered table-condensedx'>";

            $project .= "<thead>";
            $project .= "<tr>";
            $project .= "<th>&nbsp;</th>";
            $project .= "<th>&nbsp;</th>";
            $project .= "</tr>";
            $project .= "</thead>";

//            arrPrint($masterProjectField);

            $project .= "<tbody>";
            foreach ($masterProjectField as $key => $label) {
                if ($key == "closing_status") {
                    // Lewati baris tersendiri karena sudah disatukan ke baris status pengerjaan (project_start)
                    continue;
                }

                $text_color = $key == "harga" ? "text-orange text-bold" : "";

                $valToShow = isset($masterProject[$key]) ? $masterProject[$key] : "";
                if (($key == "nomor_kontrak" || $key == "nomor_job_order") && ($valToShow === "0" || $valToShow === 0 || $valToShow === "")) {
                    $valToShow = "-";
                }

                $colLabel = $label;
                if ($key == "lock") {
                    $colLabel = "status<br>bom";
                }
                if ($key == "project_start") {
                    $colLabel = "status<br>project";
                }

                $project .= "<tr>";
                $project .= "<td key='$key' class='$text_color'>$colLabel </td>";
                if ($key == "nomor_job_order") {
                    $project .= "<td class='text-left $text_color'><span id='disp_val_nomor_job_order'>" . formatField($key, $valToShow) . "</span> <a href='javascript:void(0)' onclick='bukaModalEditJoKontrak()' class='text-primary pull-right' title='Edit No. JO' style='padding-left: 6px;'><i class='fa fa-pencil'></i></a></td>";
                } elseif ($key == "nomor_kontrak") {
                    $project .= "<td class='text-left $text_color'><span id='disp_val_nomor_kontrak'>" . formatField_he_format($key, $valToShow) . "</span> <a href='javascript:void(0)' onclick='bukaModalEditJoKontrak()' class='text-primary pull-right' title='Edit No. Kontrak' style='padding-left: 6px;'><i class='fa fa-pencil'></i></a></td>";
                } elseif ($key == "project_start") {
                    $isClosed = (isset($masterProject['closing_status']) && $masterProject['closing_status'] == 1);
                    $isStarted = ($valToShow == 1);
                    if ($isClosed) {
                        $badgeStatus = "<span class='btn btn-xs btn-success' title='Project telah SELESAI dan ditutup'><i class='fa fa-flag-checkered'></i> SELESAI </span>";
                    } elseif ($isStarted) {
                        $badgeStatus = "<span class='btn btn-xs btn-success' title='Project telah RUNNING, Silahkan menambahkan TUGAS PROYEK.'><i class='fa fa-refresh fa-spin'></i> RUNNING </span>";
                    } else {
                        $badgeStatus = "<span class='btn btn-xs btn-danger' title='Menunggu Mulainya PROJECT, jika Quotation sudah diapprove silahkan untuk menombol Mulai Project.'><i class='fa fa-stop'></i> BELUM DIRUNNING </span>";
                    }
                    $project .= "<td class='text-left $text_color'>" . $badgeStatus . "</td>";
                } else {
                    $project .= "<td class='text-left $text_color'>" . formatField_he_format($key, $valToShow,$jenisTr,MODUL_PATH) . "</td>";
                }
                $project .= "</tr>";

                switch ($key) {
                    case "nomor_job_order":
                        $no_jo = isset($masterProject[$key]) && $masterProject[$key] != '' ? $masterProject[$key] : "";
                        break;
                    case "nomor_kontrak":
                        $no_kontrak = isset($masterProject[$key]) && $masterProject[$key] != '' ? $masterProject[$key] : "";
                        break;
                    case "harga":
                        $nilai_proyek = $masterProject[$key];
                        break;
                    case "end_dtime":
                        $batas_waktu = $masterProject[$key];
                        break;
                    case "lock":
                        $lock = $masterProject[$key];
                        break;
                    case "transaksi_no":
                        $transaksi_no = $masterProject[$key];
                        break;
                    case "project_start":
                        $project_start = $masterProject[$key];
                        break;
                }
            }
            $project .= "</tbody>";
            $project .= "</table>";
            $project .= "</div>";

            $valInitJo = isset($masterProject['nomor_job_order']) && $masterProject['nomor_job_order'] != '0' ? $masterProject['nomor_job_order'] : "";
            $valInitKontrak = isset($masterProject['nomor_kontrak']) && $masterProject['nomor_kontrak'] != '0' ? $masterProject['nomor_kontrak'] : "";
            $currProjId = !empty($produkID) ? $produkID : (isset($masterProject['id']) ? $masterProject['id'] : 0);
            $urlUpdateJoKontrak = MODUL_PATH . "MasterData/ajaxUpdateJoKontrak";

            $project .= "
            <!-- Modal Edit JO & Kontrak -->
            <div class='modal fade' id='modalEditJoKontrak' tabindex='-1' role='dialog' aria-labelledby='modalEditJoKontrakTitle' aria-hidden='true' data-backdrop='static' style='z-index: 10050;'>
                <div class='modal-dialog' style='max-width: 480px;'>
                    <div class='modal-content' style='border-radius: 6px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.3);'>
                        <div class='modal-header' style='background: #3c8dbc; color: #ffffff; padding: 12px 18px;'>
                            <button type='button' class='close' data-dismiss='modal' aria-hidden='true' style='color:#ffffff; opacity:0.85;'>&times;</button>
                            <h4 class='modal-title' id='modalEditJoKontrakTitle' style='font-size: 15px; font-weight: bold; margin: 0;'>
                                <i class='fa fa-pencil-square-o'></i> Edit Nomor JO & Nomor Kontrak
                            </h4>
                        </div>
                        <div class='modal-body' style='padding: 20px; background: #fbfbfb;'>
                            <div class='form-group' style='margin-bottom: 15px;'>
                                <label style='font-size: 13px; font-weight: 600; color: #333;'>Nomor JO (Job Order):</label>
                                <input type='text' id='input_edit_nomor_job_order' class='form-control' placeholder='Contoh: JO-9087776' style='border-radius: 4px; border: 1px solid #ccc; font-weight: bold;'>
                                <small class='text-muted' style='font-size: 11px;'>Kosongkan atau ketik '-' jika belum ada nomor JO.</small>
                            </div>
                            <div class='form-group' style='margin-bottom: 12px;'>
                                <label style='font-size: 13px; font-weight: 600; color: #333;'>Nomor Kontrak / PO:</label>
                                <input type='text' id='input_edit_nomor_kontrak' class='form-control' placeholder='Contoh: PO-8987776' style='border-radius: 4px; border: 1px solid #ccc; font-weight: bold;'>
                                <small class='text-muted' style='font-size: 11px;'>Kosongkan atau ketik '-' jika belum ada nomor kontrak.</small>
                            </div>
                            <div class='form-group' style='margin-bottom: 10px;'>
                                <label style='font-size: 13px; font-weight: 600; color: #333;'>Alasan / Catatan Perubahan <small class='text-muted'>(Opsional)</small>:</label>
                                <textarea id='input_edit_alasan_perubahan' class='form-control' rows='2' placeholder='Contoh: Dokumen PO/SPK baru terbit / Koreksi nomor...' style='border-radius: 4px; border: 1px solid #ccc; resize: vertical;'></textarea>
                                <small class='text-muted' style='font-size: 11px;'>Alasan ini akan dicatat secara permanen di Audit Trail (Tab Riwayat Kontrak & RAB).</small>
                            </div>
                            <div id='editJoKontrakAlert' style='display: none; margin-top: 15px;' class='alert alert-danger' style='padding: 8px 12px; font-size: 12px;'></div>
                        </div>
                        <div class='modal-footer' style='padding: 12px 18px; background: #edf2f7; text-align: right;'>
                            <button type='button' class='btn btn-default btn-sm' data-dismiss='modal'><i class='fa fa-times'></i> Batal</button>
                            <button type='button' class='btn btn-primary btn-sm' id='btnSaveEditJoKontrak' onclick='simpanEditJoKontrak()'><i class='fa fa-save'></i> Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                var curJoVal = " . json_encode($valInitJo) . ";
                var curKontrakVal = " . json_encode($valInitKontrak) . ";
                var projectMasterId = " . json_encode($currProjId) . ";
                var endpointUpdateJo = " . json_encode($urlUpdateJoKontrak) . ";

                function bukaModalEditJoKontrak() {
                    $('#editJoKontrakAlert').hide().text('');
                    $('#input_edit_nomor_job_order').val(curJoVal);
                    $('#input_edit_nomor_kontrak').val(curKontrakVal);
                    $('#input_edit_alasan_perubahan').val('');
                    $('#modalEditJoKontrak').modal('show');
                }

                function simpanEditJoKontrak() {
                    var newJo = $.trim($('#input_edit_nomor_job_order').val());
                    var newKontrak = $.trim($('#input_edit_nomor_kontrak').val());
                    var alasanPerubahan = $.trim($('#input_edit_alasan_perubahan').val());
                    var btnSave = $('#btnSaveEditJoKontrak');

                    btnSave.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i> Menyimpan...');
                    $('#editJoKontrakAlert').hide();

                    $.ajax({
                        url: endpointUpdateJo,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            project_id: projectMasterId,
                            nomor_job_order: newJo,
                            nomor_kontrak: newKontrak,
                            alasan: alasanPerubahan
                        },
                        success: function(res) {
                            btnSave.prop('disabled', false).html('<i class=\"fa fa-save\"></i> Simpan Perubahan');
                            if (res && res.status == 1) {
                                curJoVal = newJo;
                                curKontrakVal = newKontrak;
                                $('#disp_val_nomor_job_order').text((newJo != '' && newJo != '0') ? newJo : '-');
                                $('#disp_val_nomor_kontrak').text((newKontrak != '' && newKontrak != '0') ? newKontrak : '-');
                                $('#modalEditJoKontrak').modal('hide');

                                // Refresh tab Riwayat Kontrak & RAB jika ada di halaman
                                if (typeof loadKontrakHistoryStatus === 'function') {
                                    loadKontrakHistoryStatus();
                                } else if ($('#project_kontrak_history_container').length > 0) {
                                    $('#project_kontrak_history_container').load(MODUL_PATH + 'MasterData/showKontrakHistoryStatus/' + projectMasterId);
                                }

                                if (typeof swal !== 'undefined') {
                                    swal('Berhasil', 'Nomor JO dan Nomor Kontrak berhasil diperbarui dan dicatat di Audit Trail.', 'success');
                                } else {
                                    alert('Nomor JO dan Nomor Kontrak berhasil diperbarui dan dicatat di Audit Trail.');
                                }
                            } else {
                                var msg = res && res.message ? res.message : 'Gagal memperbarui data.';
                                $('#editJoKontrakAlert').show().text(msg);
                            }
                        },
                        error: function(xhr, status, err) {
                            btnSave.prop('disabled', false).html('<i class=\"fa fa-save\"></i> Simpan Perubahan');
                            $('#editJoKontrakAlert').show().text('Terjadi kesalahan jaringan atau server (' + status + ').');
                        }
                    });
                }
            </script>
            ";
        }
        //endregion

        //region komposisi produk fase

        $allowedHppRoles = array(
            'o_project_mgr',
            'o_project_spv',
            'c_owner',
            'c_holding',
            'c_special',
            'c_audit',
            'o_audit',
            'root',
            'superman',
        );
        $userMemberships = isset($_SESSION['login']['membership']) && is_array($_SESSION['login']['membership'])
            ? $_SESSION['login']['membership']
            : array();
        $canViewHppRealisasi = false;
        foreach ($allowedHppRoles as $role) {
            if (in_array($role, $userMemberships)) {
                $canViewHppRealisasi = true;
                break;
            }
        }

        $checkboxHpp = "";
        if ($canViewHppRealisasi) {
            $checkboxHpp = "<span style='margin-left: 20px; font-size: 13px; vertical-align: middle; display: inline-block;'>
                <label style='font-weight: normal; cursor: pointer; color: #333; margin-bottom: 0; background: #eef2f7; border: 1px solid #ccd6e0; padding: 2px 10px; border-radius: 4px;'>
                    <input type='checkbox' id='toggleShowHppRealisasi' style='vertical-align: middle; margin-top: -2px; margin-right: 5px; cursor: pointer;'>
                    <span style='font-weight: bold;'><i class='fa fa-eye'></i> Tampilkan Kolom HPP</span>
                </label>
            </span>";
        }

        $produkKomposisiFase = "<style>
            .hide-hpp-realisasi [hField='hpp'],
            .hide-hpp-realisasi [hField='sub_hpp'],
            .hide-hpp-realisasi .col-hpp-realisasi {
                display: none !important;
            }
        </style>";
        $produkKomposisiFase .= "<div class='box box-success'>";
        $produkKomposisiFase .= "<div class='box-header'>";
        $produkKomposisiFase .= "<h3 id=''>
        <b><u>SETTING RAB</u></b>
        <div><sup><small><r><i>adalah total bahan baku dan biaya dari total keseluruhan projek</i></r></small></sup>$checkboxHpp</div>
        </h3>";
        $produkKomposisiFase .= "</div>";

        $produkKomposisiFase .= "<div id='containerPreLoadProdukFase' class='box-body no-padding hide-hpp-realisasi'>";

//        //JIKA ADA UPDATE COPAS DARI SINI SAJA ( CONTROLLER->MasterData->showKomposisiProdukFase() )
//        if (sizeof($produk_fases) > 0) {
//
//            $produkKomposisiFase .= "<div class='nav-tabs-custom'>";
//            $produkKomposisiFase .= "<div class='tab-content no-padding'>";
//            $produkKomposisiFase .= "<ul class='nav nav-tabs' id='custom-content-below-tab' role='tablist'>";
//
//            $faseNoA = 0;
//            foreach ($produk_fase as $fase_urut => $faseData) {
//                if(isset($produk_komposisi_fase[$fase_urut])){
//
//                }
//                else{
//                    $faseNoA++;
//                }
//            }
//            $faseNo = 0;
//            $faseNoErr = 0;
//            foreach ($produk_fase as $fase_urut => $faseData) {
//                if (isset($produk_komposisi_fase[$fase_urut])) {
//                    $actLink = $faseNo == 0 && $faseNoA == 0 ? "active" : "";
//                    $produkKomposisiFase .= "
//                    <li class='nav-item $actLink'>
//                        <a class='nav-link' id='cc-tab-fase_$fase_urut' data-toggle='pill' href='#tab-fase_$fase_urut' role='tab' aria-controls='cc-tab-fase_$fase_urut' aria-selected='false'><span style='font-size: 12px;' class=''> <b>" . strtoupper(($faseData['nama'])) . "</b></span></a>
//                    </li>";
//                    $faseNo++;
//                }
//                else {
//                    $actLink = $faseNoErr == 0 ? "active" : "";
//                    $produkKomposisiFase .= "
//                    <li class='nav-item $actLink'>
//                        <a class='nav-link' id='cc-tab-fase_$fase_urut' data-toggle='pill' href='#tab-fase_$fase_urut' role='tab' aria-controls='cc-tab-fase_$fase_urut' aria-selected='false'><span style='font-size: 14px;' class='text-red'><i class='fa fa-warning blink text-yellow'></i> <b>" . strtoupper(($faseData['nama'])) . "</b></span></a>
//                    </li>";
//                    $faseNoErr++;
//                }
//            }
//
//            $produkKomposisiFase .= "</ul>";
//            $faseNoB = 0;
//            foreach ($produk_fase as $fase_urut => $faseData) {
//                if (isset($produk_komposisi_fase[$fase_urut])) {
//                    $actLink = $faseNoB == 0 && $faseNoA == 0 ? "active in" : "";
//                    $produkKomposisiFase .= "<div class='uu lv1 tab-pane fade $actLink' id='tab-fase_$fase_urut'>";
//                    $produkKomposisiFase .= "
//                    <div class='box box-solid'>
//                        <div class='box-header with-border'>
//                            <h3 class='box-title'><i class='fa fa-hand-o-right'></i>&nbsp;&nbsp;".($faseData['nama'])."&nbsp;&nbsp;&nbsp;<small><i class='fa fa-clock-o text-muted'></i>&nbsp;".date("Y-m-d H:i")."</small></h3>
//                        </div>
//                        <div class='box-body'>";
//
//                    $total_anggaran_unit = 0;
//                    $jual_per_unit = 0;
//
//                    foreach ($produk_komposisi_fase_header as $hFieldKey => $hLabelData) {
//                        //MdlProjectKomposisiWorkorder
//                        switch ($hFieldKey) {
//                            case "produk":
//                                $idForm = "bahan_baku" . "$fase_urut";
//                                $produkKomposisiFase .= "<div class='$idForm'>";
//                                $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiLink?mode=komposisi_fase&fase_id=$fase_urut'>";
//                                $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiFase .= "<thead>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiFase .= "<td>$hLabel</td>";
//                                }
//                                if($lock==0){
//                                    $produkKomposisiFase .= "<td>action</td>";
//                                }
//                                $produkKomposisiFase .= "</tr>";
//                                $produkKomposisiFase .= "</thead>";
//                                $produkKomposisiFase .= "<tbody>";
//                                $produkKomposisiFase .= "<tr>";
//                                $i = 0;
//                                $arrSelected = array();
//                                if (isset($produk_komposisi_fase[$fase_urut]["produk"]) && sizeof($produk_komposisi_fase[$fase_urut]["produk"])) {
//                                    foreach ($produk_komposisi_fase[$fase_urut]["produk"] as $DataRelsupplies) {
//                                        $tID=$DataRelsupplies["id"];
//                                        $arrSelected[] = $DataRelsupplies["produk_dasar_id"];
//                                        $produkKomposisiFase .= "<tr>";
//                                        $i++;
//                                        $produkKomposisiFase .= "<td>$i</td>";
//                                        $tmpNamaMaterial = '';
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                            $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//
////                                            cekMerah($hField);
////                                            cekMerah($transformKey);
////                                            cekMerah($val);
////                                            cekMerah("===============");
//
//                                            $origVal=$val;
//                                            $val = $hField=="produk_dasar_id" && strlen($val)>=34 ? substr($val,0,34)."..." : $val;
//
//                                            $produkKomposisiFase .= "<td hField='$hField' title='$origVal'>" . formatField($hField, $val) . "</td>";
//                                            $tmpNamaMaterial .= $hField=="produk_dasar_id" ? $val : "";
//                                            $total_anggaran_unit += $hField=="subtotal" ? $val*1 : 0;
//                                        }
//                                        $btn = "<div>";
//                                        if(isset($allowedAccess["produk_dasar_id"]["update"]) && $allowedAccess["produk_dasar_id"]["update"] == true) {
//                                            $btn .= "<button type='button' title='edit' class='btn btn-xs btn-flat btn-warning' onclick=\"showModal('" . $previewLink . "MdlProjectKomposisiWorkorder/$tID/$hField','edit $labelName')\"><span class='fa fa-fw fa-edit'></span></button>";
//                                        }
//                                        if(isset($allowedAccess["produk_dasar_id"]["delete"]) && $allowedAccess["produk_dasar_id"]["delete"] == true) {
//                                            $btn .= "<button cx_jenis='produk' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProjectKomposisiWorkorder' type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                        }
//                                        $btn .="</div>";
//                                        if($lock==0){
//                                            $produkKomposisiFase .= "<td >$btn</td>";
//                                        }
//                                        $produkKomposisiFase .= "</tr>";
//                                    }
//                                }
//
//                                if($lock==0){
//                                    //untuk tambah komponen
//                                    $produkKomposisiFase .= "</tr>";
//                                    $produkKomposisiFase .= "<tr>";
//                                    $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                    foreach ($hLabelData as $hField => $hLabel) {
//                                        if (isset($produk_fase_komposisiEditable[$hField])) {
//                                            if (isset($relSupplies[$hField])) {
//                                                $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                                $strItem .= "<option value='0'>==PILIH==</option>";
//                                                $queryParams = "";
//                                                foreach ($relSupplies[$hField] as $datas) {
//                                                    $selected = isset($newData["komposisi_fase"][$fase_urut][$produkID][$hField]) && $newData["komposisi_fase"][$fase_urut][$produkID][$hField] == $datas['id'] ? "selected" : "";
//                                                    $disable = in_array($datas['id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                    $iconCheck = in_array($datas['id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                    $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                                }
//                                                $strItem .= "</select>";
//                                            }
//                                            else {
//                                                $value = isset($newData["komposisi_fase"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase"][$fase_urut][$produkID][$hField] : "";
//                                                $strItem = "<input id='komposisi_fase_$fase_urut$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                            }
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_fase"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase"][$fase_urut][$produkID][$hField] : "";
//                                            $defValue = is_numeric($value) ? number_format($value) : $value;
//                                            $strItem = "<span id='komposisi_fase_$fase_urut$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
//                                        }
//                                        $produkKomposisiFase .= "<td>";
//                                        $produkKomposisiFase .= $strItem;
//                                        $produkKomposisiFase .= "</td>";
//                                    }
//
//
//                                    $btnRemoveFasekomposisi = "<button id='komposisi_fase_$fase_urut$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                    $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                    $produkKomposisiFase .= "</tr>";
//                                }
//
//
//                                $produkKomposisiFase .= "</tbody>";
//                                $produkKomposisiFase .= "</table>";
//                                $produkKomposisiFase .= "</form>";
//                                $produkKomposisiFase .= "</div>";
//                                break;
//                            case "biaya":
//                                $idForm = "biaya" . "$fase_urut";
//                                $produkKomposisiFase .= "<div class='$idForm'>";
//                                $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addProdukKomposisiBiayaLink?mode=komposisi_fase_biaya&fase_id=$fase_urut'>";
//                                $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiFase .= "<thead>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiFase .= "<td>$hLabel</td>";
//                                }
//
//                                if($lock==0){
//                                    $produkKomposisiFase .= "<td>Action</td>";
//                                }
//
//                                $produkKomposisiFase .= "</tr>";
//
//                                $produkKomposisiFase .= "</thead>";
//                                $produkKomposisiFase .= "<tbody>";
//                                //bagaian data relasi komposisi
//                                // arrprint($relBiaya);
//                                $i = 0;
//                                $arrSelected = array();
//                                if (isset($produk_komposisi_fase[$fase_urut]["biaya"]) && sizeof($produk_komposisi_fase[$fase_urut]["biaya"]) > 0) {
//                                    foreach ($produk_komposisi_fase[$fase_urut]["biaya"] as $DataRelsuppliesBiaya) {
//                                        $tID=$DataRelsuppliesBiaya["id"];
//                                        $arrSelected[] = $DataRelsuppliesBiaya["produk_dasar_id"];
//                                        $produkKomposisiFase .= "<tr>";
//                                        $i++;
//                                        $tmpNamaMaterial = '';
//                                        $produkKomposisiFase .= "<td>$i</td>";
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                            $val = isset($DataRelsuppliesBiaya[$transformKey]) ? $DataRelsuppliesBiaya[$transformKey] : "";
//                                            $produkKomposisiFase .= "<td hField='$hField'>" . formatField($hField, $val) . "</td>";
//                                            $tmpNamaMaterial .= $hField=="produk_dasar_id" ? $val : "";
//                                            $total_anggaran_unit += $hField=="subtotal" ? $val*1 : 0;
//                                        }
//
//                                        $btn = "<div>";
//                                        if(isset($allowedAccess["produk_dasar_id"]["delete"]) && $allowedAccess["produk_dasar_id"]["delete"] == true) {
//                                            $btn .= "<button cx_jenis='biaya' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProjectKomposisiWorkorder' type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                        }
//                                        $btn .="</div>";
//
//                                        if($lock==0){
//                                            $produkKomposisiFase .= "<td >$btn</td>";
//                                        }
//
//                                        $produkKomposisiFase .= "</tr>";
//                                    }
//                                }
//
//                                if($lock==0){
//                                    //untuk tambah komponen
//                                    $produkKomposisiFase .= "<tr>";
//                                    $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                    foreach ($hLabelData as $hField => $hLabel) {
//                                        if (isset($produk_fase_komposisiEditable[$hField])) {
//                                            if (isset($relBiaya[$hField])) {
//                                                $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_biaya&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                                $strItem .= "<option value='0'>==PILIH==</option>";
//                                                foreach ($relBiaya[$hField] as $datas) {
//                                                    $selected = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) && $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] == $datas['id'] ? "selected" : "";
////                                                    $disable = in_array($datas['id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                    $disable = "";
//                                                    $iconCheck = in_array($datas['id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                    $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                                }
//                                                $strItem .= "</select>";
//                                            }
//                                            else {
//                                                $value = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] : "";
//                                                if(is_numeric($value)){
//                                                    $strItem = "<input id='komposisi_fase_biaya_$fase_urut$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_biaya&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                                }
//                                                else{
//                                                    $strItem = "<input id='komposisi_fase_biaya_$fase_urut$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_biaya&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); });\">";
//                                                }
//                                            }
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] : "";
//                                            $defValue = is_numeric($value) ? number_format($value) : $value;
//                                            $strItem = "<span id='komposisi_fase_biaya_$fase_urut$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
////                                        $strItem = "<input id='inhide_komposisi_fase_biaya_$fase_urut$hField' class='hidden' value='$defValue'>";
//                                        }
//                                        $produkKomposisiFase .= "<td>";
//                                        $produkKomposisiFase .= $strItem;
//                                        $produkKomposisiFase .= "</td>";
//                                    }
//                                    $btnRemoveFasekomposisi = "<button id='komposisi_fase_biaya_$fase_urut$produkID' idform='$idForm' disabled type='button' title='simpan komposisi biaya' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                    $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                    $produkKomposisiFase .= "</tr>";
//                                }
//
//                                $produkKomposisiFase .= "</tbody>";
//                                $produkKomposisiFase .= "</table>";
//                                $produkKomposisiFase .= "</form>";
//                                $produkKomposisiFase .= "</div>";
//                                break;
//                            case "timwork":
//                                $idForm = "timwork" . "$fase_urut";
//                                $produkKomposisiFase .= "<div class='$idForm'>";
//                                $produkKomposisiFase .= "<div class='panel'>";
//                                $produkKomposisiFase .= "<div class='panel-header'><h4>Tim Kerja</h4></div>";
//                                $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiTimLink?mode=komposisi_fase_timwork&fase_id=$fase_urut$targetResult'>";
//                                $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiFase .= "<thead>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiFase .= "<td>$hLabel</td>";
//                                }
//                                $produkKomposisiFase .= "<td>Action</td>";
//                                $produkKomposisiFase .= "</tr>";
//
//                                $produkKomposisiFase .= "</thead>";
//                                $produkKomposisiFase .= "<tbody>";
//                                //bagaian data relasi komposisi
//                                // arrprint($relBiaya);
//                                $i = 0;
//                                if (isset($produk_komposisi_fase[$fase_urut]["timwork"]) && sizeof($produk_komposisi_fase[$fase_urut]["timwork"]) > 0) {
//                                    foreach ($produk_komposisi_fase[$fase_urut]["timwork"] as $DataRelsuppliesBiaya) {
//                                        //                                        arrPrint($DataRelsuppliesBiaya);
//                                        $produkKomposisiFase .= "<tr>";
//                                        $i++;
//                                        $produkKomposisiFase .= "<td>$i</td>";
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                            $val = isset($DataRelsuppliesBiaya[$transformKey]) ? $DataRelsuppliesBiaya[$transformKey] : "";
//                                            $produkKomposisiFase .= "<td>" . formatField($hField, $val) . "</td>";
//                                        }
//                                        $produkKomposisiFase .= "</tr>";
//                                    }
//                                }
//
//                                //untuk tambah komponen
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td></td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    if (isset($produk_fase_komposisiEditable[$hField])) {
//                                        if (isset($relEmployee[$hField])) {
//                                            $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID?mode=komposisi_fase_timwork&key=$hField&value='+encodeURI(this.value)+'$targetResult'); \">";
//                                            $strItem .= "<option value='0'>==PILIH==</option>";
//                                            foreach ($relEmployee[$hField] as $datas) {
//                                                $selected = isset($newData["komposisi_fase_timwork"][$produkID][$hField]) && $newData["komposisi_fase_timwork"][$produkID][$hField] == $datas['id'] ? "selected" : "";
//                                                $strItem .= "<option $selected value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                            }
//                                            $strItem .= "</select>";
//
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_fase_timwork"][$produkID][$hField]) ? $newData["komposisi_fase_timwork"][$produkID][$hField] : "";
//                                            $strItem = "<input class='form-control form-control-sm' type='text' value='$value' onblur=\"$('#input_temp').load('$selector" . "$produkID?mode=komposisi_fase_timwork&key=$hField&value='+encodeURI(this.value)+'$targetResult');\">";
//                                        }
//                                    }
//                                    else {
//                                        $value = isset($newData["komposisi_fase_timwork"][$produkID][$hField]) ? $newData["komposisi_fase_timwork"][$produkID][$hField] : "";
//                                        $strItem = formatField($hField, $value);
//                                        // $strItem ="";
//                                    }
//                                    $produkKomposisiFase .= "<td>";
//                                    $produkKomposisiFase .= $strItem;
//                                    $produkKomposisiFase .= "</td>";
//                                }
//                                $btnRemoveFasekomposisi = "<button onclick=\"document.getElementById('$idForm').submit();\" type='button' title='simpan tim kerja' class='btn btn-sm btn-success'><i class='fa fa-plus'></i>  tambah</button>";
//                                $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                $produkKomposisiFase .= "</tr>";
//
//
//                                $produkKomposisiFase .= "</tbody>";
//                                $produkKomposisiFase .= "</table>";
//                                $produkKomposisiFase .= "</form>";
//                                $produkKomposisiFase .= "</div>";//clas panel
//                                $produkKomposisiFase .= "</div>";
//                                break;
//                            case "target":
//                                // $idForm = "target" . $fase_urut;
//                                // $produkKomposisiFase .= "<div class=''>";
//                                // $produkKomposisiFase .= "<table class='table table-bordered'>";
//                                // $produkKomposisiFase .= "<thead>";
//                                // $produkKomposisiFase .= "<tr>";
//                                // foreach ($hLabelData as $hField => $hLabel) {
//                                //     $produkKomposisiFase .= "<td>$hLabel</td>";
//                                // }
//                                //
//                                // $produkKomposisiFase .= "</tr>";
//                                // $produkKomposisiFase .= "</thead>";
//                                // $produkKomposisiFase .= "<tbody>";
//                                //
//                                // $produkKomposisiFase .= "<tr>";
//                                // foreach ($hLabelData as $hField => $hLabel) {
//                                //     if (isset($produk_fase_komposisiEditable[$hField])) {
//                                //         if (isset($relTarget[$hField])) {
//                                //             $strItem = "<select data-style=\"btn-primary\" class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$addFaseHasilProduksi" . "/$produkID?mode=komposisi_target&key=$hField&&fase_id=$fase_urut&value='+encodeURI(this.value)+'$targetResult'); \">";
//                                //             $strItem .= "<option value='0'> ---silahkan pilih--</option>";
//                                //             foreach ($relTarget[$hField] as $datas) {
//                                //                 $selected = isset($currentTargetWip[$produkID][$fase_urut][$hField]) && $currentTargetWip[$produkID][$fase_urut][$hField] == $datas['id'] ? "selected" : "";
//                                //                 // $strItem .= "<option $selected value='" . $datas['id'] . "'>" . $datas['nama'] ."||". $currentTargetWip[$produkID][$fase_urut][$hField]."</option>";
//                                //                 $strItem .= "<option $selected value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                //             }
//                                //             $strItem .= "</select>";
//                                //         }
//                                //
//                                //     }
//                                //
//                                //     $produkKomposisiFase .= "<td>";
//                                //     $produkKomposisiFase .= $strItem;
//                                //     $produkKomposisiFase .= "</td>";
//                                // }
//                                // $produkKomposisiFase .= "</tr>";
//                                // $produkKomposisiFase .= "</tbody>";
//                                // $produkKomposisiFase .= "</table>";
//                                // $produkKomposisiFase .= "</div>";
//                                break;
//                            case "jual":
////                                arrPrint($produk_komposisi_fase);
//
//                                if (isset($produk_komposisi_fase[$fase_urut]["jual"]) && sizeof($produk_komposisi_fase[$fase_urut]["jual"])) {
//                                    foreach ($produk_komposisi_fase[$fase_urut]["jual"] as $DataRelsupplies) {
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                            $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//                                            $jual_per_unit += $hField=="subtotal" ? $val*1 : 0;
//                                        }
//                                    }
//                                }
//
//                                break;
//                            case "room":
//                                $produkKomposisiRoom = "";
//                                $idForm = "rooms" . "$fase_urut";
//                                $produkKomposisiRoom .= "<div class='$idForm'>";
//                                $produkKomposisiRoom .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiLink?mode=komposisi_fase_room&fase_id=$fase_urut'>";
//                                $produkKomposisiRoom .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiRoom .= "<thead>";
//                                $produkKomposisiRoom .= "<tr>";
//                                $produkKomposisiRoom .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiRoom .= "<td>$hLabel</td>";
//                                }
//
//                                if($lock==0){
//                                    $produkKomposisiRoom .= "<td>action</td>";
//                                }
//
//                                $produkKomposisiRoom .= "</tr>";
//                                $produkKomposisiRoom .= "</thead>";
//                                $produkKomposisiRoom .= "<tbody>";
//                                $produkKomposisiRoom .= "<tr>";
//                                $i = 0;
//                                $arrSelectedRooms = array();
//                                if (isset($produk_komposisi_fase[$fase_urut]["room"]) && sizeof($produk_komposisi_fase[$fase_urut]["room"])) {
//                                    foreach ($produk_komposisi_fase[$fase_urut]["room"] as $DataRelsupplies) {
//                                        $tID=$DataRelsupplies["id"];
//                                        $arrSelectedRooms[] = $DataRelsupplies["produk_dasar_id"];
//                                        $produkKomposisiRoom .= "<tr>";
//                                        $i++;
//                                        $produkKomposisiRoom .= "<td>$i</td>";
//                                        $tmpNamaMaterial = '';
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                            $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//                                            $produkKomposisiRoom .= "<td hField='$hField'>" . formatField($hField, $val) . "</td>";
//                                            $tmpNamaMaterial .= $hField == "room_nama" ? $val : "";
//                                        }
//                                        $btn = "<div>";
//                                        if(isset($allowedAccess["produk_dasar_id"]["update"]) && $allowedAccess["produk_dasar_id"]["update"] == true) {
//                                            $btn .= "<button type='button' title='edit' class='btn btn-xs btn-flat btn-warning' onclick=\"showModal('" . $previewLink . "MdlProjectKomposisiWorkorderRoom/$tID','edit $labelName')\"><span class='fa fa-fw fa-edit'></span></button>";
//                                        }
//                                        if(isset($allowedAccess["produk_dasar_id"]["delete"]) && $allowedAccess["produk_dasar_id"]["delete"] == true) {
//                                            $btn .= "<button cx_jenis='room' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProjectKomposisiWorkorderRoom' type='button' title='click untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                        }
//                                        $btn .="</div>";
//
//                                        if($lock==0){
//                                            $produkKomposisiRoom .= "<td>$btn</td>";
//                                        }
//
//                                        $produkKomposisiRoom .= "</tr>";
//                                    }
//                                }
//                                $produkKomposisiRoom .= "</tr>";
//
//
//                                if($lock==0){
//                                    //untuk tambah komponen
//                                    $produkKomposisiRoom .= "<tr>";
//                                    $produkKomposisiRoom .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                    foreach ($hLabelData as $hField => $hLabel) {
//                                        if (isset($produk_fase_komposisiEditable[$hField])) {
//                                            $value = isset($newData["komposisi_fase_room"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_room"][$fase_urut][$produkID][$hField] : "";
//                                            $strItem = "<input id='komposisi_fase_room_$fase_urut$hField' class='form-control' type='text' value='$value' onfocus=\"this.select()\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_room&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)) });\">";
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_fase_room"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_room"][$fase_urut][$produkID][$hField] : "";
//                                            $defValue = is_numeric($value) ? number_format($value) : $value;
//                                            $strItem = "<span id='komposisi_fase_room_$fase_urut$hField' class='form-control'>$defValue</span>";
//                                        }
//                                        $produkKomposisiRoom .= "<td>";
//                                        $produkKomposisiRoom .= $strItem;
//                                        $produkKomposisiRoom .= "</td>";
//                                    }
//
//                                    $btnRemoveFasekomposisiRoom = "<button id='komposisi_fase_room_$fase_urut$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                    $produkKomposisiRoom .= "<td class='text-center'>$btnRemoveFasekomposisiRoom</td>";
//                                    $produkKomposisiRoom .= "</tr>";
//                                }
//
//
//                                $produkKomposisiRoom .= "</tbody>";
//                                $produkKomposisiRoom .= "</table>";
//                                $produkKomposisiRoom .= "</form>";
//                                $produkKomposisiRoom .= "</div>";
//
//                                $produkKomposisiRoom .= "<div style='margin-top: 40px;' class='box box-success'>";
//                                $produkKomposisiRoom .= "<div class='box-header text-bold fa-2x text-blue'>
//                                DETAIL MATERIAL PER RUANGAN
//                                <div class='meta'></div>
//                                </div>";
//                                $produkKomposisiRoom .= "<div class='box-body no-padding'>";
//
//                                $produkKomposisiRoom .= "<div class='nav-tabs-custom'>";
//                                $produkKomposisiRoom .= "<div class='tab-content no-padding'>";
//
//                                if (isset($produk_komposisi_fase[$fase_urut]["room"]) && sizeof($produk_komposisi_fase[$fase_urut]["room"])) {
//                                    $produkKomposisiRoom .= "<ul class='nav nav-tabs' id='custom-content-below-tab' role='tablist'>";
//                                    $numTab=0;
//                                    foreach($produk_komposisi_fase[$fase_urut]["room"] as $DataRelsupplies){
//                                        $numTab++;
//                                        $val = $DataRelsupplies['room_nama'];
//                                        $selID = $DataRelsupplies['room_id'];
//                                        $selActive = $numTab== 1 ? "active" : "";
//                                        $produkKomposisiRoom .= "<li class='nav-item $selActive'>";
//                                        $produkKomposisiRoom .= "<a class='nav-link' id='cc-tab-room_$selID' data-toggle='pill' href='#tab-room_$selID' role='tab' aria-controls='cc-tab-room_$selID' aria-selected='false'>";
//                                        $produkKomposisiRoom .= "<span style='font-size: 12px;' class='text-bold text-uppercase'> <b>$val</b></span>";
//                                        $produkKomposisiRoom .= "</a>";
//                                        $produkKomposisiRoom .= "</li>";
//                                    }
//                                    $produkKomposisiRoom .= "</ul>";
//
//                                    $numTabCont=0;
//                                    foreach ($produk_komposisi_fase[$fase_urut]["room"] as $DataRelsupplies) {
//                                        $numTabCont++;
//                                        $selID = $DataRelsupplies['room_id'];
//                                        $selNama = $DataRelsupplies['room_nama'];
//                                        $selActive = $numTabCont== 1 ? "active in" : "";
//                                        $produkKomposisiRoom .= "<div class='tab-pane fade $selActive' id='tab-room_$selID'>";
//                                        foreach ($produk_komposisi_fase_header as $hFieldKey => $hLabelData) {
//                                            switch ($hFieldKey) {
//                                                case "produk_room":
//                                                    $produkKomposisiRoom .= "<div class='text-uppercase text-bold'>------------------------------- BAHAN BAKU $selNama -------------------------------</div>";
//                                                    $idForm = "rooms" . "$fase_urut$selID";
//                                                    $produkKomposisiRoom .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiLink?mode=produk_komposisi_fase_room&fase_id=$fase_urut&selID=$selID'>";
//                                                    $produkKomposisiRoom .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                                    $produkKomposisiRoom .= "<thead>";
//                                                    $produkKomposisiRoom .= "<tr>";
//                                                    $produkKomposisiRoom .= "<td>No</td>";
//
//                                                    foreach ($hLabelData as $hField => $hLabel) {
//                                                        $produkKomposisiRoom .= "<td>$hLabel</td>";
//                                                    }
//
//                                                    if($lock==0){
//                                                        $produkKomposisiRoom .= "<td>action</td>";
//                                                    }
//
//                                                    $produkKomposisiRoom .= "</tr>";
//                                                    $produkKomposisiRoom .= "</thead>";
//                                                    $produkKomposisiRoom .= "<tbody>";
//                                                    $produkKomposisiRoom .= "<tr>";
//                                                    $i = 0;
//
//                                                    $arrSelected = array();
//                                                    if (isset($produk_komposisi_fase[$fase_urut]["produk_room"][$selID]) && sizeof($produk_komposisi_fase[$fase_urut]["produk_room"][$selID])) {
//                                                        foreach ($produk_komposisi_fase[$fase_urut]["produk_room"][$selID] as $DataRelsupplies) {
//                                                            $tID=$DataRelsupplies["id"];
//                                                            $arrSelected[] = $DataRelsupplies["produk_dasar_id"];
//                                                            $produkKomposisiRoom .= "<tr>";
//                                                            $i++;
//                                                            $produkKomposisiRoom .= "<td>$i</td>";
//                                                            $tmpNamaMaterial = '';
//                                                            foreach ($hLabelData as $hField => $hLabel) {
//                                                                $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                                                $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//                                                                $produkKomposisiRoom .= "<td hField='$hField'>" . formatField($hField, $val) . "</td>";
//                                                                $tmpNamaMaterial .= $hField == "produk_dasar_id" ? $val : "";
//                                                            }
//
//                                                            $btn = "";
//                                                            if(isset($allowedAccess["produk_dasar_id"]["delete"]) && $allowedAccess["produk_dasar_id"]["delete"] == true) {
//                                                                $btn .= "<div>";
//                                                                $btn .= "<button cx_jenis='room' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProjectKomposisiWorkorderRoomProduk' type='button' title='click untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                                                $btn .="</div>";
//                                                            }
//
//                                                            if($lock==0){
//                                                                $produkKomposisiRoom .= "<td>$btn</td>";
//                                                            }
//
//                                                            $produkKomposisiRoom .= "</tr>";
//                                                        }
//                                                    }
//                                                    $produkKomposisiRoom .= "</tr>";
//
//                                                    if($lock==0){
//                                                        //untuk tambah komponen
//                                                        $produkKomposisiRoom .= "<tr>";
//                                                        $produkKomposisiRoom .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                                        foreach ($hLabelData as $hField => $hLabel) {
//                                                            if (isset($produk_fase_komposisiRoomEditable[$hField])) {
//                                                                if (isset($relSupplies[$hField])) {
//                                                                    $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=produk_komposisi_fase_room&key=$hField&value='+encodeURI(this.value)+'&selID=$selID', function(res){ eval(atob(res)) }); \">";
//                                                                    $strItem .= "<option value='0'>==PILIH==</option>";
//                                                                    $queryParams = "";
//                                                                    foreach ($produk_komposisi_fase[$fase_urut]["produk"] as $datas) {
//                                                                        $selected = isset($newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField]) && $newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField] == $datas['produk_dasar_id'] ? "selected" : "";
//                                                                        $disable = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                                        $iconCheck = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                                        $strItem .= "<option $iconCheck $selected $disable value='" . $datas['produk_dasar_id'] . "'>" . $datas['produk_dasar_nama'] . "</option>";
//                                                                    }
//                                                                    $strItem .= "</select>";
//                                                                }
//                                                                else {
//                                                                    $value = isset($newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField]) ? $newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField] : "";
//                                                                    $strItem = "<input size='4' disabled id='produk_komposisi_fase_room_$fase_urut$selID$hField' class='form-control form-control-sm text-red text-bolds text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=produk_komposisi_fase_room&key=$hField&value='+encodeURI(removeCommas(this.value))+'&selID=$selID', function(res){ eval(atob(res)) });\">";
//                                                                }
//                                                            }
//                                                            else {
//                                                                $value = isset($newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField]) ? $newData["produk_komposisi_fase_room"][$fase_urut][$selID][$produkID][$hField] : "";
//                                                                $defValue = is_numeric($value) ? number_format($value) : $value;
//                                                                $strItem = "<span id='produk_komposisi_fase_room_$fase_urut$selID$hField' class='form-control form-control-sm no-border text-red text-bolds text-right'>$defValue</span>";
//                                                            }
//                                                            $produkKomposisiRoom .= "<td>";
//                                                            $produkKomposisiRoom .= $strItem;
//                                                            $produkKomposisiRoom .= "</td>";
//                                                        }
//
//
//                                                        $btnRemoveFasekomposisi = "<button id='produk_komposisi_fase_room_$fase_urut$selID$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                                        $produkKomposisiRoom .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                                        $produkKomposisiRoom .= "</tr>";
//                                                    }
//
//                                                    $produkKomposisiRoom .= "</tbody>";
//                                                    $produkKomposisiRoom .= "</table>";
//                                                    $produkKomposisiRoom .= "</form>";
//
//                                                    break;
//                                                case "biaya_room":
//
//                                                    $produkKomposisiRoom .= "<div class='text-uppercase text-bold'>------------------------------- BIAYA/JASA $selNama -------------------------------</div>";
//
//                                                    $idForm = "biayarooms" . "$fase_urut$selID";
//                                                    $produkKomposisiRoom .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiLink?mode=produk_komposisi_fase_room_biaya&fase_id=$fase_urut&selID=$selID'>";
//                                                    $produkKomposisiRoom .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                                    $produkKomposisiRoom .= "<thead>";
//                                                    $produkKomposisiRoom .= "<tr>";
//                                                    $produkKomposisiRoom .= "<td>No</td>";
//                                                    foreach ($hLabelData as $hField => $hLabel) {
//                                                        $produkKomposisiRoom .= "<td>$hLabel</td>";
//                                                    }
//
//                                                    if($lock==0){
//                                                        $produkKomposisiRoom .= "<td>action</td>";
//                                                    }
//
//                                                    $produkKomposisiRoom .= "</tr>";
//                                                    $produkKomposisiRoom .= "</thead>";
//                                                    $produkKomposisiRoom .= "<tbody>";
//                                                    $produkKomposisiRoom .= "<tr>";
//                                                    $i = 0;
//
//                                                    $arrSelected = array();
//                                                    if (isset($produk_komposisi_fase[$fase_urut]["biaya_room"][$selID]) && sizeof($produk_komposisi_fase[$fase_urut]["biaya_room"][$selID])) {
//                                                        foreach ($produk_komposisi_fase[$fase_urut]["biaya_room"][$selID] as $DataRelsupplies) {
//                                                            $tID=$DataRelsupplies["id"];
//                                                            $arrSelected[] = $DataRelsupplies["produk_dasar_id"];
//                                                            $produkKomposisiRoom .= "<tr>";
//                                                            $i++;
//                                                            $produkKomposisiRoom .= "<td>$i</td>";
//                                                            $tmpNamaMaterial = '';
//                                                            foreach ($hLabelData as $hField => $hLabel) {
//                                                                $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                                                $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//                                                                $produkKomposisiRoom .= "<td hField='$hField'>" . formatField($hField, $val) . "</td>";
//                                                                $tmpNamaMaterial .= $hField == "produk_dasar_id" ? $val : "";
//                                                            }
//                                                            $btn = "<div>";
//                                                            if(isset($allowedAccess["produk_dasar_id"]["delete"]) && $allowedAccess["produk_dasar_id"]["delete"] == true) {
//                                                                $btn .= "<button cx_jenis='room' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProjectKomposisiWorkorderRoomProdukBiaya' type='button' title='click untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                                            }
//                                                            $btn .="</div>";
//
//                                                            if($lock==0){
//                                                                $produkKomposisiRoom .= "<td>$btn</td>";
//                                                            }
//
//                                                            $produkKomposisiRoom .= "</tr>";
//                                                        }
//                                                    }
//                                                    $produkKomposisiRoom .= "</tr>";
//
//                                                    if($lock==0){
//                                                        arrPrint($produk_komposisi_fase[$fase_urut]["biaya"]);
//                                                        //untuk tambah komponen
//                                                        $produkKomposisiRoom .= "<tr>";
//                                                        $produkKomposisiRoom .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                                        foreach ($hLabelData as $hField => $hLabel) {
//                                                            if (isset($produk_fase_komposisiRoomEditable[$hField])) {
//                                                                if (isset($relSupplies[$hField])) {
////                                                                    $hField = $hField=="produk_dasar_id" ? "id" : $hField;
//                                                                    $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=produk_komposisi_fase_room_biaya&key=$hField&value='+encodeURI(this.value)+'&selID=$selID', function(res){ eval(atob(res)) }); \">";
//                                                                    $strItem .= "<option value='0'>==PILIH==</option>";
//                                                                    $queryParams = "";
//                                                                    foreach ($produk_komposisi_fase[$fase_urut]["biaya"] as $datas) {
//                                                                        $selected = isset($newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField]) && $newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField] == $datas['produk_dasar_id'] ? "selected" : "";
//                                                                        $disable = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                                        $iconCheck = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                                        $ketRoom = $datas['keterangan'] != '' ? "(" . $datas['keterangan'] . ")" : "";
//                                                                        $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] ."-". $datas['produk_dasar_id'] . "'>" . $datas['produk_dasar_nama'] ." $ketRoom</option>";
//                                                                    }
//                                                                    $strItem .= "</select>";
//                                                                }
//                                                                else {
//                                                                    $value = isset($newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField]) ? $newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField] : "";
//                                                                    $strItem = "<input size='4' disabled id='produk_komposisi_fase_room_biaya_$fase_urut$selID$hField' class='form-control form-control-sm text-red text-bolds text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=produk_komposisi_fase_room_biaya&key=$hField&value='+encodeURI(removeCommas(this.value))+'&selID=$selID', function(res){ eval(atob(res)) });\">";
//                                                                }
//                                                            }
//                                                            else {
//                                                                $value = isset($newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField]) ? $newData["produk_komposisi_fase_room_biaya"][$fase_urut][$selID][$produkID][$hField] : "";
//                                                                $defValue = is_numeric($value) ? number_format($value) : $value;
//                                                                $strItem = "<span id='produk_komposisi_fase_room_biaya_$fase_urut$selID$hField' class='form-control form-control-sm no-border text-red text-bolds text-right'>$defValue</span>";
//                                                            }
//                                                            $produkKomposisiRoom .= "<td>";
//                                                            $produkKomposisiRoom .= $strItem;
//                                                            $produkKomposisiRoom .= "</td>";
//                                                        }
//
//                                                        $btnRemoveFasekomposisi = "<button id='produk_komposisi_fase_room_biaya_$fase_urut$selID$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                                        $produkKomposisiRoom .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                                        $produkKomposisiRoom .= "</tr>";
//                                                    }
//
//
//                                                    $produkKomposisiRoom .= "</tbody>";
//                                                    $produkKomposisiRoom .= "</table>";
//                                                    $produkKomposisiRoom .= "</form>";
//
//                                                    break;
//                                            }
//                                        }
//
//                                        $produkKomposisiRoom .= "<div class='text-uppercase text-bold'>------------------------------- $selNama -------------------------------</div>";
//
//                                        $produkKomposisiRoom .= "</div>";
//                                    }
//                                }
//                                $produkKomposisiRoom .= "</div>";
//                                $produkKomposisiRoom .= "</div>";
//
//                                $produkKomposisiRoom .= "</div>";
//                                $produkKomposisiRoom .= "</div>";
//
//                                $room_data = $faseData['nama'];
//
//                                $collapsed_box = count($arrSelectedRooms)>0 ? "" : "collapsed-box";
//                                $collapsed_checked = count($arrSelectedRooms)>0 ? "checked" : "";
//
//                                $disableCheckSparatedRoom = $lock==0 ? "" : "disabled";
//
//                                $produkKomposisiFase .= "
//                                    <div class='box box-warning box-solid $collapsed_box'>
//                                        <div class='box-header with-border'>
//                                            <h3 class='box-title text-bold text-black'>
//                                                SEPARATED ROOM &nbsp;
//                                                <input $disableCheckSparatedRoom $collapsed_checked pids='$fase_urut' id='spr_$fase_urut' type='checkbox' class='minimal spr_check'>
//                                                <div class='small text-bold text-white'>Silahkan di centang jika anda memerlukan pendistribusian bahan baku yang lebih rinci di setiap ruangan</div>
//                                            </h3>
//                                            <button id='btnColls_$fase_urut' type='button' class='btn btn-box-tool hidden' data-widget='collapse'><i class='fa fa-minus'></i></button>
//                                        </div>
//                                        <div class='box-body'>
//                                            <div class='text-bold fa-2x'>$room_data</div>
//                                            <div class='text-bolds text-red'><i>Tambahkan nama ruangan untuk mendistribusikan bahan baku lebih rinci di setiap ruangan</i></div>
//                                            $produkKomposisiRoom
//                                        </div>
//                                    </div>
//                                ";
//
//                                break;
//                        }
//                    }
//
//                    $produkKomposisiFase .= "<div style='padding: 5px;' class='bg-info'>";
//                    $produkKomposisiFase .= "<div style='margin-top: 15px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-bold'>Total BOM @Unit :</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format($total_anggaran_unit)."</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    $defValue = $jual_per_unit*1>0 && $jual_per_unit*1 > $total_anggaran_unit ? $jual_per_unit*1 : $total_anggaran_unit;
//
//                    $produkKomposisiFase .= "<div style='margin-top: 5px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-bold'>Jual Project @Unit (Rp): </span></div>";
//                    $disableInputSetJual = $lock==0 ? "" : "disabled";
//
//                    $produkKomposisiFase .= "<div class='col-md-3 no-padding text-right' id=''>
//                    <input $disableInputSetJual id='hbom_$produkID$fase_urut' class='hidden' value='$total_anggaran_unit'>
//                    <input $disableInputSetJual id='saved_jual_$produkID$fase_urut' class='hidden' value='$jual_per_unit'>
//                    <input $disableInputSetJual pidfase='$produkID$fase_urut' id='hjp_$produkID$fase_urut' class='form-control form-control-sm text-bold text-right int_jual_project' style='font-size: 16px;padding-right: 14px !important;' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(removeCommas(this.value))\" value='".number_format($defValue)." '>
//                    </div>";
//
//                    $btn_danger  = "hidden";
//                    $btn_success = "hidden";
//                    $btn_save    = "hidden";
//
//                    if($jual_per_unit*1>0 && $jual_per_unit*1 > $total_anggaran_unit){
//                        $btn_danger = "hidden";
//                        $btn_success = "";
//                        $btn_save = "hidden";
//                    }
//
//                    if($jual_per_unit*1==0 && $jual_per_unit*1 < $total_anggaran_unit){
//                        $btn_danger = "hidden";
//                        $btn_success = "hidden";
//                        $btn_save = "";
//                    }
//
//                    if($jual_per_unit*1>0 && $jual_per_unit*1 < $total_anggaran_unit){
//                        $btn_danger = "";
//                        $btn_success = "hidden";
//                        $btn_save = "hidden";
//                    }
//
//                    if($jual_per_unit*1>0 && $jual_per_unit*1 == $total_anggaran_unit){
//                        $btn_danger = "hidden";
//                        $btn_success = "";
//                        $btn_save = "hidden";
//                    }
//
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id='md1_btnSave_$produkID$fase_urut'>
//                        <span class='btn btn-sm btn-danger $btn_danger' disabled><i class='fa fa-times'></i></span>
//                        <span class='btn btn-sm btn-success $btn_success' disabled><i class='fa fa-check-circle'></i></span>
//                        <span class='btn btn-sm btn-warning save_jual_project $btn_save' cx_url='$saveJualProject?mode=save_jual_project&fase_id=$fase_urut' produk_id='$produkID' fase_id='$fase_urut'><i class='fa fa-save'></i></span>
//                    </div>";
//
//                    $produkKomposisiFase .= "</div>";
//
//                    $diskon_value = 0;
//
////                    $produkKomposisiFase .= "<div style='margin-top: 5px;' class='row'>";
////                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-bold'>Diskon (Rp): </span></div>";
////                    $produkKomposisiFase .= "<div class='col-md-3 no-padding text-right' id=''>
////                        <input $disableInputSetJual produk_id='$produkID' fase_id='$fase_urut' pidfase='$produkID$fase_urut' id='diskon_$produkID$fase_urut' cx_url='$saveJualProject?mode=save_diskon&fase_id=$fase_urut' class='form-control form-control-sm text-bold text-right int_diskon' style='font-size: 16px;padding-right: 14px !important;' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(removeCommas(this.value))\" value='".number_format($diskon_value)."'>
////                    </div>";
//
////                    $produkKomposisiFase .= "</div>";
//
////                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
////                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''>&nbsp;</div>";
////                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'>______________________</div>";
////                    $produkKomposisiFase .= "<div class='col-md-1 no-padding text-left' id=''><span style='font-size: 12px;' class='text-bold'><i class='fa fa-plus'></i></span></div>";
////                    $produkKomposisiFase .= "</div>";
//
////                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
////                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-boldx'>SUBTOTAL:</span></div>";
////                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format($defValue-$diskon_value)."</span></div>";
////                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
////                    $produkKomposisiFase .= "</div>";
//
//                    //==========================================================
//
//                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-boldx'>PPN(".$this->session->login['ppnFactor']."%):</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format((($defValue-$diskon_value)*$this->session->login['ppnFactor'])/100)."</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''>&nbsp;</div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'>______________________</div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 no-padding text-left' id=''><span style='font-size: 12px;' class='text-bold'><i class='fa fa-plus'></i></span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-boldx'>GRAND TOTAL: </span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format($defValue-$diskon_value+((($defValue-$diskon_value)*$this->session->login['ppnFactor'])/100))."</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    //==========================================================
//
//                    $produkKomposisiFase .= "<div style='margin-top: 25px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-boldx'>Total R/L @Unit :</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format($jual_per_unit-$total_anggaran_unit)."</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    $produkKomposisiFase .= "<div style='margin-top: 1px;' class='row'>";
//                    $produkKomposisiFase .= "<div class='col-md-8 text-right' id=''><span style='font-size: 16px;' class='text-boldx'>Total R/L :</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-3 no-paddingx text-right' id=''><span style='font-size: 16px;' class='text-bold'><span class='pull-left'>Rp. </span>".number_format(($jual_per_unit-$total_anggaran_unit)*$faseData['qty'])."</span></div>";
//                    $produkKomposisiFase .= "<div class='col-md-1 text-right' id=''><span style='font-size: 12px;' class='ext-bold'>&nbsp;</span></div>";
//                    $produkKomposisiFase .= "</div>";
//
//                    $produkKomposisiFase .= "</div>";
//
//                    //BOM / KOMPOSISI PAKET
//                    $produkKomposisiFase .= "<div style='margin-top: 30px;margin-bottom: 6px;' class='fa-2x text-muted'> <i class='fa fa-hand-o-right'></i>&nbsp;&nbsp;PAKET INSTALASI&nbsp;&nbsp;&nbsp;
//                                                <div class='box-tools pull-right'>
//                                                    <button type='button' class='btn btn-sm btn-success'><i class='fa fa-plus'></i> TAMBAH PAKET</button>
//                                                </div>
//                                             </div>";
//                    $produkKomposisiFase .= "<div class='box-group bom-container' id='accordion'>";
//
//                    if (sizeof($produk_paket) > 0) {
//                        foreach ($produk_paket as $pp_key => $faseData) {
//
//                            $produkKomposisiFase .= "<div class='box box-solid box-info'>";
//                            $produkKomposisiFase .= "<div class='box-header with-border'>";
//                            $produkKomposisiFase .= "<h3 class='box-title'><i class='fa fa-tags'></i>&nbsp;&nbsp;".($faseData->nama)."&nbsp;&nbsp;&nbsp;</h3>";
//                            $produkKomposisiFase .= "<div class='box-tools'>";
//
//                            if(isset($allowedAccess["produk_paket"]["delete"]) && $allowedAccess["produk_paket"]["delete"] == true) {
//                                $produkKomposisiFase .= "<button cx_jenis='produk' cx_tmpnamamaterial='".($faseData->nama)."' cx_deletelink='$deleteLink' cx_tid='$pp_key' cx_mdl='ProjectProdukPaket' type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-danger btn-box-tool delWorkOrder'><i class='fa fa-eraser'></i> Hapus Paket</button>";
//                                $produkKomposisiFase .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//                            }
//
//                            $produkKomposisiFase .= "<button type='button' class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>";
//                            $produkKomposisiFase .= "</div>";
//                            $produkKomposisiFase .= "</div>";
//
//                            $produkKomposisiFase .= "<div class='box-body'>";
//
//                            $total_anggaran_unit = 0;
//                            $jual_per_unit = 0;
//
//                            foreach ($produk_komposisi_fase_header as $hFieldKey => $hLabelData) {
//                                switch ($hFieldKey) {
//                                    case "produk":
//                                        $idForm = "komposisi_bom" . "$pp_key";
//                                        $produkKomposisiFase .= "<div class='$idForm'>";
//                                        $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiBomLink?mode=komposisi_bom&paket_id=$pp_key&fase_id=$fase_urut'>";
//                                        $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                        $produkKomposisiFase .= "<thead>";
//                                        $produkKomposisiFase .= "<tr>";
//                                        $produkKomposisiFase .= "<td>No</td>";
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $produkKomposisiFase .= "<td>$hLabel</td>";
//                                        }
//                                        $produkKomposisiFase .= "<td>action</td>";
//                                        $produkKomposisiFase .= "</tr>";
//                                        $produkKomposisiFase .= "</thead>";
//                                        $produkKomposisiFase .= "<tbody>";
//                                        $produkKomposisiFase .= "<tr>";
//                                        $i = 0;
//                                        $arrSelected = array();
//                                        if (isset($produk_komposisi_bom[$pp_key]["produk"]) && sizeof($produk_komposisi_bom[$pp_key]["produk"])) {
//                                            foreach ($produk_komposisi_bom[$pp_key]["produk"] as $DataRelsupplies) {
//                                                $tID=$DataRelsupplies["id"];
//                                                $arrSelected[] = $DataRelsupplies["produk_dasar_id"];
//                                                $produkKomposisiFase .= "<tr>";
//                                                $i++;
//                                                $produkKomposisiFase .= "<td>$i</td>";
//                                                $tmpNamaMaterial = '';
//                                                foreach ($hLabelData as $hField => $hLabel) {
//                                                    $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                                    $val = isset($DataRelsupplies[$transformKey]) ? $DataRelsupplies[$transformKey] : "";
//
//                                                    $origVal=$val;
//                                                    $val = $hField=="produk_dasar_id" && strlen($val)>=34 ? substr($val,0,34)."..." : $val;
//
//                                                    $produkKomposisiFase .= "<td hField='$hField' title='$origVal'>" . formatField($hField, $val) . "</td>";
//                                                    $tmpNamaMaterial .= $hField=="produk_dasar_id" ? $val : "";
//                                                    $total_anggaran_unit += $hField=="subtotal" ? $val*1 : 0;
//                                                }
//                                                $btn = "<div>";
////                                                if(isset($allowedAccess["produk_paket"]["update"]) && $allowedAccess["produk_paket"]["update"] == true) {
////                                                    $btn .= "<button type='button' title='edit' class='btn btn-xs btn-flat btn-warning' onclick=\"showModal('" . $previewLink . "MdlProjectKomposisiWorkorder/$tID/$hField','edit $labelName')\"><span class='fa fa-fw fa-edit'></span></button>";
////                                                }
//                                                if(isset($allowedAccess["produk_paket"]["delete"]) && $allowedAccess["produk_paket"]["delete"] == true) {
//                                                    $btn .= "<button cx_jenis='produk' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProdukKomposisiPaket' type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                                }
//                                                $btn .="</div>";
//
//                                                $produkKomposisiFase .= "<td >$btn</td>";
//
//                                                $produkKomposisiFase .= "</tr>";
//                                            }
//                                        }
//
//                                        //untuk tambah komponen
//                                        $produkKomposisiFase .= "</tr>";
//                                        $produkKomposisiFase .= "<tr>";
//                                        $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
////                                        cekMerah("=============================");
////                                        arrPrintWebs($hLabelData);
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            if ($hField=="produk_dasar_id") {
//                                                $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_bom&paket_id=$pp_key&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                                $strItem .= "<option value='0'>==PILIH==</option>";
//                                                $queryParams = "";
//                                                foreach ($produk_komposisi_fase[$fase_urut]["produk"] as $datas) {
//                                                    $selected = isset($newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField]) && $newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField] == $datas['produk_dasar_id'] ? "selected" : "";
//                                                    $disable = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                    $iconCheck = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                    $strItem .= "<option $iconCheck $selected $disable value='" . $datas['produk_dasar_id'] . "'>" . $datas['produk_dasar_nama'] . "</option>";
//                                                }
//                                                $strItem .= "</select>";
//                                            }
//                                            else if ($hField=="jml") {
//                                                $value = isset($newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField]) ? $newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField] : "";
//                                                $strItem = "<input id='komposisi_bom_$fase_urut$pp_key$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_bom&paket_id=$pp_key&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                            }
//                                            else {
//                                                $value = isset($newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField]) ? $newData["komposisi_bom"][$fase_urut][$pp_key][$produkID][$hField] : "";
//                                                $defValue = is_numeric($value) ? number_format($value) : $value;
//                                                $strItem = "<span id='komposisi_bom_$fase_urut$pp_key$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
//                                            }
//                                            $produkKomposisiFase .= "<td>";
//                                            $produkKomposisiFase .= $strItem;
//                                            $produkKomposisiFase .= "</td>";
//                                        }
//
//                                        $btnRemoveFasekomposisi = "<button id='komposisi_bom_$fase_urut$pp_key$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                        $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                        $produkKomposisiFase .= "</tr>";
//
//                                        $produkKomposisiFase .= "</tbody>";
//                                        $produkKomposisiFase .= "</table>";
//                                        $produkKomposisiFase .= "</form>";
//                                        $produkKomposisiFase .= "</div>";
//                                        break;
//                                    case "biaya":
//                                        $idForm = "biaya" . "$pp_key";
//                                        $produkKomposisiFase .= "<div class='$idForm'>";
//                                        $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiBomBiayaLink?mode=komposisi_bom_biaya&paket_id=$pp_key&fase_id=$fase_urut'>";
//                                        $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                        $produkKomposisiFase .= "<thead>";
//                                        $produkKomposisiFase .= "<tr>";
//                                        $produkKomposisiFase .= "<td>No</td>";
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            $produkKomposisiFase .= "<td>$hLabel</td>";
//                                        }
//
//                                        $produkKomposisiFase .= "<td>Action</td>";
//                                        $produkKomposisiFase .= "</tr>";
//
//                                        $produkKomposisiFase .= "</thead>";
//                                        $produkKomposisiFase .= "<tbody>";
//                                        //bagaian data relasi komposisi
//                                        $i = 0;
//                                        $arrSelected = array();
//                                        if (isset($produk_komposisi_bom[$pp_key]["biaya"]) && sizeof($produk_komposisi_bom[$pp_key]["biaya"]) > 0) {
//                                            foreach ($produk_komposisi_bom[$pp_key]["biaya"] as $DataRelsuppliesBiaya) {
//                                                $tID=$DataRelsuppliesBiaya["id"];
//                                                $arrSelected[] = $DataRelsuppliesBiaya["produk_dasar_id"];
//                                                $produkKomposisiFase .= "<tr>";
//                                                $i++;
//                                                $tmpNamaMaterial = '';
//                                                $produkKomposisiFase .= "<td>$i</td>";
//                                                foreach ($hLabelData as $hField => $hLabel) {
//                                                    $transformKey = isset($relSuppliesHeader[$hField]) ? $relSuppliesHeader[$hField] : $hField;
//                                                    $val = isset($DataRelsuppliesBiaya[$transformKey]) ? $DataRelsuppliesBiaya[$transformKey] : "";
//
//                                                    $origVal=$val;
//                                                    $val = $hField=="produk_dasar_id" && strlen($val)>=34 ? substr($val,0,34)."..." : $val;
//
//                                                    $produkKomposisiFase .= "<td hField='$hField'>" . formatField($hField, $val) . "</td>";
//                                                    $tmpNamaMaterial .= $hField=="produk_dasar_id" ? $val : "";
//                                                    $total_anggaran_unit += $hField=="subtotal" ? $val*1 : 0;
//                                                }
//
//                                                $btn = "<div>";
//                                                if(isset($allowedAccess["produk_paket"]["delete"]) && $allowedAccess["produk_paket"]["delete"] == true) {
//                                                    $btn .= "<button cx_jenis='biaya' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$tID' cx_mdl='ProdukKomposisiPaket' type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
//                                                }
//                                                $btn .="</div>";
//
//                                                $produkKomposisiFase .= "<td >$btn</td>";
//
//                                                $produkKomposisiFase .= "</tr>";
//                                            }
//                                        }
//
//                                        //untuk tambah komponen
//                                        $produkKomposisiFase .= "<tr>";
//                                        $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                        foreach ($hLabelData as $hField => $hLabel) {
//                                            if ($hField=="produk_dasar_id") {
//                                                $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_bom_biaya&paket_id=$pp_key&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                                $strItem .= "<option value='0'>==PILIH==</option>";
//                                                foreach ($produk_komposisi_fase[$fase_urut]["biaya"] as $datas) {
//                                                    $selected = isset($newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField]) && $newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField] == $datas['id'] ? "selected" : "";
//                                                    $disable = "";
//                                                    $iconCheck = in_array($datas['produk_dasar_id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                    $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] . "'>" . $datas['produk_dasar_nama'] . "</option>";
//                                                }
//                                                $strItem .= "</select>";
//                                            }
//                                            else if($hField=="jml"){
//                                                $value = isset($newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField]) ? $newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField] : "";
//                                                $strItem = "<input id='komposisi_bom_biaya_$fase_urut$pp_key$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_bom_biaya&paket_id=$pp_key&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                            }
//                                            else {
//                                                $value = isset($newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField]) ? $newData["komposisi_bom_biaya"][$fase_urut][$pp_key][$produkID][$hField] : "";
//                                                $defValue = is_numeric($value) ? number_format($value) : $value;
//                                                $strItem = "<span id='komposisi_bom_biaya_$fase_urut$pp_key$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
//                                            }
//
//                                            $produkKomposisiFase .= "<td>";
//                                            $produkKomposisiFase .= $strItem;
//                                            $produkKomposisiFase .= "</td>";
//                                        }
//                                        $btnRemoveFasekomposisi = "<button id='komposisi_bom_biaya_$fase_urut$pp_key$produkID' idform='$idForm' disabled type='button' title='simpan komposisi biaya' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                        $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                        $produkKomposisiFase .= "</tr>";
//
//                                        $produkKomposisiFase .= "</tbody>";
//                                        $produkKomposisiFase .= "</table>";
//                                        $produkKomposisiFase .= "</form>";
//                                        $produkKomposisiFase .= "</div>";
//                                        break;
//                                }
//                            }
//
//                            $produkKomposisiFase .= "</div>";
//                            $produkKomposisiFase .= "</div>";
//                            $produkKomposisiFase .= "</div>";
//
//
//                        }
//                    }
//                    else{
//                        $produkKomposisiFase .= "<div class='box box-danger box-solid box-header text-center text-bold'> SILAHKAN BUAT BOM DULU </div>";
//
//                        $produkKomposisiFase .= "
//                            <script>
//                                setTimeout(function(){
//                                    top.$('#produk_fase_qty').val(1).trigger('blur');
//                                    top.$('#produk_fase_nama').val('".$masterProject['nama']."').trigger('blur');
//                                    top.$('#produk_fase_lokasi').val('-').trigger('blur');
//                                    setTimeout(function(){
//                                        $('#addProdukFase').click();
//                                    },500);
//                                },1000);
//                            </script>";
//                    }
//
//                    $produkKomposisiFase .= "</div>";
//                    $produkKomposisiFase .= "</div>";
////                    $produkKomposisiFase .= "</div>";
////                    $produkKomposisiFase .= "</div>";
//                    //SAMPE SINI SAJA REPLACE BOM
//
//                }
//                else {
//                    $produkKomposisiFase .= "<div class='bg-ble lv12 tab-pane fade active in' id='tab-fase_$fase_urut'>";
//                    $produkKomposisiFase .= "<div class='blink text-bold text-danger'><h4 class=''>Material " . ($faseData['nama']) . " belum diseting, silahkan klik tombol tambah</h3></div>";
//                    foreach ($produk_komposisi_fase_header as $hFieldKey => $hLabelData) {
//                        switch ($hFieldKey) {
//                            case "produk":
//                                $idForm = "bahan_baku" . "$fase_urut";
//                                $produkKomposisiFase .= "<div class='$idForm'>";
//                                $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addFaseProdukKomposisiLink?mode=komposisi_fase&fase_id=$fase_urut'>";
//                                $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiFase .= "<thead>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiFase .= "<td>$hLabel</td>";
//                                }
//                                $produkKomposisiFase .= "<td>action</td>";
//                                $produkKomposisiFase .= "</tr>";
//                                $produkKomposisiFase .= "</thead>";
//                                $produkKomposisiFase .= "<tbody>";
//                                $produkKomposisiFase .= "<tr>";
//
//                                $arrSelected = array();
//
//                                $i = 0;
//                                //untuk tambah komponen
//                                $produkKomposisiFase .= "</tr>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    if (isset($produk_fase_komposisiEditable[$hField])) {
//                                        if (isset($relSupplies[$hField])) {
//                                            $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                            $strItem .= "<option value='0'>==PILIH==</option>";
//                                            $queryParams = "";
//                                            foreach ($relSupplies[$hField] as $datas) {
//                                                $selected = isset($newData["komposisi_bom"][$fase_urut][$produkID][$hField]) && $newData["komposisi_bom"][$fase_urut][$produkID][$hField] == $datas['id'] ? "selected" : "";
//                                                $disable = in_array($datas['id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                $iconCheck = in_array($datas['id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                            }
//                                            $strItem .= "</select>";
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_bom"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_bom"][$fase_urut][$produkID][$hField] : "";
//                                            $strItem = "<input id='komposisi_fase_$fase_urut$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                        }
//                                    }
//                                    else {
//                                        $value = isset($newData["komposisi_bom"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_bom"][$fase_urut][$produkID][$hField] : "";
//                                        $defValue = is_numeric($value) ? number_format($value) : $value;
//                                        $strItem = "<span id='komposisi_fase_$fase_urut$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
//                                    }
//                                    $produkKomposisiFase .= "<td>";
//                                    $produkKomposisiFase .= $strItem;
//                                    $produkKomposisiFase .= "</td>";
//                                }
//
//
//                                $btnRemoveFasekomposisi = "<button id='komposisi_fase_$fase_urut$produkID' idform='$idForm' disabled type='button' title='simpan komposisi baru' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                $produkKomposisiFase .= "</tr>";
//
//                                $produkKomposisiFase .= "</tbody>";
//                                $produkKomposisiFase .= "</table>";
//                                $produkKomposisiFase .= "</form>";
//                                $produkKomposisiFase .= "</div>";
//                                break;
//                            case "biaya":
//                                $idForm = "biaya" . "$fase_urut";
//                                $produkKomposisiFase .= "<div class='$idForm'>";
//                                $produkKomposisiFase .= "<form class='form' id='$idForm' method='post' name='$idForm' target='result' action='$addProdukKomposisiBiayaLink?mode=komposisi_fase_biaya&fase_id=$fase_urut'>";
//                                $produkKomposisiFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
//                                $produkKomposisiFase .= "<thead>";
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td>No</td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    $produkKomposisiFase .= "<td>$hLabel</td>";
//                                }
//                                $produkKomposisiFase .= "<td>Action</td>";
//                                $produkKomposisiFase .= "</tr>";
//
//                                $produkKomposisiFase .= "</thead>";
//                                $produkKomposisiFase .= "<tbody>";
//                                //bagaian data relasi komposisi
//                                // arrprint($relBiaya);
//                                $i = 0;
//                                //bagian add baru
//                                //untuk tambah komponen
//                                $produkKomposisiFase .= "<tr>";
//                                $produkKomposisiFase .= "<td><span style='font-size: 10px;' class='form-control form-control-sm no-border text-olive' title='pilih/isi '><i class='fa fa-plus-circle fa-2x'></i></span></td>";
//                                foreach ($hLabelData as $hField => $hLabel) {
//                                    if (isset($produk_fase_komposisiEditable[$hField])) {
//                                        if (isset($relBiaya[$hField])) {
//                                            $strItem = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_biaya&key=$hField&value='+encodeURI(this.value)+'', function(res){ eval(atob(res)); }); \">";
//                                            $strItem .= "<option value='0'>==PILIH==</option>";
//                                            foreach ($relBiaya[$hField] as $datas) {
//                                                $selected = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) && $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] == $datas['id'] ? "selected" : "";
//                                                $disable = in_array($datas['id'], $arrSelected, TRUE) ? "disabled" : "";
//                                                $iconCheck = in_array($datas['id'], $arrSelected, TRUE) ? "data-icon='fa fa-check-circle text-green'" : "";
//                                                $strItem .= "<option $iconCheck $selected $disable value='" . $datas['id'] . "'>" . $datas['nama'] . "</option>";
//                                            }
//                                            $strItem .= "</select>";
//                                        }
//                                        else {
//                                            $value = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] : "";
//                                            $strItem = "<input id='komposisi_fase_biaya_$fase_urut$hField' class='form-control form-control-sm text-red text-bold text-right' type='text' value='$value' onfocus=\"this.select()\" onkeyup=\"this.value = addCommas(this.value)\" onblur=\"$('#input_temp').load('$selector" . "$produkID/$fase_urut?mode=komposisi_fase_biaya&key=$hField&value='+encodeURI(removeCommas(this.value))+'', function(res){ eval(atob(res)); });\">";
//                                        }
//                                    }
//                                    else {
//                                        $value = isset($newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField]) ? $newData["komposisi_fase_biaya"][$fase_urut][$produkID][$hField] : "";
//                                        $defValue = is_numeric($value) ? number_format($value) : $value;
//                                        $strItem = "<span id='komposisi_fase_biaya_$fase_urut$hField' class='form-control form-control-sm no-border text-red text-bold text-right'>$defValue</span>";
//                                    }
//                                    $produkKomposisiFase .= "<td>";
//                                    $produkKomposisiFase .= $strItem;
//                                    $produkKomposisiFase .= "</td>";
//                                }
//                                $btnRemoveFasekomposisi = "<button id='komposisi_fase_biaya_$fase_urut$produkID' idform='$idForm' disabled type='button' title='simpan komposisi biaya' class='btn btn-sm'><i class='fa fa-plus'></i> tambah</button>";
//                                $produkKomposisiFase .= "<td class='text-center'>$btnRemoveFasekomposisi</td>";
//                                $produkKomposisiFase .= "</tr>";
//
//
//                                $produkKomposisiFase .= "</tbody>";
//                                $produkKomposisiFase .= "</table>";
//                                $produkKomposisiFase .= "</form>";
//                                $produkKomposisiFase .= "</div>";
//                                break;
//                        }
//                    }
//                    $produkKomposisiFase .= "</div>";
//                }
//            }
//
//            $produkKomposisiFase .= "</div>"; //box-body
//            $produkKomposisiFase .= "</div>";
//
//        }
//        else{
////            $produkKomposisiFase .= "<div class='box box-danger box-solid box-header text-center text-bold'> SILAHKAN BUAT RENCANA KERJA DULU </div>";
////
////            $produkKomposisiFase .= "
////            <script>
////                setTimeout(function(){
////                    top.$('#produk_fase_qty').val(1).trigger('blur');
////                    top.$('#produk_fase_nama').val('".$masterProject['nama']."').trigger('blur');
////                    top.$('#produk_fase_lokasi').val('-').trigger('blur');
////                    setTimeout(function(){
////                        $('#addProdukFase').click();
////                    },500);
////                },1000);
////            </script>";
//        }
//
//
//        $produkKomposisiFase .= "
//        <script>
//
//            $('.title_xedit').editable({
//                url: '".base_url()."master_project/MasterData/exeEditable'
//            });
//
//            $('.xedit').on('click', function(e){
//                var pid = $(this).attr('data-produk_id');
//                var nama = $(this).attr('data-sub_nama');
//                var furut = $(this).attr('data-fase_urut');
//                var sfurut = $(this).attr('data-sub_fase_urut');
//                var href = $(this).attr('data-href');
//                e.stopPropagation();
//                e.preventDefault();
//                console.log('pid: '+pid+'| nama: '+nama+'| furut: '+furut+'| sfurut: '+sfurut+'| href: '+href);
//                $('.title_xedit[href=\''+href+'\']').editable('toggle')
//                console.log( $('.title_xedit[href=\''+href+'\']') );
//            })
//
//            $('.spr_check').on('ifChanged', function(event){
//                var pids = $(this).attr('pids');
//                if( $(this).is(':checked') ){
//                    $('#btnColls_'+pids).trigger('click');
//                }
//                else{
//                    event
//                    swal('mematikan mode separated room', 'dengan mematikan mode separated room, data yang pernah tersimpan akan dihapus, apakah anda yakin dengan hal ini??', 'question')
//                    .then( (res)=>{
//                        if(res){
//                            $('#btnColls_'+pids).trigger('click');
//                        }
//                    })
//                    .then((aaa)=>{
//                        console.log(aaa);
//                    })
//                }
//            });
//
//            $('input[type=\"checkbox\"].minimal, input[type=\"radio\"].minimal').iCheck({
//              checkboxClass: 'icheckbox_minimal-blue',
//              radioClass: 'iradio_minimal-blue'
//            });
//
//            function init_int_jual_project(){
//                $('.int_jual_project').off();
//                $('.int_jual_project').on('keyup', function(){
//                    var pidfase     = $(this).attr('pidfase');
//                    var cur         = removeCommas( $(this).val() );
//                    var ori         = $('#hbom_'+pidfase).val();
//                    var saved       = $('#saved_jual_'+pidfase).val();
//                    var btn_danger  = $('#md1_btnSave_'+pidfase+' > span.btn-danger');
//                    var btn_success = $('#md1_btnSave_'+pidfase+' > span.btn-success');
//                    var btn_save    = $('#md1_btnSave_'+pidfase+' > span.btn-warning');
//
//                    if( cur*1 > ori*1 && saved!=cur ){
////                        console.log('oke');
//                        $(btn_danger).addClass('hidden');
//                        $(btn_success).addClass('hidden');
//                        $(btn_save).removeClass('hidden');
//                        $(this).addClass('text-green');
//                        $(this).removeClass('text-red');
//                        $(this).removeClass('text-orange');
//                    }
//                    else if( cur*1 < ori*1 ){
////                        console.error('not good');
//                        $(btn_danger).removeClass('hidden');
//                        $(btn_success).addClass('hidden');
//                        $(btn_save).addClass('hidden');
//                        $(this).addClass('text-red');
//                        $(this).removeClass('text-orange');
//                        $(this).removeClass('text-green');
//                    }
//                    else if( cur*1 == saved*1 ){
////                        console.log('no change');
//                        $(btn_danger).addClass('hidden');
//                        $(btn_success).removeClass('hidden');
//                        $(btn_save).addClass('hidden');
//                        $(this).removeClass('text-green');
//                        $(this).removeClass('text-red');
//                        $(this).removeClass('text-orange');
//                    }
//                    else if( cur*1 < saved*1 && cur*1 == ori*1){
////                        console.log('price change down');
//                        $(btn_danger).addClass('hidden');
//                        $(btn_success).addClass('hidden');
//                        $(btn_save).removeClass('hidden');
//
//                        $(this).removeClass('text-green');
//                        $(this).removeClass('text-red');
//                        $(this).addClass('text-orange');
//                    }
//                    else{
////                        console.log('kondisi embuh');
////                        console.log('kondisi embuh');
//                        $(btn_danger).addClass('hidden');
//                        $(btn_success).addClass('hidden');
//                        $(btn_save).addClass('hidden');
//                        $(btn_save).trigger('click');
//                    }
//
//                });
//
//                $('.int_jual_project').trigger('keyup');
//            }
//
//            function init_saveHrgProject(){
//                $('.save_jual_project').on('click', function(){
//                    var cx_url      = $(this).attr('cx_url');
//                    var produk_id   = $(this).attr('produk_id');
//                    var fase_id     = $(this).attr('fase_id');
//                    var hjp         = $('#hjp_'+produk_id+''+fase_id).val();
//                    var hbom         = $('#hbom_'+produk_id+''+fase_id).val();
//                    (async () => {
//                      const rawResponse = await fetch(cx_url, {
//                        method: 'POST',
//                        body: JSON.stringify({jual_project: removeCommas(hjp),hbom: removeCommas(hbom)})
//                      });
//                      const content = await rawResponse.json();
//
//                      if(content.status){
//                            eval(content.js)
//                      }
//
//                    })();
//                });
//            }
//
//            function init_saveDiscProject(){
//                $('.int_diskon').on('keyup', delay_v2(function(){
//                    var cx_url      = $(this).attr('cx_url');
//                    var produk_id   = $(this).attr('produk_id');
//                    var fase_id     = $(this).attr('fase_id');
//                    var diskon         = $('#diskon_'+produk_id+''+fase_id).val();
//                    (async () => {
//                      const rawResponse = await fetch(cx_url, {
//                        method: 'POST',
//                        body: JSON.stringify({diskon_project: removeCommas(diskon)})
//                      });
//                      const content = await rawResponse.json();
//                      if(content.status){
//                            eval(content.js)
//                      }
//                    })();
//                },2500));
//            }
//
//            function preLoadFase(){
//                $('.container_produk_fase').load('$modulClassLink/showProdukFase/$produkID', function(){
//                    //top.window.location.reload();
//                });
//            }
//
//            function preLoadProdukFase(){
//                $('#containerPreLoadProdukFase').load('$modulClassLink/showKomposisiProdukFase/$produkID');
//            }
//
//            function reloadSelectpicker(){
//                $('.selectpicker').selectpicker('refresh');
//            }
//
//            function reloadTombolSimpan(){
//                var formJml = document.querySelectorAll('[id*=\"jml\"]');
//                jQuery.each(formJml, function(a,b){
//                    var jml_value = $(b);
//                    if( $(jml_value).val() * 1 > 0 ){
//                        $(jml_value).trigger('blur');
//                    }
//                });
//            }
//
//            function initDeleteFunc(){
//                $('.delWorkOrder').off();
//                $('.delWorkOrder').on('click', function(){
//                    var cx_jenis = $(this).attr('cx_jenis');
//                    var cx_tmpnamamaterial = $(this).attr('cx_tmpnamamaterial');
//                    var cx_deletelink = $(this).attr('cx_deletelink');
//                    var cx_tid = $(this).attr('cx_tid');
//                    var cx_mdl = $(this).attr('cx_mdl');
//                    swal({
//                        title: 'Apakah kamu yakin untuk menghapus?',
//                        html: \"<span class='text-bold text-red text-uppercase'>\"+cx_tmpnamamaterial+\"</span> akan dihapus, data yg telah di hapus tidak bisa dikembalikan.\",
//                        type: 'question',
//                        showCancelButton: true,
//                        confirmButtonColor: '#3085d6',
//                        cancelButtonColor: '#d33',
//                        confirmButtonText: 'setuju & lanjutkan!'
//                    })
//                    .then(function(){
//                        swal('proses menghapus<br>mohon tunggu sebentar');
//                        swal.enableLoading();
//                        $.ajax({
//                            url: cx_deletelink + '' + cx_mdl + '/' + cx_tid,
//                            success: function(res){
//                                arrDatas = JSON.parse(res)
//                                if(arrDatas.status){
//                                    setTimeout(function(){
//                                        swal('SUKSES', 'data berhasil dihapus', 'success');
//                                        preLoadFase();
//                                    },1000)
//                                }
//                            }
//                        });
//                    })
//                    .catch(function(reason){
//                        //alert(\"The alert was dismissed by the user: \"+reason);
//                    });
//                })
//            }
//
//            function init_addProdukFase(){
//                top.$('#addProdukFase').off();
//                top.$('#addProdukFase').on('click', function(){
//                    var id = $(this).attr('id');
//                    var ev_aa = $(this).attr('cx_onclick');
//                    var arr_input = $('.'+id);
//                    var error = '';
//                    jQuery.each(arr_input, function(a, b){
//                        var keys = $(b).attr('key');
//                        var val = $(b).val();
//                        if( keys=='qty' && val=='' ){
//                            error += '<b><r> Jumlah </r></b> wajib di isi<br>'
//                        }
//                        if( keys=='nama' && val=='' ){
//                            error += '<b><r> Nama Rencana Kerja </r></b> wajib di isi'
//                        }
//                    });
//                    if( error=='' ){
//                        //bisa lanjut
//                        eval(ev_aa);
//                    }
//                    else{
//                        swal('isian kurang', error, 'error');
//                    }
//                });
//            }
//
//            init_addProdukFase();
//            initDeleteFunc();
//            reloadTombolSimpan();
//            init_saveHrgProject();
//            init_saveDiscProject();
//            init_int_jual_project();
//        </script>
//
//        ";
//
//        //JIKA ADA UPDATE COPAS DARI SINI SAJA

        $produkKomposisiFase .= "</div>";
        $produkKomposisiFase .= "</div>";

        $produkKomposisiFase .= "<div class='hidden' id='input_temp'></div>";

        //endregion

        //region rencana proses produksi (nama_produk)
        $produkFase = "";
        $produkFase .= "<div class='container_produk_fase'>";
        $produkFase .= "<form class='form' name='produk_fase' id='produk_fase' method='post' target='result' action='$addFaseProdukLink?mode=produk_fase'>";
        $produkFase .= "<table class='table dataTable compact display table-bordered table-condensed'>";
        $produkFase .= "<thead>";
        $produkFase .= "<tr>";
        $produkFase .= "<th>No</th>";

        foreach ($produk_fase_header as $produkfaseKey => $produkfase_alias) {
            $produkFase .= "<th>$produkfase_alias</th>";
        }

        $produkFase .= "<th>action</th>";
        $produkFase .= "</tr>";
        $produkFase .= "</thead>";
        $produkFase .= "<tbody>";
        if (isset($produk_fase) && count($produk_fase)) {
            $i = 0;
            foreach ($produk_fase as $fase_urut => $faseData) {
                $i++;
                $produkFase .= "<tr>";
                $produkFase .= "<td>$i</td>";
                $tmpNamaMaterial = "";
                foreach ($produk_fase_header as $produkfaseKey => $produkfase_alias) {
                    $newKey = isset($relSuppliesHeader[$produkfaseKey]) ? $relSuppliesHeader[$produkfaseKey] : $produkfaseKey;
                    if (isset($faseData[$newKey])) {
                        if (is_numeric($faseData[$newKey])) {
                            $fieldValue = formatField($newKey, $faseData[$newKey]);
                        }
                        else {
                            $fieldValue = $faseData[$newKey];
                        }
                    }
                    else {
                        $fieldValue = "";
                    }
                    $produkFase .= "<td>" . $fieldValue . "</td>";
                    $tmpNamaMaterial .= $newKey == "nama" ? $fieldValue : "";
                }
                //region button remove
                // $btn = "<button type='button' title='clik untuk menghapus realasi' class='btn btn-xs btn-danger' onclick=\"document.getElementById('result').src='" . $deleteTarget . "?pid=$selectedID&tokoID=$tokoID&id=$curentID&key=trash&value=0'\"><span class='glyphicon glyphicon-trash'></span></button>";
                $btn = "<div>";
                if (isset($allowedAccess["fase_id"]["update"]) && $allowedAccess["fase_id"]["update"] == true) {
                    $btn .= "<button type='button' title='edit' class='btn btn-xs btn-flat btn-warning' onclick=\"showModal('" . $previewLink . "MdlProjectWorkOrder/$fase_urut/$hField','edit relasi')\"><i class='fa fa-fw fa-edit'></i></button>";
                }
                if (isset($allowedAccess["fase_id"]["delete"]) && $allowedAccess["fase_id"]["delete"] == true) {
                    $btn .= "<button disabled cx_jenis='fase_id' cx_tmpnamamaterial='$tmpNamaMaterial' cx_deletelink='$deleteLink' cx_tid='$fase_urut' cx_mdl='ProjectWorkOrder' type='button' title='click untuk menghapus relasi' class='btn btn-xs btn-flat btn-danger delWorkOrder'><i class='fa fa-fw fa-eraser'></i></button>";
                }

                $btn .= "</div>";
                $produkFase .= "<td class='text-center'>$btn</td>";
                //endregion
                $produkFase .= "</tr>";
            }
            //tambahan tr untuk add data baru
        }

        if (!count($produk_fase) && $lock == 0) {
            //untuk penambahan
            $produkFase .= "<tr>";
            $produkFase .= "<td></td>";
            foreach ($produk_fase_header as $produkfaseKey => $produkfase_alias) {
                if (isset($produk_fase_komposisiEditable[$produkfaseKey])) {
                    if (isset($relWorkOrderEmployee[$produkfaseKey])) {
                        $strTim_wo = "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp_0').load('$selector" . "$produkID?mode=produk_fase&key=$produkfaseKey&value='+encodeURI(this.value)+'$targetResult'); \">";
                        $strTim_wo .= "<option value='0'>==PILIH==</option>";
                        $ic = 0;
                        foreach ($relWorkOrderEmployee[$produkfaseKey] as $datas) {
                            $ic++;
                            $selected = isset($newData["produk_fase"][$produkID][$produkfaseKey]) && $newData["produk_fase"][$produkID][$produkfaseKey] == $datas['employee_id'] ? "selected" : "";
                            $strTim_wo .= "<option $selected value='" . $datas['employee_id'] . "'>" . $datas['employee_nama'] . "</option>";
                        }
                        $strTim_wo .= "</select>";
                    }
                    else {
                        $preval = isset($newData["produk_fase"][$produkID][$produkfaseKey]) ? $newData["produk_fase"][$produkID][$produkfaseKey] : "";
                        $readOnly = "";
                        $strTim_wo = "<input $readOnly class='form-control form-control-sm' type='text' value='$preval' onblur=\"$('#input_temp').load('$selector" . "$produkID?mode=produk_fase&key=$produkfaseKey&value='+encodeURI(this.value));\">";
                    }
                }
                else {
                    $preval = isset($newData["produk_fase"][$produkID][$produkfaseKey]) ? $newData["produk_fase"][$produkID][$produkfaseKey] : "";
                    $readOnly = "";
                    $strTim_wo = "<input key='$produkfaseKey' $readOnly id='produk_fase_$produkfaseKey' class='form-control form-control-sm addProdukFase' type='text' value='$preval' onfocus=\"this.select()\" onblur=\"$('#input_temp').load('$selector" . "$produkID?mode=produk_fase&key=$produkfaseKey&value='+encodeURI(this.value));\">";
                }
                $produkFase .= "<td>$strTim_wo</td>";

            }
            $produkFase .= "<td> <span id='addProdukFase' class='btn btn-sm btn-success' disabled cx_onclicks=\"document.getElementById('produk_fase').submit();\"><i class='fa fa-plus'></i> tambah</span> </td>";
            $produkFase .= "</tr>";
        }

        $produkFase .= "</tbody>";
        $produkFase .= "</table>";
        $produkFase .= "</form>";
        $produkFase .= "</div>";
        //endregion rencana proses produksi (nama_produk)

        //region summary project
        $rincianProject = "<div class='text-center' style='padding:25px;'><i class='fa fa-spin fa-spinner fa-2x text-muted'></i><br><span class='text-muted'>Memuat Ringkasan Anggaran...</span></div>";
        $rincianProject .= "
            <script>
                function showHideRow(ids){
                    if( $('#hr_'+ids) && $('#hr_'+ids).hasClass('hidden') ){
                        $('#hr_'+ids).removeClass('hidden');
                        $('#wrapper_'+ids).height( $('#height_'+ids).outerHeight(true) + 10 );
                    }
                    else{
                        $('#wrapper_'+ids).height(0);
                        setTimeout( function(){
                            $('#hr_'+ids).addClass('hidden');
                        }, 450);
                    }
                }
                function reloadSummaryProject(){
                    $('#projectSummary').load('$modulClassLink/showSummaryProject/$produkID');
                }
                $(document).ready(function(){
                    reloadSummaryProject();
                });
            </script>
            ";
        //endregion
        // matiHere();
        $strMain = "";

        $strMain .= "<div class='box-header'>";
        $strMain .= $bomTitle;
        $strMain .= "</div>";
        $strMain .= "<div class='box-body no-padding table-responsive'>";
        $strMain .= $produkFase;
        $strMain .= $produkKomposisiFase;
        $strMain .= "</div>";

        $scriptBottom = "<script>$('#btnReload').on('click', function(){ })</script>";
        $scriptBottom .= "<script>$('#untuk_reload_iframe').off(); $('#untuk_reload_iframe').on('click', function(){ var iframe = top.document.getElementById('$result'); iframe.src=iframe.src; console.log(iframe.src); })</script>";
        $scriptBottom .= "<style>
            @keyframes highlight-target-anim {
                0% { box-shadow: 0 0 0 5px rgba(231, 76, 60, 0.9); transform: scale(1.02); }
                50% { box-shadow: 0 0 0 35px rgba(231, 76, 60, 0); transform: scale(1.10); }
                100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); transform: scale(1); }
            }
            .highlight-target-pulse {
                animation: highlight-target-anim 1s infinite !important;
                z-index: 9999 !important;
                position: relative;
                border-radius: 4px;
            }
            .guide-item {
                margin-bottom: 15px;
                cursor: pointer;
                transition: all 0.3s ease;
                border-radius: 4px;
                padding: 10px;
                margin-left: -10px;
                margin-right: -10px;
            }
            .guide-item:hover {
                background: #f0f0f0;
                box-shadow: 0 4px 8px rgba(0,0,0,0.05);
                transform: translateY(-2px);
            }
            .guide-item .click-hint {
                font-size: 11px;
                font-weight: normal;
                margin-top: 2px;
                color: #999;
                transition: all 0.3s;
            }
            .guide-item:hover .click-hint {
                color: #e74c3c;
                text-shadow: 0 0 8px rgba(231, 76, 60, 0.6);
                transform: scale(1.05);
            }
        </style>";
        $scriptBottom .= "<script>
        window.scrollToTarget = function(targetType) {
            var target = null;

            if (targetType === 'rab') {
                target = top.$('.linkEditBom').first();
            } else if (targetType === 'tambahan') {
                target = top.$('.btn-danger[onclick*=\"openDialogModal\"]').first();
            } else if (targetType === 'tambah_wo') {
                target = top.$('.btn-tambah-paket').first();
                if (!target.length) target = top.$('#simpanTugas').first();
            } else if (targetType === 'tasklist_tab') {
                top.$('.nav-tabs a, .nav a').each(function(){
                    if(top.$(this).text().toLowerCase().indexOf('tasklist') !== -1) target = top.$(this);
                });
            } else if (targetType === 'file_tab') {
                top.$('.nav-tabs a, .nav a').each(function(){
                    if(top.$(this).text().toLowerCase().indexOf('file') !== -1) target = top.$(this);
                });
            }

            if(target && target.length) {
                // Scroll lambat dengan jQuery (durasi 1200ms)
                var offsetTop = target.offset().top - 150;

                // Animasikan scroll di frame saat ini dan frame utama agar pasti bergerak
                $('html, body').animate({ scrollTop: offsetTop }, 1200, 'swing');
                if (window.self !== window.top) {
                    top.$('html, body').animate({ scrollTop: offsetTop }, 1200, 'swing');
                }

                target.addClass('highlight-target-pulse');

                // Jika itu tab, kita bisa tambahkan class ke parent li nya juga agar kotaknya terlihat
                var isTabAnchor = target.parent().is('li');
                if (isTabAnchor) {
                    target.parent().addClass('highlight-target-pulse');
                }

                setTimeout(function(){
                    target.removeClass('highlight-target-pulse');
                    if (isTabAnchor) target.parent().removeClass('highlight-target-pulse');
                }, 4000); // Kedip diperpanjang jadi 4 detik karena scrollnya lambat
            } else {
                console.warn('Target not found for: ' + targetType);
            }
        };
        </script>";

        $scriptBottom .= "
        <script>
            $('.nav-item').on('click', function(){
                var tabThis = $('a', $(this)).attr('id');
                localStorage.setItem('position_tab', tabThis);
            })
            var pos = localStorage.getItem('position_tab');
            if( $('#'+pos) ){
                $('#'+pos).click()
            }
            //region menyimpan dan restore posisi scroll (by chepy)
            var scroll_$result = localStorage.getItem('scroll_$result');
            top.$('#$result').contents().scrollTop(scroll_$result);
            top.$(top.$('#$result').contents()).on('scroll', function(){
                localStorage.setItem('scroll_$result', $(this).scrollTop());
            });
            //endregion menyimpan dan restore posisi scroll (by chepy)
            setTimeout( function(){
                var thisFrameHeight = top.$('#$result').contents().height();
                top.$('#$result').height(thisFrameHeight);
            }, 500)

            //region Kontrol Tampilan HPP Realisasi (LocalStorage & Verifikasi User/Dual Login)
            var activeHppUserId = '<?php echo my_id(); ?>';
            var activeHppSessId = '<?php echo session_id(); ?>';
            var storageHppKey = 'everest_rab_show_hpp';

            function verifyAndApplyHppVisibility() {
                var shouldShow = false;
                try {
                    var rawPref = localStorage.getItem(storageHppKey);
                    if (rawPref) {
                        var pref = JSON.parse(rawPref);
                        if (pref && String(pref.userId) === String(activeHppUserId) && String(pref.sessId) === String(activeHppSessId)) {
                            shouldShow = (pref.showHpp === true || pref.showHpp === 1 || pref.showHpp === '1');
                        } else {
                            // User berganti atau dual login / sesi baru -> reset default hidden
                            localStorage.removeItem(storageHppKey);
                            shouldShow = false;
                        }
                    }
                } catch (e) {
                    shouldShow = false;
                }

                $('#toggleShowHppRealisasi').prop('checked', shouldShow);
                applyHppVisibility(shouldShow);
            }

            function applyHppVisibility(show) {
                if (show) {
                    $('#containerPreLoadProdukFase').removeClass('hide-hpp-realisasi');
                } else {
                    $('#containerPreLoadProdukFase').addClass('hide-hpp-realisasi');
                }
            }

            $(document).on('change', '#toggleShowHppRealisasi', function() {
                var isChecked = $(this).is(':checked');
                applyHppVisibility(isChecked);
                try {
                    var prefData = {
                        userId: activeHppUserId,
                        sessId: activeHppSessId,
                        showHpp: isChecked ? 1 : 0
                    };
                    localStorage.setItem(storageHppKey, JSON.stringify(prefData));
                } catch (e) {}
            });

            verifyAndApplyHppVisibility();
            //endregion

            function preLoadProdukFase(){
                var winSearch = top.window.location.search;

                //swal(winSearch);
                $('#containerPreLoadProdukFase').load('$modulClassLink/showKomposisiProdukFase/$produkID'+winSearch, function(){
                    var isChecked = $('#toggleShowHppRealisasi').is(':checked');
                    applyHppVisibility(isChecked);
                });
            }

            top.preLoadProdukFase();
        </script>";

        /* -----------------------------
         * matiin project
         * -----------------------------*/
//        $img = img_maintenace();
//        $txt = dtimeNow('d F Y H:i');
//        $linkHome = base_url();
//        $txt .= " <br>Kembali ke <a href=\'$linkHome\'>HOME</a>";
//
//        $scriptBottom .= "<script>
//            swal({
//                // title:'ssss',
//                html:'$txt',
//                imageUrl: '$img',
//                showConfirmButton: false,
//                allowOutsideClick: false,
//            });
//        </script>";

        $timWorkUndone = "<div style='margin-top: 22px;' class='box-header bg-orange box-solid text-center text-bold'><i class='fa fa-warning text-white blink'></i> SETELAH QUOTATION/BOM DI APPROVE, ANDA BISA MEMBUAT TIM DISINI. <i class='fa fa-warning text-white blink'></i></div>";
        $timWorkUndone .= "<div style='margin-top: 5px;' class='box-header bg-info box-solid text-center text-bold'><i><u><r> SILAHKAN MENGHUBUNGI ATASAN ANDA, AGAR MEMPERCEPAT PROSES PENGECEKAN DAN APPROVAL </r></u></i><br><div><a href='" . MODUL_PATH . "Transaksi/index/588'>ATAU KLIK DISINI</a></div></div>";
//        $timWorkUndone .= "<div>lock: $lock || no_kontrak: $no_kontrak || transaksi_no: $transaksi_no</div>";

        $WorkOrderUnSave = "<div style='margin-top: 22px;' class='box-header bg-orange box-solid text-center text-bold'><i class='fa fa-warning text-white blink'></i> JANGAN LUPA UNTUK MENYIMPAN BOM JIKA TELAH SELESAI MENAMBAHKAN KOMPONEN-KOMPONEN. <i class='fa fa-warning text-white blink'></i></div>";

        $timWork_f = "";

//        echo "lock: $lock || no_kontrak: $no_kontrak";
//        echo "$lock*1==1 => " . ($lock*1==1) . "<br>";
//        echo "$no_kontrak!='' => " . ($no_kontrak!='') . "<br>";
//        echo "$no_kontrak!=0 => " . ($no_kontrak!='0') . "<br>";

        if ($lock * 1 == 1) {
            $pakai_teamwork = 0;
            if ($pakai_teamwork) {
                $timWork_f = $timWork;
            }
            else {
                $timWork_f = "<div class='well well-success text-center text-bold text-red'>ANDA BISA MENAMBAH PENUGASAN MELALUI TAB TASKLIST/WO</div>";
            }
        }
        else {
            if ($lock * 1 == 1) {
                $timWork_f = $timWorkUndone;
            }
            else {
                $timWork_f = $WorkOrderUnSave;
            }
        }

        $filesContent = "<div id='project_files_container'><div class='text-center'><i class='fa fa-spin fa-spinner'></i> Loading files...</div></div>";
        $filesContent .= "<script>
        function loadProjectFiles() {
            $('#project_files_container').load('" . MODUL_PATH . "MasterData/showProjectFiles/$produkID');
        }
        $(document).ready(function(){
            loadProjectFiles();
        });
        </script>";

        $purchasingContent = "<div id='project_purchase_pool_container'><div class='text-center' style='padding:25px;'><i class='fa fa-spin fa-spinner fa-2x text-muted'></i><br><span class='text-muted'>Memuat Status Pengadaan...</span></div></div>";
        $purchasingContent .= "<script>
        function loadPurchasePoolStatus() {
            $('#project_purchase_pool_container').load('" . MODUL_PATH . "MasterData/showPurchasePoolStatus/$produkID');
        }
        $(document).ready(function(){
            loadPurchasePoolStatus();
        });
        </script>";

        $hppHistoryContent = "<div id='project_hpp_history_container'><div class='text-center' style='padding:25px;'><i class='fa fa-spin fa-spinner fa-2x text-muted'></i><br><span class='text-muted'>Memuat Riwayat HPP...</span></div></div>";
        $hppHistoryContent .= "<script>
        function loadHppHistoryStatus() {
            $('#project_hpp_history_container').load('" . MODUL_PATH . "MasterData/showHppHistoryStatus/$produkID');
        }
        $(document).ready(function(){
            // loadHppHistoryStatus(); // disabled sementara saat tab di-hidden
        });
        </script>";

        $kontrakHistoryContent = "<div id='project_kontrak_history_container'><div class='text-center' style='padding:25px;'><i class='fa fa-spin fa-spinner fa-2x text-muted'></i><br><span class='text-muted'>Memuat Riwayat Kontrak & RAB...</span></div></div>";
        $kontrakHistoryContent .= "<script>
        function loadKontrakHistoryStatus() {
            $('#project_kontrak_history_container').load('" . MODUL_PATH . "MasterData/showKontrakHistoryStatus/$produkID');
        }
        $(document).ready(function(){
            loadKontrakHistoryStatus();
        });
        </script>";

        // Integrasi Workflow Stepper & Context Guidance Card (Sisi 2 UI/UX Transparency)
        $ci =& get_instance();
        $ci->load->helper("he_workflow_stepper_helper");
        $isProjectLocked = (isset($masterProject['lock']) && $masterProject['lock'] == 1);
        $currentProjectStep = $isProjectLocked ? 5 : 2;

        $workflowStepperHtml = renderWorkflowStepper($currentProjectStep);

        $projectHargaFormatted = isset($masterProject['harga']) ? 'Rp ' . number_format((float)$masterProject['harga'], 0, ',', '.') : '-';
        $contextGuidanceHtml = renderContextGuidanceCard(array(
            "role" => "Admin / Project Manager",
            "purpose" => $isProjectLocked
                ? "Proyek berstatus SELESAI & TERKUNCI. Seluruh BOM dan alokasi material terkunci sebagai acuan audit rekonsiliasi."
                : "Menetapkan alokasi komponen BOM Material dan Tim Kerja sebelum pengeluaran material oleh Bagian Gudang.",
            "origin" => "Kontrak Tender / SPK Penjualan (Nilai Proyek: " . $projectHargaFormatted . ")",
            "nextImpact" => $isProjectLocked
                ? "Verifikasi mutasi stok, kartu stok gudang, dan saldo jurnal penutupan WIP."
                : "Setelah disimpan, Bagian Gudang menerima kuota pengeluaran barang & Tim Lapangan menerima Tasklist WO.",
            "auditUrl" => MODUL_PATH . "AuditProject/audit/" . $produkID,
        ));

        $workflowStepperHeader = $workflowStepperHtml . $contextGuidanceHtml;

        $p->addTags(array(
            "workflow_stepper_header" => $workflowStepperHeader,
            "timWork" => $timWork_f,
//            "timWork"            => "-- tambahkan team work --",
            "projectData" => $project,
//            "projectData" => "",
            "projectDataDetails" => $rincianProject,
            "content" => $strMain,
            "files_content" => $filesContent,
            "purchasing_content" => $purchasingContent,
            "hpp_history_content" => $hppHistoryContent,
            "kontrak_history_content" => $kontrakHistoryContent,
            "scriptBottom" => $scriptBottom,
            "display_iframe" => "none",//biar gak panjang
            "error_msg" => $error,
            "menu_left" => callMenuLeft(),
            "trans_menu" => callTransMenu(),
            "float_menu_atas" => callFloatMenu('atas'),
            "float_menu_bawah" => callFloatMenu(),
            "menu_taskbar" => callMenuTaskbar(),
            "btn_back" => callBackNav(),
        ));
        $p->render();
        break;
    case "taskList":
//        trt
        $tabTitle = (isset($produkNama) && trim($produkNama) !== "") ? trim($produkNama) : "View Project";
        if ($load == "switch") {
            //akan load template dengan format element saja
            $p = New Layout($tabTitle, "View Your Project", MODUL_TEMPLATE_PATH . "template/tasklist.html");
        }
        else {

            if($produkID=="qc"){
                //akan load template dengan format element saja
                $p = New Layout("QC Project", "View Your Project", MODUL_TEMPLATE_PATH . "template/tasklist_qc.html");
            }
            else{
                //akan load template dengan format html lengkap
                $p = New Layout($tabTitle, "Manage Your Project", MODUL_TEMPLATE_PATH . "template/tasklist_base.html");
            }

        }

        $targetResult = isset($result) ? "&result=$result" : "&result=result";
        $showNilai = isset($_SESSION['MasterData']['show_nilai']) ? (int)$_SESSION['MasterData']['show_nilai'] : 0;

        if (count($timWork) > 0) {
            $addNewCustomer = "
                    BootstrapDialog.show({
                        title:'New Customer',
                        message: $('<div></div>').load('" . base_url() . "Data/add/Employee_all'),
                        draggable:true,
                        closable:true,
                        size:top.BootstrapDialog.SIZE_WIDE,
                    });
                ";

            $kol = "employee_id";
            $formWork_tim = "";
            $formWork_tim .= "<select multiple id='employee_id' data-style=\"btn btn-sm btn-primary\" class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID?key=employee_id&value='+encodeURI( $(this).selectpicker('val') )+'$targetResult');\">";
            $formWork_tim .= "<option value='0'>-- silahkan pilih --</option>";
            foreach ($timWork as $timWork_0) {
                $pid = $timWork_0->produk_id;
                $selected = isset($sessionData[$pid][$kol]) && ($sessionData[$pid][$kol] == $timWork_0->employee_id) ? "selected" : "";
                $formWork_tim .= "<option $selected value='" . $timWork_0->employee_id . "'>" . $timWork_0->employee_nama . "</option>";
            }
            $formWork_tim .= "</select>";
        }
        else {
            $addNewCustomer = "
                    BootstrapDialog.show({
                        title:'New Customer',
                        message: $('<div></div>').load('" . base_url() . "Data/add/Employee_all'),
                        draggable:true,
                        closable:true,
                        size:top.BootstrapDialog.SIZE_WIDE,
                    });
                ";

            $kol = "employee_id";
            $formWork_tim = "";
            $formWork_tim .= "<select id='employee_id' data-style=\"btn btn-sm btn-primary\" class=\"selectpicker\" data-live-search=\"true\">";
            $formWork_tim .= "<option value='0'>-- silahkan pilih --</option>";
            $formWork_tim .= "</select>";

//            $formWork_tim .= "<span class='btn btn-xs btn-flat' onclick=\"$addNewCustomer\"> ADD EMPLOYEE </span>";
        }

        if (count($workOrder) > 0) {

            $kol = "fase_id";
            $formWork_order = "";
            $formWork_order .= "<select id='$kol' data-style=\"btn btn-sm btn-primary\" class=\"selectpicker\" data-live-search=\"true\">";
            $formWork_order .= "<option value='0'>-- silahkan pilih --</option>";
            foreach ($workOrder as $workOrder_0) {
                $pid = $workOrder_0->produk_id;
                $selected = isset($sessionData[$pid][$kol]) && ($sessionData[$pid][$kol] == $workOrder_0->id) ? "selected" : "";
                $formWork_order .= "<option $selected value='" . $workOrder_0->id . "'>" . $workOrder_0->nama . " </option>";
            }
            $formWork_order .= "</select>";

            $showNilai = isset($_SESSION['MasterData']['show_nilai']) ? (int)$_SESSION['MasterData']['show_nilai'] : $showNilai;

            $formWork_order .= "<script>
                $('#$kol').off();
                $('#$kol').on('change', function(){
                    var values = $(this).val();
                    $.ajax({
                        url: '$selector$produkID?key=fase_id&value='+encodeURI(values)+'$targetResult',
                        success: function(r_fase_id){
                            var hsl = JSON.parse(r_fase_id);
                            var opt = \"<label class='box-title'>Pilih Pekerjaan: &nbsp;</label>\"
                                    + \"<select id='sub_fase' data-style='btn btn-sm btn-success' class='' data-live-search='true'>\"
                                    + \"<option value='0'>-- Pilih Pekerjaan --</option>\"

                            jQuery.each(hsl.$kol, function(a,b){

                                console.log(b);

                                var sel = hsl.session[$produkID].sub_fase_id*1  == b.id*1 ? 'selected' : ''
                                var disabeled = b.daftar_tugas*1 > 0 ? 'disabled' : ''
                                var icons__t = b.daftar_tugas*1 > 0 ? 'fa fa-check-circle' : ''
                                opt += \"<option data-icon='\"+icons__t+\"' \"+disabeled+\" \"+sel+\" value='\"+b.id+\"'>\"+b.nama+\"</option>\"
                            })

                            opt += \"</select>\"

                            $('#sub_fase_selector').html(opt);
                            $('#sub_fase_selector').removeClass('hidden');
                            top.$('select#sub_fase').selectpicker('refresh');
                            initSubFase();
                            top.$('select#sub_fase').trigger('change');
                        }
                    })
                });

                $('#$kol').trigger('change');

                function initSubFase(){
//                    $('#sub_fase').off();
                    $('#sub_fase').on('change', function(){
                        var values = $(this).val();
                        $.ajax({
                            url: '$selector$produkID?key=sub_fase_id&value='+encodeURI(values)+'$targetResult',
                            success: function(sub_fase_id){
                                var hsl = JSON.parse(sub_fase_id);
                                var room = hsl.room;
                                var komposisi = hsl.komposisi_room;

                                console.error('room.length'  );
                                console.log( room.length );

                                var updated = Object.values(hsl.sub_fase_id.reduce((obj, item) => {
                                    var key = item.jenis + '_' + item.produk_dasar_id;
                                    if (!obj[key]) {
                                        obj[key] = Object.assign(item)
                                        obj[key].harga = obj[key].harga*1
                                        obj[key].jml = obj[key].jml*1
                                        obj[key].saldo = obj[key].saldo*1
                                    }
                                    else {
                                        obj[key].jml += item.jml*1
                                        obj[key].saldo += item.saldo*1
                                    }
                                    return obj
                                }, {}));

                                var showNilai = $showNilai*1;
                                var table = ''
                                    table += \"<div class='row'>\"

                                    if(room.length>0){
                                        table += \"<div class='container-fluids'>\"

                                        table += \"<div class='box box-warning box-solid'>\"
                                        table += \"<div class='box-header fa-2x text-bold text-blue'>SEPARATED ROOM</div>\"
                                        table += \"<div class='box-body'>\"

                                        table += \"<div class='nav-tabs-custom'>\"
                                        table += \"<div class='tab-content no-padding'>\"
                                        table += \"<ul class='nav nav-tabs' id='custom-content-below-tab' role='tablist'>\"

                                        jQuery.each(room, function(a, b){
                                            var defActive = a==0 ? 'active' : ''
                                            var num = a+1;
                                            table += \"<li class='nav-item \"+defActive+\"'>\"
                                            table += \"<a class='nav-link' id='cc-tab-room_\"+b.room_id+\"' data-toggle='pill' href='#tsk-tab-room_\"+b.room_id+\"' role='tab' aria-controls='cc-tab-room_\"+b.room_id+\"' aria-selected='false'><span style='font-size: 12px;' class='text-bold text-uppercase'> <b>\"+num+\". \"+b.room_nama+\"</b></span></a>\"
                                            table += \"</li>\"

                                        });

                                        table += \"</ul>\"

                                            jQuery.each(room, function(a, b){
                                                var list_bahan_biaya = Object.values(komposisi[b.room_id].reduce((obj, item) => {
                                                    var key = item.jenis + '_' + item.produk_dasar_id;
                                                    if (!obj[key]) {
                                                        obj[key] = Object.assign(item)
                                                        obj[key].harga = obj[key].harga*1
                                                        obj[key].jml = obj[key].jml*1
                                                        obj[key].saldo = obj[key].saldo*1
                                                    }
                                                    else {
                                                        obj[key].jml += item.jml*1
                                                        obj[key].saldo += item.saldo*1
                                                    }
                                                    return obj
                                                }, {}));

                                                var defActive = a==0 ? 'active in' : ''

                                                table += \"<div class='tab-pane fade \"+defActive+\"' id='tsk-tab-room_\"+b.room_id+\"'>\"
                                                table += \"<div class='table-responsive'>\"
                                                table += \"<table id='table_container_material_\"+b.room_id+\"' class='table dataTable compact display table-bordered table-condensedx'>\"
                                                table += \"<thead>\"
                                                table += \"<tr>\"
                                                table += \"<th>No</th>\"
                                                table += \"<th>Bahan baku</th>\"
                                                table += \"<th>UoM</th>\"

                                                if(showNilai){
//                                                    table += \"<th class='text-center'>H.HPP<br>(satuan)</th>\"
//                                                    table += \"<th class='text-center'>H.PPV<br>(satuan)</th>\"
                                                    table += \"<th class='text-center'>H.BELI<br>(satuan)</th>\"
                                                    table += \"<th class='text-center'>H.JUAL<br>(satuan)</th>\"
                                                }

                                                table += \"<th class='text-center'>Jml</th>\"

                                                if(showNilai){
//                                                    table += \"<th class='text-center'>Total<br>HPP</th>\"
//                                                    table += \"<th class='text-center'>Total<br>PPV</th>\"
                                                    table += \"<th class='text-center'>Total<br>H.BELI</th>\"
                                                    table += \"<th class='text-center'>Total<br>H.JUAL</th>\"
                                                    table += \"<th class='text-center'>R/L</th>\"
                                                }

                                                table += \"</tr>\"
                                                table += \"</thead>\"
                                                table += \"<tbody>\"

                                                var no          = 0;
                                                var total_saldo = 0;
                                                var total_qty   = 0;

                                                var totHpp  = 0;
                                                var totPpv  = 0;
                                                var totJual = 0;
                                                var totAng  = 0;

                                                var sumHpp  = 0;
                                                var sumPpv  = 0;
                                                var sumJual = 0;
                                                var sumAng  = 0;

                                                jQuery.each(list_bahan_biaya, function(a, b){
                                                    no++;
                                                    produk_dasar_nama = b.produk_dasar_nama;
                                                    satuan = b.satuan;
                                                    harga  = b.harga*1>0 ? b.harga*1 : 0;
                                                    nilai  = b.nilai*1>0 ? b.nilai*1 : 0;
                                                    jml    = b.jml;
                                                    saldo  = b.saldo;

                                                    hpp  = b.hrg_hpp*1> 0 ? b.hrg_hpp*1 : 0;
                                                    ppv  = b.hrg_ppv*1> 0 ? b.hrg_ppv*1 : 0;
                                                    jual = b.hrg_jual*1> 0 ? b.hrg_jual*1 : 0;

                                                    table += \"<tr>\"
                                                    table += \"<td>\"+no+\"</td>\"
                                                    table += \"<td>\"+produk_dasar_nama+\"</td>\"
                                                    table += \"<td>\"+satuan+\"</td>\"

                                                    if(showNilai){
//                                                        table += \"<td class='bg-limex text-right'>\"+addCommas(hpp)+\"</td>\"
//                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(ppv)+\"</td>\"
                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(jual)+\"</td>\"
                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(harga)+\"</td>\"
                                                    }

                                                    table += \"<td class='text-bold text-center'>\"+addCommas(jml)+\"</td>\"

                                                    if(showNilai){
//                                                        table += \"<td class='bg-greenx text-right'>\"+addCommas(hpp*jml)+\"</td>\"
//                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(ppv*jml)+\"</td>\"
                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(jual*jml)+\"</td>\"
                                                        table += \"<td class='bg-infox text-right'>\"+addCommas(harga*jml)+\"</td>\"
                                                        table += \"<td class='text-right'>\"+addCommas( (harga*jml)-(jual*jml))+\"</td>\"
                                                    }

                                                    table += \"</tr>\"

                                                    total_saldo += saldo*1;
                                                    total_qty   += jml*1;

                                                    totHpp  += hpp;
                                                    totPpv  += ppv;
                                                    totJual += jual;
                                                    totAng  += harga;

                                                    sumHpp  += hpp*jml;
                                                    sumPpv  += ppv*jml;
                                                    sumJual += jual*jml;
                                                    sumAng  += harga*jml;
                                                })

                                                table += \"</tbody>\"

                                                table += \"<tfoot class='bg-gray'>\"
                                                table += \"<tr>\"
                                                table += \"<th>-</th>\"
                                                table += \"<th>-</th>\"
                                                table += \"<th>-</th>\"

                                                if(showNilai){
//                                                    table += \"<th class='text-right'>\"+addCommas(totHpp)+\"</th>\"
//                                                    table += \"<th class='text-right'>\"+addCommas(totPpv)+\"</th>\"
                                                    table += \"<th class='text-right'>\"+addCommas(totJual)+\"</th>\"
                                                    table += \"<th class='text-right'>\"+addCommas(totAng)+\"</th>\"
                                                }

                                                    table += \"<th class='text-center'>\"+addCommas(total_qty)+\"</th>\"

                                                if(showNilai){
//                                                    table += \"<th class='text-right'>\"+addCommas(sumHpp)+\"</th>\"
//                                                    table += \"<th class='text-right'>\"+addCommas(sumPpv)+\"</th>\"
                                                    table += \"<th class='text-right'>\"+addCommas(sumJual)+\"</th>\"
                                                    table += \"<th class='text-right'>\"+addCommas(sumAng)+\"</th>\"
                                                    table += \"<th class='text-right'>\"+addCommas((sumAng)-(sumJual))+\"</th>\"
                                                }

                                                table += \"</tr>\"
                                                table += \"</tfoot>\"

                                                table += \"</table>\"
                                                table += \"</div>\"
                                                table += \"</div>\"

                                        })

                                        table += \"</div>\"
                                        table += \"</div>\"

                                        table += \"</div>\"
                                        table += \"</div>\"


                                        table += \"</div>\"
                                    }

                                    table += \"<div class='container-fluid'>\"
                                    table += \"<div class='table-responsive'>\"

                                table += \"<table id='viewData no_room table_container_material' class='table dataTable compact display table-bordered table-condensedx'>\"
                                table += \"<thead>\"
                                table += \"<tr>\"
                                table += \"<th>No</th>\"
                                table += \"<th>Bahan baku</th>\"
                                table += \"<th>UoM</th>\"

                                if(showNilai){
//                                    table += \"<th class='text-center'>H.HPP<br>(satuan)</th>\"
//                                    table += \"<th class='text-center'>H.PPV<br>(satuan)</th>\"
                                    table += \"<th class='text-center'>H.BELI<br>(satuan)</th>\"
                                    table += \"<th class='text-center'>H.JUAL<br>(satuan)</th>\"
                                }

                                table += \"<th class='text-center'>Jml</th>\"

                                if(showNilai){
//                                    table += \"<th class='text-center'>Total<br>HPP</th>\"
//                                    table += \"<th class='text-center'>Total<br>PPV</th>\"
                                    table += \"<th class='text-center'>Total<br>H.BELI</th>\"
                                    table += \"<th class='text-center'>Total<br>H.JUAL</th>\"
                                    table += \"<th class='text-center'>R/L</th>\"
                                }

                                table += \"</tr>\"
                                table += \"</thead>\"
                                table += \"<tbody>\"

                                var no=0;
                                var total_saldo = 0;
                                var total_qty = 0;

                                var totHpp = 0;
                                var totPpv = 0;
                                var totJual = 0;
                                var totAng = 0;

                                var sumHpp = 0;
                                var sumPpv = 0;
                                var sumJual = 0;
                                var sumAng = 0;

                                jQuery.each(updated, function(a, b){
                                    no++;
                                    produk_dasar_nama = b.produk_dasar_nama;
                                    satuan = b.satuan;
                                    harga  = b.harga*1>0 ? b.harga*1 : 0;
                                    nilai  = b.nilai*1>0 ? b.nilai*1 : 0;
                                    jml    = b.jml;
                                    saldo  = b.saldo;

                                    hpp  = b.hrg_hpp*1> 0 ? b.hrg_hpp*1 : 0;
                                    ppv  = b.hrg_ppv*1> 0 ? b.hrg_ppv*1 : 0;
                                    jual = b.hrg_jual*1> 0 ? b.hrg_jual*1 : 0;

                                    table += \"<tr>\"
                                    table += \"<td>\"+no+\"</td>\"
                                    table += \"<td>\"+produk_dasar_nama+\"</td>\"
                                    table += \"<td>\"+satuan+\"</td>\"

                                    if(showNilai){
//                                        table += \"<td class='bg-limex text-right'>\"+addCommas(hpp)+\"</td>\"
//                                        table += \"<td class='bg-infox text-right'>\"+addCommas(ppv)+\"</td>\"
                                        table += \"<td class='bg-infox text-right'>\"+addCommas(jual)+\"</td>\"
                                        table += \"<td class='bg-infox text-right'>\"+addCommas(harga)+\"</td>\"
                                    }

                                    table += \"<td class='text-bold text-center'>\"+addCommas(jml)+\"</td>\"

                                    if(showNilai){
//                                        table += \"<td class='bg-greenx text-right'>\"+addCommas(hpp*jml)+\"</td>\"
//                                        table += \"<td class='bg-infox text-right'>\"+addCommas(ppv*jml)+\"</td>\"
                                        table += \"<td class='bg-infox text-right'>\"+addCommas(jual*jml)+\"</td>\"
                                        table += \"<td class='bg-infox text-right'>\"+addCommas(harga*jml)+\"</td>\"
                                        table += \"<td class='text-right'>\"+addCommas( (harga*jml)-(jual*jml))+\"</td>\"
                                    }

                                    table += \"</tr>\"

                                    total_saldo += saldo*1;
                                    total_qty   += jml*1;

                                    totHpp  += hpp;
                                    totPpv  += ppv;
                                    totJual += jual;
                                    totAng  += harga;

                                    sumHpp  += hpp*jml;
                                    sumPpv  += ppv*jml;
                                    sumJual += jual*jml;
                                    sumAng  += harga*jml;
                                })

                                table += \"</tbody>\"

                                table += \"<tfoot class='bg-gray'>\"
                                table += \"<tr>\"
                                table += \"<th>-</th>\"
                                table += \"<th>-</th>\"
                                table += \"<th>-</th>\"

                                if(showNilai){
//                                    table += \"<th class='text-right'>\"+addCommas(totHpp)+\"</th>\"
//                                    table += \"<th class='text-right'>\"+addCommas(totPpv)+\"</th>\"
                                    table += \"<th class='text-right'>\"+addCommas(totJual)+\"</th>\"
                                    table += \"<th class='text-right'>\"+addCommas(totAng)+\"</th>\"
                                }

                                    table += \"<th class='text-center'>\"+addCommas(total_qty)+\"</th>\"

                                if(showNilai){
//                                    table += \"<th class='text-right'>\"+addCommas(sumHpp)+\"</th>\"
//                                    table += \"<th class='text-right'>\"+addCommas(sumPpv)+\"</th>\"
                                    table += \"<th class='text-right'>\"+addCommas(sumJual)+\"</th>\"
                                    table += \"<th class='text-right'>\"+addCommas(sumAng)+\"</th>\"
                                    table += \"<th class='text-right'>\"+addCommas((sumAng)-(sumJual))+\"</th>\"
                                }

                                table += \"</tr>\"
                                table += \"</tfoot>\"

                                table += \"</table>\"
                                table += \"</div>\"
                                table += \"</div>\"
                                table += \"</div>\"

                                $('#container_material').html(table);

                                var table_material = $('#table_container_material').DataTable({
                                    dom: 'Bfrtip',
                                    buttons: [
                                        {
                                            text: 'Show Hide Column',
                                            extend: 'colvis',
                                            postfixButtons: ['colvisRestore']
                                        }
                                    ],
                                    ordering: false,
                                    searching: false,
                                    info: false,
                                    paging: false,
                                    stateSave: true
                                });

                                table_material.on('buttons-action', function ( e, buttonApi, dataTable, node, config ) {
                                    //console.log( 'Button '+buttonApi.text()+' was activated' );
                                    table_material.draw()
                                });

                                if( $('#table_container_material thead tr th') ){
                                    var header_text = $('#table_container_material thead tr th');
                                    var custom_btn = ''
                                    jQuery.each(header_text, function(a, b){
                                        if(a>0){
                                            if(custom_btn == ''){
                                                custom_btn += \"<a style='cursor: pointer;' class='toggle-vis text-uppercase' data-column='\"+a+\"'>\"+$(b).text()+\"</a>\"
                                            }
                                            else{
                                                custom_btn += \"- <a style='cursor: pointer;' class='toggle-vis text-uppercase' data-column='\"+a+\"'>\"+$(b).text()+\"</a>\"
                                            }
                                        }
                                    });
                                    $('.list_showhidecolumn').html(custom_btn);
                                }

                                if( $('.list_showhidecolumn') ){
                                    document.querySelectorAll('a.toggle-vis').forEach((el) => {
                                        el.addEventListener('click', function (e) {
                                            e.preventDefault();
                                            let columnIdx = e.target.getAttribute('data-column');
                                            let column = table_material.column(columnIdx);
                                            // Toggle the visibility
                                            column.visible(!column.visible());
                                            if( table_material.draw() ){
                                                //console.log('redraw table_material');
                                            }
                                        });
                                    });
                                }
                            }
                        })
                    });
//                    $('#sub_fase').trigger('change');
                }

            </script>";

        }

        //region option material
        $optionmaterial = "<select id='option_material' data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" >";
        $optionmaterial .= "<option value=''>-- silahkan pilih --</option>";
        $optionmaterial .= "<option value='1'>Material</option>";
        $optionmaterial .= "<option value='2'>Non material</option>";
        $optionmaterial .= "</select >";

        $optionmaterial .= "<script>
                    $('#option_material').on('change', function(){
                        var values = $(this).val();
                        switch(values){
                            case '1':
                                console.log('masuk case ' + values);
                                $.ajax({
                                    url: '$selector$produkID?key=material&value='+encodeURI(values),
                                    success: function(r_fase_id){
                                        var hsl = JSON.parse(r_fase_id);
                                        var showNilai = $showNilai*1;
                                        var table = ''
                                            table += \"<table class='table dataTable compact display table-bordered table-condensedx'>\"
                                            table += \"<thead>\"
                                            table += \"<tr>\"
                                            table += \"<th>No</th>\"
                                            table += \"<th>Bahan baku</th>\"
                                            table += \"<th>Satuan</th>\"

                                            if(showNilai){
                                                table += \"<th>Harga Beli(satuan)</th>\"
                                                table += \"<th>Harga Jual(satuan)</th>\"
                                            }

                                            table += \"<th>Jml</th>\"

                                            if(showNilai){
                                                table += \"<th>Subtotal</th>\"
                                            }

                                            table += \"</tr>\"
                                            table += \"</thead>\"
                                            table += \"<tbody>\"

                                            var no=0;
                                            var total_saldo = 0
                                            var total_qty = 0
                                            jQuery.each(hsl, function(a, b){
                                                no++;
                                                produk_dasar_nama = b.produk_dasar_nama
                                                satuan = b.satuan
                                                harga = b.harga
                                                nilai = b.nilai
                                                jml = b.jml
                                                saldo = b.saldo

                                                table += \"<tr>\"
                                                table += \"<td>\"+no+\"</td>\"
                                                table += \"<td>\"+produk_dasar_nama+\"</td>\"
                                                table += \"<td>\"+satuan+\"</td>\"

                                                if(showNilai){
                                                    table += \"<td class='text-right'>\"+addCommas(harga)+\"</td>\"
                                                    table += \"<td class='text-right'>\"+addCommas(nilai)+\"</td>\"
                                                }

                                                table += \"<td class='text-right'>\"+addCommas(jml)+\"</td>\"

                                                if(showNilai){
                                                    table += \"<td class='text-right'>\"+addCommas(saldo)+\"</td>\"
                                                }

                                                table += \"</tr>\"

                                                total_saldo += saldo*1
                                                total_qty += jml*1
                                            })

                                            table += \"</tbody>\"

                                            table += \"<tfoot>\"
                                            table += \"<tr>\"
                                            table += \"<th>-</th>\"
                                            table += \"<th>-</th>\"
                                            table += \"<th>-</th>\"

                                            if(showNilai){
                                                table += \"<th>-</th>\"
                                                table += \"<th>-</th>\"
                                            }

                                            table += \"<th class='text-right'>\"+addCommas(total_qty)+\"</th>\"

                                            if(showNilai){
                                                table += \"<th class='text-right'>\"+addCommas(total_saldo)+\"</th>\"
                                            }

                                            table += \"</tr>\"
                                            table += \"</tfoot>\"

                                            table += \"</table>\"

                                        $('#container_material').html(table)
                                    }
                                })
                            break;
                            default:
                                console.log('masuk case KOSONG');
                                $('#container_material').html('')
                            break;
                        }
                    });
                </script>";

        //endregion

        //region option progress tasklist
        $progresTask = "";
        if (count($progresTaklist) > 0) {
            $kol = "progress_id";
            $progresTask .= "<select data-style=\"btn btn-sm btn-primary\" data-width='100%' class=\"selectpicker\" data-live-search=\"true\" onchange=\"$('#input_temp').load('$selector" . "$produkID?key=progress_id&value='+encodeURI(this.value)+'$targetResult');\">";
            $progresTask .= "<option value='0'>-- silahkan pilih --</option>";
            foreach ($progresTaklist as $progresTaklist_0) {
                $pid = $produkID;
                $selected = isset($sessionData[$pid][$kol]) && ($sessionData[$pid][$kol] = $progresTaklist_0->id) ? "selected" : "";
                $progresTask .= "<option $selected value='" . $progresTaklist_0->id . "'>" . $progresTaklist_0->nama . " </option>";
            }
            $progresTask .= "</select>";
        }
        //endregion

        $project = "";
        if (!empty($masterProject) && count((array)$masterProject) > 0) {
            $project .= "<table line='" . __LINE__ . "' class='table dataTable compact display table-bordered table-condensed'>";
            foreach ($masterProjectField as $key => $label) {
                $mpVal = $masterProject->$key;
                $project .= "<tr>";
                $project .= "<td width='1%' class='text-bold'>$label</td>";
                $project .= "<td class='text-right' data-key='$key' data-value='$mpVal'>" . formatField($key, $masterProject->$key) . "</td>";
                $project .= "</tr>";
            }
            $project .= "</table>";
        }

        //==================== REGION viewPenggunaanMaterial =======================
        //==========================================================================
        //==========================================================================

        $viewPenggunaanMaterial = "";

        $tombol_show_rupiah = "";

        if (count($penggunaan_material) > 0) {
//            $showRupiah = 1;
//            if($showRupiah){

            $tombol_show_rupiah = "
                <button id='toggleRp' style='margin-right: 20px;' class='btn btn-sm btn-warning $show_rp_button'>
                      Tampilkan RP
                    </button>
                ";
//            }

            $viewPenggunaanMaterial .= "<style></style>";

            $viewPenggunaanMaterial .= "
                <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;'>
                  <h5 style='margin:0;'>INFORMASI PENGGUNAAN MATERIAL & SUPPLIES</h5>
                  <div>
                  
                  $tombol_show_rupiah

                    <label style='margin-right:5px;'>Filter Jenis:</label>
                    <select id='filterJenis' class='form-control input-sm' style='width:150px; display:inline-block;'>
                      <option value=''>Semua Jenis</option>
                      <option value='Biaya'>Biaya</option>
                      <option value='Produk'>Produk</option>
                    </select>
                  </div>
                </div>";

            $viewPenggunaanMaterial .= "<table line='" . __LINE__ . "' id='tblPenggunaanMaterial' class='table dataTable compact display table-bordered table-condensed'>";
            $viewPenggunaanMaterial .= "
                    <thead>
                      <tr class=\"text-center\">
                        <th rowspan=\"2\" width=\"1%\">#</th>
                        <th rowspan=\"2\" width=\"1%\">KODE</th>
                        <th rowspan=\"2\" width=\"1%\">Nama Material / Biaya</th>
                        <th rowspan=\"2\" width=\"1%\">Jenis</th>
                    
                        <th id=\"gh_komposisi\" class='text-center' colspan=\"2\">Komposisi</th>
                        <th id=\"gh_digunakan\" class='text-center' colspan=\"2\">Digunakan</th>
                        <th id=\"gh_retur\" class='text-center' colspan=\"2\">Retur</th>
                        <th id=\"gh_sisa\" class='text-center' colspan=\"2\">Sisa</th>
                    
                        <th rowspan=\"2\" width=\"1%\">%</th>
                      </tr>
                      <tr class=\"text-center\">
                        <th class=\"text-center\">Qty</th>
                        <th class=\"text-center bg-warning view_rp\">RP</th>
                    
                        <th class=\"text-center\">Qty</th>
                        <th class=\"text-center bg-warning view_rp\">RP</th>
                    
                        <th class=\"text-center\">Qty</th>
                        <th class=\"text-center bg-warning view_rp\">RP</th>
                    
                        <th class=\"text-center\">Qty</th>
                        <th class=\"text-center bg-warning view_rp\">RP</th>
                      </tr>
                    </thead>

                            <tbody>
                        ";

            $total_komposisi = 0;
            $total_komposisi_rp = 0;
            $total_digunakan = 0;
            $total_digunakan_rp = 0;
            $total_return = 0;
            $total_return_rp = 0;
            $total_sisa = 0;
            $total_sisa_rp = 0;
            $total_persen = 0;
            $no = 1;

            foreach ($penggunaan_material as $row) {
                $persen = 0;
                if ($row->total_komposisi > 0) {
                    // Progress = Alokasi (Digunakan) / Total Komposisi
                    // Retur adalah hasil dari alokasi, jadi tidak mengurangi progress penyelesaian.
                    $persen = ($row->total_digunakan / $row->total_komposisi) * 100;
                    $persen = round($persen, 1);
                }

                // Fallback agar kolom Sisa RP tidak nol saat Sisa Qty masih ada
                // (mis. ketika sumber harga penggunaan belum terbentuk).
                $sisaRp = isset($row->sisa_rp) ? ($row->sisa_rp * 1) : 0;
                if ($sisaRp == 0 && ($row->sisa * 1) != 0) {
                    $sisaRp = ($row->sisa * 1) * ($row->harga * 1);
                }
//                $persen = 99;
                // Warna progress bar
                if ($persen >= 90) $color = 'success';
                elseif ($persen >= 50) $color = 'warning';
                else $color = 'danger';

                $nama = $row->produk_dasar_nama;
                $kode = $row->produk_dasar_id;
                $ellipsis = (strlen($nama) > 15) ? substr($nama, 0, 15) . '...' : $nama;

                $viewPenggunaanMaterial .= "<tr>";
                $viewPenggunaanMaterial .= "<td class='text-center'>{$no}</td>";
                $viewPenggunaanMaterial .= "<td>{$kode}</td>";
                $viewPenggunaanMaterial .= "<td>{$ellipsis}</td>";
                $viewPenggunaanMaterial .= "<td class='text-center'>" . ucfirst($row->jenis) . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right'>" . number_format($row->total_komposisi, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right bg-warning xhidden view_rp'>" . number_format($row->total_komposisi_rp, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right'>" . number_format($row->total_digunakan, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right bg-warning xhidden view_rp'>" . number_format($row->total_digunakan_rp, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right'>" . number_format($row->total_return, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right bg-warning xhidden view_rp'>" . number_format($row->total_return_rp, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right'>" . number_format($row->sisa, 0, ',', '.') . "</td>";
                $viewPenggunaanMaterial .= "<td class='text-right bg-warning xhidden view_rp'>" . number_format($sisaRp, 0, ',', '.') . "</td>";

                // Kolom persentase dengan bar
                // warna progress bar sesuai nilai persen
                if ($persen >= 98) {
                    $barClass = 'progress-bar-success';  // hijau
                    $label = 'Selesai';
                }
                elseif ($persen >= 50) {
                    $barClass = 'progress-bar-warning';  // kuning
                    $label = 'Progres';
                }
                else {
                    $barClass = 'progress-bar-danger';   // merah
                    $label = 'Awal';
                }

                // progress bar
                $viewPenggunaanMaterial .= "
                    <td class='text-right'>
                        <div class='progress active' style=''>
                            <div class='progress-bar {$barClass} progress-bar-striped'
                                role='progressbar'
                                aria-valuenow='{$persen}'
                                aria-valuemin='0'
                                aria-valuemax='100'
                                style='width: {$persen}%'>
                                <span class='text-bold text-auto' style='font-size:10px;'>{$persen}% {$label}</span>
                            </div>
                        </div>
                    </td>";

                $viewPenggunaanMaterial .= "</tr>";

                $total_komposisi += isset($row->total_komposisi) ? (float)$row->total_komposisi : 0;
                $total_komposisi_rp += isset($row->total_komposisi_rp) ? (float)$row->total_komposisi_rp : ((isset($row->total_komposisi) ? (float)$row->total_komposisi : 0) * (isset($row->harga) ? (float)$row->harga : 0));
                $total_digunakan += isset($row->total_digunakan) ? (float)$row->total_digunakan : 0;
                $total_digunakan_rp += isset($row->total_digunakan_rp) ? (float)$row->total_digunakan_rp : ((isset($row->total_digunakan) ? (float)$row->total_digunakan : 0) * (isset($row->harga) ? (float)$row->harga : 0));
                $total_return += isset($row->total_return) ? (float)$row->total_return : 0;
                $total_return_rp += isset($row->total_return_rp) ? (float)$row->total_return_rp : ((isset($row->total_return) ? (float)$row->total_return : 0) * (isset($row->harga) ? (float)$row->harga : 0));
                $total_sisa += isset($row->sisa) ? (float)$row->sisa : 0;
                $total_sisa_rp += isset($sisaRp) ? (float)$sisaRp : 0;
                $total_persen += isset($persen) ? (float)$persen : 0;
                $no++;
            }

            // Total keseluruhan
            $viewPenggunaanMaterial .= "
        </tbody>
<tfoot>
    <tr class='text-right text-bold'>
        <td class='text-center'>-</td>
        <td class='text-center'>-</td>
        <td class='text-center'>-</td>
        <td class='text-center'>TOTAL</td>

        <td>" . number_format($total_komposisi, 0, ',', '.') . "</td>
        <td class='text-right bg-warning view_rp'>" . number_format($total_komposisi_rp, 0, ',', '.') . "</td>

        <td>" . number_format($total_digunakan, 0, ',', '.') . "</td>
        <td class='text-right bg-warning view_rp'>" . number_format($total_digunakan_rp, 0, ',', '.') . "</td>

        <td>" . number_format($total_return, 0, ',', '.') . "</td>
        <td class='text-right bg-warning view_rp'>" . number_format($total_return_rp, 0, ',', '.') . "</td>

        <td>" . number_format($total_sisa, 0, ',', '.') . "</td>
        <td class='text-right bg-warning view_rp'>" . number_format($total_sisa_rp, 0, ',', '.') . "</td>

        <td>" . ($total_komposisi_rp > 0 ? number_format(($total_digunakan_rp / $total_komposisi_rp)*100, 1, ',', '.') : '0') . "</td>
    </tr>
</tfoot>

    ";

            $viewPenggunaanMaterial .= "</table>";

            // Inject JS DataTables
            $viewPenggunaanMaterial .= "
    <script>
    $(document).ready(function() {

    var rpCols = [5, 7, 9, 11];

    var table = $('#tblPenggunaanMaterial').DataTable({
        paging: false,
        searching: true,
        info: false,
        ordering: false,
        scrollX: true,
        dom: 't',
        autoWidth: false,
        orderCellsTop: true,
        columnDefs: [
            { targets: rpCols, visible: false }
        ]
    });

    // Filter Jenis (kolom index 2)
    $('#filterJenis').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    // Toggle RP (pakai DataTables, jangan pakai class hidden)
    $('#toggleRp').on('click', function(){
        var show = !table.column(rpCols[0]).visible();

        rpCols.forEach(function(i){
            table.column(i).visible(show, false);
        });

        table.columns.adjust().draw(false);

        $(this).text(show ? 'Sembunyikan RP' : 'Tampilkan RP');
    });

});

    </script>";

        }
        else {
            $viewPenggunaanMaterial .= "<p class='text-center text-muted'><em>Tidak ada data penggunaan material.</em></p>";
        }

        //==================== END REGION viewPenggunaanMaterial =======================
        //==============================================================================
        //==============================================================================


        //region active tasklist
        $taskListData = "";

        $taskListData .= "
                    <div class='box-header table-responsive'>
                            <div class='col-xs-12 col-md-4 col-lg-4 no-padding'>
                        <h3>INFORMASI DETAIL PROJECT</h3>
                        $project
                    </div>
                    <div style='padding-left:50px !important;' class='col-xs-12 col-md-8 col-lg-8 no-padding'>
                        $viewPenggunaanMaterial
                    </div>
                    </div>
                ";

        if (count($taskistProject) > 0) {

            if($produkID=="qc"){
                $taskListData .= "
                    <div class=\"box-header\"><h3><i class=\"fa fa-table\"></i> Tasklist Menunggu Proses QC <div class=\"btn btn-sm btn-info pull-right\" onclick=\"qcProject();\"><i class=\"fa fa-refresh\"></i> REFRESH</div></h3></div>
                ";
                $order     = "order: [2, 'asc']";
                $searching = "searching: true";
                $paging    = "paging: true";
                $info      = "info: true";

                $custButton = "
                    dom: 'Bfrtip',
                    buttons: [
                      {
                        extend: 'excelHtml5',
                        text: '<i class=\"fa fa-file-excel-o\"></i> Export Excel',
                        className: 'btn btn-success btn-sm'
                      },
                      {
                        text: '<i class=\"fa fa-filter\"></i> Tampilkan Yang Telat',
                        className: 'btn btn-warning btn-sm',
                        action: function () {
                          table.column(2).search('Terlambat').draw();
                        }
                      },
                      {
                        text: '<i class=\"fa fa-undo\"></i> Reset Filter',
                        className: 'btn btn-default btn-sm',
                        action: function () {
                          table.search('').columns().search('').draw();
                        }
                      }
                    ],
                ";

            }
            else{

                $searching = "searching: false";
                $paging    = "paging: false";
                $info      = "info: false";
                $order     = "order: [10, 'desc']";
                $custButton = "";
            }

            $columnDefs = "
                columnDefs: [{
                    targets: [4, 7, 11],
                    visible: false
                },
                {
                    targets: 0, width: '30px'
                },
                {
                    targets: 1, // dtime
                    width: '110px',
                    render: function ( data, type, row, meta ) {
                        if (!data) return '-';
                        var d = top.moment(data, 'YYYY-MM-DD HH:mm:ss');
                        return d.format('DD MMM YYYY HH:mm') + '<br><small><i>' + d.fromNow() + '</i></small>';
                    }
                },
                {
                    targets: 2, // no_spk
                    width: '150px',
                    render: function ( data, type, row, meta ) {
                        if(!data) return '-';
                        var badgeSpk = '';
                        if (data.indexOf('SPK-TMB') !== -1) {
                            badgeSpk = \"<span class='label bg-purple' style='font-size:9px;margin-bottom:2px;display:inline-block;'><i class='fa fa-plus-circle'></i> SPK TAMBAHAN</span><br>\";
                        } else if (data.indexOf('SPK-INT') !== -1) {
                            badgeSpk = \"<span class='label label-primary' style='font-size:9px;margin-bottom:2px;display:inline-block;'><i class='fa fa-file-text-o'></i> SPK UTAMA</span><br>\";
                        }
                        return badgeSpk + \"<div class='td-ellipsis' title='\"+data+\"'><b>\" + data + \"</b></div>\";
                    }
                },
                {
                    targets: 3, // waktu jadwal
                    width: '110px',
                    render: function ( data, type, row, meta ) {
                        var start_raw = data ? top.moment(data, 'YYYY-MM-DD').format('DD MMM YYYY') : '-';
                        var end_raw = row[4] ? top.moment(row[4], 'YYYY-MM-DD').format('DD MMM YYYY') : '-';
                        var start = data ? start_raw + '<br><r>'+top.moment(data, 'YYYYMMDD').fromNow()+'</r>' : '-';
                        var end = row[4] ? end_raw + '<br><r>'+top.moment(row[4], 'YYYYMMDD').fromNow()+'</r>' : '-';
                        return start + '<br><b>s/d</b><br>' + end;
                    }
                },
                {
                    targets: 5, // konsumen
                    width: '140px',
                    render: function ( data, type, row, meta ) {
                        if(!data) return '-';
                        return \"<div class='td-ellipsis' title='\"+data+\"'>\" + data + \"</div>\";
                    }
                },
                {
                    targets: 6, // tugas & WO (+ paket dari row[7])
                    width: '180px',
                    render: function ( data, type, row, meta ) {
                        var fullText = data + (row[7] ? ' | ' + row[7] : '');
                        var projectHtml = '';
                        if (row[12] && typeof row[12] === 'object' && row[12]._project_id && row[12]._project_nama) {
                            var pUrl = '" . MODUL_PATH . "MasterData/index/' + row[12]._project_id;
                            projectHtml = \"<div class='td-ellipsis' style='margin-bottom:3px;'><a href='\" + pUrl + \"' target='_blank' class='text-bold text-primary' title='Buka Project: \" + row[12]._project_nama + \"' style='font-size:11px; text-decoration:none;'><i class='fa fa-folder-open text-yellow'></i> \" + row[12]._project_nama + \" <i class='fa fa-external-link' style='font-size:9px;'></i></a></div>\";
                        }
                        return projectHtml + \"<div class='td-ellipsis' title='\"+fullText+\"'><b>\"+ data +\"</b></div><small><i class='fa fa-dropbox text-navi'></i> <span class='text-bold text-uppercase text-navi td-ellipsis' title='\"+row[7]+\"' style='font-size:11px;'>\"+ row[7] +\"</span></small>\";
                    }
                },
                {
                    targets: 8, // pelaksana
                    width: '160px',
                    render: function ( data, type, row, meta ) {
                        if(!data) return '-';
                        var badgeType = '';
                        var statusBiayaHtml = '';
                        if (row[12] && typeof row[12] === 'object' && row[12]._type_pelaksana) {
                            var tp = row[12]._type_pelaksana;
                            var noSpk = (row[12]._no_spk) ? row[12]._no_spk : (row[2] ? row[2] : '');
                            var clickAttr = noSpk ? \" onclick=\\\"var fn = window.openModalSwitchPelaksana || (typeof top !== 'undefined' && (top.openModalSwitchPelaksanaGlobal || top.openModalSwitchPelaksana)) || (typeof parent !== 'undefined' && (parent.openModalSwitchPelaksanaGlobal || parent.openModalSwitchPelaksana)); if(typeof fn === 'function'){ fn('\"+noSpk+\"'); } else if(typeof openModalSwitchPelaksana === 'function'){ openModalSwitchPelaksana('\"+noSpk+\"'); } return false;\\\" title='Klik untuk ubah pelaksana / tipe'\" : \"\";
                            var pointerStyle = \"cursor:pointer; transition:all 0.2s ease; box-shadow:0 1px 3px rgba(0,0,0,0.15);\";
                            if (tp == '11' || tp == 'internal') {
                                if (row[12]._is_group == 1) {
                                    badgeType = \"<div style='margin-top:2px;'><span class='badge bg-purple btn-switch-pelaksana' style='background-color:#605ca8;color:#fff;font-size:9px;padding:3px 6px;\"+pointerStyle+\"' \"+clickAttr+\"><i class='fa fa-users' style='font-size:8px;margin-right:2px;'></i> GROUP PELAKSANA</span></div>\";
                                } else {
                                    badgeType = \"<div style='margin-top:2px;'><span class='badge bg-green btn-switch-pelaksana' style='font-size:9px;padding:3px 6px;\"+pointerStyle+\"' \"+clickAttr+\"><i class='fa fa-pencil' style='font-size:8px;margin-right:2px;'></i> INTERNAL</span></div>\";
                                }
                            } else if (tp == '22' || tp == 'subkon' || tp == 'external') {
                                badgeType = \"<div style='margin-top:2px;'><span class='badge btn-switch-pelaksana' style='background-color:#ff851b;color:#fff;font-size:9px;padding:3px 6px;font-weight:bold;\"+pointerStyle+\"' \"+clickAttr+\"><i class='fa fa-pencil' style='font-size:8px;margin-right:2px;'></i> PIHAK KE-3 / VENDOR / SUBKON</span></div>\";
                            }

                            // START OF COMPLETE REPEATED LOGIC
                            // Status biaya SPK di bawah badge pelaksana
                            if (row[12]._status_biaya_text) {
                                var sbText = row[12]._status_biaya_text;
                                var sbCode = row[12]._status_biaya_code ? row[12]._status_biaya_code : '';
                                var sbIcon = 'fa-info-circle';
                                var sbStyle = 'font-size:10.5px;margin-top:3px;line-height:1.3;white-space:normal !important;word-break:normal;word-wrap:break-word;display:block;';
                                var extraPostNo = '';
                                if (row[12]._post_biaya_no) {
                                    extraPostNo = \"<br><span style='font-size:9px;color:#0073b7;'><i class='fa fa-file-text-o'></i> \" + row[12]._post_biaya_no + \"</span>\";
                                }
                                if (sbCode === 'internal_no_biaya') {
                                    sbIcon = 'fa-minus-circle';
                                    sbStyle += 'color:#777;font-style:italic;';
                                } else if (sbCode === 'external_pending_qc') {
                                    sbIcon = 'fa-clock-o';
                                    sbStyle += 'color:#c07300;font-weight:600;';
                                } else if (sbCode === 'external_no_komponen') {
                                    sbIcon = 'fa-ban';
                                    sbStyle += 'color:#dd4b39;font-weight:600;';
                                } else if (sbCode === 'external_biaya_posted') {
                                    sbIcon = 'fa-check-circle';
                                    sbStyle += 'color:#00a65a;font-weight:bold;';
                                } else if (sbCode === 'external_biaya_failed') {
                                    sbIcon = 'fa-exclamation-triangle';
                                    sbStyle += 'color:#dd4b39;font-weight:bold;';
                                    var spkNoRaw = (row[12] && row[12]._no_spk) ? row[12]._no_spk : (row[2] ? row[2] : '');
                                    var spkClean = $('<div>').html(spkNoRaw).text().trim();
                                    extraPostNo += \"<div style='margin-top:4px;'><button type='button' class='btn btn-xs btn-danger' data-spk='\" + spkClean + \"' style='font-size:9.5px;padding:2px 6px;border-radius:3px;font-weight:600;' onclick='handleInlineRepostBiaya(this); event.stopPropagation();'><i class='fa fa-refresh'></i> Terbitkan Ulang Biaya</button></div>\";
                                } else {
                                    sbStyle += 'color:#666;';
                                }
                                statusBiayaHtml = \"<div style='\"+sbStyle+\"' title='Status Biaya: \"+sbText+\"'><i class='fa \"+sbIcon+\"'></i> \" + sbText + extraPostNo + \"</div>\";
                            }
                            // END OF COMPLETE REPEATED LOGIC
                        }
                        return \"<div class='td-ellipsis' title='\"+data+\"'><i class='fa fa-user-circle text-blue'></i> <span class='text-bold text-uppercase text-blue'>\"+ data +\"</span></div>\" + badgeType + statusBiayaHtml;
                    }
                },
                {
                    targets: 9, // keterangan
                    width: '160px',
                    render: function ( data, type, row, meta ) {
                        if(!data) return '-';
                        return \"<div class='td-ellipsis' title='\"+data+\"'>\" + data + \"</div>\";
                    }
                },
                {
                    targets: 10, //kolom status dan progress
                    width: '140px',
                    render: function ( data, type, row, meta ) {
                        var status_badge = \"\";
                        switch(data){
                            case '1': status_badge = \"<div class='btn btn-xs btn-warning btn-flatx' style='margin-bottom:5px;' disabled title='menunggu di proses oleh pelaksana'><i class='fa fa-hourglass'></i> menunggu di proses</div>\"; break;
                            case '2': status_badge = \"<div class='btn btn-xs btn-primary btn-flatx' style='margin-bottom:5px;' disabled title='sedang di proses oleh pelaksana'><i class='fa fa-gear fa-spin'></i> di proses</div>\"; break;
                            case '3': status_badge = \"<div class='btn btn-xs btn-success btn-flatx' style='margin-bottom:5px;' disabled title='pelaksanaan telah selesai'><i class='fa fa-check-circle'></i> selesai</div>\"; break;
                            default: status_badge = \"<span class='text-bold'>\"+data+\"</span>\"; break;
                        }

                        var progg = row[11]*1 > 0 ? row[11] : 0
                        var progg_stat = 'active'
                        var text='';
                        var bar_class = '';
                        var text_color = '';
                        var progg_wid = 0;

                        if(progg<10){ bar_class = 'danger'; text_color = 'black'; progg_wid = progg*1; }
                        else if(progg<50){ bar_class = 'warning'; text_color = 'red'; progg_wid = progg*1; }
                        else if(progg<80){ bar_class = 'primary'; text_color = 'auto'; progg_wid = progg*1; }
                        else if(progg==100){ bar_class = 'success'; text_color = 'auto'; text = 'Selesai'; progg_wid = progg*1; }
                        else if(progg==0){ bar_class = 'danger'; text_color = 'white'; progg_wid = 25; progg_stat = ''; }
                        else{ bar_class = ''; text_color = ''; progg_wid = progg*1; }

                        var btn  = \"\"
                        btn += \"<div class='progress \"+progg_stat+\"' style='margin-bottom:2px;height:18px;'>\"
                        btn += \"<div class='progress-bar progress-bar-\"+bar_class+\" progress-bar-striped' role='progressbar' aria-valuenow='\"+progg*1+\"' aria-valuemin='0' aria-valuemax='100' style='width: \"+progg_wid+\"%;line-height:18px;'>\"
                        btn += \"<span class='text-bold text-\"+text_color+\"'>\"+progg*1+\"% \"+text+\"</span>\"
                        btn += \"</div></div>\"

                        if(progg==100 && data==2){
                            var waitTime = '';
                            if(row[1]) {
                                var dTime = top.moment(row[1], 'YYYY-MM-DD HH:mm:ss');
                                waitTime = \" &bull; <span class='text-muted'><i class='fa fa-clock-o'></i> \" + dTime.fromNow() + \"</span>\";
                            }
                            return btn + \"<div class='text-center' style='font-size:11px;margin-top:2px;white-space:normal;line-height:1.2;'><span class='text-red text-bold'><i class='fa fa-hourglass-half'></i> Menunggu QC</span>\" + waitTime + \"</div>\";
                        }

                        return status_badge + btn;
                    }
                },
                {
                    targets: 12, //kolom Tindakan / tombol action
                    width: '120px',
                    render: function ( data, type, row, meta ) {
                       var btn = \"<div class='btn-group-vertical'>\"
                       if (data && typeof data === 'object') {
                           jQuery.each(data, function(a, b){
                                if(!b || typeof b !== 'object' || !b.typBtn) return;
                                var typBtn = b.typBtn;
                                var scriptID = b.scriptID;
                                var opt = b.opt;
                                var onclick = b.onclick;
                                var bclass = b.bclass;
                                var btnDisabled = b.btnDisabled;
                                var dataicon = b.dataicon;
                                var txt = b.txt;
                                var progress_id = b.progress_id;
                                var onclick_broke = b.onclick_broke;
                                var label = b.label!='' ? b.label : a;
                                var task_type = b.task_type || 'main';
                                if(typBtn=='n'){
                                    btn += \"<div data-task-type='\"+task_type+\"' data-id='\"+a+\"-\"+scriptID+\"' \"+opt+\" \"+onclick+\" class='btn btn-xs btn-flatx \"+bclass+\" text-left' \"+btnDisabled+\"><i class='\"+dataicon+\"'></i>&nbsp;&nbsp;\"+label+\" \"+txt+\"</div>\";
                                }
                                else{
                                    btn += \"<div data-task-type='\"+task_type+\"' data-id='\"+a+\"-\"+scriptID+\"' \"+opt+\" \"+onclick+\" class='btn btn-xs btn-flatx \"+bclass+\" text-left' \"+btnDisabled+\"><i class='\"+dataicon+\"'></i>&nbsp;&nbsp;\"+label+\" \"+txt+\"</div>\";
                                }
                           });
                       }
                       btn += \"</div>\"
                       return btn;
                    }
                }]
            ";

            $taskListData .= "<div class='box-body no-paddingx'>";
            $taskListData .= "<style>
                #daftar_tugas_ { table-layout: fixed; width: 100%; }
                #daftar_tugas_ th, #daftar_tugas_ td { word-wrap: break-word; overflow: hidden; }
                .td-ellipsis { max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; cursor: default; }
            </style>";
            $taskListData .= "<div class='table-responsive'>";
            $taskListData .= "<table id='daftar_tugas_' class='table dataTable compact display table-bordered table-condensed'>";
            $taskListData .= "<thead>";
            $taskListData .= "<tr>";
            $taskListData .= "<th>No</th>";
            foreach ($tasklistProjectField as $kry => $label) {
                $taskListData .= "<th>$label</th>";
            }
            $taskListData .= "</tr>";
            $taskListData .= "</thead>";
            $taskListData .= "<tbody></tbody>";

            $taskListData .= "</table>";
            $taskListData .= "</div>";
            $taskListData .= "</div>";

            $taskListData .= "<script>
                                $('#nomer_check').on('keyup', function(e){
                                    if(e.keyCode==13){
                                        checkActivatePreSpk()
                                    }
                                })
                                function checkActivatePreSpk(){
                                    var nomer_check = $('#nomer_check').val();
                                    //$('#nomer_check').val('');
                                    if(nomer_check!=''){
                                        nomer_check_f = btoa(nomer_check)
                                        $.ajax({
                                            url: '" . MODUL_PATH . "MasterData/pre_activate_task/$produkID/'+nomer_check_f,
                                            success: function(a){
                                                var arrData = JSON.parse(a)
                                                if(arrData.status){
                                                    if(arrData.data[0].progress_id==2){
                                                        //jika data sudah memiliki pelaksana
                                                        var html = ''
                                                        var pelaksana     = arrData.data[0].employee_nama
                                                        var spk           = arrData.data[0].no_spk
                                                        var pre_spk       = arrData.data[0].no_pre_spk
                                                        var no_kontrak    = arrData.data[0].no_kontrak
                                                        var nama_task     = arrData.data[0].nama
                                                        var notes         = arrData.data[0].nilai
                                                        var nilai_task    = arrData.data[0].nilai_sub_fase
                                                        var nilai_kontrak = arrData.data[0].nilai_kontrak
                                                        var date_process  = arrData.data[0].date_process
                                                        var komposisi = arrData.data[0].komposisi
                                                        if(komposisi){
                                                            jQuery.each(komposisi, function(a, b){
                                                                console.log('komposisi')
                                                                console.log(a)
                                                                console.log(b)
                                                            })
                                                        }
                                                        BootstrapDialog.show({
                                                            title:'ACTIVATION '+nama_task,
                                                            message: $('<div></div>').html(\"<div class='box box-header box-danger box-solid text-center'> TASK INI SUDAH DI PROSES OLEH <span class='text-uppercase text-bold text-red'>\"+pelaksana+\"</span> pada \"+date_process+\" dengan NO SPK <b>\"+spk+\"</b></div>\"),
                                                            draggable:true,
                                                            closable:true,
                                                            size:top.BootstrapDialog.SIZE_WIDE,
                                                            onhide: function(){
                                                                top.$('#nomer_check').focus();
                                                                console.error('hide');
                                                            },

                                                            buttons: [{
                                                                label: 'Aktivasi & Lanjutkan',
                                                                cssClass: 'btn-default',
                                                                title: 'Activasi Pre SPK',
                                                                icon: 'fa fa-send',
                                                                action: function(dialog) {
                                                                    swal('On Progress...', 'Tidak bisa dilanjut, karena Task ini sudah ada yang mengerjakan..', 'warning')
                                                                }
                                                            }, {
                                                                label: 'Batal',
                                                                cssClass: 'btn-danger',
                                                                action: function(dialog) {
                                                                    dialog.close();
                                                                }
                                                            }]
                                                        });
                                                    }
                                                    else if(arrData.data[0].progress_id==1){
                                                        //jika data belum ada pelaksana dan bisa di lanjutkan
                                                        var html = ''
                                                        var pre_spk       = arrData.data[0].no_pre_spk
                                                        var no_kontrak    = arrData.data[0].no_kontrak
                                                        var nama_task     = arrData.data[0].nama
                                                        var notes         = arrData.data[0].nilai
                                                        var nilai_task    = arrData.data[0].nilai_sub_fase
                                                        var nilai_kontrak = arrData.data[0].nilai_kontrak
                                                        var date_process  = arrData.data[0].date_process
                                                        var komposisi     = arrData.data[0].komposisi
                                                        var this_id       = arrData.data[0].id
                                                        var produk_id     = arrData.data[0].produk_id
                                                        var fase_id       = arrData.data[0].fase_id

                                                        html += \"<div style='font-size: 18px;' class='box box-header box-danger box-solid'>\"
                                                        html += \"<div class='col-md-12 no-padding'>\"

                                                        html += \"<div class='col-md-2 no-padding'>\"
                                                        html += \"<span class='text-bold'>Nama</span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-1'>\"
                                                        html += \"<span>: </span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-9 no-padding'>\"
                                                        html += \"<span>\"+nama_task+\"</span>\"
                                                        html += \"</div>\"

                                                        html += \"<div class='col-md-2 no-padding'>\"
                                                        html += \"<span class='text-bold'>No Pre SPK</span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-1'>\"
                                                        html += \"<span>: </span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-9 no-padding'>\"
                                                        html += \"<span>\"+pre_spk+\"</span>\"
                                                        html += \"</div>\"

                                                        html += \"<div class='col-md-2 no-padding'>\"
                                                        html += \"<span class='text-bold'>No Kontrak</span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-1'>\"
                                                        html += \"<span>: </span>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='col-md-9 no-padding'>\"
                                                        html += \"<span>\"+no_kontrak+\"</span>\"
                                                        html += \"</div>\"

                                                        html += \"</div>\"
                                                        html += \"</div>\"

                                                        if( typeof komposisi['room'] != 'undefined' ){
                                                            //separated room
                                                            html += \"<div class='box'>\"
                                                            html += \"<div class='box box-solid box-success'>\"
                                                            html += \"<div class='box-header text-bold text-uppercase'> \"+nama_task+\" </div>\"

                                                            jQuery.each(komposisi['room'], function(a, b){
                                                                var main_rid = b.room_id
                                                                html += \"<div style='margin-bottom: 25px;' class='container-fluid'>\"
                                                                html += \"<div class=''>\"
                                                                html += \"<table class='table dataTable table-bordered compact'>\"
                                                                html += \"<caption class='text-bold text-uppercase fa-2x'>\"+b.room_nama+\"</caption>\"
                                                                html += \"<thead>\"
                                                                html += \"<tr>\"
                                                                html += \"<th>No</th>\"
                                                                html += \"<th>Material</th>\"
                                                                html += \"<th>Jml</th>\"
                                                                html += \"<th>Satuan</th>\"
                                                                html += \"</tr>\"
                                                                html += \"</thead>\"
                                                                html += \"<tbody>\"
                                                                num_room=0;
                                                                jQuery.each(komposisi['produk_room'], function(a, b){
                                                                    if(main_rid==b.room_id){
                                                                        num_room++;
                                                                        html += \"<tr>\"
                                                                        html += \"<td>\"+num_room+\"</td>\"
                                                                        html += \"<td>\"+b.produk_dasar_nama+\"</td>\"
                                                                        html += \"<td>\"+b.jml+\"</td>\"
                                                                        html += \"<td>\"+b.satuan+\"</td>\"
                                                                        html += \"</tr>\"
                                                                    }
                                                                })
                                                                html += \"</tbody>\"
                                                                html += \"</table>\"
                                                                html += \"</div>\"
                                                                html += \"</div>\"
                                                            })
                                                            html += '</div>' //success
                                                            html += '</div>' //box
                                                        }
                                                        else{

                                                            //tanpa separated room
                                                            html += \"<div class='box'>\"
                                                            html += \"<div class='box box-solid box-success'>\"
                                                            html += \"<div class='box-header text-bold text-uppercase'> \"+nama_task+\" </div>\"

                                                            html += \"<div class='box-body'>\"
                                                            html += \"<table class='table dataTable table-bordered compact'>\"
                                                            html += \"<thead>\"
                                                            html += \"<tr>\"
                                                            html += \"<th>No</th>\"
                                                            html += \"<th>Material</th>\"
                                                            html += \"<th>Jml</th>\"
                                                            html += \"<th>Satuan</th>\"
                                                            html += \"</tr>\"
                                                            html += \"</thead>\"
                                                            html += \"<tbody>\"

                                                            num_room=0;
                                                            jQuery.each(komposisi['produk'], function(a, b){
                                                                num_room++;
                                                                html += \"<tr>\"
                                                                html += \"<td>\"+num_room+\"</td>\"
                                                                html += \"<td>\"+b.produk_dasar_nama+\"</td>\"
                                                                html += \"<td>\"+b.jml+\"</td>\"
                                                                html += \"<td>\"+b.satuan+\"</td>\"
                                                                html += \"</tr>\"
                                                            })

                                                            html += \"</tbody>\"
                                                            html += \"</table>\"

                                                            html += '</div>'
                                                            html += '</div>'
                                                            html += '</div>'

                                                        }

                                                        html += \"<SECTION>\"
                                                        html += \"  <DIV id='dropzone'>\"
                                                        html += \"    <FORM class='dropzone needsclick' id='demo-upload' action='/upload'>\"
                                                        html += \"      <DIV class='dz-message needsclick'>\"
                                                        html += \"        Seret photo ke dalam kotak ini atau klik upload.<BR>\"
                                                        html += \"        <SPAN class='note needsclick'>(untuk melanjutkan activasi Pre SPK,\"
                                                        html += \"        upload PHOTO lembar Pre SPK yang telah Anda Tanda Tangan.)</SPAN>\"
                                                        html += \"      </DIV>\"
                                                        html += \"    </FORM>\"
                                                        html += \"  </DIV>\"
                                                        html += \"</SECTION>\"

                                                        html += \"<DIV id='preview-template' style='display: none;'>\"
                                                        html += \"<DIV class='dz-preview dz-file-preview'>\"
                                                        html += \"<DIV class='dz-image'><IMG data-dz-thumbnail=''></DIV>\"
                                                        html += \"<DIV class='dz-details'>\"
                                                        html += \"<DIV class='dz-size'><SPAN data-dz-size=''></SPAN></DIV>\"
                                                        html += \"<DIV class='dz-filename'><SPAN data-dz-name=''></SPAN></DIV></DIV>\"
                                                        html += \"<DIV class='dz-progress'><SPAN class='dz-upload'\"
                                                        html += \"data-dz-uploadprogress=''></SPAN></DIV>\"
                                                        html += \"<DIV class='dz-error-message'><SPAN data-dz-errormessage=''></SPAN></DIV>\"
                                                        html += \"<div class='dz-success-mark'>\"
                                                        html += \"  <svg width='54px' height='54px' viewBox='0 0 54 54' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:sketch='http://www.bohemiancoding.com/sketch/ns'>\"
                                                        html += \"    <title>Check</title>\"
                                                        html += \"    <desc>Created with Sketch.</desc>\"
                                                        html += \"    <defs></defs>\"
                                                        html += \"    <g id='Page-1' stroke='none' stroke-width='1' fill='none' fill-rule='evenodd' sketch:type='MSPage'>\"
                                                        html += \"        <path d='M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z' id='Oval-2' stroke-opacity='0.198794158' stroke='#747474' fill-opacity='0.816519475' fill='#FFFFFF' sketch:type='MSShapeGroup'></path>\"
                                                        html += \"    </g>\"
                                                        html += \"  </svg>\"
                                                        html += \"</div>\"
                                                        html += \"<div class='dz-error-mark'>\"
                                                        html += \"  <svg width='54px' height='54px' viewBox='0 0 54 54' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:sketch='http://www.bohemiancoding.com/sketch/ns'>\"
                                                        html += \"      <title>error</title>\"
                                                        html += \"      <desc>Created with Sketch.</desc>\"
                                                        html += \"      <defs></defs>\"
                                                        html += \"      <g id='Page-1' stroke='none' stroke-width='1' fill='none' fill-rule='evenodd' sketch:type='MSPage'>\"
                                                        html += \"          <g id='Check-+-Oval-2' sketch:type='MSLayerGroup' stroke='#747474' stroke-opacity='0.198794158' fill='#FFFFFF' fill-opacity='0.816519475'>\"
                                                        html += \"              <path d='M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z' id='Oval-2' sketch:type='MSShapeGroup'></path>\"
                                                        html += \"          </g>\"
                                                        html += \"      </g>\"
                                                        html += \"  </svg>\"
                                                        html += \"</div>\"

                                                        var url_validasi_img = ''

                                                        BootstrapDialog.show({
                                                            title:'ACTIVATION '+nama_task,
                                                            message: $('<div></div>').html(html),
                                                            draggable:true,
                                                            closable:true,
                                                            size:top.BootstrapDialog.SIZE_WIDE,
                                                            buttons: [{
                                                                label: 'Aktivasi & Lanjutkan',
                                                                cssClass: 'btn-success',
                                                                title: 'Activasi Pre SPK',
                                                                icon: 'fa fa-send',
                                                                data: {
                                                                    js: 'btn-confirm',
                                                                    'user-id': '3'
                                                                },
                                                                action: function(dialog) {
                                                                    swal({
                                                                        title: nama_task,
                                                                        html: 'klik ok untuk melanjutkan...!!<br><br><img width=\'125\' src=\''+localStorage.url_validasi_img+'\'>',
                                                                    })
                                                                    .then( (res)=>{

                                                                        if(localStorage.url_validasi_img!=''){
                                                                            $.ajax({
                                                                                url: '" . MODUL_PATH . "MasterData/exe_activate_task',
                                                                                method: 'post',
                                                                                data: {id:this_id, produk_id:produk_id, pre_spk:pre_spk, fase_id:fase_id, pre_spk_validasi_img:localStorage.url_validasi_img},
                                                                                success: function(aaa){
                                                                                    var arrData = JSON.parse(aaa);
                                                                                    if(arrData.status){
                                                                                        swal('sukses', 'Pre SPK berhasil di aktivasi, anda bisa melakukan progress pada table dibawah.')
                                                                                        swal.enableLoading();
                                                                                        setTimeout(function(){
                                                                                            top.BootstrapDialog.closeAll();
                                                                                            swal.close();
                                                                                            setTimeout(function(){
                                                                                                //top.window.location.reload();
                                                                                            }, 200)
                                                                                        }, 500)
                                                                                    }
                                                                                    else{
                                                                                        swal('DEBUG:sukses', 'Pre SPK berhasil di aktivasi, anda bisa melakukan progress pada table dibawah.')
                                                                                        swal.enableLoading();
                                                                                        setTimeout(function(){
                                                                                            top.BootstrapDialog.closeAll();
                                                                                            swal.close();
                                                                                            setTimeout(function(){
                                                                                                //top.window.location.reload();
                                                                                            }, 200)
                                                                                        }, 500)
                                                                                    }
                                                                                }
                                                                            })
                                                                        }
                                                                        else{
                                                                            swal('link upload image rusak, silahkan hapus dan reupload photo');
                                                                        }


                                                                    })
                                                                }
                                                            }, {
                                                                label: 'Batal',
                                                                cssClass: 'btn-danger',
                                                                action: function(dialog) {
                                                                    dialog.close();
                                                                }
                                                            }],
                                                            onshown: function(){
                                                                top.$(\"button[data-js='btn-confirm']\").prop('disabled', true);

//                                                                console.log('this_id: ' + this_id);
//                                                                console.log('produk_id: ' + produk_id);

                                                                var dropzone = new Dropzone('#demo-upload', {
                                                                    acceptedFiles: '.jpg,.png,.jpeg',
                                                                    addRemoveLinks: true,
                                                                    maxFilesize: 10,
                                                                    maxFile: 1,
                                                                    parallelUploads: 2,
                                                                    thumbnailHeight: 120,
                                                                    thumbnailWidth: 120,
                                                                    filesizeBase: 1000,
                                                                    dictFileTooBig: 'File terlalu besar ({{filesize}}MiB). Max ukuran: {{maxFilesize}}MiB.',
                                                                    forceFallback: false, //untuk switch ke metode upload JADUL
                                                                    dictDefaultMessage: \"Klik atau Drag Drop File di sini untuk Upload\",
                                                                    dictRemoveFile: 'hapus',
                                                                    dictRemoveFileConfirmation: 'Anda yakin menghapus photo ini..??',
                                                                    dictInvalidFileType: \"Gagal Upload, Pastikan file berjenis gambar.\",
                                                                    url: '$linkUploadPhoto'+this_id+'/'+produk_id,
                                                                    thumbnail: function(file, dataUrl) {
                                                                        if (file.previewElement) {
                                                                          file.previewElement.classList.remove('dz-file-preview');
                                                                          var images = file.previewElement.querySelectorAll('[data-dz-thumbnail]');
                                                                          for (var i = 0; i < images.length; i++) {
                                                                            var thumbnailElement = images[i];
                                                                            thumbnailElement.alt = file.name;
                                                                            thumbnailElement.src = dataUrl;
                                                                          }
                                                                          setTimeout(function() { file.previewElement.classList.add('dz-image-preview'); }, 1);
                                                                        }
                                                                    },
                                                                    params(files, xhr, chunk) {
                                                                        $('.def-images').addClass('hidden');
                                                                        var uuid = files[0]['upload']['uuid'];
                                                                        var tmpImg = {}
//                                                                        console.log('uuid: '+ uuid);
                                                                        xhr.onreadystatechange = function() {
                                                                            if (xhr.readyState == XMLHttpRequest.DONE) {
                                                                                var callBack = JSON.parse(xhr.responseText)
                                                                                var id = callBack.id
                                                                                var produk_id = callBack.produk_id
                                                                                var cdn_url = callBack.uploaded.full_url

                                                                                tmpImg[uuid] = {
                                                                                    uuid,id,produk_id, cdn_url
                                                                                }

                                                                                localStorage.url_validasi_img = cdn_url

                                                                                top.$(\"button[data-js='btn-confirm']\").prop('disabled', false);

//                                                                                console.log('cdn_url: ' + cdn_url);
//                                                                                console.log('callBack: ');
//                                                                                console.log(callBack);
//                                                                                console.log('tmpImg: ');
//                                                                                console.log(tmpImg);

                                                                                //imgBankCtr(tmpImg,'','add')
                                                                                //imgCheckBtn(id,produk_id,'');
                                                                            }
                                                                        }
                                                                    },
                                                                    removedfile(file) {
                                                                        if (file.previewElement != null && file.previewElement.parentNode != null) {
                                                                            file.previewElement.parentNode.removeChild(file.previewElement);
                                                                        }
                                                                        var removeUuid = file.upload.uuid;

                                                                        localStorage.url_validasi_img = ''
                                                                        top.$(\"button[data-js='btn-confirm']\").prop('disabled', true);

                                                                        // imgBankCtr(removeUuid,'','del')
                                                                        // imgCheckBtn(this_id,produk_id,'');
                                                                    },
                                                                    error(file, message) {
                                                                        if (file.previewElement) {
                                                                              file.previewElement.classList.add('dz-error');
                                                                              if (typeof message !== 'string' && message.error) {
                                                                                    message = message.error;
                                                                              }
                                                                              for (let node of file.previewElement.querySelectorAll(
                                                                                    '[data-dz-errormessage]'
                                                                              )) {
                                                                                    node.textContent = message;
                                                                              }
                                                                        }
                                                                    },
                                                                });
                                                            },
                                                        });
                                                    }
                                                    else{

//                                                        var html = ''
//                                                        var pre_spk = arrData.data[0].no_pre_spk
//                                                        var no_kontrak = arrData.data[0].no_kontrak
//                                                        var nama_task = arrData.data[0].nama
//                                                        var notes = arrData.data[0].nilai
//                                                        var nilai_task = arrData.data[0].nilai_sub_fase
//                                                        var nilai_kontrak = arrData.data[0].nilai_kontrak
//                                                        var date_process = arrData.data[0].date_process
//                                                        var komposisi = arrData.data[0].komposisi
//                                                        if(komposisi){
//                                                            jQuery.each(komposisi, function(a, b){
//                                                                console.log('komposisi')
//                                                                console.log(b)
//                                                            })
//                                                        }
//                                                        BootstrapDialog.show({
//                                                            title:'ACTIVATION '+nama_task,
//                                                            message: $('<div></div>').html(\"<div class='box box-header box-danger box-solid text-center'> TASK INI SUDAH DI PROSES OLEH <span class='text-uppercase text-bold text-red'>\"+pelaksana+\"</span> pada \"+date_process+\" dengan NO SPK <b>\"+spk+\"</b></div>\"),
//                                                            draggable:true,
//                                                            closable:true,
//                                                            size:top.BootstrapDialog.SIZE_WIDE,
//                                                        });

                                                        console.log('TRAX MODE INI TIDAK ADA DALAM SKENARIO');
                                                        swal('TRAX MODE INI TIDAK ADA DALAM SKENARIO')
                                                    }
                                                }
                                                else{
                                                    swal('NOMER PRE SPK<br><b>' +nomer_check+ '</b> <br><r>TIDAK DI TEMUKAN...</r>');
                                                }
                                            }
                                        })
                                    }
                                    else{
                                        swal('harap mengisi nomor Pre SPK kemudian klik SEND.');
                                    }
                                }
                                top.moment.locale('id');
                            </script>
                            <script>
                                var dft_tugas = top.$('#daftar_tugas_').DataTable({
                                    serverSide: true,
                                    ajax: '" . MODUL_PATH . "MasterData/daftar_tugas/tasklist/$produkID',
                                    autoWidth: false,
                                    processing: true,
                                    $searching,
                                    $paging,
                                    $info,
                                    $order,
                                    $columnDefs,
                                    initComplete: function(a, b){
//                                        var pendingTask = b.pendingtask.length;
//                                        $('#pendingtask').html(pendingTask);
                                    }
                                });
                             </script>";

        }
        else {
            $taskListData .= "<table class='table dataTable compact display table-bordered table-condensed'>";
            $taskListData .= "<thead>";
            $taskListData .= "<tr>";
            $taskListData .= "<th>No</th>";
            foreach ($tasklistProjectField as $kry => $label) {
                $taskListData .= "<th>$label</th>";
            }
            $taskListData .= "</tr>";
            $taskListData .= "</thead>";
            $taskListData .= "<tbody>";
            $taskListData .= "<tr>";
            $taskListData .= "<td></td>";
            $taskListData .= "<td colspan='" . count($tasklistProjectField) . "' class='text-bold text-center'>Belum ada data penugasan</td>";
            $taskListData .= "</tr>";
            $taskListData .= "</tbody>";
            $taskListData .= "</table>";
        }
        //endregion

        if (count($workOrder) > 0) {
            $workOrder = "";
            $workOrder .= "<div class='blink text-bold text-info box-header'><h3>$workOrderTitle#</h3></div>";
            $workOrder .= "<div class='box-body no-paddingx table-responsivex'>";
            $workOrder .= "<form id='tasklist_form' name='tasklist_form' target='result' action='$addtaskLink?$targetResult'>";

            $workOrder .= "<div class=''>";
            $workOrder .= "<label class='box-title'>Pilih Project: &nbsp;</label>";
            $workOrder .= $formWork_order;
            $workOrder .= "</div>";

            $workOrder .= "<div id='sub_fase_selector' style='margin-top: 6px;' class='hidden'>";
            $workOrder .= "</div>";

            $workOrder .= "<div style='margin-top: 6px;' class='row'>";
            $workOrder .= "<div class='col-md-6'>";
            $workOrder .= "<label class=''>Tugas: &nbsp;</label>";

            $default_nama = isset($sessionData[$produkID]["nama"]) ? $sessionData[$produkID]["nama"] : "";

            $workOrder .= "<input id='in_tugas' type='text' class='form-control form-control-sm' value ='" . $default_nama . "'onmouseoutx=\"$('#input_temp').load('$selector" . "$produkID?key=nama&value='+encodeURI(this.value)+'$targetResult');\">";
            $workOrder .= "</div>";
            $workOrder .= "<div class='col-md-6' class='' stylex='right: 10px;'>";
            $workOrder .= "<label class=''>Keterangan: &nbsp;</label>";

            $defaultValue = isset($sessionData[$produkID]["nilai"]) ? $sessionData[$produkID]["nilai"] : "";

            $workOrder .= "<textarea id='in_keterangan' rows=\"5\" cols=\"33\" type='textarea' class='form-control' onmouseoutx=\"$('#input_temp').load('$selector" . "$produkID?key=nilai&value='+encodeURI(this.value)+'$targetResult');\">$defaultValue</textarea>";
            $workOrder .= "</div>";
            $workOrder .= "</div>";

            $workOrder .= "<div style='margin-top: 6px;' class=''>";
            $workOrder .= "<label>Pelaksana: &nbsp;</label>";
            $workOrder .= $formWork_tim;
            $workOrder .= "</div>";

            $workOrder .= "<div style='margin-top: 6px;' class='row'>";
            $workOrder .= "<div class='col-md-6'>";
            $workOrder .= "<div class='form-group'>";
            $workOrder .= "<label>Mulai: &nbsp;</label>";
            $workOrder .= "<div class='input-group date'>";
            $workOrder .= "<div class='input-group-addon'>";
            $workOrder .= "<i class='fa fa-calendar'></i>";
            $workOrder .= "</div>";

            $defaultStartDate = isset($sessionData[$produkID]["dtime_start"]) ? date("Y-m-d", strtotime($sessionData[$produkID]["dtime_start"])) : "";

            $workOrder .= "<input type='date' class='form-control date' placeholder='tenggat waktu' id='date_start' value='$defaultStartDate' onchange =\"$('#input_temp').load('$selector" . "$produkID?key=dtime_start&value='+encodeURI(this.value)+'$targetResult');\"> ";
            $workOrder .= "</div>";
            $workOrder .= "</div>";
            $workOrder .= "</div>";

            $workOrder .= "<div class='col-md-6'>";
            $workOrder .= "<div class='form-group'>";
            $workOrder .= "<label class='box-title'>Tenggat: &nbsp;</label>";
            $workOrder .= "<div class='input-group date'>";
            $workOrder .= "<div class='input-group-addon'>";
            $workOrder .= "<i class='fa fa-calendar'></i>";
            $workOrder .= "</div>";

            $defaultEndDate = isset($sessionData[$produkID]["dtime_end"]) ? date("Y-m-d", strtotime($sessionData[$produkID]["dtime_end"])) : "";

            $workOrder .= "<input type='date' class='form-control date' placeholder='tenggat waktu' id='date_end' value='$defaultEndDate' onchange =\"$('#input_temp').load('$selector" . "$produkID?key=dtime_end&value='+encodeURI(this.value)+'$targetResult');\"> ";
            $workOrder .= "</div>";
            $workOrder .= "</div>";
            $workOrder .= "</div>";

            $workOrder .= "<div id='container_material' class='col-md-12'>";
            $workOrder .= "&nbsp;";
            $workOrder .= "</div>";

            $workOrder .= "<div style='margin-top: 35px;' class='col-md-12'>";
            $workOrder .= "<button id='simpanTugas' type='button' class='btn btn-block btn-success btn-sm' cx_onclick=\"document.getElementById('tasklist_form').submit();\">Simpan</button>";
            $workOrder .= "</div>";

            $workOrder .= "</div>";

            $workOrder .= "</form>";
            $workOrder .= "</div>";

            $workOrder .= "<div id='input_temp'></div>";

            $workOrder .= "
            <script>\n

                function init_inTugas(){
                    $('#in_tugas').on('keyup', delay_v2(function(){
                        $('#input_temp').load('$selector" . "$produkID?key=nama&value='+encodeURI(this.value)+'');
                    }, 1200))
                }
                function init_inKeterangan(){
                    $('#in_keterangan').on('keyup', delay_v2(function(){
                        $('#input_temp').load('$selector" . "$produkID?key=nilai&value='+encodeURI(this.value)+'');
                    }, 1200))
                }

                init_inTugas();
                init_inKeterangan();
                function init_simpanTugas(){
                    top.$('#simpanTugas').off();
                    top.$('#simpanTugas').on('click', function(){
                        var id = $(this).attr('id');
                        var ev_aa = $(this).attr('cx_onclick');
                        var arr_input = $('.'+id);
                        var error = '';

                        var fase_id         = $('#fase_id option:selected').val();
                        var sub_fase        = $('#sub_fase option:selected').val();
                        var employee_id     = $('#employee_id option:selected').val();
                        var in_tugas        = $('#in_tugas').val();
                        var in_keterangan   = $('#in_keterangan').val();
                        var date_end        = $('#date_end').val();
                        var date_start      = $('#date_start').val();

                        if(fase_id*1 <= 0){
                            //console.error('waduh fase_id*1 > 0 yaitu: ' + fase_id);
                            error += '<div> <b>Perintah Kerja</b> Wajib di isi... </div>'
                        }

                        if(sub_fase*1 <= 0){
                            //console.error('waduh sub_fase*1 > 0 yaitu: ' + sub_fase);
                            error += '<div> <b>Sub Pekerjaan</b> Wajib di isi... </div>'
                        }

                        if(employee_id*1 <= 0){
                            //console.error('waduh employee_id*1 > 0 yaitu: ' + employee_id);
                            error += '<div> <b>Pelaksana</b> Wajib di pilih... </div>'
                        }

                        if(in_tugas==''){
                            //console.error('waduh in_tugas kosong');
                            error += '<div> <b>Tugas</b> Wajib di isi... </div>'
                        }

                        if(in_keterangan==''){
                            //console.error('waduh in_keterangan kosong');
                            error += '<div> <b>Keterangan</b> Wajib di isi... </div>'
                        }

                        if(date_end==''){
                            //console.error('waduh date_end kosong');
                            error += '<div> <b>Waktu Selesai</b> Wajib di isi... </div>'
                        }

                        if(date_start==''){
                            //console.error('waduh date_start kosong');
                            error += '<div> <b>Waktu Mulai</b> Wajib di isi... </div>'
                        }

                        if( error=='' ){
                            //bisa lanjut
                            $(this).prop('disabled', true).html('<i class=\"fa fa-spin fa-spinner\"></i> Sedang Menyimpan...');
                            top.open_holdon();
                            setTimeout(function(){
                            eval(ev_aa);
                            }, 50);
                        }
                        else{
                            swal('isian kurang', error, 'error');
                        }
                    });
                }

                init_simpanTugas();

            </script>

            ";

        }

        $projectDataDetails = "
            <div class='text-bold text-primary fa-2x text-center'>
                <u><i>TASK LIST TIMELINE</i></u> &nbsp;&nbsp;
                <span id='btnReloadTimeLine' class='btn btn-sm btn-warning'>
                    Reload &nbsp;&nbsp; <i class='fa fa-refresh'></i>
                </span>
            </div>

            <div style='overflow: hidden;'>
                <table id='tableTimeLine' style='overflow: hidden;' class='table dataTable compact table-bordered table-condensed'>
                    <thead>
                        <tr>
                            <th>Tgl</th>
                            <th>Ket</th>
                            <th>Pekerjaan</th>
                            <th>Person</th>
                            <th>Detail / Riwayat Aktivitas</th>
                            <!-- <th>Employee</th>
                            <th>Status</th>
                            <th>Status</th> -->
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <script>\n

                var timeline = $('#tableTimeLine').DataTable({
                    ajax: '$timelinetaskLink',
                    paging: false,
                    info: false,
                    processing: true,
                    searching: false,
                    ordering: false,
                    scrollCollapse: false,
                    scrollY: '200px',
                    scroller: {
                        serverWait: 500
                    },
                    columnDefs:[
                        {
                            targets: 0,
                            class: 'text-left',
                            width: '1%',
                            render: function(data, type, row, meta){
                                var input = ''
                                input += \"<span class='btn btn-xs'>\"+moment(data).format('YYYY-MM-DD HH:mm')+\"</span>\"
                                input += '<br><small><i><r>'+top.moment(data, 'YYYYMMDD HHmmss').fromNow()  + '</r></i></small>'
                                return input;
                            }
                        },
                        {
                            targets: 1,
                            class: 'text-left',
                            width: '1%',
                            render: function(data, type, row, meta){
                                var input = ''
                                if(data=='QC PROSES'){
                                    input += \"<span class='btn btn-xs bg-violet'>\"+data+\"</span>\"
                                }
                                else if(data=='create'){
                                    input += \"<span class='btn btn-xs bg-lime'>\"+data+\"</span>\"
                                }
                                else if(data=='update'){
                                    input += \"<span class='btn btn-xs bg-yellow'>\"+data+\"</span>\"
                                }
                                else if(data=='subtasklist'){
                                    input += \"<span class='btn btn-xs bg-blue'>\"+data+\"</span>\"
                                }
                                else if(data=='trashed_tmb'){
                                    input += \"<span class='btn btn-xs bg-red'>\"+data+\"</span>\"
                                }
                                else if(data=='trashed'){
                                    input += \"<span class='btn btn-xs bg-red'>\"+data+\"</span>\"
                                }
                                else if(data=='SWITCH_PELAKSANA'){
                                    input += \"<span class='btn btn-xs bg-orange text-bold' style='color:#fff;'><i class='fa fa-exchange'></i> ALIH PELAKSANA</span>\"
                                }
                                else{
                                    input += \"<span class='btn btn-xs btn-default'>\"+data+\"</span>\"
                                }
                                return input;
                            }
                        },
                        {
                            targets: 2,
                            class: 'text-left',
                            width: '1%'
                        },
                        {
                            targets: 3,
                            class: 'text-left',
                            width: '1%'
                        },
                        {
                            targets: 4,
                            class: 'text-left',
                            render: function(data, type, row, meta){
                                var input = ''
                                if(row[1]=='QC PROSES'){
                                    input += \"Melakukan QC pada Pekerjaan, \"
                                    input += \"<span class=''> dengan No SPK: <br><b>\"+row[5]+\"</b></span>\"
                                }
                                else if(row[1]=='create'){
                                    input += \"Membuat SPK untuk \"
                                    input += \"<span class='text-red text-bold text-uppercase'>\"+data+\"</span>\"
                                    input += \"<span class=''> Dengan No SPK: <br><b>\"+row[5]+\"</b></span>\"
                                }
                                else if(row[1]=='update'){
                                    input += \"Menerima & Memproses SPK, \"
                                    input += \"<span class=''> No SPK: <br><b>\"+row[5]+\"</b></span>\"
                                }
                                else if(row[1]=='subtasklist'){
                                    input += \"Menyelesaikan Tasklist/WO, \"
                                    input += \"<span class=''> dengan Nomer TASK: <br><b>\"+row[6]+\"</b></span>\"
                                }
                                else if(row[1]=='trashed_tmb'){
                                    input += \"Menghapus Tasklist/WO Tambahan, \"
                                    input += \"<span class=''> dengan Nomer TASK: <br><b>\"+row[5]+\"</b></span>\"
                                }
                                else if(row[1]=='trashed'){
                                    input += \"Menghapus Tasklist/WO, \"
                                    input += \"<span class=''> dengan Nomer TASK: <br><b>\"+row[5]+\"</b></span>\"
                                }
                                else if(row[1]=='SWITCH_PELAKSANA'){
                                    var tipeBadge = '';
                                    if (row[8] == '11' || row[8] == 'internal') {
                                        tipeBadge = \"<span class='badge bg-green' style='font-size:9px;padding:2px 5px;'><i class='fa fa-building'></i> INTERNAL</span>\";
                                    } else if (row[8] == '22' || row[8] == 'subkon' || row[8] == 'external') {
                                        tipeBadge = \"<span class='badge' style='background-color:#ff851b;color:#fff;font-size:9px;padding:2px 5px;'><i class='fa fa-truck'></i> VENDOR</span>\";
                                    }
                                    input += \"<b>Mengalihkan Pelaksana SPK ke:</b> <span class='text-primary text-bold text-uppercase'>\" + data + \"</span> \" + tipeBadge;
                                    input += \"<br><span class=''>No SPK: <b>\" + (row[5] ? row[5] : '-') + \"</b></span>\";
                                    if (row[7] && row[7] != '') {
                                        input += \"<br><small class='text-muted' style='font-size:11px;'><i class='fa fa-info-circle text-info'></i> \" + row[7] + \"</small>\";
                                    }
                                }
                                else{
                                    input += \"<span class='btn btn-xs btn-default'>\"+data+\"</span>\"
                                }
                                return input;
                            }
                        },
                    ],
                    initComplete: function(){
                        //console.log('last update table: ' + new Date());
                    },
                    rowCallback: function( row, data, displayNum, displayIndex, dataIndex ){
                        //console.log(row)
                    }
                });\n

                $('#btnReloadTimeLine').on('click', function(){
                    timeline.ajax.reload();
                });\n

            </script>
        ";

        $taskListNew = "";
        $linkTambahTugas = "
                    BootstrapDialog.show({
                        title:'BUAT PENUGASAN BARU',
                        message: $('<div></div>').load('" . MODUL_PATH . "MasterData/penugasan/$produkID?debuger=0'),
                        draggable:true,
                        closable:true,
                        size:top.BootstrapDialog.SIZE_WIDE,
                    });
                ";

        $belumStartProject = "<div style='margin-top: 22px;' class='box-header bg-orange box-solid text-center text-bold fa-2x'><i class='fa fa-warning text-white blink'></i> SETELAH QUOTATION/BOM DI APPROVE, ANDA BISA MENAMBAHKAN TUGAS / SPK DISINI. <i class='fa fa-warning text-white blink'></i></div>";
        $belumStartProject .= "<div style='margin-top: 5px;' class='box-header bg-info box-solid text-center text-bold'><i><u><r> SILAHKAN MENGHUBUNGI ATASAN ANDA, AGAR MEMPERCEPAT PROSES PENGECEKAN DAN APPROVAL </r></u></i><br><div><a href='" . MODUL_PATH . "Transaksi/index/588'>ATAU KLIK DISINI</a></div></div>";

        $btnTambahTugas = "";


        $btnTambahTugas .= "<span class='pull-right' style='display:inline-flex; gap:6px;'>";
        $memUser = isset($_SESSION['login']['membership']) && is_array($_SESSION['login']['membership']) ? $_SESSION['login']['membership'] : array();
        $isDev = isset($_SESSION['login']['debuger']) && $_SESSION['login']['debuger'] == 1;
        if (in_array('c_holding', $memUser) || in_array('root', $memUser) || in_array('superman', $memUser) || $isDev) {
            $btnTambahTugas .= "<a href='" . base_url() . "Setting/index?tab=project' target='_blank' class='btn btn-default btn-md' title='Buka Pengaturan Alur Pelaksana SPK'><i class='fa fa-sliders text-primary'></i> PENGATURAN PELAKSANA</a>";
        }
        $btnTambahTugas .= "<span onclick=\"$linkTambahTugas\" class='btn btn-info btn-md btn_tambah_tugas'>TAMBAH PELAKSANA</span>";
        $btnTambahTugas .= "</span>";

        $modalSwitchUrlCheck = MODUL_PATH . "MasterData/checkPelaksanaBiayaAjax";
        $modalSwitchUrlSave = MODUL_PATH . "MasterData/saveSwitchPelaksanaAjax";
        $modalSwitchUrlRepost = MODUL_PATH . "MasterData/repostBiayaAjax";

        $taskListData .= "
        <!-- Modal Switch Pelaksana -->
        <div class='modal fade' id='modalSwitchPelaksana' tabindex='-1' role='dialog' aria-labelledby='modalSwitchPelaksanaTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false' style='z-index: 10050;'>
            <div class='modal-dialog modal-lg' style='width: 92%; max-width: 980px;'>
                <div class='modal-content' style='border-radius: 8px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none;'>
                    <div class='modal-header' style='background: #3c8dbc; color: #ffffff; padding: 12px 18px;'>
                        <button type='button' class='close' data-dismiss='modal' aria-hidden='true' style='color: #ffffff; opacity: 0.85; font-size: 24px; line-height: 1;'>&times;</button>
                        <h4 class='modal-title' id='modalSwitchPelaksanaTitle' style='font-weight: bold; font-size: 16px; margin: 0;'>
                            <i class='fa fa-users'></i> Ubah Pelaksana Pekerjaan
                        </h4>
                    </div>
                    <div class='modal-body' style='padding: 16px 20px; background-color: #fafafa;'>
                        <!-- Loading State -->
                        <div id='switchPelaksanaLoading' style='text-align: center; padding: 30px 10px;'>
                            <i class='fa fa-spinner fa-spin fa-3x text-primary'></i>
                            <p style='margin-top: 10px; font-weight: 600; color: #555;'>Memeriksa data SPK & status biaya...</p>
                        </div>

                        <!-- Form Content -->
                        <div id='switchPelaksanaContent' style='display: none;'>
                            <!-- SPK Header Info Box (Data Project Lengkap) -->
                            <div style='background: #ffffff; border: 1px solid #dce4ec; border-radius: 8px; padding: 12px 16px; margin-bottom: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);'>
                                <!-- Baris 1: No SPK & Tanggal SPK -->
                                <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #edf2f7;'>
                                    <div>
                                        <span class='text-muted' style='font-size: 11px; text-transform: uppercase; font-weight: 600;'>Nomor SPK:</span>
                                        <div id='swSpkNo' style='font-weight: bold; font-size: 13px; color: #1e293b; font-family: monospace;'>-</div>
                                    </div>
                                    <div style='text-align: right;'>
                                        <span class='text-muted' style='font-size: 11px; text-transform: uppercase; font-weight: 600;'>Tanggal SPK:</span>
                                        <div id='swSpkDate' style='font-weight: 600; font-size: 12px; color: #475569;'>-</div>
                                    </div>
                                </div>

                                <!-- Baris 2: Nama Proyek & Owner/Klien -->
                                <div style='display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px;'>
                                    <div style='flex: 1; padding-right: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Nama Proyek:</span>
                                        <span id='swSpkProject' style='font-weight: bold; color: #0f172a;'>-</span>
                                    </div>
                                    <div style='flex: 1; text-align: right; padding-left: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Klien / Owner:</span>
                                        <span id='swSpkOwner' style='font-weight: 600; color: #334155;'>-</span>
                                    </div>
                                </div>

                                <!-- Baris 3: Unit / Penugasan & Paket WO -->
                                <div style='display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px;'>
                                    <div style='flex: 1; padding-right: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Penugasan / Lokasi Unit:</span>
                                        <span id='swSpkUnit' style='font-weight: 600; color: #2563eb;'>-</span>
                                    </div>
                                    <div style='flex: 1; text-align: right; padding-left: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Paket Pekerjaan / WO:</span>
                                        <span id='swSpkPaket' style='font-weight: 600; color: #475569;'>-</span>
                                    </div>
                                </div>

                                <!-- Baris 4: Nilai SPK & Progress Fisik -->
                                <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 12px;'>
                                    <div style='flex: 1; padding-right: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Nilai SPK / Pagu Anggaran:</span>
                                        <span id='swSpkNilai' style='font-weight: bold; color: #059669; font-size: 13px;'>-</span>
                                    </div>
                                    <div style='flex: 1; text-align: right; padding-left: 8px;'>
                                        <span class='text-muted' style='font-size: 11px; display: block;'>Progress Pekerjaan:</span>
                                        <span id='swSpkProgress'>-</span>
                                    </div>
                                </div>

                                <!-- Baris 5: Status Komponen Biaya (Tegas & Jelas) -->
                                <div id='swContainerStatusBiaya' style='margin-bottom: 8px; padding: 6px 10px; border-radius: 6px; font-size: 12px;'>
                                    <!-- Dynamic content -->
                                </div>

                                <!-- Baris 6: Pelaksana Saat Ini -->
                                <div style='padding-top: 8px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center;'>
                                    <span class='text-muted' style='font-size: 11px; font-weight: 600;'>Pelaksana Saat Ini:</span>
                                    <div id='swCurrentPelaksana'>-</div>
                                </div>
                            </div>

                            <form id='formSwitchPelaksana' onsubmit='return false;'>
                                <input type='hidden' id='swNoSpk' name='no_spk' value=''>
                                
                                <!-- Radio Tipe Pelaksana (3 Opsi: Internal Tunggal, Vendor Tunggal, Multi-Pelaksana/Group) -->
                                <div class='form-group' style='margin-bottom: 14px;'>
                                    <label style='font-weight: 600; font-size: 13px; color: #333; margin-bottom: 6px; display: block;'>
                                        <i class='fa fa-tag text-primary'></i> Formasi / Tipe Pelaksana:
                                    </label>
                                    <div style='display: flex; gap: 10px;'>
                                        <!-- Opsi Internal (Karyawan) -->
                                        <label id='lblTypeInternal' style='font-weight: 600; cursor: pointer; color: #2e7d32; background: #e8f5e9; padding: 10px 14px; border-radius: 6px; border: 1.5px solid #a5d6a7; flex: 1; text-align: center; margin-bottom: 0; font-size: 13px;'>
                                            <input type='radio' name='sw_type_pelaksana' id='swTypeInternal' value='11' style='vertical-align: middle; margin-right: 6px;'>
                                            <i class='fa fa-user'></i> INTERNAL (KARYAWAN)
                                        </label>
                                        <label id='lblTypeExternal' style='font-weight: 600; cursor: pointer; color: #d35400; background: #fdf2e9; padding: 10px 14px; border-radius: 6px; border: 1.5px solid #f5cba7; flex: 1; text-align: center; margin-bottom: 0; font-size: 13px;'>
                                            <input type='radio' name='sw_type_pelaksana' id='swTypeExternal' value='22' style='vertical-align: middle; margin-right: 6px;'>
                                            <i class='fa fa-truck'></i> VENDOR (SUBKON)
                                        </label>
                                        <!-- Opsi Multi-Pelaksana di-hide sementara sesuai arahan (bisa diaktifkan kembali jika diperlukan) -->
                                        <label id='lblTypeGroup' style='font-weight: 600; cursor: pointer; color: #6b21a8; background: #f3e8ff; padding: 8px 10px; border-radius: 6px; border: 1px solid #d8b4fe; flex: 1.2; text-align: center; margin-bottom: 0; font-size: 12px; display: none;'>
                                            <input type='radio' name='sw_type_pelaksana' id='swTypeGroup' value='group' style='vertical-align: middle; margin-right: 5px;'>
                                            <i class='fa fa-users'></i> MULTI-PELAKSANA / GROUP
                                        </label>
                                    </div>
                                </div>

                                <!-- Dropdown Karyawan (Internal) dinonaktifkan -->
                                <div class='form-group' id='containerSelectKaryawan' style='margin-bottom: 14px;'>
                                    <label for='swSelectKaryawan' style='font-weight: 600; font-size: 13px; color: #333;'>
                                        <i class='fa fa-users text-green'></i> Pilih Karyawan / Group Pelaksana (Internal):
                                    </label>
                                    <select id='swSelectKaryawan' class='form-control' style='width: 100%; border-radius: 4px;'>
                                        <option value=''>-- Pilih Karyawan / Group --</option>
                                    </select>
                                </div>

                                <!-- Dropdown Vendor (External) -->
                                <div class='form-group' id='containerSelectVendor' style='margin-bottom: 14px; display: none;'>
                                    <label for='swSelectVendor' style='font-weight: 600; font-size: 13px; color: #333;'>
                                        <i class='fa fa-truck text-orange'></i> Pilih Vendor / Group Subkon Pelaksana (Eksternal):
                                    </label>
                                    <select id='swSelectVendor' class='form-control' style='width: 100%; border-radius: 4px;'>
                                        <option value=''>-- Pilih Vendor / Group --</option>
                                    </select>
                                </div>

                                <!-- Container 2 Kolom: Daftar Member & Keterangan (Aktif saat Group dipilih baik Internal maupun Vendor) -->
                                <div id='containerGroupMemberDanKeterangan' style='display: none; margin-bottom: 14px;'>
                                    <div style='display: flex; gap: 12px; align-items: stretch;'>
                                        <!-- Kolom Kiri: Daftar Member -->
                                        <div style='flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; display: flex; flex-direction: column;'>
                                            <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;'>
                                                <div style='display: flex; align-items: center; gap: 6px; flex-wrap: wrap;'>
                                                    <label id='swMemberHeaderLabel' style='font-weight: 700; font-size: 12px; color: #1e293b; margin: 0; text-transform: lowercase;'>
                                                        <i class='fa fa-users text-primary'></i> daftar member
                                                    </label>
                                                    <span id='swMemberSelectedCount' class='badge bg-blue' style='font-size: 10px; font-weight: 600; padding: 2px 6px;'>0 terpilih</span>
                                                    <span id='swMemberTaxLockBadge' class='badge' style='display: none; font-size: 9.5px; padding: 2px 6px;'></span>
                                                </div>
                                                <div style='font-size: 11px;'>
                                                    <a href='javascript:void(0);' id='btnSwSelectAllMembers' style='font-weight: 600; color: #2563eb; margin-right: 8px;'>Pilih Semua</a>
                                                    <a href='javascript:void(0);' id='btnSwDeselectAllMembers' style='font-weight: 600; color: #64748b;'>Kosongkan</a>
                                                </div>
                                            </div>
                                            <!-- Pencarian Cepat -->
                                            <div style='margin-bottom: 6px;'>
                                                <div class='input-group input-group-sm' style='width: 100%;'>
                                                    <input type='text' id='swSearchMember' class='form-control' placeholder='Cari nama / ID...' style='height: 28px; font-size: 11.5px; border-radius: 4px 0 0 4px;'>
                                                    <span class='input-group-btn'>
                                                        <button class='btn btn-default' type='button' id='btnClearSearchMember' style='height: 28px; padding: 2px 8px; font-size: 11px;' title='Hapus filter pencarian'><i class='fa fa-times'></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div id='swListGroupMembers' style='max-height: 180px; overflow-y: auto; padding-right: 4px;'>
                                                <!-- Dynamic checkboxes of members -->
                                            </div>
                                            <!-- Notifikasi Petunjuk Master Data -->
                                            <div id='swMemberNoticeInfo' style='margin-top: 8px; padding: 6px 8px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; font-size: 11px; color: #1e40af; line-height: 1.35;'>
                                                <i class='fa fa-info-circle text-primary'></i> <b>Karyawan tidak ditemukan?</b><br>
                                                Hanya menampilkan karyawan dengan status <code>allow_project = 1</code> di <b>Master Data Employee</b>. Hubungi bagian Master Data untuk mengeset <i>Allow Project = 1</i> jika ingin menambah karyawan ke daftar/group ini.
                                            </div>
                                        </div>

                                        <!-- Kolom Kanan: Keterangan (Dapat diedit & auto-sync dua arah) -->
                                        <div style='flex: 1; min-width: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; display: flex; flex-direction: column;'>
                                            <div style='margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; display: flex; justify-content: space-between; align-items: center;'>
                                                <label for='swCatatanPelaksana' style='font-weight: 700; font-size: 12px; color: #1e293b; margin: 0; text-transform: lowercase;'>
                                                    <i class='fa fa-edit text-info'></i> keterangan
                                                </label>
                                                <span class='text-muted' style='font-size: 10.5px;'>otomatis &amp; manual (2 arah)</span>
                                            </div>
                                            <textarea id='swCatatanPelaksana' class='form-control' style='flex: 1; min-height: 214px; font-family: monospace; font-size: 12px; resize: vertical; line-height: 1.5; border-radius: 4px;' placeholder='Daftar member otomatis dan catatan manual tambahan...'></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Container Multi-Pelaksana / Group (Hybrid / Multi-Vendor / Karyawan Group) -->
                                <div id='containerMultiPelaksanaGroup' style='display: none; margin-bottom: 14px; background: #ffffff; border: 1.5px solid #d8b4fe; border-radius: 6px; padding: 12px 14px; box-shadow: 0 2px 6px rgba(107,33,168,0.06);'>
                                    <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f3e8ff;'>
                                        <div style='display: flex; align-items: center; gap: 8px;'>
                                            <span style='background: #6b21a8; color: #fff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;'><i class='fa fa-users'></i></span>
                                            <div>
                                                <span style='font-weight: 700; font-size: 13px; color: #581c87;'>Formasi Multi-Pelaksana (Alokasi & PPh Fleksibel)</span>
                                                <span id='swGroupTypeBadge' class='badge' style='background: #9333ea; font-size: 10px; margin-left: 6px;'>Group</span>
                                                <span id='swGroupPlafonBadge' class='badge' style='background: #059669; font-size: 10.5px; margin-left: 6px;' title='Plafon Alokasi Biaya'><i class='fa fa-shield'></i> Plafon: Rp 0</span>
                                            </div>
                                        </div>
                                        <div style='display: flex; align-items: center; gap: 8px;'>
                                            <span class='text-muted' style='font-size: 11px;'>Template Master:</span>
                                            <select id='swSelectGroupTemplate' class='form-control input-sm' style='width: 170px; height: 28px; font-size: 11px; border-radius: 4px; border: 1px solid #cbd5e1;'>
                                                <option value=''>-- Muat Template --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Nama Group & Toolbar Tambah Anggota -->
                                    <div style='display: flex; gap: 8px; align-items: center; margin-bottom: 10px; flex-wrap: wrap;'>
                                        <div style='flex: 1.5; min-width: 180px;'>
                                            <input type='text' id='swGroupNameInput' class='form-control input-sm' placeholder='Nama Group Pelaksana...' style='height: 30px; font-size: 11.5px; font-weight: 600;'>
                                        </div>
                                        <div style='width: 140px;'>
                                            <select id='swAddEntityType' class='form-control input-sm' style='height: 30px; font-size: 11.5px;'>
                                                <option value='11' disabled>Karyawan (Internal) [Nonaktif]</option>
                                                <option value='22' selected>Vendor (Eksternal)</option>
                                            </select>
                                        </div>
                                        <div style='flex: 2; min-width: 200px;'>
                                            <select id='swAddMemberPicker' class='form-control input-sm' style='width: 100%; height: 30px; font-size: 11.5px;'>
                                                <option value=''>-- Pilih Pelaksana --</option>
                                            </select>
                                        </div>
                                        <button type='button' id='btnAddMemberToGroup' class='btn btn-success btn-sm' style='height: 30px; font-size: 11.5px; font-weight: 600;'>
                                            <i class='fa fa-plus-circle'></i> Tambah
                                        </button>
                                        <button type='button' id='btnAutoSplitGroup' class='btn btn-info btn-sm' style='height: 30px; font-size: 11.5px; font-weight: 600;' title='Bagi rata 100% ke semua member aktif'>
                                            <i class='fa fa-balance-scale'></i> Bagi Rata
                                        </button>
                                    </div>

                                    <!-- Tabel Alokasi Anggota -->
                                    <div style='border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 8px;'>
                                        <table class='table table-condensed table-striped' style='margin-bottom: 0; font-size: 11.5px;' id='tblGroupAllocations'>
                                            <thead>
                                                <tr style='background: #f8fafc; color: #475569;'>
                                                    <th style='width: 25px; text-align: center;'>#</th>
                                                    <th style='width: 80px; text-align: center;'>Entitas</th>
                                                    <th>Nama Pelaksana</th>
                                                    <th style='width: 110px; text-align: center;'>Porsi (%)</th>
                                                    <th style='width: 140px; text-align: right;'>Estimasi (Rp)</th>
                                                    <th style='width: 60px; text-align: center;' title='Pilih 1 Lead / Koordinator'>Lead</th>
                                                    <th style='width: 130px; text-align: center;'>Pajak Default</th>
                                                    <th style='width: 40px; text-align: center;'>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id='swTbodyGroupAllocations'>
                                                <!-- Dinamis via JS -->
                                            </tbody>
                                            <tfoot>
                                                <tr style='background: #fdf4ff; font-weight: 700;'>
                                                    <td colspan='3' style='text-align: right; padding-right: 10px; color: #581c87;'>TOTAL PROPORSI & ESTIMASI:</td>
                                                    <td style='text-align: center;'>
                                                        <span id='swTotalAllocPercent' style='font-size: 12.5px; font-weight: 700; color: #6b21a8;'>0.00%</span>
                                                    </td>
                                                    <td style='text-align: right; font-size: 12px; color: #047857;' id='swTotalAllocNominal'>Rp 0</td>
                                                    <td colspan='2' style='text-align: center;' id='swStatusAllocBadge'>
                                                        <!-- Badge status pas 100% / selisih -->
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Alert Zero-Sum Plafond Guard -->
                                    <div id='swAlertAllocZeroSum' style='display: none; padding: 6px 10px; border-radius: 4px; font-size: 11px; margin-bottom: 8px; line-height: 1.4;'>
                                        <!-- Dinamis via JS -->
                                    </div>

                                    <!-- Catatan Tambahan Group -->
                                    <div style='margin-top: 6px;'>
                                        <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;'>
                                            <label for='swCatatanPelaksanaGroup' style='font-weight: 600; font-size: 11.5px; color: #334155; margin: 0;'>
                                                <i class='fa fa-edit text-info'></i> Catatan Pelaksana / SPK Group:
                                            </label>
                                            <span class='text-muted' style='font-size: 10.5px;'>Auto-sync dari tabel alokasi & catatan manual</span>
                                        </div>
                                        <textarea id='swCatatanPelaksanaGroup' class='form-control' style='height: 60px; font-family: monospace; font-size: 11px; resize: vertical;' placeholder='Catatan pelaksana group...'></textarea>
                                    </div>
                                </div>

                                <!-- Panel Status Biaya Aktif (BEFORE) -->
                                <div id='containerBiayaAktif' style='display: none; margin-top: 15px;'>
                                    <div style='background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px; padding: 12px 14px;'>
                                        <div id='swBiayaAktifTitle' style='font-weight: bold; color: #b78103; margin-bottom: 8px; font-size: 13px;'>
                                            <i class='fa fa-exclamation-triangle'></i> Tagihan Biaya Vendor Ditemukan:
                                        </div>
                                        <div style='background: #fff; padding: 10px 14px; border-radius: 6px; border: 1px solid #fae7b5; margin-bottom: 10px; font-size: 12px; line-height: 1.6;'>
                                            <div style='display: flex; justify-content: space-between; margin-bottom: 3px;'>
                                                <span class='text-muted'>Vendor Sebelumnya:</span>
                                                <span style='font-weight: bold; color: #d35400;' id='swBiayaVendorNama'>-</span>
                                            </div>
                                            <div style='display: flex; justify-content: space-between; margin-bottom: 3px;'>
                                                <span class='text-muted'>Total Tagihan Biaya:</span>
                                                <span style='font-weight: bold; color: #2c3e50;' id='swBiayaNilai'>-</span>
                                            </div>
                                            <div style='display: flex; justify-content: space-between; margin-bottom: 3px;'>
                                                <span class='text-muted'>Status Pembayaran:</span>
                                                <span id='swBiayaStatusBayar'>-</span>
                                            </div>
                                            <div style='display: flex; justify-content: space-between; margin-bottom: 3px;'>
                                                <span class='text-muted'>Payment Source (3675):</span>
                                                <span id='swBiayaPsStatus'>-</span>
                                            </div>
                                            <div style='display: flex; justify-content: space-between;'>
                                                <span class='text-muted'>Nomor Transaksi:</span>
                                                <span style='color: #666; font-family: monospace;' id='swBiayaNoNota'>-</span>
                                            </div>
                                        </div>

                                        <!-- Alert Missing Payment Source (Hanya terbit biaya tanpa PS 3675) & Tombol Reposting -->
                                        <div id='containerAlertMissingPaymentSource' style='display: none; background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 6px; padding: 12px 14px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(245,158,11,0.1);'>
                                            <div style='display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;'>
                                                <div style='flex: 1;'>
                                                    <div style='font-weight: 700; color: #b45309; font-size: 12.5px; margin-bottom: 4px;'>
                                                        <i class='fa fa-exclamation-triangle text-warning'></i> TRANSAKSI BIAYA (3674) TERBIT, TAPI PAYMENT SOURCE (3675) BELUM ADA!
                                                    </div>
                                                    <div style='font-size: 11.5px; color: #78350f; line-height: 1.5;'>
                                                        Dokumen biaya telah tercatat di transaksi, namun <b>Payment Source (Target Jenis 3675)</b> sebagai sumber pembayaran kasir belum terbentuk di database sehingga kasir belum dapat melakukan pencairan/pembayaran.
                                                    </div>
                                                    <div style='margin-top: 6px; font-size: 11.5px; color: #92400e;'>
                                                        <b>Rekomendasi:</b> Lakukan <b>Reposting Biaya</b> sekarang untuk meregenerasi transaksi biaya dan menerbitkan Payment Source (3675).
                                                    </div>
                                                </div>
                                                <button type='button' class='btn btn-warning btn-sm' id='btnRepostBiaya' onclick='triggerRepostBiaya();' style='font-weight: 700; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                                                    <i class='fa fa-refresh'></i> Reposting Biaya
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Checkbox Void Opsi 2 -->
                                        <div id='containerCheckboxVoid' style='background: #fff; padding: 8px 10px; border-radius: 4px; border: 1px solid #ffd54f;'>
                                            <label style='margin: 0; font-weight: 600; color: #c0392b; cursor: pointer; font-size: 12px;'>
                                                <input type='checkbox' id='swCheckVoidBiaya' checked style='vertical-align: middle; margin-right: 6px;'>
                                                <span id='swCheckVoidBiayaLabel'>Batalkan tagihan biaya vendor via Modul Pembatalan Resmi (9911) &amp; balik jurnal akuntansi</span>
                                            </label>
                                        </div>

                                        <!-- Info jika vendor masih sama (belum diganti) -->
                                        <div id='swBiayaInfoSameVendor' style='display: none; background: #fff; padding: 8px 10px; border-radius: 4px; border: 1px solid #fae7b5; font-size: 11.5px; color: #475569; line-height: 1.45; margin-top: 6px;'>
                                            <!-- Dynamic text via JS -->
                                        </div>

                                        <!-- Alert jika biaya sudah dicairkan (Finance Locked) -->
                                        <div id='alertBiayaDisbursed' style='display: none; background: #fef2f2; border: 1.5px solid #f87171; border-radius: 6px; padding: 12px 14px; margin-top: 10px; box-shadow: 0 2px 6px rgba(239,68,68,0.12);'>
                                            <div style='display: flex; align-items: flex-start; gap: 10px;'>
                                                <div style='background: #fee2e2; color: #dc2626; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;'>
                                                    <i class='fa fa-lock'></i>
                                                </div>
                                                <div style='flex: 1;'>
                                                    <div style='font-weight: 700; font-size: 12.5px; color: #991b1b; margin-bottom: 3px;'>
                                                        PENGALIHAN TERKUNCI: TAGIHAN SUDAH DIBAYARKAN KASIR
                                                    </div>
                                                    <div style='font-size: 11.5px; color: #7f1d1d; line-height: 1.5; margin-bottom: 6px;'>
                                                        Tagihan biaya <b id='swAlertNoNota'>-</b> (<b id='swAlertNilaiBayar'>-</b>) telah <b>DIBAYARKAN OLEH KASIR/KEUANGAN</b>.<br>
                                                        Pengalihan ke Internal maupun ke Vendor lain dicegah secara ketat demi integritas saldo kas/bank perusahaan.
                                                    </div>
                                                    <div style='background: #ffffff; border: 1px dashed #fca5a5; border-radius: 4px; padding: 6px 10px; font-size: 11px; color: #b91c1c;'>
                                                        <i class='fa fa-info-circle'></i> <b>Solusi:</b> Jika pekerjaan ini memang harus dialihkan, silakan lakukan <b>Pembatalan Pencairan Kas / Retur Kasir</b> terlebih dahulu di modul Keuangan sebelum mengubah pelaksana.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Status Biaya Belum Pernah Terbit (Belum QC) -->
                                <div id='containerBiayaBelumQC' style='display: none; margin-top: 15px;'>
                                    <div style='background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 6px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);'>
                                        <div style='display: flex; align-items: flex-start; gap: 10px;'>
                                            <div style='background: #e0f2fe; color: #0284c7; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;'>
                                                <i class='fa fa-clock-o'></i>
                                            </div>
                                            <div style='flex: 1;'>
                                                <div style='font-weight: 700; font-size: 12.5px; color: #0369a1; margin-bottom: 3px;'>
                                                    TAGIHAN BIAYA BELUM PERNAH DITERBITKAN (BELUM QC)
                                                </div>
                                                <div id='swTextBelumQCDesc' style='font-size: 11.5px; color: #334155; line-height: 1.5; margin-bottom: 8px;'>
                                                    <!-- Dynamic via JS -->
                                                </div>
                                                <div style='background: #ffffff; border: 1px solid #e0f2fe; border-radius: 4px; padding: 7px 10px; font-size: 11.5px; display: flex; justify-content: space-between; align-items: center;'>
                                                    <span class='text-muted' style='font-weight: 600;'>Estimasi Nilai Tagihan Saat QC Selesai:</span>
                                                    <span style='font-weight: 700; color: #0284c7; font-size: 12.5px;' id='swBelumQCEstimasiNilai'>Rp 0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transition Divider (Between BEFORE and AFTER when switching vendors) -->
                                <div id='swTransitionDivider' style='display: none; text-align: center; margin: 12px 0;'>
                                    <span style='background: #e0f2fe; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 20px; padding: 5px 16px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);'>
                                        <i class='fa fa-arrow-down text-primary'></i> DIALIHKAN KE VENDOR BARU (PENGGANTIAN) <i class='fa fa-arrow-down text-primary'></i>
                                    </span>
                                </div>

                                <!-- Panel Transparansi Rincian Biaya Auto-Posting ke Vendor (AFTER) -->
                                <div id='containerInfoAutoPost' style='display: none; margin-top: 14px;'>
                                    <!-- Kasus A: Jika Paket Non-Biaya (Murni Material) -->
                                    <div id='swBoxNonBiaya' style='display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px 14px; font-size: 12px; color: #b45309;'>
                                        <i class='fa fa-ban'></i> <b>Paket Non-Biaya (Murni Material):</b> Paket pekerjaan ini tidak memiliki komponen biaya/upah kerja. Pengalihan ke Vendor tidak akan menerbitkan transaksi tagihan biaya.
                                    </div>

                                    <!-- Kasus B: Jika Ada Komponen Biaya (Detail Breakdown Transparan) -->
                                    <div id='swBoxAdaBiaya' style='background: #ffffff; border: 1px solid #93c5fd; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); overflow: hidden;'>
                                        <!-- Header Panel -->
                                        <div style='background: #f0f9ff; border-bottom: 1px solid #bfdbfe; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;'>
                                            <span id='swAutoPostTitle' style='font-weight: 700; font-size: 12px; color: #0369a1;'>
                                                <i class='fa fa-file-text-o text-primary'></i> Rencana Penerbitan Tagihan Vendor (Auto-Post 3674)
                                            </span>
                                            <div id='swAutoPostStatusBadge'>-</div>
                                        </div>

                                        <div style='padding: 10px 12px; font-size: 12px;'>
                                            <!-- Vendor Penerima Tagihan -->
                                            <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; background: #fafafa; padding: 6px 10px; border-radius: 4px; border: 1px solid #e5e7eb;'>
                                                <span class='text-muted' style='font-size: 11px; font-weight: 600;'>Tagihan Akan Diterbitkan Untuk:</span>
                                                <div id='swAutoPostVendorNama' style='font-weight: 700; font-size: 12px; color: #d97706;'>- Belum Dipilih -</div>
                                            </div>

                                            <!-- Tabel Breakdown Rincian Pekerjaan Jasa -->
                                            <div style='border: 1px solid #e2e8f0; border-radius: 4px; overflow-x: auto; overflow-y: hidden; margin-bottom: 8px;'>
                                                <table class='table table-condensed table-striped' style='width: 100%; margin-bottom: 0; font-size: 11.5px;'>
                                                    <thead>
                                                        <tr style='background: #f8fafc; color: #475569;'>
                                                            <th style='width: 28px; text-align: center;'>#</th>
                                                            <th>Pekerjaan / Item Jasa</th>
                                                            <th style='width: 75px; text-align: center;'>Volume</th>
                                                            <th style='width: 100px; text-align: right;'>Tarif Satuan</th>
                                                            <th style='width: 115px; text-align: right; padding-right: 12px;'>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id='swTbodyRincianBiaya'>
                                                        <!-- Diisi dinamis via JS -->
                                                    </tbody>
                                                    <tfoot>
                                                        <tr id='swRowPaguBudget' style='background: #f8fafc; font-size: 11px; color: #475569;'>
                                                            <td colspan='4' style='text-align: right; padding-right: 12px;'>PAGU BUDGET BIAYA SPK:</td>
                                                            <td style='text-align: right; font-weight: 700; padding-right: 12px; white-space: nowrap;' id='swPaguBudgetBiayaTxt'>Rp 0</td>
                                                        </tr>
                                                        <tr style='background: #ecfdf5; font-weight: 700; color: #065f46;'>
                                                            <td colspan='4' style='text-align: right; padding-right: 12px;'>TOTAL TAGIHAN BIAYA VENDOR:</td>
                                                            <td style='text-align: right; font-size: 12.5px; color: #047857; padding-right: 12px; white-space: nowrap;' id='swTotalBiayaPostingTxt'>Rp 0</td>
                                                        </tr>
                                                        <tr id='swRowEfisiensiSummary' style='background: #f0fdf4; font-weight: 700; color: #15803d; border-top: 1px dashed #86efac;'>
                                                            <td colspan='4' style='text-align: right; padding-right: 12px;'>
                                                                <i class='fa fa-arrow-down'></i> EFISIENSI BIAYA PROYEK (DIHEMAT):
                                                            </td>
                                                            <td style='text-align: right; font-size: 13px; color: #16a34a; padding-right: 12px; white-space: nowrap;' id='swTotalEfisiensiTxt'>Rp 0</td>
                                                        </tr>
                                                        <tr id='swRowNonVendorSummary' style='display: none; background: #fffbeb; font-size: 11px; color: #b45309;'>
                                                            <td colspan='4' style='text-align: right; padding-right: 12px;'>
                                                                <i class='fa fa-info-circle'></i> Total Fisik: <b id='swTotalFisikTxt'>Rp 0</b> | Dikecualikan (Non-Vendor):
                                                            </td>
                                                            <td style='text-align: right; font-weight: 600; color: #b45309; padding-right: 12px; white-space: nowrap;' id='swTotalNonVendorTxt'>Rp 0</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <!-- Catatan Eksekusi Otomatis -->
                                            <div id='swAlertExecutionInfo' style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; font-size: 11px; line-height: 1.45; color: #334155;'>
                                                <!-- Diisi dinamis via JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class='modal-footer' style='background-color: #f1f5f9; padding: 10px 18px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;'>
                        <button type='button' class='btn btn-default btn-sm' data-dismiss='modal'>
                            <i class='fa fa-times'></i> Batal
                        </button>
                        <button type='button' class='btn btn-primary btn-sm' id='btnSubmitSwitchPelaksana' onclick='submitSwitchPelaksana();' style='font-weight: 600;'>
                            <i class='fa fa-save'></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Styling Optgroup Header Bootstrap-Select di seluruh Modul Master Project */
            .bootstrap-select .dropdown-menu li.dropdown-header.optgroup-1,
            li.dropdown-header.optgroup-1.bg-gray {
                background-color: #d2d6de !important;
                padding: 6px 12px !important;
            }
            .bootstrap-select .dropdown-menu li.dropdown-header.optgroup-1 .text,
            li.dropdown-header.optgroup-1 span.text {
                color: #dd4b39 !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px;
            }
            .bootstrap-select .dropdown-menu li.dropdown-header.optgroup-2,
            li.dropdown-header.optgroup-2.bg-gray {
                background-color: #d2d6de !important;
                padding: 6px 12px !important;
            }
            .bootstrap-select .dropdown-menu li.dropdown-header.optgroup-2 .text,
            li.dropdown-header.optgroup-2 span.text {
                color: #0073b7 !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px;
            }
            #swSelectKaryawan optgroup[label='Karyawan Group'] {
                background-color: #d2d6de;
                color: #dd4b39;
                font-weight: bold;
            }

            #swSelectKaryawan optgroup[label='KARYAWAN INDIVIDUAL'] {
                background-color: #d2d6de;
                color: #0073b7;
                font-weight: bold;
            }
            #swSelectVendor optgroup[label='Vendor Group'] {
                background-color: #d2d6de;
                color: #dd4b39;
                font-weight: bold;
            }
            #swSelectVendor optgroup[label='VENDOR INDIVIDUAL'] {
                background-color: #d2d6de;
                color: #0073b7;
                font-weight: bold;
            }
            .swal2-container, .sweet-alert, .sweet-overlay, .swal2-backdrop {
                z-index: 99999999 !important;
            }
            .swal2-modal {
                z-index: 100000000 !important;
            }
        </style>

        <script>
        function ensureSwalTopZIndex() {
            var css = '.swal2-container, .sweet-alert, .sweet-overlay, .swal2-backdrop { z-index: 99999999 !important; } .swal2-modal { z-index: 100000000 !important; }';
            var applyDoc = function(doc) {
                try {
                    if (doc && doc.head) {
                        var el = doc.getElementById('swal-high-zindex-rule');
                        if (!el) {
                            el = doc.createElement('style');
                            el.id = 'swal-high-zindex-rule';
                            el.type = 'text/css';
                            doc.head.appendChild(el);
                        }
                        if (el.styleSheet) {
                            el.styleSheet.cssText = css;
                        } else {
                            el.innerHTML = css;
                        }
                    }
                } catch(e) {}
            };
            applyDoc(document);
            if (typeof parent !== 'undefined' && parent.document) applyDoc(parent.document);
            if (typeof top !== 'undefined' && top.document) applyDoc(top.document);
        }
        ensureSwalTopZIndex();

        function showSwalAlert(title, text, type, callback) {
            ensureSwalTopZIndex();
            var targetSwal = (typeof swal === 'function') ? swal : ((typeof top !== 'undefined' && typeof top.swal === 'function') ? top.swal : null);
            if (targetSwal) {
                var boostZ = function() {
                    var applyBoost = function(w) {
                        try {
                            if (!w || !w.document) return;
                            var els = w.document.querySelectorAll('.swal2-container, .swal2-modal, .sweet-alert, .sweet-overlay, .swal2-backdrop');
                            for (var i = 0; i < els.length; i++) {
                                els[i].style.setProperty('z-index', '99999999', 'important');
                            }
                        } catch(e) {}
                    };
                    applyBoost(window);
                    if (typeof parent !== 'undefined') applyBoost(parent);
                    if (typeof top !== 'undefined') applyBoost(top);
                };
                boostZ();
                var resAlert = targetSwal({
                    title: title,
                    text: text,
                    type: type
                }, function(isConfirm) {
                    if (typeof callback === 'function') callback(isConfirm);
                });
                boostZ();
                setTimeout(boostZ, 10);
                setTimeout(boostZ, 50);
                setTimeout(boostZ, 150);
                setTimeout(boostZ, 300);
                if (resAlert && typeof resAlert.then === 'function') {
                    resAlert.then(function(result) {
                        if (typeof callback === 'function') callback(result);
                    }).catch(function() {
                        if (typeof callback === 'function') callback(false);
                    });
                }
            } else {
                alert(title + (text ? ': ' + text : ''));
                if (typeof callback === 'function') callback(true);
            }
        }

        function showSwalConfirm(title, text, confirmBtnText, callback) {
            ensureSwalTopZIndex();
            var targetSwal = (typeof swal === 'function') ? swal : ((typeof top !== 'undefined' && typeof top.swal === 'function') ? top.swal : null);
            if (targetSwal) {
                var boostZ = function() {
                    var applyBoost = function(w) {
                        try {
                            if (!w || !w.document) return;
                            var els = w.document.querySelectorAll('.swal2-container, .swal2-modal, .sweet-alert, .sweet-overlay, .swal2-backdrop');
                            for (var i = 0; i < els.length; i++) {
                                els[i].style.setProperty('z-index', '99999999', 'important');
                            }
                        } catch(e) {}
                    };
                    applyBoost(window);
                    if (typeof parent !== 'undefined') applyBoost(parent);
                    if (typeof top !== 'undefined') applyBoost(top);
                };
                boostZ();
                var resConfirm = targetSwal({
                    title: title,
                    text: text,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f39c12',
                    confirmButtonText: confirmBtnText || 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm) {
                        if (typeof callback === 'function') callback(true);
                    } else {
                        if (typeof callback === 'function') callback(false);
                    }
                });
                boostZ();
                setTimeout(boostZ, 10);
                setTimeout(boostZ, 50);
                setTimeout(boostZ, 150);
                setTimeout(boostZ, 300);
                if (resConfirm && typeof resConfirm.then === 'function') {
                    resConfirm.then(function(result) {
                        var isYes = (result === true || (result && result.value === true) || (result && result.isConfirmed === true));
                        if (typeof callback === 'function') callback(isYes);
                    }).catch(function() {
                        if (typeof callback === 'function') callback(false);
                    });
                }
            } else {
                var ans = confirm(title + (text ? ': ' + text : ''));
                if (typeof callback === 'function') callback(ans);
            }
        }

        function applyMasterProjectOptgroupStyles() {
            $('.bootstrap-select .dropdown-header.optgroup-1').addClass('bg-gray').find('.text').addClass('text-red text-bold text-uppercase');
            $('.bootstrap-select .dropdown-header.optgroup-2').addClass('bg-gray').find('.text').addClass('text-blue text-bold text-uppercase');
        }
        $(document).on('loaded.bs.select refreshed.bs.select show.bs.select rendered.bs.select', function() {
            applyMasterProjectOptgroupStyles();
        });
        $(document).ready(function() {
            applyMasterProjectOptgroupStyles();
        });

        var currentSwitchSpkData = null;

        function setSubmitButtonState(isLocked) {
            var btn = $('#btnSubmitSwitchPelaksana');
            if (isLocked) {
                btn.prop('disabled', true)
                    .attr('disabled', 'disabled')
                    .css({'cursor': 'not-allowed', 'opacity': '0.65', 'pointer-events': 'auto'})
                    .removeClass('btn-primary')
                    .addClass('btn-danger')
                    .html('<i class=\"fa fa-lock\"></i> Terkunci (Sudah Dibayar Kasir)');
            } else {
                btn.prop('disabled', false)
                    .removeAttr('disabled')
                    .css({'cursor': 'pointer', 'opacity': '1', 'pointer-events': 'auto'})
                    .removeClass('btn-danger')
                    .addClass('btn-primary')
                    .html('<i class=\"fa fa-save\"></i> Simpan Perubahan');
            }
        }

        window.openModalSwitchPelaksana = function(noSpk) {
            if (!noSpk) return;
            ensureSwalTopZIndex();
            $('#modalSwitchPelaksana').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
            $('#switchPelaksanaLoading').show();
            $('#switchPelaksanaContent').hide();
            $('#btnSubmitSwitchPelaksana').prop('disabled', true).attr('disabled', 'disabled').html('<i class=\"fa fa-spinner fa-spin\"></i> Memeriksa...');

            var swalFn = function(title, text, type, callback) {
                showSwalAlert(title, text, type, callback);
            };

            $.ajax({
                url: '$modalSwitchUrlCheck',
                type: 'GET',
                data: { no_spk: noSpk },
                dataType: 'json',
                success: function(res) {
                    if (res.status != 1) {
                        swalFn('Gagal', res.message || 'Tidak dapat membaca data SPK', 'error');
                        $('#modalSwitchPelaksana').modal('hide');
                        return;
                    }

                    // 1. Validasi Saklar Master Global
                    if (res.settings && parseInt(res.settings.switch_pelaksana_enabled) === 0) {
                        swalFn('Fitur Dinonaktifkan', 'Fitur Alih Pelaksana Proyek saat ini sedang dinonaktifkan di Pengaturan Sistem.', 'warning');
                        $('#modalSwitchPelaksana').modal('hide');
                        return;
                    }

                    currentSwitchSpkData = res;
                    $('#switchPelaksanaLoading').hide();
                    $('#switchPelaksanaContent').show();
                    try {
                        populateSwitchPelaksanaModal(res);
                    } catch (errPopulate) {
                        console.error('populateSwitchPelaksanaModal error:', errPopulate);
                    }
                    // Status keaktifan tombol dikendalikan secara akurat oleh renderSwitchFormState() di dalam populateSwitchPelaksanaModal()
                },
                error: function(xhr, status, error) {
                    swalFn('Error', 'Terjadi kesalahan jaringan saat mengambil data', 'error');
                    $('#modalSwitchPelaksana').modal('hide');
                }
            });
        };

        if (typeof top !== 'undefined') {
            top.openModalSwitchPelaksana = window.openModalSwitchPelaksana;
            top.openModalSwitchPelaksanaGlobal = window.openModalSwitchPelaksana;
        }
        if (typeof parent !== 'undefined') {
            parent.openModalSwitchPelaksana = window.openModalSwitchPelaksana;
            parent.openModalSwitchPelaksanaGlobal = window.openModalSwitchPelaksana;
        }

        function populateSwitchPelaksanaModal(res) {
            var spk = res.spk;
            lastRenderedGroupId = null;
            activeGroupMembers = [];
            $('#containerGroupMemberDanKeterangan').hide();
            $('#swListGroupMembers').empty();
            $('#swCatatanPelaksana').val('');
            $('#swNoSpk').val(spk.no_spk);
            $('#swSpkNo').text(spk.no_spk || '-');
            $('#swSpkDate').text(spk.tgl_spk || '-');
            $('#swSpkProject').text(spk.project_nama || '-');
            $('#swSpkOwner').text(spk.owner_nama || '-');
            $('#swSpkUnit').text(spk.unit_nama || '-');
            $('#swSpkPaket').text(spk.paket_nama || '-');
            $('#swSpkNilai').text('Rp ' + (spk.nilai_spk_format || '0'));

            var progressPercent = spk.progress_percent !== undefined ? parseInt(spk.progress_percent) : 0;
            var progressNama = spk.progress_nama || '-';
            var progressBadgeClass = 'label-info';
            if (progressPercent >= 100 || spk.progress_id == 3) {
                progressBadgeClass = 'label-success';
            } else if (progressPercent > 0) {
                progressBadgeClass = 'label-primary';
            } else {
                progressBadgeClass = 'label-default';
            }
            $('#swSpkProgress').html('<span class=\"label ' + progressBadgeClass + '\" style=\"font-size:11px; padding:3px 8px;\">' + progressPercent + '% (' + progressNama + ')</span>');

            // Render Status Komponen Biaya (Tegas: ADA BIAYA vs TANPA BIAYA)
            var primaryTrxHeader = null;
            if (res.biaya_list && res.biaya_list.length > 0) {
                $.each(res.biaya_list, function(i, b) {
                    if (b.jenis == '3674') {
                        primaryTrxHeader = b;
                        return false;
                    }
                });
                if (!primaryTrxHeader) {
                    primaryTrxHeader = res.biaya_list[0];
                }
            }

            if (spk.has_biaya == 1) {
                if (res.has_active_biaya && primaryTrxHeader) {
                    var isPaidTrx = (parseFloat(primaryTrxHeader.nilai_bayar) > 0);
                    var isCurrentVendor = (spk.type_pelaksana == 22);
                    var bayarBadge = '';
                    var labelDokumen = '';
                    var psBadge = '';
                    if (res.has_payment_source_3675) {
                        psBadge = '<span class=\"label label-success\" style=\"font-size:10px; margin-left:6px;\"><i class=\"fa fa-check-circle\"></i> PS (3675) Aktif</span>';
                    } else {
                        psBadge = '<span class=\"label label-warning\" style=\"font-size:10px; margin-left:6px; background-color:#d97706;\" title=\"Hanya terbit biaya (3674), Payment Source 3675 belum terbit!\"><i class=\"fa fa-exclamation-triangle\"></i> PS (3675) Belum Terbit</span>';
                    }
                    if (isCurrentVendor) {
                        bayarBadge = isPaidTrx ? '<span class=\"label label-danger\" style=\"font-size:10px; margin-left:6px;\"><i class=\"fa fa-money\"></i> Sudah Dibayar Kasir</span>' : '<span class=\"label label-success\" style=\"font-size:10px; margin-left:6px;\"><i class=\"fa fa-check\"></i> Belum Dibayar Kasir</span>';
                        labelDokumen = 'Tagihan Vendor: ';
                    } else {
                        bayarBadge = '<span class=\"label label-info\" style=\"font-size:10px; margin-left:6px;\"><i class=\"fa fa-building\"></i> Biaya Internal</span>';
                        labelDokumen = 'Dokumen Biaya: ';
                    }
                    $('#swContainerStatusBiaya').css({
                        'background': '#ecfdf5',
                        'border': '1px solid #a7f3d0',
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'space-between',
                        'flex-wrap': 'wrap',
                        'gap': '4px'
                    }).html(
                        '<div>' +
                        '<span class=\"label label-success\" style=\"font-size:11px; padding:3px 8px; margin-right:6px;\"><i class=\"fa fa-check-circle\"></i> ' + (spk.status_biaya_badge || 'ADA KOMPONEN BIAYA') + '</span>' +
                        '<span style=\"font-size:11px; font-weight:600; color:#047857;\">' + labelDokumen + escapeHtmlSw(primaryTrxHeader.nomer) + ' (Rp ' + primaryTrxHeader.transaksi_nilai_format + ')</span>' +
                        psBadge +
                        bayarBadge +
                        '</div>'
                    );
                } else if (spk.progress_id == 3 || progressPercent >= 100) {
                    $('#swContainerStatusBiaya').css({
                        'background': '#f0fdf4',
                        'border': '1px solid #bbf7d0',
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'space-between',
                        'flex-wrap': 'wrap',
                        'gap': '4px'
                    }).html(
                        '<div>' +
                        '<span class=\"label label-success\" style=\"font-size:11px; padding:3px 8px; margin-right:6px;\"><i class=\"fa fa-check-circle\"></i> SIAP TERBIT (SUDAH QC)</span>' +
                        '<span style=\"font-size:11px; color:#15803d;\">Pekerjaan fisik telah selesai (<b>Quality Control Selesai</b>). Tagihan biaya akan otomatis terbit saat dialihkan ke Vendor.</span>' +
                        '</div>'
                    );
                } else {
                    var progNamaHeader = spk.progress_nama || 'Dalam Pengerjaan';
                    $('#swContainerStatusBiaya').css({
                        'background': '#f0f9ff',
                        'border': '1px solid #bae6fd',
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'space-between',
                        'flex-wrap': 'wrap',
                        'gap': '4px'
                    }).html(
                        '<div>' +
                        '<span class=\"label label-info\" style=\"font-size:11px; padding:3px 8px; margin-right:6px;\"><i class=\"fa fa-clock-o\"></i> BIAYA BELUM DITERBITKAN (BELUM QC)</span>' +
                        '<span style=\"font-size:11px; color:#0369a1;\">Progress: <b>' + escapeHtmlSw(progNamaHeader) + '</b>. Dokumen tagihan biaya (3674) belum pernah terbit karena pekerjaan fisik belum Quality Control (QC).</span>' +
                        '</div>'
                    );
                }
            } else {
                $('#swContainerStatusBiaya').css({
                    'background': '#fffbeb',
                    'border': '1px solid #fde68a',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'space-between'
                }).html(
                    '<div>' +
                    '<span class=\"label label-warning\" style=\"font-size:11px; padding:3px 8px; margin-right:6px;\"><i class=\"fa fa-ban\"></i> ' + (spk.status_biaya_badge || 'TANPA BIAYA (NON-BIAYA)') + '</span>' +
                    '<span style=\"font-size:11px; color:#b45309;\">' + (spk.status_biaya_desc || 'Paket WO murni material unit (tanpa biaya upah / tagihan vendor).') + '</span>' +
                    '</div>'
                );
            }

            if (spk.is_group == 1) {
                $('#swCurrentPelaksana').html('<span class=\"label label-primary\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-users\"></i> GROUP: ' + escapeHtmlSw(spk.group_nama || spk.employee_nama || '-') + '</span>');
            } else if (spk.type_pelaksana == 22) {
                $('#swCurrentPelaksana').html('<span class=\"label label-warning\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-truck\"></i> VENDOR: ' + escapeHtmlSw(spk.employee_nama || '-') + '</span>');
            } else {
                $('#swCurrentPelaksana').html('<span class=\"label label-success\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-building\"></i> INTERNAL: ' + escapeHtmlSw(spk.employee_nama || '-') + '</span>');
            }

            var kSel = $('#swSelectKaryawan');
            kSel.empty().append('<option value=\"\">-- Pilih Karyawan / Group --</option>');

            var listMasterInternal = (res.list_group_master_internal && res.list_group_master_internal.length > 0)
                ? res.list_group_master_internal
                : (res.list_group_master ? $.grep(res.list_group_master, function(mg) { return mg.group_type !== 'external'; }) : []);

            if (listMasterInternal && listMasterInternal.length > 0) {
                var grpOpt = $('<optgroup label=\"Karyawan Group\"></optgroup>');
                $.each(listMasterInternal, function(i, mg) {
                    var isMatch = false;
                    if (spk.type_pelaksana == 11 && (spk.is_group == 1 || spk.is_group == '1')) {
                        if (spk.master_group_id && String(spk.master_group_id) === String(mg.id)) {
                            isMatch = true;
                        } else if (spk.matched_group_val && spk.matched_group_val === ('group_master_' + mg.id)) {
                            isMatch = true;
                        } else if (String(spk.group_id) === String(mg.id)) {
                            isMatch = true;
                        } else if (String(spk.employee_id) === ('group_master_' + mg.id)) {
                            isMatch = true;
                        } else if (spk.group_nama && mg.nama && spk.group_nama.trim().toLowerCase() === mg.nama.trim().toLowerCase()) {
                            isMatch = true;
                        }
                    }
                    var isSel = isMatch ? ' selected' : '';
                    grpOpt.append('<option value=\"group_master_'+mg.id+'\" data-nama=\"'+escapeHtmlSw(mg.nama)+'\" data-isgroup=\"1\" disabled'+isSel+'>'+escapeHtmlSw(mg.nama)+' [Nonaktif]</option>');
                });
                kSel.append(grpOpt);
            }

            var indOpt = $('<optgroup label=\"KARYAWAN INDIVIDUAL\"></optgroup>');
            if (res.list_karyawan && res.list_karyawan.length > 0) {
                $.each(res.list_karyawan, function(i, k) {
                    var isSel = (spk.type_pelaksana == 11 && (!spk.is_group || spk.is_group == 0) && spk.employee_id == k.id) ? ' selected' : '';
                    indOpt.append('<option value=\"'+k.id+'\" data-nama=\"'+escapeHtmlSw(k.nama)+'\" data-isgroup=\"0\"'+isSel+'>'+escapeHtmlSw(k.nama)+'</option>');
                });
            }
            kSel.append(indOpt);

            if (spk.type_pelaksana == 11 && (spk.is_group == 1 || spk.is_group == '1')) {
                var targetVal = spk.matched_group_val || (spk.master_group_id ? ('group_master_' + spk.master_group_id) : '');
                if (targetVal && kSel.find('option[value=\"' + targetVal + '\"]').length > 0) {
                    kSel.val(targetVal);
                } else if (spk.group_nama) {
                    kSel.find('option').each(function() {
                        if ($(this).data('nama') && $(this).data('nama').trim().toLowerCase() === spk.group_nama.trim().toLowerCase()) {
                            kSel.val($(this).val());
                            return false;
                        }
                    });
                }
            }

            var vSel = $('#swSelectVendor');
            vSel.empty().append('<option value=\"\">-- Pilih Vendor / Group --</option>');

            var listMasterExternal = (res.list_group_master_external && res.list_group_master_external.length > 0)
                ? res.list_group_master_external
                : (res.list_group_master ? $.grep(res.list_group_master, function(mg) { return mg.group_type === 'external'; }) : []);

            if (listMasterExternal && listMasterExternal.length > 0) {
                var grpExtOpt = $('<optgroup label=\"Vendor Group\"></optgroup>');
                $.each(listMasterExternal, function(i, mg) {
                    var isMatch = false;
                    if (spk.type_pelaksana == 22 && (spk.is_group == 1 || spk.is_group == '1')) {
                        if (spk.master_group_id && String(spk.master_group_id) === String(mg.id)) {
                            isMatch = true;
                        } else if (spk.matched_group_val && spk.matched_group_val === ('group_master_' + mg.id)) {
                            isMatch = true;
                        } else if (String(spk.group_id) === String(mg.id)) {
                            isMatch = true;
                        } else if (String(spk.employee_id) === ('group_master_' + mg.id)) {
                            isMatch = true;
                        } else if (spk.group_nama && mg.nama && spk.group_nama.trim().toLowerCase() === mg.nama.trim().toLowerCase()) {
                            isMatch = true;
                        }
                    }
                    var isSel = isMatch ? ' selected' : '';
                    grpExtOpt.append('<option value=\"group_master_'+mg.id+'\" data-nama=\"'+escapeHtmlSw(mg.nama)+'\" data-isgroup=\"1\"'+isSel+'>'+escapeHtmlSw(mg.nama)+'</option>');
                });
                vSel.append(grpExtOpt);
            }

            var indExtOpt = $('<optgroup label=\"VENDOR INDIVIDUAL\"></optgroup>');
            if (res.list_vendor && res.list_vendor.length > 0) {
                $.each(res.list_vendor, function(i, v) {
                    var isSel = (spk.type_pelaksana == 22 && (!spk.is_group || spk.is_group == 0) && spk.employee_id == v.id) ? ' selected' : '';
                    indExtOpt.append('<option value=\"'+v.id+'\" data-nama=\"'+escapeHtmlSw(v.nama)+'\" data-isgroup=\"0\"'+isSel+'>'+escapeHtmlSw(v.nama)+'</option>');
                });
            }
            vSel.append(indExtOpt);

            if (spk.type_pelaksana == 22 && (spk.is_group == 1 || spk.is_group == '1')) {
                var targetVal = spk.matched_group_val || (spk.master_group_id ? ('group_master_' + spk.master_group_id) : '');
                if (targetVal && vSel.find('option[value=\"' + targetVal + '\"]').length > 0) {
                    vSel.val(targetVal);
                } else if (spk.group_nama) {
                    vSel.find('option').each(function() {
                        if ($(this).data('nama') && $(this).data('nama').trim().toLowerCase() === spk.group_nama.trim().toLowerCase()) {
                            vSel.val($(this).val());
                            return false;
                        }
                    });
                }
            }

            // Reset status disabled pilihan tipe
            $('#swTypeExternal').prop('disabled', false);
            $('#lblTypeExternal').css('opacity', '1').removeAttr('title');
            $('#swTypeInternal').prop('disabled', false);
            $('#lblTypeInternal').css('opacity', '1').removeAttr('title');

            // Terapkan izin arah berdasarkan setting
            if (res.settings) {
                if (spk.type_pelaksana == 11 && parseInt(res.settings.allow_internal_to_external) === 0) {
                    $('#swTypeExternal').prop('disabled', true);
                    $('#lblTypeExternal').css('opacity', '0.4').attr('title', 'Alih ke Vendor dilarang oleh Pengaturan Sistem');
                }
                if (spk.type_pelaksana == 22 && parseInt(res.settings.allow_external_to_internal) === 0) {
                    $('#swTypeInternal').prop('disabled', true);
                    $('#lblTypeInternal').css('opacity', '0.4').attr('title', 'Alih ke Internal dilarang oleh Pengaturan Sistem');
                }
            }

            // Radio selection: default sesuai SPK saat ini
            if (spk.type_pelaksana == 22) {
                $('#swTypeExternal').prop('checked', true);
            } else {
                $('#swTypeInternal').prop('checked', true);
            }

            // Inisialisasi Group Template Master Dropdown
            var tmplSel = $('#swSelectGroupTemplate');
            tmplSel.empty().append('<option value=\"\">-- Muat Template --</option>');
            if (res.list_group_master && res.list_group_master.length > 0) {
                $.each(res.list_group_master, function(i, mg) {
                    var isInternal = (mg.group_type === 'internal' || mg.group_type !== 'external');
                    var hasBadan = false;
                    if (mg.members && mg.members.length > 0) {
                        $.each(mg.members, function(mi, mm) {
                            if (mm.pajak_default === 'pph23' || (mm.entity_type == 22 && mm.tipe === 2)) {
                                hasBadan = true;
                            }
                        });
                    }
                    if (isInternal) {
                        tmplSel.append('<option value=\"' + mg.id + '\" disabled>' + escapeHtmlSw(mg.nama) + ' (Internal) [Nonaktif]</option>');
                    } else if (hasBadan) {
                        tmplSel.append('<option value=\"' + mg.id + '\" disabled>' + escapeHtmlSw(mg.nama) + ' [Mengandung PT/Badan - Nonaktif]</option>');
                    } else {
                        tmplSel.append('<option value=\"' + mg.id + '\">' + escapeHtmlSw(mg.nama) + ' (Vendor Perorangan)</option>');
                    }
                });
            }

            // Inisialisasi Nama Group
            var initGroupName = spk.group_nama || (spk.is_group == 1 ? spk.employee_nama : '') || ('Group SPK ' + spk.no_spk);
            $('#swGroupNameInput').val(initGroupName);

            // Inisialisasi Member Picker Dropdown
            populateMemberPickerOptions();

            // Inisialisasi Data Alokasi Group
            currentGroupAllocations = [];
            if (res.existing_member_details && res.existing_member_details.length > 0) {
                var seenMemberKeys = {};
                $.each(res.existing_member_details, function(i, em) {
                    var mKey = String(em.employee_id) + '_' + (em.entity_type || 11);
                    if (seenMemberKeys[mKey]) return;
                    seenMemberKeys[mKey] = true;
                    var isBadanMember = (em.entity_type == 22 && (em.pajak_default === 'pph23' || em.is_badan == 1));
                    if (isBadanMember) return;
                    currentGroupAllocations.push({
                        employee_id: String(em.employee_id),
                        employee_nama: em.employee_nama || ('Pelaksana ' + em.employee_id),
                        entity_type: parseInt(em.entity_type) || 11,
                        proporsi_persen: parseFloat(em.proporsi_persen || 0),
                        proporsi_nominal: parseFloat(em.proporsi_nominal || 0),
                        is_lead: parseInt(em.is_lead) || (currentGroupAllocations.length === 0 ? 1 : 0),
                        pajak_default: 'pph21'
                    });
                });
            } else if (res.existing_members && res.existing_members.length > 0) {
                var allKaryawanMap = {};
                if (res.list_karyawan) {
                    $.each(res.list_karyawan, function(i, k) {
                        allKaryawanMap[String(k.id)] = k.nama;
                    });
                }
                $.each(res.existing_members, function(i, emId) {
                    currentGroupAllocations.push({
                        employee_id: String(emId),
                        employee_nama: allKaryawanMap[String(emId)] || ('Karyawan ' + emId),
                        entity_type: 11,
                        proporsi_persen: 0.00,
                        proporsi_nominal: 0,
                        is_lead: (i === 0 ? 1 : 0),
                        pajak_default: 'pph21'
                    });
                });
                autoSplitGroupAllocations();
            } else if (spk.catatan_pelaksana) {
                var catLines = spk.catatan_pelaksana.split(/[\\r\\n]+/);
                var allKaryawanMap = {};
                if (res.list_karyawan) {
                    $.each(res.list_karyawan, function(i, k) {
                        allKaryawanMap[String(k.id)] = k.nama;
                    });
                }
                var parsedMembers = [];
                $.each(catLines, function(i, cl) {
                    var m = cl.trim().match(/^([0-9]+)\\\\s*-\\\\s*(.*)$/);
                    if (m) {
                        var eId = m[1];
                        var eNm = allKaryawanMap[eId] || m[2].split(' (')[0].trim();
                        parsedMembers.push({
                            employee_id: String(eId),
                            employee_nama: eNm,
                            entity_type: 11,
                            proporsi_persen: 0.00,
                            proporsi_nominal: 0,
                            is_lead: (parsedMembers.length === 0 ? 1 : 0),
                            pajak_default: 'pph21'
                        });
                    }
                });
                if (parsedMembers.length > 0) {
                    currentGroupAllocations = parsedMembers;
                    autoSplitGroupAllocations();
                }
            }

            renderGroupAllocationsTable();

            renderSwitchFormState();
        }

        var currentGroupAllocations = [];

        function formatRupiahSw(num) {
            if (isNaN(num)) return '0';
            return Number(Math.round(num)).toLocaleString('id-ID');
        }

        function populateMemberPickerOptions() {
            var entType = $('#swAddEntityType').val();
            var picker = $('#swAddMemberPicker');
            picker.empty().append('<option value=\"\">-- Pilih Pelaksana --</option>');
            if (!currentSwitchSpkData) return;

            if (entType == '22') {
                if (currentSwitchSpkData.list_vendor && currentSwitchSpkData.list_vendor.length > 0) {
                    $.each(currentSwitchSpkData.list_vendor, function(i, v) {
                        var isBadan = (v.is_badan == 1 || v.tipe === 2 || (v.pajak_default && v.pajak_default === 'pph23'));
                        if (isBadan) {
                            picker.append('<option value=\"' + v.id + '\" disabled data-nama=\"' + escapeHtmlSw(v.nama) + '\" data-pajak=\"pph23\" data-tipe=\"2\" data-is_badan=\"1\">' + escapeHtmlSw(v.nama) + ' [Badan - Khusus Individual]</option>');
                        } else {
                            picker.append('<option value=\"' + v.id + '\" data-nama=\"' + escapeHtmlSw(v.nama) + '\" data-pajak=\"pph21\" data-tipe=\"1\" data-is_badan=\"0\">' + escapeHtmlSw(v.nama) + ' [Perorangan - PPh 21]</option>');
                        }
                    });
                }
            } else {
                if (currentSwitchSpkData.list_karyawan && currentSwitchSpkData.list_karyawan.length > 0) {
                    $.each(currentSwitchSpkData.list_karyawan, function(i, k) {
                        picker.append('<option value=\"' + k.id + '\" disabled data-nama=\"' + escapeHtmlSw(k.nama) + '\">' + escapeHtmlSw(k.nama) + ' (ID: ' + k.id + ') [Nonaktif]</option>');
                    });
                }
            }
        }

        $(document).on('change', '#swAddEntityType', function() {
            populateMemberPickerOptions();
        });

        $(document).on('click', '#btnAddMemberToGroup', function() {
            var entType = parseInt($('#swAddEntityType').val()) || 11;
            var picker = $('#swAddMemberPicker');
            var empId = picker.val();
            var selOpt = picker.find('option:selected');
            var empNama = selOpt.data('nama') || selOpt.text();

            if (!empId) {
                showSwalAlert('Peringatan', 'Silakan pilih pelaksana terlebih dahulu.', 'warning');
                return;
            }

            if (entType === 11) {
                showSwalAlert('Peringatan', 'Anggota Karyawan dalam Group dinonaktifkan. Group hanya diperbolehkan untuk Vendor.', 'warning');
                return;
            }

            if (entType == 22 && (selOpt.data('tipe') == 2 || selOpt.data('pajak') === 'pph23' || selOpt.data('is_badan') == 1)) {
                showSwalAlert('Peringatan', 'Vendor Badan Usaha (PT/CV) tidak diperbolehkan masuk ke dalam Group Pelaksana. Vendor Badan wajib ditugaskan langsung melalui Vendor Individual.', 'warning');
                return;
            }

            var isDuplicate = false;
            $.each(currentGroupAllocations, function(i, a) {
                if (String(a.employee_id) === String(empId) && parseInt(a.entity_type) === entType) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                showSwalAlert('Peringatan', empNama + ' sudah ada di daftar alokasi group.', 'warning');
                return;
            }

            var defaultTax = 'pph21';
            var isFirst = (currentGroupAllocations.length === 0);

            currentGroupAllocations.push({
                employee_id: String(empId),
                employee_nama: empNama,
                entity_type: entType,
                proporsi_persen: 0.00,
                proporsi_nominal: 0,
                is_lead: isFirst ? 1 : 0,
                pajak_default: defaultTax
            });

            autoSplitGroupAllocations();
            populateMemberPickerOptions();
        });

        function autoSplitGroupAllocations() {
            if (!currentGroupAllocations || currentGroupAllocations.length === 0) {
                renderGroupAllocationsTable();
                return;
            }
            var count = currentGroupAllocations.length;
            var equalShare = parseFloat((100 / count).toFixed(2));
            var accumulated = 0;

            for (var i = 0; i < count; i++) {
                if (i === count - 1) {
                    currentGroupAllocations[i].proporsi_persen = parseFloat((100 - accumulated).toFixed(2));
                } else {
                    currentGroupAllocations[i].proporsi_persen = equalShare;
                    accumulated += equalShare;
                }
            }
            renderGroupAllocationsTable();
        }

        $(document).on('click', '#btnAutoSplitGroup', function() {
            autoSplitGroupAllocations();
        });

        $(document).on('click', '.sw-remove-alloc', function() {
            var idx = $(this).data('idx');
            if (idx !== undefined && currentGroupAllocations[idx]) {
                var wasLead = (currentGroupAllocations[idx].is_lead == 1);
                currentGroupAllocations.splice(idx, 1);
                if (wasLead && currentGroupAllocations.length > 0) {
                    currentGroupAllocations[0].is_lead = 1;
                }
                autoSplitGroupAllocations();
                populateMemberPickerOptions();
            }
        });

        $(document).on('input change', '.sw-alloc-pct', function() {
            var idx = $(this).data('idx');
            var val = parseFloat($(this).val()) || 0;
            if (idx !== undefined && currentGroupAllocations[idx]) {
                currentGroupAllocations[idx].proporsi_persen = val;
                renderGroupAllocationsTable(true);
            }
        });

        $(document).on('change', '.sw-alloc-tax', function() {
            var val = $(this).val();
            // Seragamkan pilihan pajak ke seluruh member dalam group
            $.each(currentGroupAllocations, function(i, a) {
                a.pajak_default = val;
            });
            renderGroupAllocationsTable(false);
            populateMemberPickerOptions();
            syncGroupCatatanFromAllocations();
        });

        $(document).on('change', 'input[name=\"sw_alloc_lead_radio\"]', function() {
            var leadIdx = $(this).data('idx');
            $.each(currentGroupAllocations, function(i, a) {
                a.is_lead = (i == leadIdx) ? 1 : 0;
            });
            syncGroupCatatanFromAllocations();
        });

        $(document).on('change', '#swSelectGroupTemplate', function() {
            var templateId = $(this).val();
            if (!templateId || !currentSwitchSpkData || !currentSwitchSpkData.list_group_master) return;

            var chosenTemplate = null;
            $.each(currentSwitchSpkData.list_group_master, function(i, mg) {
                if (String(mg.id) === String(templateId)) {
                    chosenTemplate = mg;
                    return false;
                }
            });

            if (!chosenTemplate) return;

            if (chosenTemplate.members && chosenTemplate.members.length > 0) {
                var tmplTaxSeen = {};
                var hasInternal = false;
                var hasBadan = false;
                $.each(chosenTemplate.members, function(i, m) {
                    var ent = parseInt(m.entity_type) || 11;
                    if (ent === 11) hasInternal = true;
                    var t = m.pajak_default || (ent == 22 ? 'pph23' : 'pph21');
                    tmplTaxSeen[t] = true;
                    if (t === 'pph23' || (ent == 22 && m.tipe === 2)) hasBadan = true;
                });
                if (hasInternal) {
                    showSwalAlert('Peringatan', 'Template ini mengandung anggota Karyawan Internal yang telah dinonaktifkan.', 'warning');
                    $(this).val('');
                    return;
                }
                if (hasBadan) {
                    showSwalAlert('Template Tidak Kompatibel', 'Template ini mengandung anggota Badan Usaha (PT/CV). Group Pelaksana hanya diperuntukkan bagi Vendor Perorangan (PPh 21).', 'error');
                    $(this).val('');
                    return;
                }

                if (chosenTemplate.nama) {
                    $('#swGroupNameInput').val(chosenTemplate.nama);
                }

                currentGroupAllocations = [];
                $.each(chosenTemplate.members, function(i, m) {
                    var ent = parseInt(m.entity_type) || 11;
                    currentGroupAllocations.push({
                        employee_id: String(m.employee_id),
                        employee_nama: m.employee_nama || ('Member ' + m.employee_id),
                        entity_type: ent,
                        proporsi_persen: parseFloat(m.proporsi_persen || 0),
                        proporsi_nominal: parseFloat(m.proporsi_nominal || 0),
                        is_lead: parseInt(m.is_lead) || (i === 0 ? 1 : 0),
                        pajak_default: 'pph21'
                    });
                });
                autoSplitGroupAllocations();
                populateMemberPickerOptions();
            }
        });

        function getEffectiveSpkCostBudget() {
            if (!currentSwitchSpkData) return 0;
            // 1. Jika ada transaksi biaya utama (jenis 3674) di biaya_list
            if (currentSwitchSpkData.biaya_list && currentSwitchSpkData.biaya_list.length > 0) {
                var foundPrimary = null;
                $.each(currentSwitchSpkData.biaya_list, function(i, b) {
                    if (b.jenis == '3674' && parseFloat(b.transaksi_nilai || 0) > 0) {
                        foundPrimary = parseFloat(b.transaksi_nilai);
                        return false;
                    }
                });
                if (foundPrimary !== null) return foundPrimary;
                if (parseFloat(currentSwitchSpkData.biaya_list[0].transaksi_nilai || 0) > 0) {
                    return parseFloat(currentSwitchSpkData.biaya_list[0].transaksi_nilai);
                }
            }
            // 2. Jika ada total estimasi biaya dari RAB Sub / item biaya (> 0), gunakan itu
            if (parseFloat(currentSwitchSpkData.total_estimasi_biaya || 0) > 0) {
                return parseFloat(currentSwitchSpkData.total_estimasi_biaya);
            }
            // 3. Fallback jika borongan tanpa rincian biaya: gunakan nilai_sub_fase atau nilai_kontrak
            if (currentSwitchSpkData.spk) {
                return parseFloat(currentSwitchSpkData.spk.nilai_sub_fase || currentSwitchSpkData.spk.nilai_kontrak || 0);
            }
            return 0;
        }

        function syncGroupCatatanFromAllocations() {
            var totalNilaiSpk = getEffectiveSpkCostBudget();

            // Reset: bangun ulang seluruh isi textarea dari data alokasi aktif
            var lines = [];
            $.each(currentGroupAllocations, function(i, a) {
                var entLbl = (a.entity_type == 22) ? 'Vendor' : 'Karyawan';
                var leadStr = (a.is_lead == 1) ? ' [LEAD]' : '';
                var nom = Math.round((a.proporsi_persen / 100) * totalNilaiSpk);
                var taxLbl = (a.pajak_default || 'pph21').toUpperCase();
                lines.push(a.employee_id + ' - ' + a.employee_nama + ' (' + entLbl + ') | ' + parseFloat(a.proporsi_persen).toFixed(2) + '% (Rp ' + formatRupiahSw(nom) + ') | ' + taxLbl + leadStr);
            });

            // Reset textarea lalu inject baris auto yang sudah fresh
            var finalContent = lines.join('\\n');
            $('#swCatatanPelaksanaGroup').val(finalContent);
        }

        function renderGroupAllocationsTable(keepInputs) {
            var tbody = $('#swTbodyGroupAllocations');
            var totalNilaiSpk = getEffectiveSpkCostBudget();

            var countEmp = 0;
            var countVen = 0;
            var sumPct = 0;
            var totalNominal = 0;

            $.each(currentGroupAllocations, function(i, a) {
                var p = parseFloat(a.proporsi_persen || 0);
                sumPct += p;
                var nom = Math.round((p / 100) * totalNilaiSpk);
                a.proporsi_nominal = nom;
                totalNominal += nom;

                if (a.entity_type == 22) {
                    countVen++;
                } else {
                    countEmp++;
                }
            });

            var hasPph21 = false;
            var hasPph23 = false;
            $.each(currentGroupAllocations, function(i, a) {
                if (a.pajak_default === 'pph21') hasPph21 = true;
                if (a.pajak_default === 'pph23') hasPph23 = true;
            });

            var grpTypeStr = 'Group Vendor';
            var grpBadgeClass = 'label-warning';
            if (hasPph23 && !hasPph21) {
                grpTypeStr = 'Group Vendor Badan (PPh 23)';
                grpBadgeClass = 'label-warning';
            } else if (hasPph21 && !hasPph23) {
                grpTypeStr = 'Group Vendor Perorangan (PPh 21)';
                grpBadgeClass = 'label-info';
            } else if (hasPph21 && hasPph23) {
                grpTypeStr = 'PERINGATAN: Pajak Campuran!';
                grpBadgeClass = 'label-danger';
            }

            $('#swGroupTypeBadge').removeClass('label-success label-primary label-warning label-info label-danger bg-purple')
                .addClass(grpBadgeClass)
                .text(grpTypeStr);
            $('#swGroupPlafonBadge').html('<i class=\"fa fa-shield\"></i> Plafon: Rp ' + formatRupiahSw(totalNilaiSpk));

            if (!keepInputs) {
                tbody.empty();
                if (currentGroupAllocations.length === 0) {
                    tbody.html('<tr><td colspan=\"8\" style=\"text-align: center; color: #94a3b8; padding: 14px;\"><i class=\"fa fa-info-circle\"></i> Belum ada pelaksana yang ditambahkan. Gunakan dropdown di atas untuk menambahkan Karyawan atau Vendor.</td></tr>');
                } else {
                    $.each(currentGroupAllocations, function(i, a) {
                        var entBadge = (a.entity_type == 22)
                            ? '<span class=\"label label-warning\" style=\"font-size:10px;\"><i class=\"fa fa-truck\"></i> Vendor</span>'
                            : '<span class=\"label label-success\" style=\"font-size:10px;\"><i class=\"fa fa-user\"></i> Karyawan</span>';

                        var tr = '<tr style=\"vertical-align: middle;\">';
                        tr += '<td style=\"text-align: center; color: #64748b;\">' + (i + 1) + '</td>';
                        tr += '<td style=\"text-align: center;\">' + entBadge + '</td>';
                        tr += '<td><b>' + escapeHtmlSw(a.employee_nama) + '</b> <span class=\"text-muted\" style=\"font-size:10px; font-family:monospace;\">ID: ' + a.employee_id + '</span></td>';
                        tr += '<td style=\"text-align: center;\"><input type=\"number\" step=\"0.01\" min=\"0\" max=\"100\" class=\"form-control input-sm sw-alloc-pct\" data-idx=\"' + i + '\" value=\"' + parseFloat(a.proporsi_persen).toFixed(2) + '\" style=\"width:75px; height:26px; text-align:right; font-size:11.5px; font-weight:bold; display:inline-block;\"> %</td>';
                        tr += '<td style=\"text-align: right; font-weight:600; color:#047857; font-family:monospace;\" id=\"swNominal_' + i + '\">Rp ' + formatRupiahSw(a.proporsi_nominal) + '</td>';
                        tr += '<td style=\"text-align: center;\"><input type=\"radio\" name=\"sw_alloc_lead_radio\" data-idx=\"' + i + '\" ' + (a.is_lead == 1 ? 'checked' : '') + ' style=\"cursor:pointer;\"></td>';
                        tr += '<td style=\"text-align: center;\">';
                        tr += '<select class=\"form-control input-sm sw-alloc-tax\" data-idx=\"' + i + '\" style=\"width:120px; height:26px; font-size:11px; padding:2px 4px;\">';
                        tr += '<option value=\"pph21\" ' + (a.pajak_default == 'pph21' ? 'selected' : '') + '>PPh 21 (Perorangan)</option>';
                        tr += '<option value=\"pph23\" ' + (a.pajak_default == 'pph23' ? 'selected' : '') + '>PPh 23 (Vendor/Jasa)</option>';
                        tr += '<option value=\"pph42\" ' + (a.pajak_default == 'pph42' ? 'selected' : '') + '>PPh Final 4(2)</option>';
                        tr += '<option value=\"none\" ' + (a.pajak_default == 'none' ? 'selected' : '') + '>Bebas Pajak (0%)</option>';
                        tr += '</select>';
                        tr += '</td>';
                        tr += '<td style=\"text-align: center;\"><button type=\"button\" class=\"btn btn-danger btn-xs sw-remove-alloc\" data-idx=\"' + i + '\" style=\"padding:1px 6px;\" title=\"Hapus member\"><i class=\"fa fa-trash\"></i></button></td>';
                        tr += '</tr>';
                        tbody.append(tr);
                    });
                }
            } else {
                $.each(currentGroupAllocations, function(i, a) {
                    $('#swNominal_' + i).text('Rp ' + formatRupiahSw(a.proporsi_nominal));
                });
            }

            $('#swTotalAllocPercent').text(sumPct.toFixed(2) + '%');
            $('#swTotalAllocNominal').text('Rp ' + formatRupiahSw(totalNominal));

            var diff = Math.abs(sumPct - 100);
            var is100 = (diff <= 0.05);

            if (is100 && currentGroupAllocations.length > 0) {
                $('#swStatusAllocBadge').html('<span class=\"label label-success\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-check-circle\"></i> PAS 100.00%</span>');
                $('#swAlertAllocZeroSum').hide();
            } else {
                if (currentGroupAllocations.length === 0) {
                    $('#swStatusAllocBadge').html('<span class=\"label label-default\" style=\"font-size:11px; padding:3px 8px;\">Kosong</span>');
                    $('#swAlertAllocZeroSum').html('<div class=\"alert alert-warning\" style=\"margin:0; padding:6px 10px; font-size:11px;\"><i class=\"fa fa-warning\"></i> Alokasi masih kosong. Silakan tambahkan minimal 1 pelaksana.</div>').show();
                } else if (sumPct < 100) {
                    var diffShort = (100 - sumPct).toFixed(2);
                    $('#swStatusAllocBadge').html('<span class=\"label label-danger\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-exclamation-triangle\"></i> KURANG ' + diffShort + '%</span>');
                    $('#swAlertAllocZeroSum').html('<div class=\"alert alert-warning\" style=\"margin:0; padding:6px 10px; font-size:11px;\"><i class=\"fa fa-warning\"></i> Total proporsi masih <b>KURANG ' + diffShort + '%</b>. Klik tombol <b>Bagi Rata</b> atau sesuaikan persentase hingga pas 100.00%.</div>').show();
                } else {
                    var diffExcess = (sumPct - 100).toFixed(2);
                    $('#swStatusAllocBadge').html('<span class=\"label label-danger\" style=\"font-size:11px; padding:3px 8px;\"><i class=\"fa fa-exclamation-triangle\"></i> LEBIH ' + diffExcess + '%</span>');
                    $('#swAlertAllocZeroSum').html('<div class=\"alert alert-danger\" style=\"margin:0; padding:6px 10px; font-size:11px;\"><i class=\"fa fa-ban\"></i> Total proporsi <b>LEBIH ' + diffExcess + '%</b>. Total proporsi wajib tepat 100.00%.</div>').show();
                }
            }

            syncGroupCatatanFromAllocations();
            checkGroupZeroSumSubmitState();
        }

        function checkGroupZeroSumSubmitState() {
            var selectedType = $('input[name=\"sw_type_pelaksana\"]:checked').val();
            if (selectedType !== 'group') return;
            if (currentSwitchSpkData && currentSwitchSpkData.has_active_biaya && !currentSwitchSpkData.can_void) {
                setSubmitButtonState(true);
                return;
            }
            if (!currentGroupAllocations || currentGroupAllocations.length === 0) {
                setSubmitButtonState(true, 'Wajib Memilih Minimal 1 Pelaksana');
                return;
            }
            var sumPct = 0;
            $.each(currentGroupAllocations, function(i, a) {
                sumPct += parseFloat(a.proporsi_persen || 0);
            });
            if (Math.abs(sumPct - 100) > 0.05) {
                setSubmitButtonState(true, 'Alokasi Wajib 100% (Saat Ini: ' + sumPct.toFixed(2) + '%)');
                return;
            }
            setSubmitButtonState(false);
        }

        function escapeHtmlSw(str) {
            if (!str) return '';
            return $('<div>').text(str).html();
        }

        function updateSelectedVendorBadge() {
            var vVal = $('#swSelectVendor').val();
            var vNama = $('#swSelectVendor option:selected').data('nama') || $('#swSelectVendor option:selected').text();
            if (vVal && vVal !== '') {
                $('#swAutoPostVendorNama').html('<i class=\"fa fa-truck text-orange\"></i> ' + escapeHtmlSw(vNama));
            } else {
                $('#swAutoPostVendorNama').html('<span class=\"text-danger\"><i class=\"fa fa-exclamation-triangle\"></i> Belum Dipilih</span>');
            }
        }

        $(document).on('change', '#swSelectVendor', function() {
            renderSwitchFormState();
        });

        $(document).on('change', '#swSelectKaryawan', function() {
            renderSwitchFormState();
        });

        var activeGroupMembers = [];
        var lastRenderedGroupId = null;
        var lastRenderedEntityType = null;
        var isSyncingFromCheckbox = false;
        var isSyncingFromTextarea = false;

        function renderGroupMembersAndCatatan(selectedId, entityType) {
            if (!currentSwitchSpkData) return;
            entityType = entityType ? parseInt(entityType) : 11;
            var spk = currentSwitchSpkData.spk;
            var selectedGroupId = selectedId ? String(selectedId).replace('group_master_', '').replace('group_proj_', '').replace('group_', '') : '';
            var currentGroupObj = null;

            if (currentSwitchSpkData.list_group_master) {
                $.each(currentSwitchSpkData.list_group_master, function(i, mg) {
                    if (String(mg.id) === String(selectedGroupId)) {
                        currentGroupObj = mg;
                        return false;
                    }
                });
            }

            if (!currentGroupObj) {
                $('#containerGroupMemberDanKeterangan').hide();
                activeGroupMembers = [];
                lastRenderedGroupId = null;
                lastRenderedEntityType = null;
                return;
            }

            $('#containerGroupMemberDanKeterangan').show();

            // Set Header label dan Notice Info dinamis sesuai entityType (Vendor vs Karyawan)
            if (entityType == 22) {
                $('#swMemberHeaderLabel').html('<i class=\"fa fa-truck text-orange\"></i> daftar member vendor');
                $('#swMemberNoticeInfo').html(
                    '<i class=\"fa fa-info-circle text-primary\"></i> <b>Vendor tidak ditemukan?</b><br>' +
                    'Hanya menampilkan vendor aktif di <b>Master Data Vendor / Supplier</b>. Hubungi bagian Master Data jika ingin menambah vendor ke sistem.'
                );
            } else {
                $('#swMemberHeaderLabel').html('<i class=\"fa fa-users text-primary\"></i> daftar member karyawan');
                $('#swMemberNoticeInfo').html(
                    '<i class=\"fa fa-info-circle text-primary\"></i> <b>Karyawan tidak ditemukan?</b><br>' +
                    'Hanya menampilkan karyawan dengan status <code>allow_project = 1</code> di <b>Master Data Employee</b>. Hubungi bagian Master Data untuk mengeset <i>Allow Project = 1</i> jika ingin menambah karyawan ke daftar/group ini.'
                );
            }

            if (String(lastRenderedGroupId) !== String(selectedGroupId) || lastRenderedEntityType !== entityType) {
                lastRenderedGroupId = selectedGroupId;
                lastRenderedEntityType = entityType;
                activeGroupMembers = currentGroupObj.members || [];

                // Cek apakah group yang dipilih sama persis dengan group aktif SPK saat ini
                var isPrevGroup = (spk.is_group == 1 || spk.is_group == '1');
                var spkEntType = (spk.type_pelaksana == 22) ? 22 : 11;
                var isSameGroup = false;

                if (isPrevGroup && spkEntType === entityType && selectedId) {
                    if (String(spk.employee_id) === String(selectedId) ||
                        ('group_master_' + spk.group_id) === String(selectedId) ||
                        ('group_master_' + spk.master_group_id) === String(selectedId) ||
                        (spk.matched_group_val && spk.matched_group_val === String(selectedId)) ||
                        (spk.master_group_id && String(spk.master_group_id) === String(selectedGroupId)) ||
                        (spk.group_id && String(spk.group_id) === String(selectedGroupId))) {
                        isSameGroup = true;
                    }
                }

                // Kumpulkan set ID member yang aktif untuk group ini / SPK ini
                var activeMemberIdSet = {};
                var currentCatVal = '';

                if (isSameGroup) {
                    // Skenario 1: Group yang dipilih SAMA dengan group aktif SPK saat ini
                    // Pertahankan member dan catatan kustom yang sudah ada di SPK ini

                    // 1. Dari master group members
                    if (currentGroupObj.members && currentGroupObj.members.length > 0) {
                        $.each(currentGroupObj.members, function(i, m) {
                            activeMemberIdSet[String(m.employee_id)] = true;
                        });
                    }

                    // 2. Dari existing_members pada SPK
                    if (currentSwitchSpkData.existing_members && currentSwitchSpkData.existing_members.length > 0) {
                        $.each(currentSwitchSpkData.existing_members, function(i, emId) {
                            activeMemberIdSet[String(emId)] = true;
                        });
                    }

                    // 3. Dari catatan_pelaksana di SPK atau textarea saat ini (format '1051 - nama')
                    currentCatVal = (spk.catatan_pelaksana || $('#swCatatanPelaksana').val() || '');
                    if (currentCatVal) {
                        var catLines = currentCatVal.split(/[\\r\\n]+/);
                        $.each(catLines, function(i, cl) {
                            var m = cl.trim().match(/^([0-9]+)\\s*-/);
                            if (m) {
                                activeMemberIdSet[String(m[1])] = true;
                            }
                        });
                    }
                } else {
                    // Skenario 2: Group yang dipilih adalah GROUP BARU (berbeda dari pelaksana lama SPK)
                    // Ambil murni seluruh member dari master data group yang baru dipilih
                    if (currentGroupObj.members && currentGroupObj.members.length > 0) {
                        $.each(currentGroupObj.members, function(i, m) {
                            activeMemberIdSet[String(m.employee_id)] = true;
                        });
                    }
                    // Reset textarea catatan pelaksana agar bersih dari catatan pelaksana lama SPK
                    $('#swCatatanPelaksana').val('');
                }

                // Tampilkan kandidat member berdasarkan entityType (Vendor vs Karyawan)
                var candidateList = (entityType == 22)
                    ? (currentSwitchSpkData.list_vendor || [])
                    : (currentSwitchSpkData.list_karyawan || []);
                var htmlMembers = '';
                var checkedCount = 0;

                if (candidateList.length > 0) {
                    $.each(candidateList, function(i, item) {
                        var isBadan = (item.is_badan == 1 || item.tipe === 2 || (item.pajak_default && item.pajak_default === 'pph23'));
                        if (entityType == 22 && isBadan) {
                            return; // Lewati vendor Badan Usaha (PT/CV), Group khusus Perorangan
                        }
                        var isChecked = activeMemberIdSet[String(item.id)] ? true : false;
                        if (isChecked) {
                            checkedCount++;
                        }
                        var searchMeta = (item.nama + ' ' + item.id).toLowerCase();
                        var itemTax = (entityType == 22)
                            ? (item.pajak_default || (item.tipe === 2 ? 'pph23' : 'pph21'))
                            : 'pph21';
                        var badgeHtml = '';
                        if (entityType == 22) {
                            badgeHtml = '<span class=\"badge\" style=\"background-color: #059669; color: #fff; font-size: 9.5px; padding: 1px 5px; margin-left: 4px; font-weight: 600;\">[Perorangan - PPh 21]</span>';
                        }

                        htmlMembers += '<div class=\"sw-member-row\" data-id=\"' + item.id + '\" data-pajak=\"' + itemTax + '\" data-search=\"' + escapeHtmlSw(searchMeta) + '\" style=\"padding: 4px 6px; border-bottom: 1px dashed #f1f5f9; display: flex; align-items: center; transition: opacity 0.2s;\">';
                        htmlMembers += '  <label style=\"margin: 0; font-weight: 500; font-size: 12px; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 8px; width: 100%;\">';
                        htmlMembers += '    <input type=\"checkbox\" class=\"sw-check-member\" value=\"' + item.id + '\" data-id=\"' + item.id + '\" data-pajak=\"' + itemTax + '\" data-nama=\"' + escapeHtmlSw(item.nama) + '\" ' + (isChecked ? 'checked' : '') + ' style=\"cursor: pointer; margin: 0; width: 15px; height: 15px;\">';
                        htmlMembers += '    <span style=\"flex: 1;\">' + escapeHtmlSw(item.nama) + badgeHtml + '</span>';
                        htmlMembers += '    <span class=\"text-muted\" style=\"font-size: 10.5px; font-family: monospace;\">ID: ' + item.id + '</span>';
                        htmlMembers += '  </label>';
                        htmlMembers += '</div>';
                    });
                } else {
                    var emptyMsg = (entityType == 22) ? 'Tidak ada data vendor aktif' : 'Tidak ada data karyawan allow_project';
                    htmlMembers = '<div style=\"padding: 12px; text-align: center; color: #94a3b8; font-size: 11.5px;\"><i class=\"fa fa-info-circle\"></i> ' + emptyMsg + '</div>';
                }
                $('#swListGroupMembers').html(htmlMembers);
                $('#swMemberSelectedCount').text(checkedCount + ' terpilih');
                $('#swSearchMember').val('');

                // Inisialisasi status penguncian pajak & isi keterangan
                updateSwMemberCheckboxesLockState(entityType);

                if (isSameGroup && currentCatVal && currentCatVal.trim() !== '') {
                    $('#swCatatanPelaksana').val(currentCatVal);
                    syncSwCheckboxesFromTextarea();
                } else {
                    syncSwCatatanPelaksana();
                }
            }
        }

        var currentLockedSwTax = null;

        // Fungsi Pengunci Anti-Lintas Pajak: 1 Group Vendor WAJIB Homogen (PPh 23 ATAU PPh 21)
        function updateSwMemberCheckboxesLockState(entityType) {
            entityType = (entityType !== undefined) ? parseInt(entityType) : (lastRenderedEntityType ? parseInt(lastRenderedEntityType) : 11);

            if (entityType !== 22) {
                currentLockedSwTax = null;
                $('#swMemberTaxLockBadge').hide();
                $('#swListGroupMembers .sw-member-row').removeClass('sw-row-disabled').css({'opacity': '1', 'cursor': 'default'});
                $('#swListGroupMembers .sw-check-member').prop('disabled', false).css({'cursor': 'pointer'});
                $('#swListGroupMembers .sw-member-row label').css({'cursor': 'pointer'});
                return;
            }

            currentLockedSwTax = 'pph21';
            var checkedMembers = $('#swListGroupMembers .sw-check-member:checked');
            var badgeText = (checkedMembers.length > 0)
                ? '<i class=\"fa fa-lock\"></i> Terkunci: Perorangan (PPh 21)'
                : '<i class=\"fa fa-users\"></i> Khusus Vendor Perorangan (PPh 21)';
            $('#swMemberTaxLockBadge').css({'background-color': '#059669', 'color': '#ffffff'}).html(badgeText).show();

            $('#swListGroupMembers .sw-member-row').each(function() {
                var chk = $(this).find('.sw-check-member');
                var chkTax = chk.data('pajak') || 'pph21';

                if (chkTax !== 'pph23') {
                    $(this).removeClass('sw-row-disabled').css({'opacity': '1', 'cursor': 'default'});
                    chk.prop('disabled', false).css({'cursor': 'pointer'});
                    $(this).find('label').css({'cursor': 'pointer'});
                } else {
                    if (chk.is(':checked')) {
                        chk.prop('checked', false);
                    }
                    $(this).addClass('sw-row-disabled').css({'opacity': '0.35', 'cursor': 'not-allowed'});
                    chk.prop('disabled', true).css({'cursor': 'not-allowed'});
                    $(this).find('label').css({'cursor': 'not-allowed'});
                }
            });
        }

        // Cegah klik pada baris member yang terkunci karena beda jenis pajak dan tampilkan dialog edukatif
        $(document).on('click', '.sw-member-row.sw-row-disabled, .sw-member-row.sw-row-disabled label, .sw-member-row.sw-row-disabled input', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showSwalAlert(
                'Khusus Vendor Perorangan',
                'Group pelaksana eksternal <b>hanya diperuntukkan bagi Vendor Perorangan (Freelance/Tukang - PPh 21)</b>.<br><br>' +
                'Vendor Badan Usaha (PT/CV - PPh 23) wajib ditugaskan langsung melalui pilihan <b>Vendor Individual</b>.',
                'warning'
            );
            return false;
        });

        // Sinkronisasi Sisi 1 -> Sisi 2 (Checkbox -> Textarea Keterangan)
        function syncSwCatatanPelaksana() {
            if (isSyncingFromTextarea) return;
            isSyncingFromCheckbox = true;

            var currentTxt = $('#swCatatanPelaksana').val() || '';
            var lines = currentTxt.split(/[\\r\\n]+/);

            var manualLines = [];
            var knownEntityMap = {};

            if (currentSwitchSpkData) {
                if (currentSwitchSpkData.list_karyawan) {
                    $.each(currentSwitchSpkData.list_karyawan, function(i, k) {
                        knownEntityMap[String(k.id)] = k.nama;
                    });
                }
                if (currentSwitchSpkData.list_vendor) {
                    $.each(currentSwitchSpkData.list_vendor, function(i, v) {
                        knownEntityMap[String(v.id)] = v.nama;
                    });
                }
            }

            $.each(lines, function(i, line) {
                var trimmed = line.trim();
                if (!trimmed) return;

                var match = trimmed.match(/^([0-9]+)\\s*-\\s*(.*)$/);
                if (match) {
                    var empId = match[1];
                    if (knownEntityMap[empId]) {
                        return; // Ini adalah baris member yang dikenal, akan di-rebuild dari checkbox
                    }
                }
                manualLines.push(trimmed);
            });

            var checkedMemberLines = [];
            var checkedCount = 0;
            $('.sw-check-member:checked').each(function() {
                var empId = $(this).data('id');
                var empNama = $(this).data('nama');
                checkedMemberLines.push(empId + ' - ' + empNama);
                checkedCount++;
            });

            var newLines = [];
            if (checkedMemberLines.length > 0) {
                newLines = newLines.concat(checkedMemberLines);
            }
            if (manualLines.length > 0) {
                newLines = newLines.concat(manualLines);
            }

            $('#swCatatanPelaksana').val(newLines.join('\\n'));
            $('#swMemberSelectedCount').text(checkedCount + ' terpilih');
            isSyncingFromCheckbox = false;
        }

        // Sinkronisasi Sisi 2 -> Sisi 1 (Textarea Keterangan -> Checkbox)
        function syncSwCheckboxesFromTextarea() {
            if (isSyncingFromCheckbox) return;
            isSyncingFromTextarea = true;

            var currentTxt = $('#swCatatanPelaksana').val() || '';
            var lines = currentTxt.split(/[\\r\\n]+/);
            var presentIdMap = {};

            $.each(lines, function(i, line) {
                var trimmed = line.trim();
                if (!trimmed) return;
                var match = trimmed.match(/^([0-9]+)\\s*-/);
                if (match) {
                    presentIdMap[String(match[1])] = true;
                }
            });

            var checkedCount = 0;
            $('.sw-check-member').each(function() {
                var empId = String($(this).data('id'));
                var shouldBeChecked = presentIdMap[empId] ? true : false;
                $(this).prop('checked', shouldBeChecked);
                if (shouldBeChecked) {
                    checkedCount++;
                }
            });

            $('#swMemberSelectedCount').text(checkedCount + ' terpilih');
            updateSwMemberCheckboxesLockState(lastRenderedEntityType);
            isSyncingFromTextarea = false;
        }

        $(document).on('change', '.sw-check-member', function() {
            var entType = lastRenderedEntityType ? parseInt(lastRenderedEntityType) : 11;
            updateSwMemberCheckboxesLockState(entType);
            syncSwCatatanPelaksana();
        });

        $(document).on('input keyup', '#swCatatanPelaksana', function() {
            syncSwCheckboxesFromTextarea();
        });

        $(document).on('keyup input', '#swSearchMember', function() {
            var q = $(this).val().toLowerCase().trim();
            $('#swListGroupMembers .sw-member-row').each(function() {
                var searchMeta = $(this).data('search') || '';
                if (q === '' || searchMeta.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $(document).on('click', '#btnClearSearchMember', function() {
            $('#swSearchMember').val('').trigger('input');
        });

        $(document).on('click', '#btnSwSelectAllMembers', function() {
            var entType = lastRenderedEntityType ? parseInt(lastRenderedEntityType) : 11;
            if (entType == 22) {
                if (!currentLockedSwTax) {
                    showSwalAlert(
                        'Pilih Tipe Anggota Terlebih Dahulu',
                        'Silakan centang minimal 1 anggota <b>Badan (PPh 23)</b> atau <b>Perorangan (PPh 21)</b> terlebih dahulu sebelum menggunakan fitur <i>Pilih Semua</i> untuk mengunci tipe pajak group.',
                        'info'
                    );
                    return;
                }
                var visibleRows = $('#swListGroupMembers .sw-member-row:visible:not(.sw-row-disabled)');
                visibleRows.find('.sw-check-member:not(:disabled)').prop('checked', true);
            } else {
                var visibleRows = $('#swListGroupMembers .sw-member-row:visible');
                if (visibleRows.length > 0) {
                    visibleRows.find('.sw-check-member').prop('checked', true);
                } else {
                    $('.sw-check-member').prop('checked', true);
                }
            }
            updateSwMemberCheckboxesLockState(entType);
            syncSwCatatanPelaksana();
        });

        $(document).on('click', '#btnSwDeselectAllMembers', function() {
            var entType = lastRenderedEntityType ? parseInt(lastRenderedEntityType) : 11;
            var visibleRows = $('#swListGroupMembers .sw-member-row:visible');
            if (visibleRows.length > 0 && $('#swSearchMember').val().trim() !== '') {
                visibleRows.find('.sw-check-member').prop('checked', false);
            } else {
                $('.sw-check-member').prop('checked', false);
            }
            updateSwMemberCheckboxesLockState(entType);
            syncSwCatatanPelaksana();
        });

        function renderSwitchFormState() {
            if (!currentSwitchSpkData) return;
            var selectedType = $('input[name=\"sw_type_pelaksana\"]:checked').val();
            var spk = currentSwitchSpkData.spk;
            var hasActiveBiaya = currentSwitchSpkData.has_active_biaya;
            var canVoid = currentSwitchSpkData.can_void;
            var isPrevGroup = (spk.is_group == 1);
            var isPrevVendor = (spk.type_pelaksana == 22 && !isPrevGroup);
            var progressPercent = (spk.progress_percent !== undefined) ? parseInt(spk.progress_percent) : 0;

            // Transaksi biaya vendor/group sebelumnya (3674)
            var vendorNama = spk.employee_nama || '-';
            var displayNilai = '0';
            var displayDoc = '-';
            var isPaid = false;
            var primaryTrx = null;
            if (currentSwitchSpkData.biaya_list && currentSwitchSpkData.biaya_list.length > 0) {
                $.each(currentSwitchSpkData.biaya_list, function(i, b) {
                    if (b.jenis == '3674') {
                        primaryTrx = b;
                        return false;
                    }
                });
                if (!primaryTrx) {
                    primaryTrx = currentSwitchSpkData.biaya_list[0];
                }
            }
            if (primaryTrx) {
                displayNilai = primaryTrx.transaksi_nilai_format || '0';
                displayDoc = primaryTrx.nomer || '-';
                if (parseFloat(primaryTrx.nilai_bayar) > 0) {
                    isPaid = true;
                }
            }

            // Status Payment Source (Target Jenis 3675) & Rekomendasi Reposting
            if (currentSwitchSpkData.has_payment_source_3675) {
                var psDetail = (currentSwitchSpkData.payment_source_list && currentSwitchSpkData.payment_source_list.length > 0) ? currentSwitchSpkData.payment_source_list[0] : null;
                var psText = psDetail ? ('Terbit (No: ' + escapeHtmlSw(psDetail.nomer) + ' | Tagihan: Rp ' + psDetail.tagihan_format + ')') : 'Terbit (Target Jenis 3675)';
                $('#swBiayaPsStatus').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> ' + psText + '</span>');
                $('#containerAlertMissingPaymentSource').hide();
            } else {
                if (hasActiveBiaya) {
                    $('#swBiayaPsStatus').html('<span class=\"label label-danger\"><i class=\"fa fa-exclamation-triangle\"></i> Belum Terbit (Target Jenis 3675 Tidak Ditemukan)</span>');
                    if (currentSwitchSpkData.rekomendasi_reposting || currentSwitchSpkData.only_biaya_tanpa_ps) {
                        $('#containerAlertMissingPaymentSource').show();
                    } else {
                        $('#containerAlertMissingPaymentSource').hide();
                    }
                } else {
                    $('#swBiayaPsStatus').html('<span class=\"text-muted\">- Belum Ada Biaya -</span>');
                    $('#containerAlertMissingPaymentSource').hide();
                }
            }

            var progName = spk.progress_nama || 'Dalam Pengerjaan';
            var totalEstimasiTxt = 'Rp ' + (currentSwitchSpkData.total_estimasi_biaya_format || '0');

            if (selectedType == 'group') {
                $('#containerSelectKaryawan').hide();
                $('#containerSelectVendor').hide();
                $('#containerGroupMemberDanKeterangan').hide();
                $('#containerMultiPelaksanaGroup').show();

                var currentGrpName = $('#swGroupNameInput').val() || ('Group ' + spk.no_spk);
                $('#swAutoPostVendorNama').html('<i class=\"fa fa-users text-primary\"></i> ' + escapeHtmlSw(currentGrpName));

                if (spk.has_biaya == 0) {
                    $('#containerBiayaAktif').hide();
                    $('#containerBiayaBelumQC').hide();
                    $('#swTransitionDivider').hide();
                    $('#containerInfoAutoPost').show();
                    checkGroupZeroSumSubmitState();
                } else if (hasActiveBiaya) {
                    $('#containerBiayaBelumQC').hide();
                    $('#containerBiayaAktif').show();
                    var prevRoleTitle = isPrevVendor ? 'TAGIHAN VENDOR SEBELUMNYA (BEFORE)' : 'TAGIHAN GROUP SEBELUMNYA (BEFORE)';
                    $('#swBiayaAktifTitle').html('<i class=\"fa fa-history text-danger\"></i> <b>1. ' + prevRoleTitle + '</b>');
                    $('#swBiayaVendorNama').text(vendorNama);
                    $('#swBiayaNilai').text('Rp ' + displayNilai);
                    if (isPaid) {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-danger\" style=\"font-weight:bold;\"><i class=\"fa fa-lock\"></i> Sudah Dibayar Kasir</span>');
                    } else {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> Belum Dibayar Kasir (Aman Dibatalkan)</span>');
                    }
                    $('#swBiayaNoNota').text(displayDoc);
                    $('#swCheckVoidBiayaLabel').html('<b>Batalkan tagihan lama (' + escapeHtmlSw(displayDoc) + ') via Modul Pembatalan (9911) &amp; balik jurnal akuntansi lama</b>');
                    $('#swBiayaInfoSameVendor').hide();

                    if (canVoid) {
                        $('#containerCheckboxVoid').show();
                        $('#swCheckVoidBiaya').prop('checked', true).prop('disabled', false);
                        $('#alertBiayaDisbursed').hide();
                        checkGroupZeroSumSubmitState();
                    } else {
                        $('#containerCheckboxVoid').hide();
                        $('#swCheckVoidBiaya').prop('checked', false).prop('disabled', true);
                        $('#swAlertNoNota').text(displayDoc);
                        $('#swAlertNilaiBayar').text('Rp ' + displayNilai);
                        $('#alertBiayaDisbursed').show();
                        setSubmitButtonState(true);
                    }

                    $('#swTransitionDivider').html(
                        '<span style=\"background: #e0f2fe; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 20px; padding: 5px 16px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);\">' +
                        '<i class=\"fa fa-arrow-down text-primary\"></i> DIALIHKAN KE MULTI-PELAKSANA / GROUP (PENGGANTIAN) <i class=\"fa fa-arrow-down text-primary\"></i>' +
                        '</span>'
                    ).show();

                    $('#containerInfoAutoPost').show();
                    $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>2. RENCANA TAGIHAN MULTI-PELAKSANA / GROUP (AFTER - AUTO-POST 3674)</b>');
                } else if (!hasActiveBiaya) {
                    if (spk.progress_id == 3 || progressPercent >= 100) {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN MULTI-PELAKSANA / GROUP (SUDAH QC - AUTO-POST 3674)</b>');
                    } else {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').show();
                        $('#swBelumQCEstimasiNilai').text(totalEstimasiTxt);
                        $('#swTextBelumQCDesc').html(
                            '<i class=\"fa fa-info-circle text-info\"></i> <b>Penugasan Multi-Pelaksana / Group (Belum QC):</b> SPK ini masih dalam progress (<b>' + escapeHtmlSw(progName) + '</b>). ' +
                            'Tagihan biaya belum pernah diterbitkan ke keuangan. Rencana tagihan di bawah ini akan otomatis diterbitkan untuk group <b>' + escapeHtmlSw(currentGrpName) + '</b> saat pekerjaan fisik selesai dan lolos Quality Control (QC).'
                        );
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN MULTI-PELAKSANA / GROUP (TERBIT SAAT QC)</b>');
                    }
                    checkGroupZeroSumSubmitState();
                }
            } else if (selectedType == '11') {
                $('#containerMultiPelaksanaGroup').hide();
                $('#containerSelectKaryawan').show();
                $('#containerSelectVendor').hide();

                var selectedKaryawanOpt = $('#swSelectKaryawan option:selected');
                var isGroupSelected = (selectedKaryawanOpt.data('isgroup') == 1 || selectedKaryawanOpt.data('isgroup') == '1');
                var groupNama = selectedKaryawanOpt.data('nama') || selectedKaryawanOpt.text();
                var selectedKaryawanId = $('#swSelectKaryawan').val();

                if (isGroupSelected) {
                    renderGroupMembersAndCatatan(selectedKaryawanId);

                    // TARGET ADALAH KARYAWAN GROUP (FREELANCE) - BERBIAYA (AUTO-POST 3674)
                    $('#swAutoPostVendorNama').html('<i class=\"fa fa-users text-primary\"></i> ' + escapeHtmlSw(groupNama));

                    var isSameGroup = (isPrevGroup && selectedKaryawanId && (spk.employee_id == selectedKaryawanId || ('group_master_' + spk.group_id) == selectedKaryawanId || ('group_master_' + spk.master_group_id) == selectedKaryawanId || (spk.matched_group_val && spk.matched_group_val == selectedKaryawanId)));
                    var isGroupChanged = !isSameGroup;

                    if (spk.has_biaya == 0) {
                        // Kasus Non-Biaya / Murni Material
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').show();
                        setSubmitButtonState(false);
                    } else if (isGroupChanged && hasActiveBiaya) {
                        // ADA TAGIHAN BIAYA LAMA (Vendor atau Group sebelumnya) -> VOID 9911 & AUTO-POST TAGIHAN BARU
                        $('#containerBiayaBelumQC').hide();
                        $('#containerBiayaAktif').show();
                        var prevRoleTitle = isPrevVendor ? 'TAGIHAN VENDOR SEBELUMNYA (BEFORE)' : 'TAGIHAN GROUP SEBELUMNYA (BEFORE)';
                        $('#swBiayaAktifTitle').html('<i class=\"fa fa-history text-danger\"></i> <b>1. ' + prevRoleTitle + '</b>');
                        $('#swBiayaVendorNama').text(vendorNama);
                        $('#swBiayaNilai').text('Rp ' + displayNilai);
                        if (isPaid) {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-danger\" style=\"font-weight:bold;\"><i class=\"fa fa-lock\"></i> Sudah Dibayar Kasir</span>');
                        } else {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> Belum Dibayar Kasir (Aman Dibatalkan)</span>');
                        }
                        $('#swBiayaNoNota').text(displayDoc);
                        $('#swCheckVoidBiayaLabel').html('<b>Batalkan tagihan lama (' + escapeHtmlSw(displayDoc) + ') via Modul Pembatalan (9911) &amp; balik jurnal akuntansi lama</b>');
                        $('#swBiayaInfoSameVendor').hide();

                        if (canVoid) {
                            $('#containerCheckboxVoid').show();
                            $('#swCheckVoidBiaya').prop('checked', true).prop('disabled', false);
                            $('#alertBiayaDisbursed').hide();
                            setSubmitButtonState(false);
                        } else {
                            $('#containerCheckboxVoid').hide();
                            $('#swCheckVoidBiaya').prop('checked', false).prop('disabled', true);
                            $('#swAlertNoNota').text(displayDoc);
                            $('#swAlertNilaiBayar').text('Rp ' + displayNilai);
                            $('#alertBiayaDisbursed').show();
                            setSubmitButtonState(true);
                        }

                        // Tampilkan divider alih ke group baru
                        $('#swTransitionDivider').html(
                            '<span style=\"background: #e0f2fe; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 20px; padding: 5px 16px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);\">' +
                            '<i class=\"fa fa-arrow-down text-primary\"></i> DIALIHKAN KE GROUP BARU (PENGGANTIAN) <i class=\"fa fa-arrow-down text-primary\"></i>' +
                            '</span>'
                        ).show();

                        // Tampilkan AFTER (Rencana Tagihan Group Baru)
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>2. RENCANA TAGIHAN KARYAWAN GROUP BARU (AFTER - AUTO-POST 3674)</b>');
                    } else if (isGroupChanged && !hasActiveBiaya) {
                        // Belum ada biaya terbit
                        if (spk.progress_id == 3 || progressPercent >= 100) {
                            $('#containerBiayaAktif').hide();
                            $('#containerBiayaBelumQC').hide();
                            $('#swTransitionDivider').hide();
                            $('#containerInfoAutoPost').show();
                            $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN KARYAWAN GROUP (SUDAH QC - AUTO-POST 3674)</b>');
                        } else {
                            $('#containerBiayaAktif').hide();
                            $('#containerBiayaBelumQC').show();
                            $('#swBelumQCEstimasiNilai').text(totalEstimasiTxt);
                            $('#swTextBelumQCDesc').html(
                                '<i class=\"fa fa-info-circle text-info\"></i> <b>Penugasan Karyawan Group (Belum QC):</b> SPK ini masih dalam progress (<b>' + escapeHtmlSw(progName) + '</b>). ' +
                                'Tagihan biaya belum pernah diterbitkan ke keuangan. Rencana tagihan di bawah ini akan otomatis diterbitkan untuk group <b>' + escapeHtmlSw(groupNama) + '</b> saat pekerjaan fisik selesai dan lolos Quality Control (QC).'
                            );
                            $('#swTransitionDivider').hide();
                            $('#containerInfoAutoPost').show();
                            $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN KARYAWAN GROUP (TERBIT SAAT QC)</b>');
                        }
                        setSubmitButtonState(false);
                    } else if (isSameGroup && hasActiveBiaya) {
                        // Group sama & biaya aktif
                        $('#containerBiayaBelumQC').hide();
                        $('#containerBiayaAktif').show();
                        $('#swBiayaAktifTitle').html('<i class=\"fa fa-info-circle text-primary\"></i> <b>STATUS TAGIHAN BIAYA GROUP SAAT INI (AKTIF)</b>');
                        $('#swBiayaVendorNama').text(vendorNama);
                        $('#swBiayaNilai').text('Rp ' + displayNilai);
                        if (isPaid) {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-danger\" style=\"font-weight:bold;\"><i class=\"fa fa-lock\"></i> Sudah Dibayar Kasir</span>');
                        } else {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> Belum Dibayar Kasir</span>');
                        }
                        $('#swBiayaNoNota').text(displayDoc);
                        $('#containerCheckboxVoid').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').hide();
                        setSubmitButtonState(false);
                    } else {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA TAGIHAN KARYAWAN GROUP (AUTO-POST 3674)</b>');
                        setSubmitButtonState(false);
                    }
                } else {
                    $('#containerGroupMemberDanKeterangan').hide();
                    activeGroupMembers = [];
                    lastRenderedGroupId = null;

                    // TARGET ADALAH KARYAWAN INDIVIDUAL BIASA (PAYROLL / NON-TAGIHAN 3674)
                    $('#swTransitionDivider').hide();

                    if ((isPrevVendor || isPrevGroup) && hasActiveBiaya) {
                        $('#containerBiayaBelumQC').hide();
                        $('#containerBiayaAktif').show();
                        var prevLabel = isPrevVendor ? 'Tagihan Vendor Sebelumnya' : 'Tagihan Group Sebelumnya';
                        $('#swBiayaAktifTitle').html('<i class=\"fa fa-exclamation-triangle text-danger\"></i> ' + prevLabel + ' (Akan Dibatalkan via 9911):');
                        $('#swBiayaVendorNama').text(vendorNama);
                        $('#swBiayaNilai').text('Rp ' + displayNilai);
                        if (isPaid) {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-danger\">Sudah Dibayar Kasir</span>');
                        } else {
                            $('#swBiayaStatusBayar').html('<span class=\"label label-success\">Belum Dibayar (Aman Dibatalkan)</span>');
                        }
                        $('#swBiayaNoNota').text(displayDoc);
                        $('#swCheckVoidBiayaLabel').html('Batalkan tagihan biaya via Modul Pembatalan Resmi (9911) &amp; balik jurnal akuntansi');

                        if (canVoid) {
                            $('#containerCheckboxVoid').show();
                            $('#swCheckVoidBiaya').prop('checked', true).prop('disabled', false);
                            $('#alertBiayaDisbursed').hide();
                            setSubmitButtonState(false);
                        } else {
                            $('#containerCheckboxVoid').hide();
                            $('#swCheckVoidBiaya').prop('checked', false).prop('disabled', true);
                            $('#swAlertNoNota').text(displayDoc);
                            $('#swAlertNilaiBayar').text('Rp ' + displayNilai);
                            $('#alertBiayaDisbursed').show();
                            setSubmitButtonState(true);
                        }
                    } else if ((isPrevVendor || isPrevGroup) && !hasActiveBiaya && spk.has_biaya == 1) {
                        if (spk.progress_id == 3 || progressPercent >= 100) {
                            $('#containerBiayaAktif').hide();
                            $('#containerBiayaBelumQC').hide();
                        } else {
                            $('#containerBiayaAktif').hide();
                            $('#containerBiayaBelumQC').show();
                            $('#swBelumQCEstimasiNilai').text(totalEstimasiTxt);
                            $('#swTextBelumQCDesc').html(
                                'SPK ini saat ini ditugaskan ke <b>' + escapeHtmlSw(vendorNama) + '</b>, namun status pekerjaan fisik masih <b>' + escapeHtmlSw(progName) + ' (Belum QC)</b>.<br>' +
                                'Oleh karena itu, <b>tagihan biaya belum pernah diterbitkan</b> ke keuangan. Pengalihan ke <b>Internal Karyawan</b> aman diproses langsung tanpa perlu pembatalan tagihan (Void 9911). Setelah dialihkan ke karyawan individual, SPK ini tidak akan membebankan tagihan biaya saat QC nanti.'
                            );
                        }
                        setSubmitButtonState(false);
                    } else {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        setSubmitButtonState(false);
                    }

                    // Tampilkan container rincian biaya jika SPK memiliki rincian biaya
                    if (spk.has_biaya == 1 || (currentSwitchSpkData.estimasi_biaya_posting && currentSwitchSpkData.estimasi_biaya_posting.length > 0)) {
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-building text-green\"></i> <b>ALOKASI BIAYA PEKERJAAN INTERNAL (100% EFISIENSI PROYEK)</b>');
                        var empNama = $('#swSelectKaryawan option:selected').data('nama') || $('#swSelectKaryawan option:selected').text() || 'Internal Karyawan';
                        $('#swAutoPostVendorNama').html('<i class=\"fa fa-user text-green\"></i> ' + escapeHtmlSw(empNama));
                    } else {
                        $('#containerInfoAutoPost').hide();
                    }
                }
            } else {
                // selectedType == '22' (Vendor)
                $('#containerMultiPelaksanaGroup').hide();
                $('#containerSelectKaryawan').hide();
                $('#containerSelectVendor').show();

                var selectedVendorOpt = $('#swSelectVendor option:selected');
                var isGroupSelected = (selectedVendorOpt.data('isgroup') == 1 || selectedVendorOpt.data('isgroup') == '1');
                var groupNama = selectedVendorOpt.data('nama') || selectedVendorOpt.text();
                var selectedVendorId = $('#swSelectVendor').val();

                if (isGroupSelected) {
                    renderGroupMembersAndCatatan(selectedVendorId, 22);
                    $('#swAutoPostVendorNama').html('<i class=\"fa fa-truck text-orange\"></i> ' + escapeHtmlSw(groupNama));
                } else {
                    $('#containerGroupMemberDanKeterangan').hide();
                    activeGroupMembers = [];
                    lastRenderedGroupId = null;
                    lastRenderedEntityType = null;
                    updateSelectedVendorBadge();
                }

                var isSameVendor = false;
                var isVendorChanged = false;
                if (isGroupSelected) {
                    isSameVendor = (isPrevGroup && selectedVendorId && (spk.employee_id == selectedVendorId || ('group_master_' + spk.group_id) == selectedVendorId || ('group_master_' + spk.master_group_id) == selectedVendorId || (spk.matched_group_val && spk.matched_group_val == selectedVendorId)));
                    isVendorChanged = !isSameVendor;
                } else {
                    isVendorChanged = (isPrevVendor && selectedVendorId && selectedVendorId != spk.employee_id);
                    isSameVendor = (isPrevVendor && selectedVendorId && selectedVendorId == spk.employee_id);
                }

                var roleLabelVendor = isGroupSelected ? 'VENDOR GROUP BARU' : 'VENDOR BARU';

                if (spk.has_biaya == 0) {
                    // Kasus Non-Biaya / Murni Material
                    $('#containerBiayaAktif').hide();
                    $('#containerBiayaBelumQC').hide();
                    $('#swTransitionDivider').hide();
                    $('#containerInfoAutoPost').show();
                    setSubmitButtonState(false);
                } else if ((isVendorChanged || isPrevGroup) && hasActiveBiaya) {
                    // SKENARIO GANTI SESAMA VENDOR ATAU DARI GROUP KE VENDOR - SUDAH QC / SUDAH TERBIT BIAYA
                    $('#containerBiayaBelumQC').hide();
                    $('#containerBiayaAktif').show();
                    var prevRoleTitle = isPrevVendor ? 'TAGIHAN VENDOR SEBELUMNYA (BEFORE)' : 'TAGIHAN GROUP SEBELUMNYA (BEFORE)';
                    $('#swBiayaAktifTitle').html('<i class=\"fa fa-history text-danger\"></i> <b>1. ' + prevRoleTitle + '</b>');
                    $('#swBiayaVendorNama').text(vendorNama);
                    $('#swBiayaNilai').text('Rp ' + displayNilai);
                    if (isPaid) {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-danger\" style=\"font-weight:bold;\"><i class=\"fa fa-lock\"></i> Sudah Dibayar Kasir</span>');
                    } else {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> Belum Dibayar Kasir (Aman Dibatalkan)</span>');
                    }
                    $('#swBiayaNoNota').text(displayDoc);
                    $('#swCheckVoidBiayaLabel').html('<b>Batalkan tagihan lama (' + escapeHtmlSw(displayDoc) + ') via Modul Pembatalan (9911) &amp; balik jurnal akuntansi lama</b>');
                    $('#swBiayaInfoSameVendor').hide();

                    if (canVoid) {
                        $('#containerCheckboxVoid').show();
                        $('#swCheckVoidBiaya').prop('checked', true).prop('disabled', false);
                        $('#alertBiayaDisbursed').hide();
                        setSubmitButtonState(false);
                    } else {
                        $('#containerCheckboxVoid').hide();
                        $('#swCheckVoidBiaya').prop('checked', false).prop('disabled', true);
                        $('#swAlertNoNota').text(displayDoc);
                        $('#swAlertNilaiBayar').text('Rp ' + displayNilai);
                        $('#alertBiayaDisbursed').show();
                        setSubmitButtonState(true);
                    }

                    // Tampilkan panah transisi divider
                    $('#swTransitionDivider').html(
                        '<span style=\"background: #e0f2fe; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 20px; padding: 5px 16px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);\">' +
                        '<i class=\"fa fa-arrow-down text-primary\"></i> DIALIHKAN KE ' + roleLabelVendor + ' (PENGGANTIAN) <i class=\"fa fa-arrow-down text-primary\"></i>' +
                        '</span>'
                    ).show();

                    // Tampilkan AFTER (Rencana Tagihan Vendor Baru)
                    $('#containerInfoAutoPost').show();
                    $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>2. RENCANA TAGIHAN ' + roleLabelVendor + ' (AFTER - AUTO-POST 3674)</b>');
                } else if ((isVendorChanged || isPrevGroup) && !hasActiveBiaya) {
                    // SKENARIO GANTI VENDOR - BELUM ADA BIAYA TERBIT
                    if (spk.progress_id == 3 || progressPercent >= 100) {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        $('#swTransitionDivider').show();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN ' + roleLabelVendor + ' (SUDAH QC - AUTO-POST 3674)</b>');
                    } else {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').show();
                        $('#swBelumQCEstimasiNilai').text(totalEstimasiTxt);
                        $('#swTextBelumQCDesc').html(
                            '<i class=\"fa fa-info-circle text-info\"></i> <b>Informasi Pergantian ' + (isGroupSelected ? 'Vendor Group' : 'Vendor') + ' (Belum QC):</b> SPK ini masih dalam progress (<b>' + escapeHtmlSw(progName) + '</b>). ' +
                            'Tagihan atas nama pelaksana sebelumnya (<b>' + escapeHtmlSw(vendorNama) + '</b>) <b>belum pernah diterbitkan</b> ke keuangan. ' +
                            'Pengalihan ke ' + (isGroupSelected ? 'vendor group' : 'vendor baru') + ' dapat langsung diproses tanpa pembatalan tagihan (Void 9911). Rencana tagihan di bawah ini akan otomatis diterbitkan atas nama ' + (isGroupSelected ? 'vendor group baru' : 'vendor baru') + ' saat pekerjaan fisik selesai dan lolos QC.'
                        );
                        $('#swTransitionDivider').show();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN ' + roleLabelVendor + ' (TERBIT SAAT QC)</b>');
                    }
                    setSubmitButtonState(false);
                } else if (isPrevVendor && hasActiveBiaya && (!selectedVendorId || isSameVendor)) {
                    // STATUS BIAYA SAAT INI (SUDAH QC & ADA BIAYA AKTIF)
                    $('#containerBiayaBelumQC').hide();
                    $('#containerBiayaAktif').show();
                    $('#swBiayaAktifTitle').html('<i class=\"fa fa-info-circle text-primary\"></i> <b>STATUS TAGIHAN BIAYA SAAT INI (AKTIF)</b>');
                    $('#swBiayaVendorNama').text(vendorNama);
                    $('#swBiayaNilai').text('Rp ' + displayNilai);
                    if (isPaid) {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-danger\" style=\"font-weight:bold;\"><i class=\"fa fa-lock\"></i> Sudah Dibayar Kasir</span>');
                    } else {
                        $('#swBiayaStatusBayar').html('<span class=\"label label-success\"><i class=\"fa fa-check-circle\"></i> Belum Dibayar Kasir (Aman Dibatalkan)</span>');
                    }
                    $('#swBiayaNoNota').text(displayDoc);
                    $('#containerCheckboxVoid').hide();
                    $('#swTransitionDivider').hide();
                    $('#containerInfoAutoPost').hide();

                    if (!canVoid || isPaid) {
                        $('#swAlertNoNota').text(displayDoc);
                        $('#swAlertNilaiBayar').text('Rp ' + displayNilai);
                        $('#alertBiayaDisbursed').show();
                        $('#swBiayaInfoSameVendor').hide();
                        setSubmitButtonState(true);
                    } else {
                        $('#alertBiayaDisbursed').hide();
                        $('#swBiayaInfoSameVendor').html('<i class=\"fa fa-info-circle text-info\"></i> Dokumen tagihan biaya (Jenis 3674) telah terbit atas nama vendor <b>' + escapeHtmlSw(vendorNama) + '</b>. Jika Anda mengubah pelaksana ke <b>Internal</b> atau <b>Vendor Lain</b>, sistem otomatis menyediakan opsi pembatalan resmi via Modul 9911 &amp; balik jurnal akuntansi.').show();
                        setSubmitButtonState(false);
                    }
                } else if (isPrevVendor && !hasActiveBiaya && (!selectedVendorId || isSameVendor)) {
                    if (spk.progress_id == 3 || progressPercent >= 100) {
                        $('#containerBiayaAktif').hide();
                        $('#containerBiayaBelumQC').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').show();
                        $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN VENDOR (SUDAH QC)</b>');
                    } else {
                        // STATUS BIAYA SAAT INI (BELUM QC / BIAYA BELUM PERNAH TERBIT)
                        $('#containerBiayaAktif').hide();
                        $('#swTransitionDivider').hide();
                        $('#containerInfoAutoPost').hide();
                        $('#containerBiayaBelumQC').show();
                        $('#swBelumQCEstimasiNilai').text(totalEstimasiTxt);
                        $('#swTextBelumQCDesc').html(
                            'Pekerjaan SPK ini masih dalam progress (<b>' + escapeHtmlSw(progName) + '</b>) dan belum lolos tahap <b>Quality Control (QC)</b>.<br>' +
                            'Oleh karena itu, <b>belum ada transaksi tagihan biaya (3674) yang diterbitkan</b> untuk vendor <b>' + escapeHtmlSw(vendorNama) + '</b>. ' +
                            'Tagihan biaya baru akan diterbitkan secara otomatis setelah pekerjaan fisik selesai dan dinyatakan lulus QC.'
                        );
                    }
                    setSubmitButtonState(false);
                } else if (!isPrevVendor && !isPrevGroup) {
                    // Internal Individual -> Vendor
                    $('#containerBiayaAktif').hide();
                    $('#containerBiayaBelumQC').hide();
                    $('#swTransitionDivider').hide();
                    $('#containerInfoAutoPost').show();
                    $('#swAutoPostTitle').html('<i class=\"fa fa-file-text-o text-primary\"></i> <b>RENCANA PENERBITAN TAGIHAN VENDOR (AUTO-POST 3674)</b>');
                    setSubmitButtonState(false);
                } else {
                    $('#containerBiayaAktif').hide();
                    $('#containerBiayaBelumQC').hide();
                    $('#swTransitionDivider').hide();
                    $('#containerInfoAutoPost').hide();
                    setSubmitButtonState(false);
                }
            }

            // Render detail breakdown jasa untuk vendor / group / internal jika containerInfoAutoPost ditampilkan
            var shouldRenderBreakdown = ($('#containerInfoAutoPost').css('display') !== 'none') || 
                                        $('#containerInfoAutoPost').is(':visible') || 
                                        (selectedType == '11' && (spk.has_biaya == 1 || (currentSwitchSpkData.estimasi_biaya_posting && currentSwitchSpkData.estimasi_biaya_posting.length > 0)));

            if (shouldRenderBreakdown) {
                $('#containerInfoAutoPost').show();
                if (spk.has_biaya == 0 && (!currentSwitchSpkData.estimasi_biaya_posting || currentSwitchSpkData.estimasi_biaya_posting.length === 0)) {
                    $('#swBoxNonBiaya').show();
                    $('#swBoxAdaBiaya').hide();
                } else {
                    $('#swBoxNonBiaya').hide();
                    $('#swBoxAdaBiaya').show();

                    var isInternalTarget = (selectedType == '11');
                    var totalBudgetVal = currentSwitchSpkData.total_budget_biaya_format || currentSwitchSpkData.total_biaya_fisik_format || '0';
                    var totalBudgetBiayaNum = parseFloat(currentSwitchSpkData.total_budget_biaya) || parseFloat(currentSwitchSpkData.total_biaya_fisik) || 0;
                    $('#swPaguBudgetBiayaTxt').text('Rp ' + totalBudgetVal);

                    var trs = '';
                    if (currentSwitchSpkData.estimasi_biaya_posting && currentSwitchSpkData.estimasi_biaya_posting.length > 0) {
                        $.each(currentSwitchSpkData.estimasi_biaya_posting, function(idx, itm) {
                            var badgeSub = '';
                            if (itm.has_sub_biaya == 1) {
                                badgeSub = ' <span class=\"label label-info\" style=\"font-size:9.5px; padding:1px 5px;\" title=\"Dihitung berdasarkan rincian sub-biaya RAB Sub\"><i class=\"fa fa-list\"></i> RAB Sub</span>';
                            } else {
                                badgeSub = ' <span class=\"label label-default\" style=\"font-size:9.5px; padding:1px 5px; color:#555; background:#e2e8f0;\" title=\"Dihitung langsung dari tarif dasar jasa\"><i class=\"fa fa-tag\"></i> Tarif Jasa</span>';
                            }

                            var isItemNonVendor = (itm.is_non_vendor == 1 || itm.posting_vendor == 0 || (itm.id == 45) || (itm.nama && itm.nama.toLowerCase().indexOf('bobok') !== -1));
                            var bQty = parseFloat(itm.budget_qty) || parseFloat(itm.jml) || 0;
                            var bHrg = parseFloat(itm.budget_harga) || parseFloat(itm.tarif_satuan) || 0;
                            var bSub = parseFloat(itm.budget_subtotal) || parseFloat(itm.subtotal) || (bQty * bHrg);

                            if (isInternalTarget) {
                                // MODE INTERNAL: READONLY SEMUA BIAYA MASUK KE EFISIENSI
                                var badgeEfisiensi = ' <span class=\"label label-success\" style=\"font-size:9.5px; padding:2px 6px; font-weight:700;\"><i class=\"fa fa-arrow-down\"></i> 100% Masuk Efisiensi</span>';
                                trs += '<tr style=\"background:#f4fbf5;\">';
                                trs += '<td style=\"text-align:center; color:#888; vertical-align:middle;\">' + (idx + 1) + '</td>';
                                trs += '<td><span style=\"font-weight:600; color:#1e293b;\">' + escapeHtmlSw(itm.nama) + '</span>' + badgeSub + badgeEfisiensi + '<br><small style=\"font-size:10px; color:#15803d;\"><i class=\"fa fa-info-circle\"></i> Dikerjakan Internal - Tidak ada tagihan vendor/kasir (Masuk Efisiensi Proyek)</small></td>';
                                trs += '<td style=\"text-align:center; font-weight:600; vertical-align:middle;\">' + itm.jml + ' ' + escapeHtmlSw(itm.satuan) + '</td>';
                                trs += '<td style=\"text-align:right; vertical-align:middle;\">Rp ' + itm.tarif_satuan_format + '</td>';
                                trs += '<td style=\"text-align:right; font-weight:700; color:#15803d; padding-right:12px; white-space:nowrap; vertical-align:middle;\">Rp ' + itm.subtotal_format + '</td>';
                                trs += '</tr>';
                            } else {
                                // MODE VENDOR / EXTERNAL
                                if (isItemNonVendor) {
                                    var isBobok = (itm.is_bobokan == 1 || itm.id == 45 || (itm.nama && itm.nama.toLowerCase().indexOf('bobok') !== -1));
                                    var badgeNonVendor = ' <span class=\"label label-warning\" style=\"font-size:9.5px; padding:1px 5px;\" title=\"Biaya Non-Vendor (posting_vendor = 0)\"><i class=\"fa fa-ban\"></i> NON-VENDOR</span>';
                                    if (isBobok) {
                                        badgeNonVendor += ' <span class=\"badge\" style=\"background:#d97706; font-size:9px; margin-left:3px;\" title=\"Pekerjaan Bobokan\"><i class=\"fa fa-gavel\"></i> Bobokan</span>';
                                    }
                                    var alasanTxt = isBobok ? 'Biaya Bobokan (Pekerjaan Internal Perusahaan)' : 'Master Biaya Non-Vendor';
                                    trs += '<tr style=\"background:#fffbeb;\">';
                                    trs += '<td style=\"text-align:center; color:#888; vertical-align:middle;\">' + (idx + 1) + '</td>';
                                    trs += '<td><span style=\"font-weight:600; color:#475569;\">' + escapeHtmlSw(itm.nama) + '</span>' + badgeSub + badgeNonVendor + '<br><small style=\"font-size:10px; color:#b45309;\"><i class=\"fa fa-info-circle\"></i> Status: <code>posting_vendor = 0</code> (' + alasanTxt + ' - Dikecualikan dari tagihan vendor)</small></td>';
                                    trs += '<td style=\"text-align:center; color:#64748b; vertical-align:middle;\">' + itm.jml + ' ' + escapeHtmlSw(itm.satuan) + '</td>';
                                    trs += '<td style=\"text-align:right; color:#64748b; vertical-align:middle;\">Rp ' + itm.tarif_satuan_format + '</td>';
                                    trs += '<td style=\"text-align:right; padding-right:12px; white-space:nowrap; vertical-align:middle;\"><span style=\"text-decoration:line-through; color:#94a3b8; font-size:11px;\">Rp ' + itm.subtotal_format + '</span><br><span class=\"label label-warning\" style=\"font-size:9px; padding:1px 5px; font-weight:700;\"><i class=\"fa fa-ban\"></i> Dikecualikan</span></td>';
                                    trs += '</tr>';
                                } else {
                                    // BIAYA YANG AKAN DIPOSTING KE VENDOR: BISA DIEDIT! PENURUNAN JADI EFISIENSI
                                    var curQty = parseFloat(itm.jml) || 0;
                                    var curHrg = parseFloat(itm.tarif_satuan) || 0;
                                    var curSub = parseFloat(itm.subtotal) || (curQty * curHrg);
                                    var badgeVendor = ' <span class=\"label label-success\" style=\"font-size:9.5px; padding:1px 5px;\" title=\"Ditagihkan ke Vendor (posting_vendor = 1)\"><i class=\"fa fa-check\"></i> Ditagihkan ke Vendor</span>';

                                    trs += '<tr class=\"sw-biaya-row-vendor\">';
                                    trs += '<td style=\"text-align:center; color:#888; vertical-align:middle;\">' + (idx + 1) + '</td>';
                                    trs += '<td><span style=\"font-weight:600; color:#334155;\">' + escapeHtmlSw(itm.nama) + '</span>' + badgeSub + badgeVendor + '<br><small style=\"font-size:10px; color:#64748b;\">Budget Asli: ' + bQty + ' ' + escapeHtmlSw(itm.satuan) + ' @ Rp ' + addCommas(bHrg.toFixed(2)) + ' = <b>Rp ' + addCommas(bSub.toFixed(2)) + '</b></small></td>';
                                    
                                    // INPUT VOLUME (Bisa s.d. 0)
                                    trs += '<td style=\"text-align:center; vertical-align:middle;\"><input type=\"number\" min=\"0\" step=\"any\" class=\"form-control input-sm sw-input-qty text-center\" data-idx=\"' + idx + '\" data-budget-qty=\"' + bQty + '\" data-budget-harga=\"' + bHrg + '\" data-budget-subtotal=\"' + bSub + '\" value=\"' + curQty + '\" style=\"height:28px; padding:2px 4px; font-weight:bold; width:65px; display:inline-block;\"> <small class=\"text-muted\">' + escapeHtmlSw(itm.satuan) + '</small></td>';
                                    
                                    // INPUT TARIF SATUAN
                                    trs += '<td style=\"text-align:right; vertical-align:middle;\"><input type=\"text\" class=\"form-control input-sm sw-input-harga text-right\" data-idx=\"' + idx + '\" data-budget-qty=\"' + bQty + '\" data-budget-harga=\"' + bHrg + '\" data-budget-subtotal=\"' + bSub + '\" value=\"' + addCommas(curHrg.toFixed(2)) + '\" style=\"height:28px; padding:2px 6px; font-weight:bold; width:95px; display:inline-block;\"></td>';
                                    
                                    // INPUT SUBTOTAL & BADGE EFISIENSI
                                    trs += '<td style=\"text-align:right; vertical-align:middle; padding-right:12px; white-space:nowrap;\">';
                                    trs += '<input type=\"text\" class=\"form-control input-sm sw-input-subtotal text-right\" data-idx=\"' + idx + '\" data-budget-qty=\"' + bQty + '\" data-budget-harga=\"' + bHrg + '\" data-budget-subtotal=\"' + bSub + '\" value=\"' + addCommas(curSub.toFixed(2)) + '\" style=\"height:28px; padding:2px 6px; font-weight:bold; width:110px; display:inline-block;\">';
                                    var itemHemat = (bSub > curSub) ? (bSub - curSub) : 0;
                                    var hematHtml = itemHemat > 0 ? '<div class=\"sw-item-hemat-txt text-green\" style=\"font-size:10px; font-weight:bold; margin-top:2px;\"><i class=\"fa fa-arrow-down\"></i> Efisiensi: Rp ' + addCommas(itemHemat.toFixed(2)) + '</div>' : '<div class=\"sw-item-hemat-txt\" style=\"font-size:10px; color:#888; margin-top:2px;\">Sesuai Budget</div>';
                                    trs += hematHtml;
                                    trs += '</td>';
                                    trs += '</tr>';
                                }
                            }
                        });
                    } else {
                        trs = '<tr><td colspan=\"5\" style=\"text-align:center; color:#999; padding:10px;\">Tidak ada rincian item biaya jasa</td></tr>';
                    }
                    $('#swTbodyRincianBiaya').html(trs);

                    if (isInternalTarget) {
                        $('#swTotalBiayaPostingTxt').html('<span style=\"color:#888;\">Rp 0 (Tidak Diterbitkan ke Kasir)</span>');
                        $('#swTotalEfisiensiTxt').html('<b>Rp ' + totalBudgetVal + ' (100% Masuk Efisiensi)</b>');
                        $('#swRowEfisiensiSummary').show();
                        $('#swRowNonVendorSummary').hide();
                        $('#swAutoPostStatusBadge').html('<span class=\"label label-success\" style=\"font-size:10px; padding:3px 7px;\"><i class=\"fa fa-lock\"></i> 100% Masuk Efisiensi Proyek</span>');
                        $('#swAlertExecutionInfo').html('<i class=\"fa fa-check-circle fa-lg text-green\"></i> <b>Pelaksana Internal (Karyawan):</b> Seluruh pekerjaan dilaksanakan oleh tim internal perusahaan. Tidak ada tagihan yang diterbitkan ke vendor/pihak ke-3 (Kasir Rp 0), dan <b>100% pagu biaya SPK (Rp ' + totalBudgetVal + ') otomatis dialokasikan menjadi efisiensi biaya proyek</b>.');
                    } else {
                        window.recalcSwitchBiayaTotals();
                        $('#swRowEfisiensiSummary').show();

                        if (currentSwitchSpkData.total_non_vendor_biaya && parseFloat(currentSwitchSpkData.total_non_vendor_biaya) > 0) {
                            $('#swTotalFisikTxt').text('Rp ' + (currentSwitchSpkData.total_biaya_fisik_format || '0'));
                            $('#swTotalNonVendorTxt').text('Rp ' + (currentSwitchSpkData.total_non_vendor_biaya_format || '0'));
                            $('#swRowNonVendorSummary').show();
                        } else {
                            $('#swRowNonVendorSummary').hide();
                        }

                        var isTargetGroup = ($('#swSelectVendor option:selected').data('isgroup') == 1);
                        var targetName = $('#swSelectVendor option:selected').data('nama') || $('#swSelectVendor option:selected').text();
                        var targetRoleLabel = isTargetGroup ? 'Vendor Group' : 'Vendor';

                        if (spk.progress_id == 3 || progressPercent >= 100) {
                            $('#swAutoPostStatusBadge').html('<span class=\"label label-success\" style=\"font-size:10px; padding:3px 7px;\"><i class=\"fa fa-check-circle\"></i> Siap Terbit Otomatis (Sudah QC)</span>');
                            if (hasActiveBiaya) {
                                $('#swAlertExecutionInfo').html('<i class=\"fa fa-refresh text-warning\"></i> <b>Siklus Pergantian Tagihan:</b> Saat tombol <b>Simpan Perubahan</b> ditekan, tagihan biaya sebelumnya <b>' + escapeHtmlSw(displayDoc) + '</b> akan dibatalkan resmi (Void 9911 & Balik Jurnal), lalu sistem otomatis menerbitkan tagihan biaya baru yang disesuaikan untuk ' + targetRoleLabel + ' <b>' + escapeHtmlSw(targetName) + '</b>, dan setiap penurunan biaya langsung dicatat sebagai <b>Efisiensi Proyek</b>.');
                            } else {
                                $('#swAlertExecutionInfo').html('<i class=\"fa fa-bolt text-warning\"></i> <b>Pekerjaan ini sudah selesai QC:</b> Saat tombol <b>Simpan Perubahan</b> ditekan, sistem akan otomatis menerbitkan tagihan biaya yang disesuaikan kepada ' + targetRoleLabel + ' <b>' + escapeHtmlSw(targetName) + '</b>, dan selisih penurunan menjadi <b>Efisiensi Proyek</b>.');
                            }
                        } else {
                            $('#swAutoPostStatusBadge').html('<span class=\"label label-info\" style=\"font-size:10px; padding:3px 7px;\"><i class=\"fa fa-clock-o\"></i> Estimasi (Terbit Saat QC)</span>');
                            $('#swAlertExecutionInfo').html('<i class=\"fa fa-info-circle text-info\"></i> <b>Estimasi Tagihan Vendor &amp; Efisiensi:</b> Tagihan biaya akan diterbitkan secara resmi saat pekerjaan fisik lolos Quality Control (QC). Nilai yang disesuaikan di atas akan menjadi pagu tagihan vendor dan selisihnya otomatis menjadi <b>Efisiensi Proyek</b>.');
                        }
                    }
                }
            }
        }

        window.recalcSwitchBiayaTotals = function() {
            if (!currentSwitchSpkData) return;
            var totalBudgetBiayaNum = parseFloat(currentSwitchSpkData.total_budget_biaya) || parseFloat(currentSwitchSpkData.total_biaya_fisik) || 0;
            var currentVendorSubtotal = 0;

            $('#swTbodyRincianBiaya tr.sw-biaya-row-vendor').each(function() {
                var rowSub = parseFloat(removeCommas($(this).find('.sw-input-subtotal').val())) || 0;
                currentVendorSubtotal += rowSub;
            });

            var totalEfisiensi = totalBudgetBiayaNum - currentVendorSubtotal;
            if (totalEfisiensi < 0) totalEfisiensi = 0;

            $('#swTotalBiayaPostingTxt').text('Rp ' + addCommas(currentVendorSubtotal.toFixed(2)));
            $('#swTotalEfisiensiTxt').html('<b>Rp ' + addCommas(totalEfisiensi.toFixed(2)) + '</b>');
        };

        $(document).on('input', '.sw-input-qty, .sw-input-harga, .sw-input-subtotal', function() {
            var row = $(this).closest('tr');
            var qtyField = row.find('.sw-input-qty');
            var hargaField = row.find('.sw-input-harga');
            var subtotalField = row.find('.sw-input-subtotal');
            var hematTxt = row.find('.sw-item-hemat-txt');

            var qty = parseFloat(qtyField.val());
            if (isNaN(qty)) qty = 0;
            var harga = parseFloat(removeCommas(hargaField.val())) || 0;
            var subtotal = parseFloat(removeCommas(subtotalField.val())) || 0;

            var budget_qty = parseFloat(qtyField.attr('data-budget-qty'));
            if (isNaN(budget_qty)) budget_qty = qty;
            var budget_harga = parseFloat(hargaField.attr('data-budget-harga')) || 0;
            var budget_subtotal = parseFloat(subtotalField.attr('data-budget-subtotal')) || (budget_qty * budget_harga);

            if ($(this).hasClass('sw-input-qty')) {
                if (qty < 0) {
                    qty = 0;
                    qtyField.val(0);
                }
                if (budget_qty > 0 && qty > budget_qty) {
                    showSwalAlert('Peringatan', 'Volume melebihi budget maksimal (' + budget_qty + ')', 'warning');
                    qty = budget_qty;
                    qtyField.val(qty);
                }
                subtotal = qty * harga;
                if (budget_subtotal > 0 && subtotal > budget_subtotal) {
                    subtotal = budget_subtotal;
                }
                subtotalField.val(addCommas(subtotal.toFixed(2)));
            }
            else if ($(this).hasClass('sw-input-harga')) {
                if (budget_harga > 0 && harga > budget_harga) {
                    showSwalAlert('Peringatan', 'Tarif satuan melebihi tarif budget (Rp ' + addCommas(budget_harga.toFixed(2)) + ')', 'warning');
                    harga = budget_harga;
                    hargaField.val(addCommas(harga.toFixed(2)));
                }
                subtotal = qty * harga;
                if (budget_subtotal > 0 && subtotal > budget_subtotal) {
                    subtotal = budget_subtotal;
                }
                subtotalField.val(addCommas(subtotal.toFixed(2)));
            }
            else if ($(this).hasClass('sw-input-subtotal')) {
                if (budget_subtotal > 0 && subtotal > budget_subtotal) {
                    showSwalAlert('Peringatan', 'Subtotal melebihi total budget (Rp ' + addCommas(budget_subtotal.toFixed(2)) + ')', 'warning');
                    subtotal = budget_subtotal;
                    subtotalField.val(addCommas(subtotal.toFixed(2)));
                }
                harga = qty > 0 ? (subtotal / qty) : 0;
                if (budget_harga > 0 && harga > budget_harga) {
                    harga = budget_harga;
                    subtotal = qty * harga;
                    subtotalField.val(addCommas(subtotal.toFixed(2)));
                }
                hargaField.val(addCommas(harga.toFixed(2)));
            }

            var curItemHemat = (budget_subtotal > subtotal) ? (budget_subtotal - subtotal) : 0;
            if (curItemHemat > 0) {
                hematTxt.html('<i class=\"fa fa-arrow-down\"></i> Efisiensi: Rp ' + addCommas(curItemHemat.toFixed(2))).removeClass('text-muted').addClass('text-green').css('font-weight', 'bold');
            } else {
                hematTxt.text('Sesuai Budget').removeClass('text-green').addClass('text-muted').css('font-weight', 'normal');
            }

            window.recalcSwitchBiayaTotals();
        });

        $(document).on('change', 'input[name=\"sw_type_pelaksana\"]', function() {
            renderSwitchFormState();
        });

        window.submitSwitchPelaksana = function() {
            if (!currentSwitchSpkData) return;
            var noSpk = $('#swNoSpk').val();
            var rawSelectedType = $('input[name=\'sw_type_pelaksana\']:checked').val();
            var newType = rawSelectedType;
            var newId = '';
            var newNama = '';
            var isGroupMode = (rawSelectedType === 'group');
            var swalFn = function(title, text, type, callback) {
                showSwalAlert(title, text, type, callback);
            };

            // Validasi izin switch arah
            if (currentSwitchSpkData.settings) {
                if (currentSwitchSpkData.spk.type_pelaksana == 11 && newType == '22' && parseInt(currentSwitchSpkData.settings.allow_internal_to_external) === 0) {
                    swalFn('Dilarang', 'Pengalihan dari Internal ke Vendor (Eksternal) dilarang oleh Pengaturan Sistem.', 'error');
                    return;
                }
                if (currentSwitchSpkData.spk.type_pelaksana == 22 && newType == '11' && parseInt(currentSwitchSpkData.settings.allow_external_to_internal) === 0) {
                    swalFn('Dilarang', 'Pengalihan dari Vendor (Eksternal) ke Internal dilarang oleh Pengaturan Sistem.', 'error');
                    return;
                }
            }

            if (isGroupMode) {
                if (!currentGroupAllocations || currentGroupAllocations.length === 0) {
                    swalFn('Peringatan', 'Formasi Group belum memiliki anggota pelaksana. Silakan tambahkan minimal satu pelaksana.', 'warning');
                    return;
                }
                var hasInternalMember = false;
                var totalPersen = 0;
                $.each(currentGroupAllocations, function(i, a) {
                    if (parseInt(a.entity_type) === 11) {
                        hasInternalMember = true;
                    }
                    totalPersen += parseFloat(a.proporsi_persen || 0);
                });
                if (hasInternalMember) {
                    swalFn('Peringatan', 'Anggota Karyawan dalam Group dinonaktifkan. Group hanya diperbolehkan untuk Vendor.', 'warning');
                    return;
                }
                var taxSeen = {};
                $.each(currentGroupAllocations, function(i, a) {
                    var t = a.pajak_default || 'pph23';
                    taxSeen[t] = true;
                });
                if (taxSeen['pph21'] && taxSeen['pph23']) {
                    swalFn('Dilarang Lintas Pajak', 'Dilarang mencampur Vendor Badan (PPh 23) dan Vendor Perorangan (PPh 21) dalam 1 Group. Seluruh anggota group wajib memiliki jenis pajak yang seragam.', 'error');
                    return;
                }
                if (Math.abs(totalPersen - 100) > 0.05) {
                    swalFn('Peringatan', 'Total proporsi alokasi anggota group harus tepat 100% (saat ini: ' + totalPersen.toFixed(2) + '%). Silakan sesuaikan proporsi atau klik Bagi Rata.', 'warning');
                    return;
                }

                var tmplVal = $('#swSelectGroupTemplate').val();
                var grpNameInput = $.trim($('#swGroupNameInput').val());
                if (!grpNameInput) {
                    grpNameInput = 'Group Pelaksana SPK ' + noSpk;
                }
                newNama = grpNameInput;

                if (currentSwitchSpkData.spk.is_group == 1 && currentSwitchSpkData.spk.group_id) {
                    newId = 'group_proj_' + currentSwitchSpkData.spk.group_id;
                } else if (tmplVal) {
                    newId = 'group_master_' + tmplVal;
                } else {
                    newId = 'group_new';
                }
                newType = '11';
            } else if (newType == '11') {
                newId = $('#swSelectKaryawan').val();
                newNama = $('#swSelectKaryawan option:selected').data('nama') || '';
                if (!newId) {
                    swalFn('Peringatan', 'Silakan pilih Karyawan pelaksana', 'warning');
                    return;
                }
                if (String(newId).indexOf('group_') === 0) {
                    swalFn('Peringatan', 'Pelaksana Karyawan Group dinonaktifkan. Karyawan hanya dapat ditunjuk sebagai pelaksana individual.', 'warning');
                    return;
                }
            } else {
                newId = $('#swSelectVendor').val();
                newNama = $('#swSelectVendor option:selected').data('nama') || '';
                if (!newId) {
                    swalFn('Peringatan', 'Silakan pilih Vendor / Group pelaksana', 'warning');
                    return;
                }
            }

            // Proteksi Tombol & Kasir Ketat
            if ($('#btnSubmitSwitchPelaksana').is(':disabled') || $('#btnSubmitSwitchPelaksana').prop('disabled')) {
                return false;
            }

            if (currentSwitchSpkData.has_active_biaya && !currentSwitchSpkData.can_void) {
                swalFn('Terkunci', 'Penggantian pelaksana tidak dapat diproses karena tagihan biaya sudah dicairkan/dibayarkan oleh kasir.', 'error');
                return false;
            }

            var isVendorChanged = (currentSwitchSpkData.spk.type_pelaksana == 22 && newType == '22' && newId != currentSwitchSpkData.spk.employee_id);
            if ((newType == '11' || isVendorChanged) && currentSwitchSpkData.spk.type_pelaksana == 22 && currentSwitchSpkData.has_active_biaya && !currentSwitchSpkData.can_void) {
                swalFn('Ditolak', 'Penggantian pelaksana tidak dapat diproses karena tagihan vendor lama sudah dicairkan/dibayarkan oleh kasir.', 'error');
                return;
            }

            var voidBiaya = $('#swCheckVoidBiaya').is(':checked') ? 1 : 0;
            var selectedKaryawanOpt = $('#swSelectKaryawan option:selected');
            var selectedVendorOpt = $('#swSelectVendor option:selected');
            var isGroupSelected = (newType == '11' && (selectedKaryawanOpt.data('isgroup') == 1 || selectedKaryawanOpt.data('isgroup') == '1')) ||
                                  (newType == '22' && (selectedVendorOpt.data('isgroup') == 1 || selectedVendorOpt.data('isgroup') == '1'));
            var memberIds = [];
            var catatanPelaksana = '';

            if (isGroupMode) {
                catatanPelaksana = $('#swCatatanPelaksanaGroup').val() || '';
                $.each(currentGroupAllocations, function(i, a) {
                    memberIds.push(a.employee_id);
                });
            } else if (isGroupSelected) {
                var selectedTaxes = {};
                $('.sw-check-member:checked').each(function() {
                    memberIds.push($(this).data('id'));
                    var tx = $(this).data('pajak') || 'pph21';
                    selectedTaxes[tx] = true;
                });
                catatanPelaksana = $('#swCatatanPelaksana').val() || '';

                if (memberIds.length === 0 && catatanPelaksana.trim() === '') {
                    swalFn('Perhatian', 'Group pelaksana wajib memiliki minimal 1 member yang dipilih.', 'warning');
                    return false;
                }

                if (newType == 22 && selectedTaxes['pph21'] && selectedTaxes['pph23']) {
                    swalFn('Dilarang Lintas Pajak', 'Dilarang mencampur Vendor Badan (PPh 23) dan Vendor Perorangan (PPh 21) dalam 1 Group. Seluruh anggota group wajib memiliki jenis pajak yang seragam.', 'error');
                    return false;
                }
            }

            var btn = $('#btnSubmitSwitchPelaksana');
            btn.prop('disabled', true).attr('disabled', 'disabled').html('<i class=\"fa fa-spinner fa-spin\"></i> Menyimpan...');

            var payload = {
                no_spk: noSpk,
                new_type: newType,
                new_pelaksana_id: newId,
                new_pelaksana_nama: newNama,
                void_biaya: voidBiaya,
                member_ids: memberIds,
                catatan_pelaksana: catatanPelaksana
            };

            if (isGroupMode) {
                payload.member_allocations = JSON.stringify(currentGroupAllocations);
            }

            var adjustedBiaya = [];
            $('#swTbodyRincianBiaya tr.sw-biaya-row-vendor').each(function() {
                var rowQty = $(this).find('.sw-input-qty');
                if (rowQty.length) {
                    var idx = rowQty.data('idx');
                    var itm = currentSwitchSpkData.estimasi_biaya_posting[idx];
                    var qtyVal = parseFloat(rowQty.val());
                    if (isNaN(qtyVal)) qtyVal = 0;
                    var hrgVal = parseFloat(removeCommas($(this).find('.sw-input-harga').val())) || 0;
                    var subVal = parseFloat(removeCommas($(this).find('.sw-input-subtotal').val())) || 0;
                    adjustedBiaya.push({
                        id: itm.id,
                        nama: itm.nama,
                        qty: qtyVal,
                        harga: hrgVal,
                        subtotal: subVal,
                        budget_subtotal: parseFloat(itm.budget_subtotal) || (parseFloat(itm.jml) * parseFloat(itm.tarif_satuan)),
                        posting_vendor: itm.posting_vendor
                    });
                }
            });
            if (adjustedBiaya.length > 0) {
                payload.adjusted_biaya = JSON.stringify(adjustedBiaya);
            }

            $.ajax({
                url: '$modalSwitchUrlSave',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    setSubmitButtonState(false);
                    if (res.status == 1) {
                        $('#modalSwitchPelaksana').modal('hide');
                        if (typeof top.$ !== 'undefined' && top.$('#modalSwitchPelaksana').length > 0) {
                            top.$('#modalSwitchPelaksana').modal('hide');
                        }

                        var doRefreshTasklistDanWO = function() {
                            var refreshed = false;

                            // 1. Refresh modal detail Work Order / Pelaksana jika sedang terbuka
                            if ($('#btnRefreshModal').length > 0) {
                                $('#btnRefreshModal').click();
                                refreshed = true;
                            }
                            if (typeof top.$ !== 'undefined' && top.$('#btnRefreshModal').length > 0) {
                                top.$('#btnRefreshModal').click();
                                refreshed = true;
                            }
                            if (typeof parent.$ !== 'undefined' && parent.$('#btnRefreshModal').length > 0) {
                                parent.$('#btnRefreshModal').click();
                                refreshed = true;
                            }

                            // 2. Refresh Tab Tasklist / WO (#tab_tasklist)
                            if (typeof top.$ !== 'undefined' && top.$(\"a[href='#tab_tasklist']\").length > 0) {
                                top.$(\"a[href='#tab_tasklist']\").trigger('hide.bs.tab').trigger('shown.bs.tab');
                                refreshed = true;
                            }
                            if ($(\"a[href='#tab_tasklist']\").length > 0) {
                                $(\"a[href='#tab_tasklist']\").trigger('hide.bs.tab').trigger('shown.bs.tab');
                                refreshed = true;
                            }
                            if (typeof parent.$ !== 'undefined' && parent.$(\"a[href='#tab_tasklist']\").length > 0) {
                                parent.$(\"a[href='#tab_tasklist']\").trigger('hide.bs.tab').trigger('shown.bs.tab');
                                refreshed = true;
                            }

                            // 3. Refresh DataTable Daftar Tugas / Tasklist jika aktif
                            if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('#daftar_tugas_')) {
                                try {
                                    $('#daftar_tugas_').DataTable().ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }
                            if (typeof top.$ !== 'undefined' && top.$.fn && top.$.fn.DataTable && top.$.fn.DataTable.isDataTable('#daftar_tugas_')) {
                                try {
                                    top.$('#daftar_tugas_').DataTable().ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }
                            if (typeof parent.$ !== 'undefined' && parent.$.fn && parent.$.fn.DataTable && parent.$.fn.DataTable.isDataTable('#daftar_tugas_')) {
                                try {
                                    parent.$('#daftar_tugas_').DataTable().ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }
                            if (typeof top.dft_tugas !== 'undefined' && typeof top.dft_tugas.ajax !== 'undefined') {
                                try {
                                    top.dft_tugas.ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }

                            // 4. Panggil loadTasklistTab jika tersedia di scope
                            if (typeof loadTasklistTab === 'function') {
                                try {
                                    var hUrl = window.location.href.split('#')[0];
                                    var tLoc = hUrl.substring(hUrl.lastIndexOf('/') + 1).split('?')[0];
                                    loadTasklistTab(tLoc);
                                    refreshed = true;
                                } catch(e) {}
                            }
                            if (typeof top.loadTasklistTab === 'function') {
                                try {
                                    var hUrl = top.window.location.href.split('#')[0];
                                    var tLoc = hUrl.substring(hUrl.lastIndexOf('/') + 1).split('?')[0];
                                    top.loadTasklistTab(tLoc);
                                    refreshed = true;
                                } catch(e) {}
                            }

                            // 5. Refresh Timeline jika ada
                            if ($('#btnReloadTimeLine').length > 0) {
                                $('#btnReloadTimeLine').click();
                                refreshed = true;
                            }
                            if (typeof top.$ !== 'undefined' && top.$('#btnReloadTimeLine').length > 0) {
                                top.$('#btnReloadTimeLine').click();
                                refreshed = true;
                            }
                            if (typeof parent.$ !== 'undefined' && parent.$('#btnReloadTimeLine').length > 0) {
                                parent.$('#btnReloadTimeLine').click();
                                refreshed = true;
                            }
                            if (typeof timeline !== 'undefined' && typeof timeline.ajax !== 'undefined') {
                                try {
                                    timeline.ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }
                            if (typeof top.timeline !== 'undefined' && typeof top.timeline.ajax !== 'undefined') {
                                try {
                                    top.timeline.ajax.reload(null, false);
                                    refreshed = true;
                                } catch(e) {}
                            }

                            // 6. Fallback jika tidak ada satupun handle yang aktif
                            if (!refreshed) {
                                if (typeof location !== 'undefined' && typeof location.reload === 'function') {
                                    location.reload();
                                } else if (typeof window !== 'undefined' && typeof window.location.reload === 'function') {
                                    window.location.reload();
                                }
                            }
                        };

                        var hasTriggeredRefresh = false;
                        var triggerRefreshOnce = function() {
                            if (hasTriggeredRefresh) return;
                            hasTriggeredRefresh = true;
                            doRefreshTasklistDanWO();
                        };

                        showSwalAlert('Berhasil', res.message, 'success', function() {
                            triggerRefreshOnce();
                        });
                    } else {
                        btn.prop('disabled', false).removeAttr('disabled').html('<i class=\"fa fa-save\"></i> Simpan Perubahan');
                        setSubmitButtonState(false);
                        showSwalAlert('Gagal', res.message || 'Gagal menyimpan perubahan', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).removeAttr('disabled').html('<i class=\"fa fa-save\"></i> Simpan Perubahan');
                    setSubmitButtonState(false);
                    showSwalAlert('Error', 'Terjadi kesalahan sistem saat menyimpan data', 'error');
                }
            });
        };

        window.triggerRepostBiaya = function() {
            var noSpk = $('#swNoSpk').val() || (currentSwitchSpkData && currentSwitchSpkData.spk && currentSwitchSpkData.spk.no_spk);
            if (!noSpk) {
                showSwalAlert('Peringatan', 'Nomor SPK tidak valid atau data SPK belum termuat.', 'warning');
                return;
            }

            var confirmMsg = 'Transaksi biaya sebelumnya (3674) belum memiliki Payment Source (3675) sehingga belum dapat diproses kasir. Sistem akan meregenerasi transaksi biaya untuk SPK ini dan menerbitkan Payment Source (3675) yang sah untuk vendor aktif. Apakah Anda yakin ingin memproses Reposting Biaya sekarang?';
            showSwalConfirm('Konfirmasi Reposting Biaya', confirmMsg, 'Ya, Reposting Sekarang', function(confirmed) {
                if (!confirmed) return;

                var btn = $('#btnRepostBiaya');
                var origHtml = btn.html();
                btn.prop('disabled', true).attr('disabled', 'disabled').html('<i class=\"fa fa-spinner fa-spin\"></i> Memproses...');

                $.ajax({
                    url: '$modalSwitchUrlRepost',
                    type: 'POST',
                    data: { no_spk: noSpk },
                    dataType: 'json',
                    success: function(res) {
                        btn.prop('disabled', false).removeAttr('disabled').html(origHtml);
                        if (res && res.status == 1) {
                            showSwalAlert('Berhasil Reposting', res.message || 'Transaksi biaya dan Payment Source (3675) berhasil diterbitkan.', 'success', function() {
                                if (typeof openModalSwitchPelaksana === 'function') {
                                    openModalSwitchPelaksana(noSpk);
                                }
                            });
                        } else {
                            showSwalAlert('Gagal Reposting', (res && res.message) ? res.message : 'Gagal memproses reposting biaya.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        btn.prop('disabled', false).removeAttr('disabled').html(origHtml);
                        showSwalAlert('Error', 'Terjadi kesalahan sistem saat menghubungi server untuk reposting biaya.', 'error');
                    }
                });
            });
        };

        // START OF COMPLETE REPEATED LOGIC
        window.handleInlineRepostBiaya = function(el) {
            var noSpk = $(el).attr('data-spk');
            if (!noSpk) {
                showSwalAlert('Peringatan', 'Nomor SPK tidak valid.', 'warning');
                return;
            }
            var confirmMsg = 'Sistem akan menerbitkan ulang transaksi Estimasi Pembayaran Biaya (EPB-3674) dan Payment Source (3675) untuk SPK ' + noSpk + '. Lanjutkan?';
            showSwalConfirm('Terbitkan Ulang Biaya', confirmMsg, 'Ya, Terbitkan Ulang', function(confirmed) {
                if (!confirmed) return;
                var btnEl = $(el);
                var origText = btnEl.html();
                btnEl.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i> Memproses...');
                $.ajax({
                    url: '$modalSwitchUrlRepost',
                    type: 'POST',
                    data: { no_spk: noSpk },
                    dataType: 'json',
                    success: function(res) {
                        btnEl.prop('disabled', false).html(origText);
                        if (res && res.status == 1) {
                            showSwalAlert('Berhasil', res.message || 'Biaya berhasil diterbitkan ulang.', 'success', function() {
                                if (typeof oTable !== 'undefined' && oTable) {
                                    oTable.draw(false);
                                } else if (typeof fnTaskList !== 'undefined' && typeof fnTaskList.reload === 'function') {
                                    fnTaskList.reload();
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            showSwalAlert('Gagal', (res && res.message) ? res.message : 'Gagal menerbitkan ulang biaya.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        btnEl.prop('disabled', false).html(origText);
                        showSwalAlert('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error');
                    }
                });
            });
        };

        if (typeof top !== 'undefined') {
            top.handleInlineRepostBiaya = window.handleInlineRepostBiaya;
        }
        if (typeof parent !== 'undefined') {
            parent.handleInlineRepostBiaya = window.handleInlineRepostBiaya;
        }
        // END OF COMPLETE REPEATED LOGIC

        if (typeof top !== 'undefined') {
            top.triggerRepostBiaya = window.triggerRepostBiaya;
        }
        if (typeof parent !== 'undefined') {
            parent.triggerRepostBiaya = window.triggerRepostBiaya;
        }
        </script>
        ";

        $p->addTags(array(
            "taskListNew" => $taskListNew,
            "btnTambahTugas" => $btnTambahTugas,
//            "taskListNew"        => $taskListNew,
//            "workOrder"          => $workOrder,
//            "workOrder"          => "workOrder",
            "tasklist" => $taskListData, //daftar tugas <== dimatikan dulu belum di perlukan
//            "tasklist"           => "", //daftar tugas
//            "tasklist"           => "", //daftar tugas
            "timWork" => "",
            "projectData" => "", //$project
            "projectDataDetails" => $projectDataDetails,
//            "projectDataDetails" => "",
//            "content"            => $strMain,
            "content" => "",
            //"workOrder"          => $workOrder,
            "scriptBottom" => isset($scriptBottom) ? $scriptBottom : "",
            "scriptBottomCtr" => isset($scriptBottomCtr) ? $scriptBottomCtr : "",
            "display_iframe" => "none",//biar gak panjang
            "error_msg" => isset($error) ? $error : "",
            "menu_left" => callMenuLeft(),
            "trans_menu" => callTransMenu(),
            "float_menu_atas" => callFloatMenu('atas'),
            "float_menu_bawah" => callFloatMenu(),
            "menu_taskbar" => callMenuTaskbar(),
            "btn_back" => callBackNav(),
            "ci_session" => $ci_session,
            "class_projectSudahStart" => $produkID=="qc" ? "" : ($projectStart ? "" : "hidden"),
            "class_projectBelumStart" => $projectStart ? "hidden" : "",
            "projectBelumStart" => "$belumStartProject",
//            "allowCreateTugas" => "$allowCreateTugas",
        ));

        $p->render();
        break;
    case "barcodeView":
        $p = New Layout("", "", MODUL_TEMPLATE_PATH . "template/modalBarcode.html");
        $p->addTags(array(
            "content" => $content,
            "jsBottom" => $jsBottom,
        ));

        $p->render();
        break;
}