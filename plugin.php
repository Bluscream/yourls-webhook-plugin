<?php
/*
Plugin Name: Webhook Notification
Description: Sends a JSON POST webhook notification when a new shortened link is created. Works with Home Assistant, Discord, Slack, and generic webhooks.
Version: 1.0
Author: Bluscream, Antigravity.AI
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook into new link creation
yourls_add_action( 'insert_link', 'wh_on_insert_link', 10, 6 );

function wh_on_insert_link( $insert, $url, $keyword, $title, $timestamp, $ip ) {
    // If the insert failed, do nothing
    if ( !$insert ) {
        return;
    }

    $settings = yourls_get_option( 'wh_settings', array(
        'webhook_url' => '',
        'auth_header' => '',
    ));

    $webhook_url = trim( $settings['webhook_url'] );
    if ( empty( $webhook_url ) ) {
        return;
    }

    $short_url = yourls_link( $keyword );

    // Prepare JSON payload
    $payload = array(
        'event'       => 'link_created',
        'keyword'     => $keyword,
        'short_url'   => $short_url,
        'long_url'    => $url,
        'title'       => $title,
        'timestamp'   => $timestamp,
        'ip'          => $ip
    );

    $body = json_encode( $payload );

    // Set up headers
    $headers = array(
        'Content-Type' => 'application/json',
    );

    $auth_header = trim( $settings['auth_header'] );
    if ( !empty( $auth_header ) ) {
        // If they entered the full "Header: Value", parse it, otherwise default to Authorization
        if ( strpos( $auth_header, ':' ) !== false ) {
            list( $name, $value ) = explode( ':', $auth_header, 2 );
            $headers[ trim( $name ) ] = trim( $value );
        } else {
            $headers['Authorization'] = $auth_header;
        }
    }

    // Send HTTP POST asynchronously (non-blocking request using YOURLS HTTP class)
    yourls_http_post( $webhook_url, $headers, $body );
}

// Register settings page in admin panel
yourls_add_action( 'plugins_loaded', 'wh_init' );
function wh_init() {
    yourls_register_plugin_page( 'webhook_notification', 'Webhook Notification Settings', 'wh_display_settings_page' );
}

// Display settings page
function wh_display_settings_page() {
    $settings = yourls_get_option( 'wh_settings', array(
        'webhook_url' => '',
        'auth_header' => '',
    ));

    $nonce = yourls_create_nonce( 'wh_settings_nonce' );

    // Handle form submissions
    if ( isset( $_POST['action'] ) && $_POST['action'] == 'update_settings' ) {
        yourls_verify_nonce( 'wh_settings_nonce' );

        $settings['webhook_url'] = trim( $_POST['webhook_url'] );
        $settings['auth_header'] = trim( $_POST['auth_header'] );

        yourls_update_option( 'wh_settings', $settings );
        echo '<div class="alert alert-success" style="padding: 10px; margin: 15px 0; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">Settings saved successfully!</div>';
    }

    echo <<<HTML
    <div style="margin: 20px; max-width: 700px; font-family: sans-serif;">
        <h2>Webhook Notification Settings</h2>
        <p>Send details about newly shortened URLs to an external endpoint (e.g. Home Assistant, Node-RED, or custom web API).</p>

        <form method="post">
            <input type="hidden" name="action" value="update_settings" />
            <input type="hidden" name="nonce" value="{$nonce}" />

            <table class="tblTheme" cellpadding="10" cellspacing="0" style="width: 100%; border: 1px solid #ddd; margin-bottom: 25px; border-collapse: collapse;">
                <tbody>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="width: 200px; font-weight: bold; padding: 12px;">Webhook URL</td>
                        <td style="padding: 12px;">
                            <input type="url" name="webhook_url" value="{$settings['webhook_url']}" placeholder="https://your-homeassistant.local:8123/api/webhook/some_event" style="padding: 6px; width: 100%; box-sizing: border-box;" required />
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">The POST endpoint that will receive the JSON payload.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 12px;">Authentication / Custom Header</td>
                        <td style="padding: 12px;">
                            <input type="text" name="auth_header" value="{$settings['auth_header']}" placeholder="Bearer your_secret_token" style="padding: 6px; width: 100%; box-sizing: border-box;" />
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Optional. Provide the raw token (sent as <code>Authorization</code> header) or a custom header mapping like <code>X-HA-Access: token</code>.</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="button" style="padding: 8px 20px; font-weight: bold; cursor: pointer;">Save Settings</button>
        </form>
    </div>
HTML;
}
