#!/bin/bash

BASE_URL="http://localhost:8080"
HEADER="Content-Type: application/json"

echo "--- Waiting for the services to be ready... ---"
# Give the containers some time to start up
sleep 5

# Find the container ID of the running app
CONTAINER_ID=$(docker compose ps -q app)

if [ -z "$CONTAINER_ID" ]; then
    echo "Error: Docker container not found. Is it running? Please run 'docker compose up -d' first."
    exit 1
fi

echo "--- Initializing database table ---"
# Use a more robust way to ensure the directory exists and the table is created
docker exec "$CONTAINER_ID" mkdir -p /var/www/html/database
# Change the ownership of the database directory to the www-data user
docker exec "$CONTAINER_ID" chown -R www-data:www-data /var/www/html/database
docker exec "$CONTAINER_ID" sqlite3 /var/www/html/database/tasks.sqlite "
CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
"

echo "--- 1. Creating a new task (pending) ---"
TASK_CREATE_RESPONSE=$(curl -X POST -H "$HEADER" -d '{"title": "Write a report", "description": "Draft the Q4 summary.", "status": "pending"}' "$BASE_URL/tasks")
TASK_ID=$(echo "$TASK_CREATE_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
echo "$TASK_CREATE_RESPONSE"
echo ""

echo "--- 2. Creating another task (completed) ---"
curl -X POST -H "$HEADER" -d '{"title": "Send weekly email", "description": "Send the weekly status email to the team.", "status": "completed"}' "$BASE_URL/tasks"
echo ""

echo "--- 3. Retrieving all tasks ---"
curl -X GET "$BASE_URL/tasks"
echo ""

echo "--- 4. Retrieving only completed tasks ---"
curl -X GET "$BASE_URL/tasks?status=completed"
echo ""

echo "--- 5. Retrieving the task by its ID ($TASK_ID) ---"
curl -X GET "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 6. Updating the task with ID ($TASK_ID) to in-progress ---"
curl -X PUT -H "$HEADER" -d '{"status": "in-progress"}' "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 7. Deleting the task with ID ($TASK_ID) ---"
curl -X DELETE "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 8. Verifying deletion (should show 'Task not found') ---"
curl -X GET "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 9. Verifying database contents directly ---"
# Find the container ID of the running app
CONTAINER_ID=$(docker compose ps -q app)

if [ -z "$CONTAINER_ID" ]; then
    echo "Error: Docker container not found. Is it running?"
else
    # Execute sqlite3 inside the container to query the tasks table
    docker exec "$CONTAINER_ID" sqlite3 /var/www/html/database/tasks.sqlite "SELECT * FROM tasks;"
fi
