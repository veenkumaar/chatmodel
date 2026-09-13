---
name: chatmodel-assistant
description: Interact with ChatModel AI conversational assistants, trigger automated business workflows, and query enterprise workspaces.
version: 1.0.0
---

# ChatModel Assistant Skill

Use this skill to interact with ChatModel (https://chatmodel.in) conversational AI assistants and automated workflow webhooks.

## Capabilities

- **Conversational Chat**: Query assistant via streaming SSE or standard JSON messages.
- **Workflow Automation**: Trigger actions via n8n / Make webhooks connected to tenant workspaces.
- **Workspace Discovery**: Identify tenant subdomains and authentication modes.

## Endpoints

- **Chat API**: `POST https://chatmodel.in/api/chat.php`
- **MCP Server**: `https://chatmodel.in/api/mcp.php`
- **OpenAPI 3.0**: `https://chatmodel.in/api/openapi.json`
- **Auth.md**: `https://chatmodel.in/auth.md`

## Example Request

```http
POST https://chatmodel.in/api/chat.php HTTP/1.1
Content-Type: application/json

{
  "message": "What plans and features does ChatModel offer?",
  "subdomain": "demo",
  "sessionId": "agent_session_123"
}
```
