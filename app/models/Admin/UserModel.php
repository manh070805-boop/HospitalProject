<?php

class UserModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../../config/database.php';
    }
    public function createUser($data)
    {
        $sql = "
            insert into users (full_name, username, password_hash, role_id, department_id, status)
            values (:full_name, :username, :password_hash, :role_id, :department_id, :status)
        ";

        $stmt = $this->conn->prepare($sql);

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':role_id', $data['role_id']);
        $stmt->bindParam(':department_id', $data['department_id']);
        $stmt->bindParam(':status', $data['status']);

        return $stmt->execute();
    }
    public function getUserByID($id)
    {
        $sql = "select * from users where user_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateUser($id, $data)
    {
        if (!empty($data['password'])) {
            $sql = "update users set full_name = :full_name,
                username = :username,
                password_hash = :password_hash,
                role_id = :role_id,
                department_id = :department_id,
                status = :status
                where user_id = :id";
            $stmt = $this->conn->prepare($sql);

            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':role_id', $data['role_id'], PDO::PARAM_INT);
            $stmt->bindParam(':department_id', $data['department_id'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            $sql = 'update users set full_name = :full_name,
            username = :username,
            role_id = :role_id,
            department_id = :department_id,
            status = :status
            where user_id = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':role_id', $data['role_id'], PDO::PARAM_INT);
            $stmt->bindParam(':department_id', $data['department_id'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
    public function lockUser(int $id): bool
    {
        $sql = "update users set status = 'locked' where user_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function getAllRoles()
    {
        $sql = "select role_id, role_name from roles order by role_name asc";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDepartments()
    {
        $sql = "select department_id, department_name from departments order by department_name asc";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findByUsername($username)
    {
        $sql = "select * from users where username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function findByUsernameExceptID($username, $userID)
    {
        $sql = "select * from users where username = :username and user_id != :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function countUsers($keyword = '', $status = '', $department_id = '')
    {
        $sql = 'select count(*) as total from users u';
        $conditions = [];
        $params = [];

        if (!empty($department_id)) {
            $conditions[] = "u.department_id = :dept_id";
            $params[':dept_id'] = $department_id;
        }

        if (!empty($keyword)) {
            $conditions[] = "(u.full_name like :kw1 or u.username like :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }

        if (!empty($status)) {
            $conditions[] = "u.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getUsersByPage($keyword = '', $status = '', $department_id = '', $limit = 5, $offset = 0)
    {
        $sql = "select u.*, r.role_name, d.department_name from users u
                left join roles r on u.role_id = r.role_id
                left join departments d on u.department_id = d.department_id";
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(u.full_name like :kw1 or u.username like :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }

        if (!empty($department_id)) {
            $conditions[] = "u.department_id = :dept_id";
            $params[':dept_id'] = $department_id;
        }

        if (!empty($status)) {
            $conditions[] = "u.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $sql .= " order by u.user_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUserForAuthByUsername($username)
    {
        $sql = "select u.*, r.role_name, d.department_name
                from users u
                left join roles r on u.role_id = r.role_id
                left join departments d on u.department_id = d.department_id
                where u.username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

}

?>