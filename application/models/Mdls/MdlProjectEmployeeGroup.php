<?php

class MdlProjectEmployeeGroup extends MdlMother
{
    protected $tableName = "dta_project_employee_group";
    protected $indexFields = "id";

    protected $listedFieldsForm = array();
    protected $listedFieldsHidden = array();
    protected $search;
    protected $filters = array(
        "status='1'",
        "trash='0'",
    );

    protected $validationRules = array(
        "nama"       => array("required", "singleOnly"),
        "group_type" => array("required"),
    );

    protected $listedFieldsView = array("nama", "anggota_group");
    protected $fields = array(
        "id" => array(
            "label"     => "id",
            "type"      => "int",
            "length"    => "24",
            "kolom"     => "id",
            "inputType" => "hidden",
        ),
        "nama" => array(
            "label"     => "nama group",
            "type"      => "varchar",
            "length"    => "255",
            "kolom"     => "nama",
            "inputType" => "text",
        ),
        "group_type" => array(
            "label"        => "tipe group",
            "type"         => "varchar",
            "length"       => "20",
            "kolom"        => "group_type",
            "inputType"    => "combo",
            "dataSource"   => array(
                "external" => "External (Vendor / Freelance)",
                "internal" => "Internal (Karyawan - Nonaktif)"
            ),
            "defaultValue" => "external",
        ),
        "anggota_group" => array(
            "label"     => "anggota group",
            "type"      => "text",
            "kolom"     => "id",
            "inputType" => "hidden",
        ),
        "keterangan" => array(
            "label"     => "keterangan",
            "type"      => "text",
            "length"    => "255",
            "kolom"     => "keterangan",
            "inputType" => "text",
        ),
        "cabang_id" => array(
            "label"        => "cabang",
            "type"         => "int",
            "length"       => "11",
            "kolom"        => "cabang_id",
            "inputType"    => "hidden",
            "defaultValue" => 0,
        ),
        "status" => array(
            "label"        => "status",
            "type"         => "int",
            "length"       => "1",
            "kolom"        => "status",
            "inputType"    => "combo",
            "dataSource"   => array(0 => "inactive", 1 => "active"),
            "defaultValue" => 1,
        ),
        "last_update" => array(
            "label"     => "last update",
            "type"      => "datetime",
            "kolom"     => "last_update",
            "inputType" => "hidden",
        ),
    );

    protected $listedFields = array(
        "nama"          => "NAMA GROUP",
        "anggota_group" => "ANGGOTA GROUP",
        "keterangan"    => "KETERANGAN",
    );

    public function __construct()
    {
        parent::__construct();
    }

    //region gs
    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getIndexFields()
    {
        return $this->indexFields;
    }

    public function setIndexFields($indexFields)
    {
        $this->indexFields = $indexFields;
    }

    public function getListedFieldsForm()
    {
        return $this->listedFieldsForm;
    }

    public function setListedFieldsForm($listedFieldsForm)
    {
        $this->listedFieldsForm = $listedFieldsForm;
    }

    public function getListedFieldsHidden()
    {
        return $this->listedFieldsHidden;
    }

    public function setListedFieldsHidden($listedFieldsHidden)
    {
        $this->listedFieldsHidden = $listedFieldsHidden;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getValidationRules()
    {
        return $this->validationRules;
    }

    public function setValidationRules($validationRules)
    {
        $this->validationRules = $validationRules;
    }

    public function getListedFieldsView()
    {
        return $this->listedFieldsView;
    }

    public function setListedFieldsView($listedFieldsView)
    {
        $this->listedFieldsView = $listedFieldsView;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getListedFields()
    {
        return $this->listedFields;
    }

    public function setListedFields($listedFields)
    {
        $this->listedFields = $listedFields;
    }
    //endregion
}
