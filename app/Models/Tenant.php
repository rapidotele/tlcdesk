<?php

namespace App\Models;

use App\Core\Model;

class Tenant extends Model {
    protected $table = 'tenants';

    // Tenants table usually doesn't have a tenant_id column itself (it IS the tenant definition).
    // So we disable tenant scoping.
    protected function isTenantScoped() {
        return false;
    }

    public function create($data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
}
