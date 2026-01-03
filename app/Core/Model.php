<?php

namespace App\Core;

abstract class Model {
    protected $db;
    protected $table;
    protected static $tenantId = null;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function setTenantId($id) {
        self::$tenantId = $id;
    }

    public static function getTenantId() {
        return self::$tenantId;
    }

    public function findAll() {
        $sql = "SELECT * FROM {$this->table}";
        if (self::$tenantId && $this->isTenantScoped()) {
            $sql .= " WHERE tenant_id = :tenant_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tenant_id' => self::$tenantId]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        if (self::$tenantId && $this->isTenantScoped()) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = ['id' => $id];
        if (self::$tenantId && $this->isTenantScoped()) {
            $params['tenant_id'] = self::$tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Override in child classes if table does not have tenant_id
    protected function isTenantScoped() {
        return true;
    }
}
