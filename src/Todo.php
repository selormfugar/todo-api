<?php

namespace TodoApi;

class Todo{
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
        
        }

        //Create 
   public function create(array $data): int {
        $this->validateTask($data, true);
        $sql = "INSERT INTO
         tasks (`title`, `description`, `priority`, `due_date`, `status`)
         VALUES 
         (:title, :description, :priority, :due_date, :status)";
       
       $this->db->query($sql, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'] ?? 'pending'
        ]);

        $stmt = $this->db->query("SELECT LAST_INSERT_ID()");
        return (int)$stmt->fetchColumn();
    }

    // Read All Filters
    public function getAll(array $filters = []): array 
    {
        $allowedFilters = [
    'status',
    'priority',
    'search',
    'sort',
    'order',
    'limit',
    'page',
    'due_date'
];

foreach (array_keys($filters) as $key) {
    if (!in_array($key, $allowedFilters, true)) {
        throw new \InvalidArgumentException(
            "Unsupported query parameter: $key"
        );
    }
}
        $sql = "SELECT * FROM tasks where 1=1";
        $params = [];

      
        if (isset($filters['status'])) {
             $allowedstatus = ['pending', 'in_progress', 'done'];
        if (!in_array($filters['status'] ?? null, $allowedstatus, true)) {

            throw new \InvalidArgumentException("Invalid status filter. Allowed values are: pending, in_progress, done");
        } 
        
        $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['priority'])) {


        $allowedPriority = ['low', 'medium', 'high'];
        if (!in_array($filters['priority'] ?? null, $allowedPriority, true)) {
            throw new \InvalidArgumentException("Invalid priority filter. Allowed values are: low, medium, high");
        } 
        
        $sql .= " AND priority = :priority";
            $params['priority'] = $filters['priority'];
        }

       
        if (isset($filters['due_date'])) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $filters['due_date']);
            if (!$date || $date->format('Y-m-d') !== $filters['due_date']) {
                throw new \InvalidArgumentException("Invalid due_date format. Expected format: YYYY-MM-DD");
            }
            $sql .= " AND due_date >=:dateStart AND due_date <=:dateEnd";
            $params['dateStart'] = $date->format('Y-m-d');
            $params['dateEnd'] = $date->modify('+1 day')->format('Y-m-d 00:00:00');
        }


        if (isset($filters['search'])) {
            $sql .= " AND (title LIKE :searchTitle 
            OR description LIKE :searchDescription)";

            $search = '%' . $filters['search'] . '%';
            $params['searchTitle'] = $search;
            $params['searchDescription'] = $search;
        }

            $allowedSort = [
            'id', 'title', 'priority', 
            'due_date', 'status','created_at'
            ];

            $sort = $filters['sort'] ?? 'created_at';

            if (!in_array($sort, $allowedSort, true)) {
                throw new \InvalidArgumentException("Invalid sort field");
            }

            $order = strtoupper($filters['order'] ?? 'Desc');

            if (!in_array($order, ['ASC', 'DESC'], true)) {
                throw new \InvalidArgumentException("Invalid order value");
            }

            $sql .= " ORDER BY $sort $order, id DESC";

            $limit = filter_var(
                $filters['limit'] ?? 10,
                 FILTER_VALIDATE_INT);

            $page = filter_var(
                $filters['page'] ?? 1,
                 FILTER_VALIDATE_INT);

            if ($limit === false || $limit < 1 || $limit > 100) {
                throw new \InvalidArgumentException("Invalid limit value");
            }

            if ($page === false || $page < 1) {
                throw new \InvalidArgumentException("Invalid page value");
            }

            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";

           
        return $this->db->query($sql, $params)->fetchAll();
    }

    //Read one task by id
    public function getById(int $id): array|false 
    {
        $sql = "SELECT * FROM tasks WHERE id = :id";

        return $this->db->query($sql, [
            'id' => $id
        ])->fetch();
        
    }

    // Update a task by id
    public function update(int $id, array $data): bool 
    {
        $allowedFields = [
            'title',
            'description', 
            'priority', 
            'due_date',
            'status'
            ];

        $fields = [];
        $params = ['id' => $id];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException(
                "No valid fields to update");
        }
     $this->validateTask($data, false);

        $sql = "UPDATE tasks SET "
         . implode(', ', $fields) 
         . " WHERE id = :id";
         $stmt = $this->db->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    // Delete a task by id
    public function delete(int $id): bool {
        $sql = "DELETE FROM tasks WHERE id = :id";

        $stmt = $this->db->query($sql, [
            'id' => $id
            ]);
        return (bool)$stmt->rowCount();
    }

    // Validate task data
    private function validateTask(
        array $data, 
        bool $creating ): void {
            if ($creating && !isset($data['title'])) {
                
                    throw new \InvalidArgumentException(
                        "Title is required"
                        );
                
            }

        if (array_key_exists('title', $data)) {
            if (!is_string($data['title']) 
                || trim($data['title']) === ''
                || strlen($data['title']) > 255)
                {
                throw new \InvalidArgumentException(
                    "Title must contain 1 to 255 characters"
                 );
                
            }
        }
        if (array_key_exists('description', $data)
            && $data['description'] !== null
            && !is_string($data['description']))            
            {
            throw new \InvalidArgumentException(
                    "Description must be a string"
                );
            }

        if (isset($data['priority'])
            && !in_array($data['priority'], ['low', 'medium', 'high'],
             true)) 
            {
                throw new \InvalidArgumentException(
                    "Priority must be one of: low, medium, high"
                );
            }

        if (isset($data['status'])
            && !in_array(
            $data['status'],
             ['pending', 'in_progress', 'done'],
             true)) 
            {
                throw new \InvalidArgumentException(
                    "Status must be either 'pending', 'in_progress', or 'done'"
                );
            }

        if (array_key_exists('due_date', $data) ) {
            if ($data['due_date'] !== null) {

                if (!is_string($data['due_date'])) {
                    throw new \InvalidArgumentException(
                        "Due date must be a string in 'YYYY-MM-DD HH:MM:SS' format"
                    );
                }
            
            $date = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d H:i:s', 
                    $data['due_date']
                );           

                
               
                if (!$date || 
                    $date->format('Y-m-d H:i:s') !== $data['due_date'])
                    {
                    throw new \InvalidArgumentException(
                        "Due date must be in 'YYYY-MM-DD HH:MM:SS' format"
                    );
                }
            }
        }

    
}
}

