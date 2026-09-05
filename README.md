# AI Web Chat Interface

A lightweight, feature-rich web chat interface designed to connect directly with an n8n AI Agent backend. 

This project consists of a clean, responsive frontend (`index.html`) and a Vercel Serverless Function (`api/webhook.js`) that acts as a secure proxy to hide your actual n8n webhook URL from the public browser.

## Features
- **Markdown Support:** Automatically parses Markdown responses from the AI, rendering beautiful tables, bold text, and formatting.
- **Clickable Bullet Points:** Any list item (`<li>`) or bullet point returned by the AI becomes a clickable action that users can tap to auto-reply.
- **Quick Reply Buttons:** The chat supports rendering dynamic "Quick Reply" buttons at the bottom of a message if the backend passes them.
- **Session Management:** Generates a unique, random Session ID on every page load to keep chat memory isolated per user/session.

## Setup & Deployment

### 1. Configure your n8n Webhook URL
To connect the frontend to your specific n8n workflow:
1. Open `api/webhook.js`.
2. Locate the following line:
   ```javascript
   const webhookUrl = 'https://your-n8n-domain.com/webhook/your-webhook-id';
   ```
3. Replace the URL with your actual n8n webhook URL.

### 2. Deploying
Because this project utilizes a serverless function proxy (`/api/webhook`), it is designed to be deployed on platforms like **Vercel** or **Netlify**.
- **Vercel:** Simply import this repository into Vercel. It will automatically detect `index.html` as the static entry point and the `api/` folder as Serverless Functions.

---

## n8n Backend Configuration Guide

To get the most out of this chat interface, you should configure your n8n workflow properly.

### 1. Memory Isolation (Clearing Cache on Refresh)
To ensure the AI forgets previous conversations when a user refreshes the page, you must link the Session ID.
- In your n8n **Window Buffer Memory** node (or equivalent memory node), look for the **Session ID** field.
- Set it to an **Expression**: `{{ $json.body.sessionId }}`
- Now, every page refresh creates a brand new blank slate for the AI.

### 2. Rendering Quick Reply Buttons
You can send structured choices back to the user that render as buttons below the chat bubble.
- In your n8n webhook response node, set it to return **JSON**.
- Format the JSON response like this:
  ```json
  {
    "output": "The Red Bull is priced at 250. Would you like to add this to your order?",
    "quick_replies": [
      "Yes, add Red Bull", 
      "No, thanks", 
      "Show me other drinks"
    ]
  }
  ```
- The frontend will look for the `quick_replies` array and automatically render them as one-time clickable buttons.

### 3. Clickable Menus (Lists)
If you want the AI to output a menu of options without writing JSON, simply prompt the AI to use bullet points (Markdown lists). 
For example, if the AI says:
```markdown
Here is what you can do:
- Make a reservation request
- View the drink menu
- Speak to a human
```
The frontend automatically converts those bullet points into clickable elements that the user can tap to instantly send that exact text back to the AI.
