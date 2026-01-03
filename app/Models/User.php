<?php

namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getRole($userId) {
        // Since roles are linked table, we might need a join or separate query.
        // UserRole model? Or just helper here.
        $sql = "
            SELECT r.name
            FROM roles r
            JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function isOnboardingComplete($userId) {
        $user = $this->find($userId);
        return $user ? (bool)$user['is_onboarding_complete'] : false;
    }

    public function completeOnboarding($userId) {
        // Scoped update. 'find' is scoped, but for update we should be careful.
        // If we use parent::find($id) it respects tenant scope if set.
        // However, updating a specific user by ID usually implies we own it.
        // Let's rely on standard SQL update with tenant check if needed,
        // but $userId is usually from session which is trusted.

        $sql = "UPDATE {$this->table} SET is_onboarding_complete = 1 WHERE id = :id";
        if (self::$tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = ['id' => $userId];
        if (self::$tenantId) {
            $params['tenant_id'] = self::$tenantId;
        }
        return $stmt->execute($params);
    }
}
