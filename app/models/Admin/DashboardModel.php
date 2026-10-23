<?php

class DashboardModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function countUsers()
    {
        $sql = "select count(*) as total from users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    public function countUserByStatus($status)
    {
        $sql = "select count(*) as total from users where status = :status";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    public function countRoles()
    {
        $sql = "select count(*) as total from roles";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    public function countDepartments()
    {
        $sql = "select count(*) as total from departments";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    public function getRecentUsers($limit = 8)
    {
        $sql = "select u.user_id,u.username,u.full_name,u.status,u.created_at,r.role_name,d.department_name
        from users u
        left join roles r on u.role_id = r.role_id
        left join departments d on u.department_id = d.department_id
        order by u.created_at desc
        offset 0 rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}

?>