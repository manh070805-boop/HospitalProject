<?php
class SystemLogModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../../config/database.php';
    }

    public function log($user_id, $action_type, $module, $description)
    {

        $sql = "insert into system_activity_logs (user_id, action, description, created_at)
                values (:user_id, :action, :description, GETDATE())";

        $stmt = $this->conn->prepare($sql);
        if (!empty($module)) {
            $description = "[$module] $description";
        }
        if ($user_id === null) {
            $stmt->bindValue(":user_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        }

        $stmt->bindParam(":action", $action_type);
        $stmt->bindParam(":description", $description);

        return $stmt->execute();
    }

    public function getLogsByPage($keyword = '', $action = '', $limit = 10, $offset = 0)
    {
        $sql = "select l.*, u.full_name, u.username
                from system_activity_logs l
                left join users u on l.user_id = u.user_id";

        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(l.description like :keyword or u.full_name like :keyword or u.username like :keyword)";
            $params[':keyword'] = "%$keyword%";
        }

        if (!empty($action)) {
            $conditions[] = "l.action = :action";
            $params[':action'] = $action;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $sql .= " order by l.log_id desc offset :offset rows fetch next :limit rows only";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countLogs($keyword = '', $action = '')
    {
        $sql = "select count(*) as total from system_activity_logs l
                left join users u on l.user_id = u.user_id";

        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(l.description like :keyword or u.full_name like :keyword or u.username like :keyword)";
            $params[':keyword'] = "%$keyword%";
        }

        if (!empty($action)) {
            $conditions[] = "l.action = :action";
            $params[':action'] = $action;
        }

        if (!empty($conditions)) {
            $sql .= " where " . implode(" and ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getLogById($id)
    {
        $sql = "select l.*, u.full_name, u.username from system_activity_logs l
                left join users u on l.user_id = u.user_id
                where l.log_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countLogsByAction()
    {
        $sql = "select action, count(*) as total from system_activity_logs group by action";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = [];
        foreach ($results as $row) {
            $counts[strtoupper($row['action'])] = (int) $row['total'];
        }
        return $counts;
    }

    public function getRecentLogs($limit = 5)
    {
        $sql = "select top " . (int) $limit . " l.*, u.full_name, u.username from system_activity_logs l
                left join users u on l.user_id = u.user_id
                order by l.log_id desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>