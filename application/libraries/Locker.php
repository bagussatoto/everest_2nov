<?php

/**
 * Created by JetBrains PhpStorm.
 * User: azes
 * Date: 5/9/12
 * Time: 11:56 AM
 * To change this template use File | Settings | File Templates.
 */

//include_once "Bs_37.php";


class Locker
{
    protected $loginSessions;

    public function getLoginSessions()
    {
        return $this->loginSessions;
    }

    public function setLoginSessions($loginSessions)
    {
        $this->loginSessions = $loginSessions;
    }

    // protected $toko_id;
    //
    // public function getTokoId()
    // {
    //     return $this->toko_id;
    // }
    //
    // public function setTokoId($toko_id)
    // {
    //     $this->toko_id = $toko_id;
    // }

    public function __construct()
    {
        // parent::__construct();
        $this->CI =& get_instance();

    }

// START OF COMPLETE REPEATED LOGIC
    public function normalisasiStok($isAutoClean = false)
    {
        $login_session = isset($this->loginSessions) ? $this->loginSessions : matiDisini("prameter session login harap diset dulu");

        $this->CI->load->model("Mdls/MdlLockerStock");
        $this->CI->load->model("Coms/ComLockerStock");
        //region locker finish goods
        $c = new MdlLockerStock();
        $c->addFilter("stock_locker.jenis='produk'");
        $c->addFilter("state='hold'");
        $c->addFilter("jumlah>'0'");
        // $c->addFilter("cabang_id=" . $login_session['cabang_id']);
        // $c->addFilter("gudang_id=" . $login_session['gudang_id']);
        $c->addFilter("oleh_id=" . $login_session['id']);
        $c->addFilter("transaksi_id='0'");
        $tmpC = $c->lookupAll()->result();


        if (sizeof($tmpC) > 0) {

            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpC as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                //==param untuk melepas stok HOLD
                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $login_session['id'],
                        "transaksi_id" => 0,

                    ),
                );
                $sentParams[] = $subParams;

                //==param untuk mengembalikan stok aktiv
                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,

                    ),
                );
                $sentParams2[] = $subParams2;

            }
            $cs = new ComLockerStock();
            $cs->pair($sentParams) or die("Unable to pair locker for releasing");
            $cs->exec();
            //
            $cs = new ComLockerStock();
            $cs->pair($sentParams2) or die("Unable to pair locker for putting back");
            $cs->exec();

        }
        //endregion

        $this->CI->load->model("Mdls/MdlLockerStockSupplies");
        $this->CI->load->model("Coms/ComLockerStockSupplies");
        //region locker supplies
        $s = new MdlLockerStockSupplies();
        $s->addFilter("stock_locker.jenis='supplies'");
        $s->addFilter("state='hold'");
        // $s->addFilter("cabang_id=" . $login_session['cabang_id']); //dimatiakan untuk global normalisasi
        // $s->addFilter("gudang_id=" . $login_session['gudang_id']);
        $s->addFilter("oleh_id=" . $login_session['id']);
        $s->addFilter("transaksi_id='0'");
        $s->addFilter("jumlah>'0'");
        $tmpS = $s->lookupAll()->result();

        if (sizeof($tmpS) > 0) {
            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpS as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $login_session['id'],
                        "transaksi_id" => 0,
                        "biaya_id" => $row->biaya_id,
                        "nama" => $row->nama,

                    ),
                );
                $sentParams[] = $subParams;

                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,

                    ),
                );
                $sentParams2[] = $subParams2;

            }
            $ss = new ComLockerStockSupplies();
            $ss->pair($sentParams) or die("Unable to pair locker for releasing");
            $ss->exec();
            //
            $ss = new ComLockerStockSupplies();
            $ss->pair($sentParams2) or die("Unable to pair locker for putting back");
            $ss->exec();
        }
        //endregion

        $this->CI->load->model("Mdls/MdlLockerStockAktiva");
        $this->CI->load->model("Coms/ComLockerStockAktiva");
        //region locker asset tetap
        $s = new MdlLockerStockAktiva();
        $s->addFilter("stock_locker.jenis='aktiva'");
        $s->addFilter("state='hold'");
        // $s->addFilter("cabang_id=" . $login_session['cabang_id']); //dimatiakan untuk global normalisasi
        // $s->addFilter("gudang_id=" . $login_session['gudang_id']);
        $s->addFilter("oleh_id=" . $login_session['id']);
        $s->addFilter("transaksi_id='0'");
        $tmpS = $s->lookupAll()->result();
        if (sizeof($tmpS) > 0) {

            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpS as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                //==param untuk melepas stok HOLD
                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $login_session['id'],
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams[] = $subParams;

                //==param untuk mengembalikan stok aktiv
                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,

                    ),
                );
                $sentParams2[] = $subParams2;

            }
            $ss = new ComLockerStockAktiva();
            $ss->pair($sentParams) or die("Unable to pair locker for releasing");
            $ss->exec();
            //
            $ss = new ComLockerStockAktiva();
            $ss->pair($sentParams2) or die("Unable to pair locker for putting back");
            $ss->exec();
        }
        //endregion

        if ($isAutoClean) {
        $this->CI->load->model("Mdls/MdlLockerTransaksi");
        $this->CI->load->model("Coms/ComLockerTransaksi");
        //region locker transaksi
        $s = new MdlLockerTransaksi();
        $s->addFilter("stock_locker_transaksi.jenis='transaksi'");
        $s->addFilter("stock_locker_transaksi.jenis_locker='transaksi'");
        $s->addFilter("state='hold'");
        // $s->addFilter("cabang_id=" . $login_session['cabang_id']); //dimatiakan untuk global normalisasi
        $s->addFilter("oleh_id=" . $login_session['id']);
        $s->addFilter("transaksi_id>'0'");
        $s->addFilter("jumlah>'0'");
        $tmpS = $s->lookupAll()->result();
        if (sizeof($tmpS) > 0) {

            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpS as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                //==param untuk melepas stok HOLD
                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => 0,
                        "jenis" => $row->jenis,
                        "jenis_locker" => $row->jenis_locker,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $login_session['id'],
                        "transaksi_id" => $row->transaksi_id,
                    ),
                );
                $sentParams[] = $subParams;

                //==param untuk mengembalikan stok aktiv
                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => 0,
                        "jenis" => $row->jenis,
                        "jenis_locker" => $row->jenis_locker,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => $row->transaksi_id,
                    ),
                );
                $sentParams2[] = $subParams2;

            }
            $ss = new ComLockerTransaksi();
            $ss->pair($sentParams) or die("Unable to pair locker for releasing");
            $ss->exec();
            //
            $ss = new ComLockerTransaksi();
            $ss->pair($sentParams2) or die("Unable to pair locker for putting back");
            $ss->exec();
        }
        //endregion
        }


        $this->CI->load->model("Mdls/MdlProdukPerSerialNumberLocker");
        $sl = New MdlProdukPerSerialNumberLocker();
        $sl->addFilter("oleh_id=" . $login_session['id']);
        $sl->addFilter("jumlah>'0'");
        $tmpSl = $sl->lookupAll()->result();
        if (sizeof($tmpSl) > 0) {
            foreach ($tmpSl as $row) {
                $id_tbl = $row->id;
                $data = array(
                    "jumlah" => 0,
                );
                $where = array(
                    "id" => $id_tbl,
                );
                $sl->setFilters(array());
                $sl->updateData($where, $data);
            }
        }


    }

    public function autoNormalisasiStok()
    {
        $this->CI->load->config("heWebs");
        $coLogins = $this->CI->config->item('logins');
        $idleTime = isset($coLogins['idleTime']) ? intval($coLogins['idleTime']) : 20;
        $holdTimeLocker = $idleTime * 1.5;
        $cutoffTime = date("Y-m-d H:i:s", strtotime("-" . intval($holdTimeLocker) . " minutes"));

        //region 1. Sweeper Langsung Tabel stock_locker (Produk Finish Goods)
        $this->CI->load->model("Mdls/MdlLockerStock");
        $this->CI->load->model("Coms/ComLockerStock");
        $c = new MdlLockerStock();
        $c->setFilters(array());
        $c->addFilter("stock_locker.jenis='produk'");
        $c->addFilter("state='hold'");
        $c->addFilter("jumlah>'0'");
        $c->addFilter("transaksi_id='0'");
        $c->addFilter("last_dtime<='$cutoffTime'");
        $tmpC = $c->lookupAll()->result();

        if (sizeof($tmpC) > 0) {
            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpC as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $row->oleh_id,
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams[] = $subParams;

                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams2[] = $subParams2;
            }
            $cs = new ComLockerStock();
            $cs->pair($sentParams) or die("Unable to pair locker for auto releasing produk");
            $cs->exec();

            $cs = new ComLockerStock();
            $cs->pair($sentParams2) or die("Unable to pair locker for auto putting back produk");
            $cs->exec();
        }
        //endregion

        //region 2. Sweeper Langsung Tabel stock_locker (Supplies)
        $this->CI->load->model("Mdls/MdlLockerStockSupplies");
        $this->CI->load->model("Coms/ComLockerStockSupplies");
        $s = new MdlLockerStockSupplies();
        $s->setFilters(array());
        $s->addFilter("stock_locker.jenis='supplies'");
        $s->addFilter("state='hold'");
        $s->addFilter("jumlah>'0'");
        $s->addFilter("transaksi_id='0'");
        $s->addFilter("last_dtime<='$cutoffTime'");
        $tmpS = $s->lookupAll()->result();

        if (sizeof($tmpS) > 0) {
            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpS as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $row->oleh_id,
                        "transaksi_id" => 0,
                        "biaya_id" => $row->biaya_id,
                        "nama" => $row->nama,
                    ),
                );
                $sentParams[] = $subParams;

                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams2[] = $subParams2;
            }
            $ss = new ComLockerStockSupplies();
            $ss->pair($sentParams) or die("Unable to pair locker for auto releasing supplies");
            $ss->exec();

            $ss = new ComLockerStockSupplies();
            $ss->pair($sentParams2) or die("Unable to pair locker for auto putting back supplies");
            $ss->exec();
        }
        //endregion

        //region 3. Sweeper Langsung Tabel stock_locker (Aktiva)
        $this->CI->load->model("Mdls/MdlLockerStockAktiva");
        $this->CI->load->model("Coms/ComLockerStockAktiva");
        $a = new MdlLockerStockAktiva();
        $a->setFilters(array());
        $a->addFilter("stock_locker.jenis='aktiva'");
        $a->addFilter("state='hold'");
        $a->addFilter("jumlah>'0'");
        $a->addFilter("transaksi_id='0'");
        $a->addFilter("last_dtime<='$cutoffTime'");
        $tmpA = $a->lookupAll()->result();

        if (sizeof($tmpA) > 0) {
            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpA as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $row->oleh_id,
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams[] = $subParams;

                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => $row->gudang_id,
                        "jenis" => $row->jenis,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => 0,
                    ),
                );
                $sentParams2[] = $subParams2;
            }
            $sa = new ComLockerStockAktiva();
            $sa->pair($sentParams) or die("Unable to pair locker for auto releasing aktiva");
            $sa->exec();

            $sa = new ComLockerStockAktiva();
            $sa->pair($sentParams2) or die("Unable to pair locker for auto putting back aktiva");
            $sa->exec();
        }
        //endregion

        //region 4. Sweeper Lock Transaksi Kedaluwarsa (stock_locker_transaksi)
        $this->CI->load->model("Mdls/MdlLockerTransaksi");
        $this->CI->load->model("Coms/ComLockerTransaksi");
        $lt = new MdlLockerTransaksi();
        $lt->setFilters(array());
        $lt->addFilter("stock_locker_transaksi.jenis='transaksi'");
        $lt->addFilter("stock_locker_transaksi.jenis_locker='transaksi'");
        $lt->addFilter("state='hold'");
        $lt->addFilter("transaksi_id>'0'");
        $lt->addFilter("jumlah>'0'");
        $lt->addFilter("dtime<='$cutoffTime'");
        $tmpLt = $lt->lookupAll()->result();

        if (sizeof($tmpLt) > 0) {
            $sentParams = array();
            $sentParams2 = array();
            foreach ($tmpLt as $row) {
                $pID = $row->produk_id;
                $jml = $row->jumlah;

                $subParams = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => 0,
                        "jenis" => $row->jenis,
                        "jenis_locker" => $row->jenis_locker,
                        "state" => "hold",
                        "jumlah" => -($jml),
                        "produk_id" => $pID,
                        "oleh_id" => $row->oleh_id,
                        "transaksi_id" => $row->transaksi_id,
                    ),
                );
                $sentParams[] = $subParams;

                $subParams2 = array(
                    "static" => array(
                        "cabang_id" => $row->cabang_id,
                        "gudang_id" => 0,
                        "jenis" => $row->jenis,
                        "jenis_locker" => $row->jenis_locker,
                        "state" => "active",
                        "jumlah" => $jml,
                        "produk_id" => $pID,
                        "oleh_id" => 0,
                        "transaksi_id" => $row->transaksi_id,
                    ),
                );
                $sentParams2[] = $subParams2;
            }
            $slt = new ComLockerTransaksi();
            $slt->pair($sentParams) or die("Unable to pair locker transaksi for auto releasing");
            $slt->exec();

            $slt = new ComLockerTransaksi();
            $slt->pair($sentParams2) or die("Unable to pair locker transaksi for auto putting back");
            $slt->exec();
        }
        //endregion

        //region 5. Force Logout Pegawai yang Idle > holdTimeLocker
        $this->CI->load->model("Mdls/MdlEmployee");
        $em = new MdlEmployee();
        $Srcs = $em->callLastActive();
        if (sizeof($Srcs) > 0) {
            foreach ($Srcs as $src) {
                $id = $src->id;
                $last_dtime_active = $src->last_dtime_active;
                $umur_last_active = umurHour($last_dtime_active, 'i');
                if ($umur_last_active > $holdTimeLocker) {
                    $em->forceLogout($id);
                }
            }
        }
        //endregion
    }
}
// END OF COMPLETE REPEATED LOGIC
