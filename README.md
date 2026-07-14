# Webhook Notification Plugin for YOURLS

Sends a JSON POST webhook notification immediately when a new shortened link is successfully created. This is designed to integrate YOURLS with Home Assistant, Discord, Slack, Node-RED, or any custom API.

## Features

- **Automated Webhooks**: Fires a POST request immediately after a new redirect is created.
- **Custom Authorization/Headers**: Supports a simple token authorization value (defaulting to the `Authorization` header) or custom header mappings (e.g. `X-HA-Access: your-token`).
- **Standard Payload**: Contains complete metadata for the created link.

## Screenshots

<details>
<summary>Screenshot(s)</summary>

| Webhook Notification Settings |
| :---: |
| ![Webhook Notification Settings Panel](https://i.imgur.com/tErKdg4.png) |

</details>

## Installation

1. Copy or move the `webhook` directory into your YOURLS `user/plugins/` directory.
2. Go to your YOURLS Administration Panel and navigate to **Plugins**.
3. Locate **Webhook Notification** and click **Activate**.

## Settings & Configuration

Navigate to **Webhook Notification Settings** in the admin sidebar navigation menu to configure the following settings:

| Setting Option | Type | Default Value | Description |
| :--- | :--- | :--- | :--- |
| `Webhook URL` | String | `""` | The destination URL to send HTTP POST requests to (e.g. `https://your-api.com/webhook`). |
| `Authorization / Custom Header` | String | `""` | Optional authorization key. Entering a token defaults to the `Authorization` header. Enter `HeaderName: value` to send custom headers. |

### Payload Format (JSON)

```json
{
  "event": "link_created",
  "keyword": "example",
  "short_url": "https://sho.rt/example",
  "long_url": "https://example.com/some/destination/page",
  "title": "Example Page Title",
  "timestamp": "2026-07-11 22:30:00",
  "ip": "192.168.1.50"
}
```

## Home Assistant Integration Example

To catch these notifications in Home Assistant:
1. Configure an automation in Home Assistant using the `webhook` trigger.
2. In the YOURLS Webhook settings, set the Webhook URL to:
   `https://<your-homeassistant-url>/api/webhook/<your_webhook_id>`
3. Set your access headers if your Home Assistant instance is protected.
4. You can access the payload fields (e.g., `trigger.json.short_url` or `trigger.json.long_url`) directly in your automation actions.

## Authors

- **Bluscream**
- **Antigravity.AI**

## Other Plugins

Check out our other YOURLS plugins:
- [Manage Protocols](../manage-protocols): Add, view, toggle, and delete allowed URL protocols.
- [Prune Inactive Links](../prune-inactive-links): Automatically deletes old links that receive no clicks.
- [Public Shortener Front Page](../public-shortener): A premium, Turnstile-secured public URL shortener.
- [Modern Clicks Log Viewer](../modern-log-viewer): Responsive table of click logs with GeoLite2 geolocation.

## AI Disclaimer

This plugin was created and is maintained with the assistance of Antigravity, an agentic AI coding assistant by Google DeepMind.
