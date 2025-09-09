#!/bin/bash

# Laravel GCS Storage API - cURL Examples
# Comprehensive examples for testing all API endpoints

BASE_URL="http://localhost:8000/api"
echo "=== Laravel GCS Storage API - cURL Examples ==="
echo "Base URL: $BASE_URL"
echo ""

# User credentials
EMAIL="test@example.com"
PASSWORD="password123"
NAME="Test User"

echo "1. Health Check"
echo "curl -X GET '$BASE_URL/health'"
curl -X GET "$BASE_URL/health"
echo -e "\n\n"

echo "2. User Registration"
echo "curl -X POST '$BASE_URL/auth/register' \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{\"name\":\"$NAME\", \"email\":\"$EMAIL\", \"password\":\"$PASSWORD\", \"password_confirmation\":\"$PASSWORD\"}'"

REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"$NAME\", \"email\":\"$EMAIL\", \"password\":\"$PASSWORD\", \"password_confirmation\":\"$PASSWORD\"}")

echo "$REGISTER_RESPONSE"
echo -e "\n"

# Extract token
TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Registration failed, trying login..."
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
      -H "Content-Type: application/json" \
      -d "{\"email\":\"$EMAIL\", \"password\":\"$PASSWORD\"}")
    
    echo "$LOGIN_RESPONSE"
    TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
fi

echo -e "\n3. Get Current User"
echo "curl -X GET '$BASE_URL/auth/user' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
curl -X GET "$BASE_URL/auth/user" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n\n"

echo "4. List Public Files (Empty initially)"
echo "curl -X GET '$BASE_URL/public/files'"
curl -X GET "$BASE_URL/public/files"
echo -e "\n\n"

echo "5. Get Public Stats"
echo "curl -X GET '$BASE_URL/public/stats'"
curl -X GET "$BASE_URL/public/stats"
echo -e "\n\n"

echo "6. List User Files (Empty initially)"
echo "curl -X GET '$BASE_URL/internal/files' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
curl -X GET "$BASE_URL/internal/files" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n\n"

echo "7. Get User File Stats"
echo "curl -X GET '$BASE_URL/internal/files/stats' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
curl -X GET "$BASE_URL/internal/files/stats" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n\n"

echo "8. File Upload Example (requires actual file)"
echo "# Create a sample file first"
echo "echo 'Sample file content' > /tmp/sample.txt"
echo ""
echo "curl -X POST '$BASE_URL/internal/files' \\"
echo "  -H 'Authorization: Bearer \$TOKEN' \\"
echo "  -F 'file=@/tmp/sample.txt' \\"
echo "  -F 'description=Sample text file'"
echo ""
echo "# Note: This will fail without actual GCS configuration"
echo -e "\n"

echo "9. Search Files Example"
echo "curl -X GET '$BASE_URL/public/files?search=sample'"
curl -X GET "$BASE_URL/public/files?search=sample"
echo -e "\n\n"

echo "10. Logout"
echo "curl -X POST '$BASE_URL/auth/logout' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
curl -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n\n"

echo "=== Advanced Examples ==="
echo ""

echo "11. File Update Example (after upload)"
echo "curl -X PUT '$BASE_URL/internal/files/1' \\"
echo "  -H 'Authorization: Bearer \$TOKEN' \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{\"description\":\"Updated description\", \"is_published\":true}'"
echo ""

echo "12. Toggle Publication Status"
echo "curl -X POST '$BASE_URL/internal/files/1/toggle-publication' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
echo ""

echo "13. Download File"
echo "curl -X GET '$BASE_URL/internal/files/1/download' \\"
echo "  -H 'Authorization: Bearer \$TOKEN' \\"
echo "  -o downloaded_file.ext"
echo ""

echo "14. Delete File"
echo "curl -X DELETE '$BASE_URL/internal/files/1' \\"
echo "  -H 'Authorization: Bearer \$TOKEN'"
echo ""

echo "=== Error Handling Examples ==="
echo ""

echo "15. Unauthorized Access"
echo "curl -X GET '$BASE_URL/internal/files'"
curl -X GET "$BASE_URL/internal/files"
echo -e "\n\n"

echo "16. Invalid File Access"
echo "curl -X GET '$BASE_URL/public/files/999'"
curl -X GET "$BASE_URL/public/files/999"
echo -e "\n\n"

echo "17. Invalid Login"
echo "curl -X POST '$BASE_URL/auth/login' \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{\"email\":\"wrong@email.com\", \"password\":\"wrongpass\"}'"
curl -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"wrong@email.com", "password":"wrongpass"}'
echo -e "\n\n"

echo "=== Examples Complete ==="