# ChatModel auth.md

> Automated AI Agent Authentication & Registration Protocol Specification

Welcome to ChatModel (https://chatmodel.in). This document specifies how autonomous AI agents, multi-agent frameworks, and automated client systems can authenticate, register, and interact with the ChatModel Conversational API.

---

## 1. Discovery & Metadata Endpoints

- **OAuth Protected Resource Metadata (RFC 9728)**: `https://chatmodel.in/.well-known/oauth-protected-resource`
- **OAuth Authorization Server Metadata (RFC 8414)**: `https://chatmodel.in/.well-known/oauth-authorization-server`
- **OpenID Connect Discovery**: `https://chatmodel.in/.well-known/openid-configuration`
- **API Catalog (RFC 9727)**: `https://chatmodel.in/.well-known/api-catalog`
- **OpenAPI 3.0 Specification**: `https://chatmodel.in/api/openapi.json`

---

## 2. Agent Registration & Provisioning

AI agents can register programmatically using the following endpoints:

- **Register Endpoint**: `POST https://chatmodel.in/api/agent/register.php`
- **Claim Endpoint**: `POST https://chatmodel.in/api/agent/claim.php`
- **Revoke Endpoint**: `POST https://chatmodel.in/api/agent/revoke.php`
- **Token Endpoint**: `POST https://chatmodel.in/api/token.php`

### Supported Identity Types:
1. `identity_assertion`:
   - `urn:ietf:params:oauth:token-type:id-jag` (Identity-JWT Assertion Grant)
   - `verified_email`
2. `anonymous`:
   - Ephemeral guest agent access for public conversational endpoints.

### Supported Credential Types:
- `bearer_token` (JWT / Bearer Token)
- `api_key`

---

## 3. Authenticated API Usage

Once an access token or API key is obtained, include it in the `Authorization` header on all API requests:

```http
POST /api/chat.php HTTP/1.1
Host: chatmodel.in
Authorization: Bearer <your_access_token>
Content-Type: application/json

{
  "message": "Hello! What services does ChatModel provide?",
  "sessionId": "agent_session_01"
}
```

---

## 4. Scopes & Permissions

| Scope | Description |
| :--- | :--- |
| `chat` | Send conversational prompts and receive streaming / JSON responses |
| `tenants:read` | Read tenant workspace metadata and assistant configurations |
| `tenants:write` | Update tenant settings and automation webhooks |

---

## 5. Security & Revocation

Tokens can be revoked immediately via `POST https://chatmodel.in/api/agent/revoke.php` or through the administrator dashboard.
For questions, contact `support@chatmodel.in`.
