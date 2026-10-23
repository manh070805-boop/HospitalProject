<?php

class DepartmentModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function createDepartment($data)
    {
        $sql = "insert into departments (department_name, description)
        values (:department_name, :description)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_name', $data['department_name']);
        $stmt->bindParam(':description', $data['description']);
        return $stmt->execute();
    }
    public function getDepartmentByID($id)
    {
        $sql = "select * from departments where department_id = :department_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateDepartment($id, $data)
    {
        $sql = "update departments set department_name = :department_name, description = :description where department_id = :department_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_name', $data['department_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':department_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function deleteDepartment($id)
    {
        $sql = "delete from departments where department_id = :department_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function findByDepartmentName($department_name)
    {
        $sql = "select * from departments where department_name = :department_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_name', $department_name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function findByDepartmentNameExceptID($departmentName, $department_id)
    {
        $sql = "select * from departments where department_name = :department_name and department_id != :department_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':department_name', $departmentName);
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function countDepartments($keyword = '')
    {
        $sql = "select count(*) as total from departments";
        $condition = [];
        $params = [];
        if (!empty($keyword)) {
            $condition[] = "(department_name like :kw1 or description like :kw2)";
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
    public function getDepartmentsByPage($keyword = '', $limit = 5, $offset = 0)
    {
        $sql = "select * from departments";
        $conditions = [];
        $params = [];
        if (!empty($keyword)) {
            $conditions[] = "(department_name like :kw1 or description like :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }
        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }
        $sql .= " order by department_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDepartments()
    {
        $sql = "select department_id, department_name from departments order by department_name asc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>

