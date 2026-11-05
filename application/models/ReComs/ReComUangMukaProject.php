<?php

class ReComUangMukaProject extends MdlMother
{
    private $jenisTr;


    public function getJenisTr()
    {
        return $this->jenisTr;
    }

    public function setJenisTr($jenisTr)
    {
        $this->jenisTr = $jenisTr;
    }


    public function __construct()
    {
        parent::__construct();
        $this->jenisTr = $this->uri->segment(4);
        $this->blacklist = array("9911", "9912");
    }

    public function pair($gate, $source)
    {
        arrPrint($gate);
        $cCode = "_TR_" . $this->jenisTr;
        $compareVal = isset($gate['pair_source']) ? $gate['pair_source'] : "sisa_tagihan";
        $srcValue = isset($gate['source']) ?  $_SESSION[$cCode]['main'][$gate['source']] : $_SESSION[$cCode]['main']["sisa_tagihan"];
        $key = $gate['target'];
        $pair_to_rekening = isset($gate['pair_to_rekening']) ? $gate['pair_to_rekening'] : NULL;

        if (!in_array($this->jenisTr, $this->blacklist)) {

            $validVal = $_SESSION[$cCode]['main'][$compareVal] > $srcValue * 1 ? $srcValue : $_SESSION[$cCode]['main'][$compareVal];
            cekKuning("[$key] [$validVal] [$compareVal] [$srcValue]");
            if (sizeof($_SESSION[$cCode]['main']) > 0) {
                if (!isset($_SESSION[$cCode]['main'][$key])) {
                    $_SESSION[$cCode]['main'][$key] = $validVal;
                }
                else {
                    $_SESSION[$cCode]['main'][$key] = $validVal;
                }
            }
        }
//matiHEre(__LINE__);
        return true;
    }

    public function exec()
    {

//        mati_disini();
        return true;
    }
}