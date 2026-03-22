#!/bin/bash
php artisan serve --port=8083 > test_server.log 2>&1 &
SERVER_PID=$!
sleep 2

echo "--- API TEST START ---"

echo -e "\n1. POST /api/auth/lookup (Testing Email Routing Context)"
curl -s -X POST http://127.0.0.1:8083/api/auth/lookup -H "Content-Type: application/json" -H "Accept: application/json" -d '{"email": "admin@dashboard.com"}' | php -r 'echo json_encode(json_decode(file_get_contents("php://stdin")), JSON_PRETTY_PRINT)."\n";'

echo -e "\n2. POST /api/schools/register (Getting Token for Admin)"
RES=$(curl -s -X POST http://127.0.0.1:8083/api/schools/register -H "Content-Type: application/json" -H "Accept: application/json" -d '{"school_name": "API Test", "school_slug": "api-test", "admin_name": "Tester", "email": "tester@api.com", "password": "password"}')
TOKEN=$(echo $RES | php -r 'echo json_decode(file_get_contents("php://stdin"))->token ?? "";')
echo "Token Acquired. Running protected routes..."

if [ -n "$TOKEN" ]; then
    echo -e "\n3. GET /api/me (Testing User Payload and School Relation)"
    curl -s -X GET http://127.0.0.1:8083/api/me -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" | php -r 'echo json_encode(json_decode(file_get_contents("php://stdin")), JSON_PRETTY_PRINT)."\n";'

    echo -e "\n4. POST /api/me/profile (Testing Name Update logic)"
    curl -s -X POST http://127.0.0.1:8083/api/me/profile -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name": "Awesome Tester"}' | php -r 'echo json_encode(json_decode(file_get_contents("php://stdin")), JSON_PRETTY_PRINT)."\n";'

    echo -e "\n5. GET /api/dashboard (Testing Dashboard Endpoint)"
    curl -s -X GET http://127.0.0.1:8083/api/dashboard -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" | php -r 'echo json_encode(json_decode(file_get_contents("php://stdin")), JSON_PRETTY_PRINT)."\n";'
else
    echo "Failed to register/get token. Response: $RES"
fi

echo -e "\n--- API TEST END ---"
kill $SERVER_PID
