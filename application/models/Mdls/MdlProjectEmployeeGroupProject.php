<?php
// START OF COMPLETE REPEATED LOGIC

class MdlProjectEmployeeGroupProject extends CI_Model
{
    protected $tableGroup = "project_employee_group";
    protected $tableMember = "project_employee_group_member";
    protected $tableMasterGroup = "dta_project_employee_group";
    protected $tableMasterMember = "dta_project_employee_group_member";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil semua master group dari tabel statik dta_project_employee_group
     *
     * @param string|null $groupType 'internal' atau 'external'
     */
    public function getMasterGroups($groupType = null)
    {
        $this->db->where("status", 1)->where("trash", 0);
        if ($groupType !== null && $groupType !== "") {
            $this->db->where("group_type", $groupType);
        }
        return $this->db->order_by("nama", "ASC")->get($this->tableMasterGroup)->result();
    }

    /**
     * Ambil anggota dari master group statik
     */
    public function getMasterGroupMembers($masterGroupId)
    {
        return $this->db
            ->where("group_id", $masterGroupId)
            ->where("status", 1)
            ->where("trash", 0)
            ->order_by("is_lead", "DESC")
            ->order_by("id", "ASC")
            ->get($this->tableMasterMember)
            ->result();
    }

    /**
     * Ambil entitas group project aktif
     */
    public function getProjectGroups($projectId = null, $noSpk = null)
    {
        if ($projectId !== null && intval($projectId) > 0) {
            $this->db->where("project_id", $projectId);
        }
        if ($noSpk !== null && $noSpk !== "") {
            $this->db->where("no_spk", $noSpk);
        }
        return $this->db
            ->where("status", 1)
            ->where("trash", 0)
            ->order_by("nama", "ASC")
            ->get($this->tableGroup)
            ->result();
    }

    /**
     * Ambil anggota group berdasarkan project_employee_group_id
     */
    public function getProjectGroupMembers($projectEmployeeGroupId, $noSpk = null)
    {
        $this->db
            ->where("project_employee_group_id", $projectEmployeeGroupId)
            ->where("status", 1)
            ->where("trash", 0);

        if (!empty($noSpk)) {
            $this->db->where("no_spk", $noSpk);
        }

        return $this->db
            ->order_by("is_lead", "DESC")
            ->order_by("id", "ASC")
            ->get($this->tableMember)
            ->result();
    }

    /**
     * Ambil daftar anggota group pelaksana aktif untuk suatu SPK secara langsung.
     * Filter utama wajib WHERE no_spk = $noSpk agar formasi melekat pada masing-masing SPK.
     *
     * @param string $noSpk Nomor SPK
     * @return array List object anggota dari project_employee_group_member
     */
    public function getMembersBySpk($noSpk)
    {
        if (empty($noSpk)) {
            return array();
        }

        // 1. Filter utama: ambil data member yang dikunci untuk no_spk ini
        $members = $this->db
            ->where("no_spk", $noSpk)
            ->where("status", 1)
            ->where("trash", 0)
            ->order_by("is_lead", "DESC")
            ->order_by("id", "ASC")
            ->get($this->tableMember)
            ->result();

        if (!empty($members)) {
            return $members;
        }

        // 2. Fallback: Cari group_id dari project_tasklist
        $spk = $this->db->select("group_id, is_group")
            ->where("no_spk", $noSpk)
            ->where("trash", 0)
            ->get("project_tasklist")
            ->row();

        if (empty($spk)) {
            $spk = $this->db->select("group_id, is_group")
                ->where("no_spk", $noSpk)
                ->where("trash", 0)
                ->get("project_tasklist_tambahan")
                ->row();
        }

        if (!empty($spk) && !empty($spk->is_group) && !empty($spk->group_id)) {
            return $this->getProjectGroupMembers(intval($spk->group_id),$noSpk);
        }

        return array();
    }

    /**
     * Cari atau buat satu ID entitas group tetap (Entity Payee).
     * Group memiliki SATU ID entitas tetap yang dire-use di semua SPK (tidak di-insert ulang berulang kali).
     *
     * @param string $groupName Nama Group
     * @param string $groupType 'internal' atau 'external'
     * @param int $masterGroupId ID di dta_project_employee_group (jika ada)
     * @param int $projectId ID Project
     * @return int ID entitas tetap di project_employee_group
     */
    public function findOrCreateGroupEntity($groupName, $groupType = "internal", $masterGroupId = 0, $projectId = 0)
    {
        $groupName = trim($groupName);

        // 1. Cari berdasarkan master_group_id jika ada
        if ($masterGroupId > 0) {
            $existing = $this->db->where("master_group_id", $masterGroupId)
                ->where("trash", 0)
                ->order_by("id", "ASC")
                ->get($this->tableGroup)
                ->row();
            if ($existing) {
                return intval($existing->id);
            }
        }

        // 2. Cari berdasarkan nama group yang sama di project_employee_group
        if (!empty($groupName)) {
            $existing = $this->db->where("LOWER(nama)", strtolower($groupName))
                ->where("trash", 0)
                ->order_by("id", "ASC")
                ->get($this->tableGroup)
                ->row();
            if ($existing) {
                return intval($existing->id);
            }
        }

        // 3. Jika benar-benar belum ada, buat 1 entitas tetap baru
        $dataGroup = array(
            "project_id"      => $projectId,
            "master_group_id" => $masterGroupId,
            "nama"            => $groupName,
            "keterangan"      => "Entitas Group Pelaksana Mandiri",
            "group_type"      => $groupType,
            "status"          => 1,
            "trash"           => 0,
            "last_update"     => date("Y-m-d H:i:s"),
            "dtime"           => date("Y-m-d H:i:s")
        );
        $this->db->insert($this->tableGroup, $dataGroup);
        return intval($this->db->insert_id());
    }

    /**
     * Snapshot master group ke entitas project_employee_group.
     * Menerapkan re-use ID entitas tetap: tidak membuat baris group baru jika group sudah ada.
     *
     * @param int $projectId ID Project
     * @param int $masterGroupId ID Master Group (dta_project_employee_group)
     * @param string|null $noSpk Nomor SPK
     * @return int ID entitas tetap di project_employee_group
     */
    public function snapshotMasterGroupToProject($projectId, $masterGroupId, $noSpk = null)
    {
        // Ambil header master
        $masterHeader = $this->db
            ->where("id", $masterGroupId)
            ->get($this->tableMasterGroup)
            ->row();

        if (!$masterHeader) {
            return 0;
        }

        $groupName = $masterHeader->nama;
        $groupType = !empty($masterHeader->group_type) ? $masterHeader->group_type : "internal";

        // Re-use ID entitas tetap yang sudah ada
        $finalGroupId = $this->findOrCreateGroupEntity($groupName, $groupType, $masterGroupId, $projectId);

        // Jika ada noSpk, pastikan default anggota disiapkan untuk SPK ini jika belum ada
        if (!empty($noSpk) && $finalGroupId > 0) {
            $cntMem = $this->db->where("no_spk", $noSpk)
                ->where("project_employee_group_id", $finalGroupId)
                ->where("trash", 0)
                ->count_all_results($this->tableMember);

            if ($cntMem == 0) {
                $masterMembers = $this->getMasterGroupMembers($masterGroupId);
                if (!empty($masterMembers)) {
                    foreach ($masterMembers as $mm) {
                        $dataMember = array(
                            "project_id"                => $projectId,
                            "project_employee_group_id" => $finalGroupId,
                            "employee_id"               => $mm->employee_id,
                            "employee_nama"             => $mm->employee_nama,
                            "entity_type"               => isset($mm->entity_type) ? (int)$mm->entity_type : ($groupType == "external" ? 22 : 11),
                            "proporsi_persen"           => isset($mm->proporsi_persen) ? floatval($mm->proporsi_persen) : 0,
                            "proporsi_nominal"          => isset($mm->proporsi_nominal) ? floatval($mm->proporsi_nominal) : 0,
                            "is_lead"                   => isset($mm->is_lead) ? (int)$mm->is_lead : 0,
                            "pajak_default"             => !empty($mm->pajak_default) ? $mm->pajak_default : ($groupType == "external" ? "pph23" : "pph21"),
                            "no_spk"                    => $noSpk,
                            "status"                    => 1,
                            "trash"                     => 0,
                            "last_update"               => date("Y-m-d H:i:s"),
                            "dtime"                     => date("Y-m-d H:i:s"),
                        );
                        $this->db->insert($this->tableMember, $dataMember);
                    }
                }
            }
        }

        return $finalGroupId;
    }

    /**
     * Sinkronisasi formasi member group pelaksana yang melekat pada nomor SPK tertentu.
     * Mengisolasi member aktif pada SPK bersangkutan dan menandai trash=1 untuk yang tidak dipilih.
     *
     * @param int $projectId ID Project
     * @param int $projectGroupId ID Group di project_employee_group
     * @param string $noSpk Nomor SPK
     * @param array $selectedMemberIds Daftar ID member yang dipilih (karyawan / vendor)
     * @param string $groupType 'internal' atau 'external'
     * @param int $masterGroupId ID Master Group jika ada
     * @return bool
     */
    public function syncSpkGroupMembers($projectId, $projectGroupId, $noSpk, $selectedMemberIds = array(), $groupType = "internal", $masterGroupId = 0)
    {
        if (empty($projectGroupId) || empty($noSpk)) {
            return false;
        }

        // 1. Jika ada masterGroupId, pastikan master group di-snapshot dulu jika belum ada member untuk no_spk
        if ($masterGroupId > 0) {
            $this->snapshotMasterGroupToProject($projectId, $masterGroupId, $noSpk);
        }

        // 2. Jika ada daftar specific selectedMemberIds
        if (!empty($selectedMemberIds)) {
            $cleanMemberIds = array();
            foreach ($selectedMemberIds as $mid) {
                $mid = trim($mid);
                if ($mid !== '') {
                    $cleanMemberIds[] = $mid;
                }
            }

            if (!empty($cleanMemberIds)) {
                $defaultEntityType = ($groupType == "external") ? 22 : 11;
                $defaultTax = ($groupType == "external") ? "pph23" : "pph21";

                foreach ($cleanMemberIds as $mid) {
                    $memRow = $this->db->where("project_employee_group_id", $projectGroupId)
                        ->where("employee_id", $mid)
                        ->where("no_spk", $noSpk)
                        ->where("trash", 0)
                        ->get($this->tableMember)
                        ->row();

                    if (!$memRow) {
                        if ($defaultEntityType == 22) {
                            $supRow = $this->db->select("nama")->where("id", $mid)->get("per_supplier")->row();
                            $memNama = $supRow ? $supRow->nama : "Vendor " . $mid;
                        } else {
                            $empRow = $this->db->select("nama")->where("id", $mid)->get("per_employee")->row();
                            $memNama = $empRow ? $empRow->nama : "Karyawan " . $mid;
                        }
                        $this->db->insert($this->tableMember, array(
                            "project_id"                => $projectId,
                            "project_employee_group_id" => $projectGroupId,
                            "employee_id"               => $mid,
                            "employee_nama"             => $memNama,
                            "entity_type"               => $defaultEntityType,
                            "proporsi_persen"           => 0,
                            "proporsi_nominal"          => 0,
                            "is_lead"                   => 0,
                            "pajak_default"             => $defaultTax,
                            "no_spk"                    => $noSpk,
                            "status"                    => 1,
                            "trash"                     => 0,
                            "last_update"               => date("Y-m-d H:i:s"),
                            "dtime"                     => date("Y-m-d H:i:s")
                        ));
                    } else {
                        // Pastikan aktif
                        $this->db->where("id", $memRow->id)->update($this->tableMember, array(
                            "status"      => 1,
                            "trash"       => 0,
                            "last_update" => date("Y-m-d H:i:s")
                        ));
                    }
                }

                // Nonaktifkan member pada SPK ini yang tidak termasuk dalam cleanMemberIds
                $this->db->where("project_employee_group_id", $projectGroupId)
                    ->where("no_spk", $noSpk)
                    ->where_not_in("employee_id", $cleanMemberIds)
                    ->update($this->tableMember, array(
                        "status"      => 0,
                        "trash"       => 1,
                        "last_update" => date("Y-m-d H:i:s")
                    ));
            }
        }

        return true;
    }

    /**
     * Format string catatan pelaksana: 1 baris per pelaksana (ID - Nama [Tipe] [Proporsi%])
     */
    public function formatEmployeeCatatan($members)
    {
        $lines = array();
        if (!empty($members)) {
            foreach ($members as $m) {
                $empId = isset($m->employee_id) ? $m->employee_id : "";
                $empNama = isset($m->employee_nama) ? $m->employee_nama : "";
                $entType = isset($m->entity_type) ? (int)$m->entity_type : 11;
                $typeTag = ($entType == 22) ? " [Vendor]" : " [Karyawan]";
                $propPersen = isset($m->proporsi_persen) ? floatval($m->proporsi_persen) : 0;
                $propTag = ($propPersen > 0) ? " (" . number_format($propPersen, 0) . "%)" : "";
                $lines[] = $empId . " - " . $empNama . $typeTag . $propTag;
            }
        }
        return implode("\n", $lines);
    }
}

// END OF COMPLETE REPEATED LOGIC
