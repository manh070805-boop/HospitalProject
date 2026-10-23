<?php
class RoleModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function createRole($data)
    {
        $sql = "insert into roles (role_name,description) values (:role_name, :description)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_name", $data['role_name']);
        $stmt->bindParam(":description", $data['description']);
        return $stmt->execute();
    }
    public function getRoleByID($id)
    {
        $sql = "select * from roles where role_id = :role_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateRole($id, $data)
    {
        $sql = "update roles set role_name = :role_name, description = :description where role_id = :role_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_name", $data['role_name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":role_id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function deleteRole($id)
    {
        $sql = "delete from roles where role_id = :role_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function findByRoleName($roleName)
    {
        $sql = "select * from roles where role_name = :role_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_name", $roleName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function findByRoleNameExceptID($roleName, $role_id)
    {
        $sql = "select * from roles where role_name = :role_name and role_id != :role_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":role_name", $roleName);
        $stmt->bindParam(":role_id", $role_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function countRoles($keyword = '')
    {
        $sql = "select count(*) as total from roles";
        $condition = [];
        $params = [];
        if (!empty($keyword)) {
            $condition[] = "(role_name like :kw1 or description like :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }
        if (!empty($condition)) {
            $sql .= " where " . implode(" and ", $condition);
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    public function getRolesByPage($keyword = '', $limit = 5, $offset = 0)
    {
        $sql = "select * from roles";
        $conditions = [];
        $params = [];
        if (!empty($keyword)) {
            $conditions[] = "(role_name like :kw1 or description like :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }
        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }
        $sql .= " order by role_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
