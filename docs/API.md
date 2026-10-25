## Base URL

http://localhost/todo-api/public/api/v1/tasks

All requests can be tested using thunder client extension on vs code locally

## API Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/tasks` | Get all tasks |
| GET | `/tasks/{id}` | Get a task by ID |
| POST | `/tasks` | Create a task |
| PUT | `/tasks/{id}` | Update a task |
| DELETE | `/tasks/{id}` | Delete a task |

## 1. Create a task

**Method:** POST

**Endpoint:** `/tasks`

In Thunder Client, select Body → JSON.

```json
{
  "title": "Complete PHP assignment",
  "description": "Test all API endpoints",
  "priority": "high",
  "due_date": "2026-11-05 15:00:00",
  "status": "pending"
}
```

Successful response — `201 Created`:

```json
{
  "message": "Task Created",
  "task_id": 5
}
```

The task ID will depend on the database.

## 2. Get All Tasks

**Method:** GET

**Endpoint:** `/tasks`

Returns the tasks stored in the database.

Successful response — `200 OK`:

```json
{
  "message": "Tasks retrieved successfully",
  "count": 1,
  "data": [
    {
      "id": 5,
      "title": "Complete PHP assignment",
      "description": "Test all API endpoints",
      "priority": "high",
      "due_date": "2026-11-05 15:00:00",
      "created_at": "2026-10-25 13:03:21",
      "updated_at": "2026-10-25 13:03:21",
      "status": "pending"
    }
  ]
}
```

If no tasks match:

```json
{
  "message": "No tasks found matching the given filters",
  "count": 0,
  "data": []
}
```

## 3. Get Task by ID

**Method:** GET

**Endpoint:** `/tasks/5`

Returns the task with the specified ID.

If the task doesn't exist, the API returns `404 Not Found`.

```json
{
  "error": "Task Not Found"
}
```

## 4. Update Task

**Method:** PUT

**Endpoint:** `/tasks/5`

In Thunder Client, select Body → JSON.

```json
{
  "title": "Updated PHP assignment",
  "priority": "medium",
  "status": "done"
}
```

Successful response — `200 OK`:

```json
{
  "message": "Task Updated"
}
```

Only the fields included in the request are updated.

## 5. Delete Task

**Method:** DELETE

**Endpoint:** `/tasks/5`

Successful response — `200 OK`:

```json
{
  "message": "Task Deleted"
}
```

If the task doesn't exist, the API returns `404 Not Found`.

## Query Parameters

Query parameters can be added using the Query tab in Thunder Client.

| Parameter | Example | Description |
|---|---|---|
| `status` | `pending` | Filter by task status |
| `priority` | `high` | Filter by priority |
| `due_date` | `2026-11-05` | Filter tasks due on a specific date |
| `search` | `PHP` | Search title and description |
| `sort` | `due_date` | Sort by a supported field |
| `order` | `asc` | Ascending or descending |
| `limit` | `10` | Number of tasks per page |
| `page` | `1` | Page number |

### Query Examples

**Filter by status**

```http
GET /tasks?status=pending
```

**Filter by priority**

```http
GET /tasks?priority=high
```

**Filter by due date**

```http
GET /tasks?due_date=2026-11-05
```

**Search tasks**

```http
GET /tasks?search=PHP
```

**Sort by due date**

```http
GET /tasks?sort=due_date&order=asc
```

**Pagination**

```http
GET /tasks?limit=2&page=1
```

**Combined filtering**

```http
GET /tasks?status=pending&priority=high&sort=due_date&order=asc
```

## Validation

The API validates task data and query parameters.

| Field | Accepted values |
|---|---|
| `title` | Required when creating a task; 1–255 characters |
| `description` | String or null |
| `priority` | `low`, `medium`, `high` |
| `status` | `pending`, `in_progress`, `done` |
| `due_date` (POST/PUT) | `YYYY-MM-DD HH:MM:SS` |
| `due_date` (GET filter) | `YYYY-MM-DD` |
| `order` | `asc`, `desc` |
| `limit` | Integer from 1 to 100 |
| `page` | Positive integer |

Example invalid request:

```http
GET /tasks?due_date=tomorrow
```

Response — `400 Bad Request`:

```json
{
  "error": "Invalid due_date format. Expected format: YYYY-MM-DD"
}
```

## HTTP Status Codes

| Status | Meaning |
|---|---|
| 200 | Request successful |
| 201 | Task created successfully |
| 400 | Invalid request or parameter |
| 404 | Task or endpoint not found |
| 405 | HTTP method not allowed |
| 500 | Internal server error |

## Testing with Thunder Client

1. Open Thunder Client in VS Code.
2. Create a collection called `Todo API v1`.
3. Add the GET, POST, PUT and DELETE requests.
4. Use the Query tab to test filtering, searching, sorting and pagination.
5. Use Body → JSON when creating or updating tasks.
6. Check the HTTP status code and JSON response after each request.

The requests can be exported as a Thunder Client collection and shared with the project.

## API Versioning

The API currently uses version 1:

/api/v1/tasks

