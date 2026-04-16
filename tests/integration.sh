#!/bin/bash

# Our application's address in Docker
BASE_URL="http://localhost:8080"

echo "=== GameNest Integration Tests ==="

# TEST 1: Check if the home page is working (Expected code 200)
echo "[Test 1] Pinging Store Homepage..."
HTTP_STATUS=$(curl -o /dev/null -s -w "%{http_code}\n" $BASE_URL/)

if [ "$HTTP_STATUS" -eq 200 ]; then
    echo "Success: Homepage is online (200 OK)."
else
    echo "Failed: Homepage returned $HTTP_STATUS."
fi

# TEST 2: Check access to the admin panel without logging in (Expected code 403 or redirect)
echo "[Test 2] Accessing Admin Panel without login..."
ADMIN_STATUS=$(curl -o /dev/null -s -w "%{http_code}\n" $BASE_URL/admin)

if [ "$ADMIN_STATUS" -eq 302 ]; then
    echo "Success: Admin panel protected. Unauthenticated user redirected to login (302)."
else
    echo "Failed: Expected 302 Redirect, got $ADMIN_STATUS."
fi

echo "=== Tests Completed ==="