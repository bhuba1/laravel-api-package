#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"

echo "Smoke testing auth-profile integration at ${BASE_URL}"

login_response="$(curl -sf -X POST "${BASE_URL}/api/auth-profile/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}')"

package_token="$(php -r 'echo json_decode(file_get_contents("php://stdin"), true)["token"] ?? "";' <<<"${login_response}")"

if [ -z "${package_token}" ]; then
  echo "Failed to obtain package token via login"
  echo "${login_response}"
  exit 1
fi

echo "Package token acquired via login."

profile_response="$(curl -sf "${BASE_URL}/api/auth-profile/profile" \
  -H "Authorization: Bearer ${package_token}" \
  -H "Accept: application/json")"

echo "Profile: ${profile_response}"

host_me_response="$(curl -sf "${BASE_URL}/api/me" \
  -H "Authorization: Bearer ${package_token}" \
  -H "Accept: application/json")"

echo "Host /api/me: ${host_me_response}"

refresh_response="$(curl -sf -X POST "${BASE_URL}/api/auth-profile/tokens/refresh" \
  -H "Authorization: Bearer ${package_token}" \
  -H "Accept: application/json")"

new_package_token="$(php -r 'echo json_decode(file_get_contents("php://stdin"), true)["token"] ?? "";' <<<"${refresh_response}")"

if [ -z "${new_package_token}" ]; then
  echo "Failed to refresh package token"
  echo "${refresh_response}"
  exit 1
fi

curl -sf "${BASE_URL}/api/auth-profile/profile" \
  -H "Authorization: Bearer ${new_package_token}" \
  -H "Accept: application/json" >/dev/null

old_token_status="$(curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}/api/auth-profile/profile" \
  -H "Authorization: Bearer ${package_token}" \
  -H "Accept: application/json")"

if [ "${old_token_status}" != "401" ]; then
  echo "Expected revoked package token to return 401, got ${old_token_status}"
  exit 1
fi

register_response="$(curl -sf -X POST "${BASE_URL}/api/auth-profile/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Smoke Test User","email":"smoke@example.com","password":"password123"}')"

register_token="$(php -r 'echo json_decode(file_get_contents("php://stdin"), true)["token"] ?? "";' <<<"${register_response}")"

if [ -z "${register_token}" ]; then
  echo "Failed to register and obtain package token"
  echo "${register_response}"
  exit 1
fi

curl -sf "${BASE_URL}/api/auth-profile/profile" \
  -H "Authorization: Bearer ${register_token}" \
  -H "Accept: application/json" >/dev/null

revoke_status="$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/api/auth-profile/tokens/revoke" \
  -H "Authorization: Bearer ${register_token}" \
  -H "Accept: application/json")"

if [ "${revoke_status}" != "200" ]; then
  echo "Expected revoke to return 200, got ${revoke_status}"
  exit 1
fi

revoked_token_status="$(curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}/api/auth-profile/profile" \
  -H "Authorization: Bearer ${register_token}" \
  -H "Accept: application/json")"

if [ "${revoked_token_status}" != "401" ]; then
  echo "Expected revoked register token to return 401, got ${revoked_token_status}"
  exit 1
fi

echo "Smoke test passed."
