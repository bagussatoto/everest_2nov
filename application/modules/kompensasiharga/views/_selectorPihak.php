<?php
/**
 * Created by PhpStorm.
 * User: jasmanto
 * Date: 17/09/2018
 * Time: 19.35
 */


switch ($mode) {

    case "view":
//        arrprint($items);


        $strContent = "";
        if (isset($items)) {
            if (is_array($items) && (sizeof($items) > 0)) {
                $strContent .= "<ul class='list-group text-left' style='line-height:1.3; margin-bottom:0;'>";
                foreach ($items as $iSpec) {
                    $strContent .= "<li class='list-group-item' style='padding: 6px 8px;'>";
                    if (isset($iSpec['minValue'])) {
                        $defaultValue = $iSpec['minValue'];
                        $defVal = "&minValue=$defaultValue";
                    }
                    else {
                        $defVal = "";
                    }

                    if (isset($iSpec['disabled']) && $iSpec['disabled'] == true) {
                        $strContent .= " <div style='cursor: not-allowed; opacity: 0.85; background: #fafafa; padding: 2px 4px; border-radius: 3px; pointer-events: none; -webkit-user-select: none; user-select: none;'>";
                        $strContent .= $iSpec['label'];
                        if (isset($iSpec['label_view'])) {
                            if (strlen($iSpec['label_view']) > 0) {
                                $strContent .= "<span class='text-red' ><small>(" . $iSpec['label_view'] . ")</small></span>";
                            }
                        }
                        $strContent .= "</div>";
                    }
                    else {
                        if (isset($iSpec["confirm"]) && ($iSpec["confirm"] == 1)) {
                            $anu = $iSpec["label_orig"];
                            $supplier = $iSpec["supplier_nama"];
                            $arrAlert = array(
                                "html" => "Apakah anda akan klaim <span style=font-size:25px;color:#000000;font-weight:bold;>$anu</span> ke supplier <span style=font-size:15px;color:#000000;font-weight:bold;>$supplier </span>???",
                            );
                            $arrAlertFix = array(
                                "type" => "warning",
                                "title" => "<span style=color: red;>Perhatian</span>",
                                "showCloseButton" => false,
                                "allowOutsideClick" => false,
                                "allowEscapeKey" => false,
                                "confirmButtonText" => "YA, LANJUTKAN",
                                "cancelButtonText" => "CANCEL",
                                "showCancelButton" => true,
    //                                                    "background" => "#737574",
                            );
                            $arrAlertFinal = $arrAlert + $arrAlertFix;
                            $hasil = "";
                            foreach ($arrAlertFinal as $ky => $val) {
                                $var = "$ky: '$val'";
                                if ($hasil == "") {
                                    $hasil = "$var";
                                }
                                else {
                                    $hasil = "$hasil, $var";
                                }
                            }

                            $strContent .= " <a href='javascript:void(0);' style='font-size:0.9em; text-decoration:none; display:block; color:inherit;'
                                            onclick=\"swal({".$hasil."}).then(function(isConfirm) {
                                                        if (isConfirm) {
                                                                document.getElementById('result').src='$iSpec[target]?id=$iSpec[id]$defVal'
                                                        }
                                                    }
                                                    );\">";

                        }
                        else{
                            $strContent .= " <a href='javascript:void(0);' style='font-size:0.9em; text-decoration:none; display:block; color:inherit;'
                                            onclick=\"document.getElementById('result').src='$iSpec[target]?id=$iSpec[id]$defVal'\">";
                        }


                        $strContent .= $iSpec['label'];
                        if (isset($iSpec['label_view'])) {
                            if (strlen($iSpec['label_view']) > 0) {

                                $strContent .= "<span class='text-red' ><small>(" . $iSpec['label_view'] . ")</small></span>";
                            }

                        }


                        $strContent .= "</a>";
                    }

                    $strContent .= "</li class='list-group-item'>";
                }

                $strContent .= "<li class='list-group-item text-center text-muted' style='background:#e5e5c5;'><small>... type more keywords<br>for more specific results ...</small>";
                $strContent .= "</li>";
                $strContent .= "</ul class='list-group'>";

                $strContent .= "</ul class='list-group text-left'>";
            }
            else {
                $searchKeyword = isset($search) ? htmlspecialchars($search) : "";
                $strContent .= "<div style='background: #fff; border: 1px dashed #d2d6de; border-radius: 4px; padding: 18px 15px; margin: 6px 0; text-align: center; color: #555;'>";
                $strContent .= "  <div style='margin-bottom: 8px;'><i class='fa fa-search' style='font-size: 26px; color: #f39c12;'></i></div>";
                $strContent .= "  <div style='font-size: 13px; font-weight: bold; color: #2c3e50; margin-bottom: 4px;'>Data Tidak Ditemukan</div>";
                if (!empty($searchKeyword)) {
                    $strContent .= "  <div style='font-size: 12px; color: #7f8c8d; margin-bottom: 8px;'>Tidak ada transaksi yang cocok dengan kata kunci pencarian: <b style='color: #c0392b;'>\"{$searchKeyword}\"</b></div>";
                } else {
                    $strContent .= "  <div style='font-size: 12px; color: #7f8c8d; margin-bottom: 8px;'>Belum ada transaksi diskon yang tersedia untuk dipilih.</div>";
                }
                $strContent .= "  <div style='font-size: 11px; color: #7f8c8d; background: #f8f9fa; border-radius: 3px; padding: 8px 12px; max-width: 480px; margin: 0 auto; text-align: left; line-height: 1.5;'>";
                $strContent .= "    <b>Petunjuk &amp; Kemungkinan Penyebab:</b>";
                $strContent .= "    <ul style='margin: 3px 0 0 16px; padding: 0;'>";
                $strContent .= "      <li>Pastikan format penulisan nomor transaksi sudah sesuai (contoh: <code>467.-1.xxxx</code> atau <code>4643.-1.xxxx</code>).</li>";
                $strContent .= "      <li>Dokumen belum memiliki alokasi diskon vendor atau item promo free produk saat penerimaan.</li>";
                $strContent .= "      <li>Periksa apakah transaksi tersebut sudah diverifikasi dan disetujui di modul asalnya.</li>";
                $strContent .= "    </ul>";
                $strContent .= "  </div>";
                $strContent .= "</div>";
            }
        }
        else {
            $searchKeyword = isset($search) ? htmlspecialchars($search) : "";
            $strContent .= "<div style='background: #fff; border: 1px dashed #d2d6de; border-radius: 4px; padding: 18px 15px; margin: 6px 0; text-align: center; color: #555;'>";
            $strContent .= "  <div style='margin-bottom: 8px;'><i class='fa fa-search' style='font-size: 26px; color: #f39c12;'></i></div>";
            $strContent .= "  <div style='font-size: 13px; font-weight: bold; color: #2c3e50; margin-bottom: 4px;'>Data Tidak Ditemukan</div>";
            if (!empty($searchKeyword)) {
                $strContent .= "  <div style='font-size: 12px; color: #7f8c8d; margin-bottom: 8px;'>Tidak ada transaksi yang cocok dengan kata kunci pencarian: <b style='color: #c0392b;'>\"{$searchKeyword}\"</b></div>";
            } else {
                $strContent .= "  <div style='font-size: 12px; color: #7f8c8d; margin-bottom: 8px;'>Belum ada transaksi diskon yang tersedia untuk dipilih.</div>";
            }
            $strContent .= "  <div style='font-size: 11px; color: #7f8c8d; background: #f8f9fa; border-radius: 3px; padding: 8px 12px; max-width: 480px; margin: 0 auto; text-align: left; line-height: 1.5;'>";
            $strContent .= "    <b>Petunjuk &amp; Kemungkinan Penyebab:</b>";
            $strContent .= "    <ul style='margin: 3px 0 0 16px; padding: 0;'>";
            $strContent .= "      <li>Pastikan format penulisan nomor transaksi sudah sesuai (contoh: <code>467.-1.xxxx</code> atau <code>4643.-1.xxxx</code>).</li>";
            $strContent .= "      <li>Dokumen belum memiliki alokasi diskon vendor atau item promo free produk saat penerimaan.</li>";
            $strContent .= "      <li>Periksa apakah transaksi tersebut sudah diverifikasi dan disetujui di modul asalnya.</li>";
            $strContent .= "    </ul>";
            $strContent .= "  </div>";
            $strContent .= "</div>";
        }

        echo $strContent;

        break;


}

?>