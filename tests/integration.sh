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

# TEST 3: Check global 404 Error handler (Expected code 404)
echo "[Test 3] Requesting non-existent page to test 404 handler..."
NOT_FOUND_STATUS=$(curl -o /dev/null -s -w "%{http_code}\n" $BASE_URL/this-page-does-not-exist)

if [ "$NOT_FOUND_STATUS" -eq 404 ]; then
    echo "Success: Application correctly catches unknown routes (404 Not Found)."
else
    echo "Failed: Expected 404, got $NOT_FOUND_STATUS."
fi

# TEST 4: Check Login page availability (Expected code 200)
echo "[Test 4] Accessing Login page..."
LOGIN_STATUS=$(curl -o /dev/null -s -w "%{http_code}\n" $BASE_URL/login)

if [ "$LOGIN_STATUS" -eq 200 ]; then
    echo "Success: Login page is accessible (200 OK)."
else
    echo "Failed: Expected 200, got $LOGIN_STATUS."
fi

echo "=== Tests Completed ==="