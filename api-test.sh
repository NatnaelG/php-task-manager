#!/bin/bash

BASE_URL="http://localhost:8080"
HEADER="Content-Type: application/json"

echo "--- 1. Creating a new task (pending) ---"
TASK_CREATE_RESPONSE=$(curl -s -X POST -H "$HEADER" -d '{"title": "Write a report", "description": "Draft the Q4 summary.", "status": "pending"}' "$BASE_URL/tasks")
TASK_ID=$(echo "$TASK_CREATE_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
echo "$TASK_CREATE_RESPONSE"
echo ""

echo "--- 2. Creating another task (completed) ---"
curl -s -X POST -H "$HEADER" -d '{"title": "Send weekly email", "description": "Send the weekly status email to the team.", "status": "completed"}' "$BASE_URL/tasks"
echo ""

echo "--- 3. Retrieving all tasks ---"
curl -s -X GET "$BASE_URL/tasks"
echo ""

echo "--- 4. Retrieving only completed tasks ---"
curl -s -X GET "$BASE_URL/tasks?status=completed"
echo ""

echo "--- 5. Retrieving the task by its ID ($TASK_ID) ---"
curl -s -X GET "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 6. Updating the task with ID ($TASK_ID) to in-progress ---"
curl -s -X PUT -H "$HEADER" -d '{"status": "in-progress"}' "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 7. Deleting the task with ID ($TASK_ID) ---"
curl -s -X DELETE "$BASE_URL/tasks/$TASK_ID"
echo ""

echo "--- 8. Verifying deletion (should show 'Task not found') ---"
curl -s -X GET "$BASE_URL/tasks/$TASK_ID"
echo ""
