<?php

namespace AgoLab\MailPilot\Admin;

use AgoLab\MailPilot\Plugin;
use AgoLab\MailPilot\Presets;
use AgoLab\MailPilot\Logger;
use AgoLab\MailPilot\Mailer;

defined( 'ABSPATH' ) || exit;

class Page {

    public static function render(): void {
        $settings = get_option( Plugin::OPTION_KEY, [] );
        $notice   = self::pop_notice();
        $presets  = Presets::all();
        $dns_data = self::pop_dns_result();
        $stored_pw = (string) ( $settings['password'] ?? '' );
        $is_aes   = Mailer::is_encrypted( $stored_pw );
        $from_dom = self::default_domain( $settings );
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( AGOMP_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo Mail Pilot', 'ago-mail-pilot' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGOMP_VERSION ); ?></span>
            </h1>

            <?php if ( $notice ) : ?>
                <div class="notice notice-<?php echo esc_attr( $notice['status'] ); ?> is-dismissible" style="margin:15px 0">
                    <p><?php echo wp_kses_post( $notice['message'] ); ?></p>
                </div>
            <?php endif; ?>

            <div class="ago-layout">
                <div class="ago-main">

                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'SMTP configuration', 'ago-mail-pilot' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Pick a provider preset to auto-fill host, port and encryption. The credentials wizard opens automatically and shows you, step by step, how to obtain a username and password for that provider.', 'ago-mail-pilot' ); ?>
                        </p>

                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'agomp_save' ); ?>
                            <input type="hidden" name="action" value="agomp_save">

                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><label for="ago-preset"><?php esc_html_e( 'Provider preset', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <select id="ago-preset" name="preset" class="regular-text">
                                            <option value="custom"><?php esc_html_e( 'Custom (enter manually)', 'ago-mail-pilot' ); ?></option>
                                            <?php foreach ( $presets as $key => $p ) : ?>
                                                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['preset'] ?? '', $key ); ?>>
                                                    <?php echo esc_html( $p['label'] ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">
                                            <a href="#" id="ago-show-guide" style="display:none">
                                                <?php esc_html_e( 'Show step-by-step guide for this provider', 'ago-mail-pilot' ); ?>
                                            </a>
                                        </p>
                                    </td>
                                </tr>

                                <tr id="ago-guide-row" style="display:none">
                                    <th></th>
                                    <td>
                                        <div id="ago-guide-box" class="ago-guide-box">
                                            <h3 id="ago-guide-title"></h3>
                                            <p id="ago-guide-note" class="description"></p>
                                            <ol id="ago-guide-steps"></ol>
                                            <p style="text-align:right;margin:6px 0 0">
                                                <a href="#" id="ago-hide-guide" class="button-link">
                                                    <?php esc_html_e( 'Hide guide', 'ago-mail-pilot' ); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th><label for="ago-host"><?php esc_html_e( 'SMTP host', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="text" id="ago-host" name="host" class="regular-text" value="<?php echo esc_attr( $settings['host'] ?? '' ); ?>" placeholder="smtp.example.com"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-port"><?php esc_html_e( 'Port', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="number" id="ago-port" name="port" class="small-text" value="<?php echo esc_attr( $settings['port'] ?? 587 ); ?>" min="1" max="65535"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-encryption"><?php esc_html_e( 'Encryption', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <select id="ago-encryption" name="encryption">
                                            <option value="none" <?php selected( $settings['encryption'] ?? '', 'none' ); ?>><?php esc_html_e( 'None', 'ago-mail-pilot' ); ?></option>
                                            <option value="ssl" <?php selected( $settings['encryption'] ?? '', 'ssl' ); ?>>SSL</option>
                                            <option value="tls" <?php selected( $settings['encryption'] ?? 'tls', 'tls' ); ?>>TLS</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-username"><?php esc_html_e( 'Username', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="text" id="ago-username" name="username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings['username'] ?? '' ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-password"><?php esc_html_e( 'Password', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <input type="password" id="ago-password" name="password" class="regular-text" autocomplete="new-password" placeholder="<?php echo ! empty( $settings['password'] ) ? esc_attr__( 'Leave blank to keep current password', 'ago-mail-pilot' ) : ''; ?>">
                                        <p class="description">
                                            <?php
                                            if ( $is_aes ) {
                                                esc_html_e( 'Stored encrypted (AES-256-CBC) tied to your WordPress AUTH_KEY.', 'ago-mail-pilot' );
                                            } else {
                                                echo wp_kses_post( __( 'For higher security, define <code>AGOMP_PASSWORD</code> in <code>wp-config.php</code>. When defined, the constant overrides this field.', 'ago-mail-pilot' ) );
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-from-name"><?php esc_html_e( 'From name', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="text" id="ago-from-name" name="from_name" class="regular-text" value="<?php echo esc_attr( $settings['from_name'] ?? '' ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-from-email"><?php esc_html_e( 'From email', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="email" id="ago-from-email" name="from_email" class="regular-text" value="<?php echo esc_attr( $settings['from_email'] ?? '' ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"></td>
                                </tr>
                            </table>

                            <h3 style="margin-top:24px"><?php esc_html_e( 'Failure rate alerts', 'ago-mail-pilot' ); ?></h3>
                            <p class="description">
                                <?php esc_html_e( 'Get an email when too many recent emails fail. Useful to detect provider outages early. Throttled to one alert per 24 hours.', 'ago-mail-pilot' ); ?>
                            </p>
                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><?php esc_html_e( 'Alerts', 'ago-mail-pilot' ); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="alerts_enabled" value="1" <?php checked( ! empty( $settings['alerts_enabled'] ) ); ?>>
                                            <?php esc_html_e( 'Notify the admin if the failure rate exceeds the threshold.', 'ago-mail-pilot' ); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-threshold"><?php esc_html_e( 'Threshold (%)', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <input type="number" id="ago-alerts-threshold" name="alerts_threshold" class="small-text" min="1" max="100" value="<?php echo esc_attr( $settings['alerts_threshold'] ?? 30 ); ?>"> %
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-min"><?php esc_html_e( 'Minimum sample size', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <input type="number" id="ago-alerts-min" name="alerts_min_count" class="small-text" min="1" value="<?php echo esc_attr( $settings['alerts_min_count'] ?? 5 ); ?>">
                                        <span class="description"><?php esc_html_e( 'Do not alert until at least this many emails have been sent.', 'ago-mail-pilot' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-email"><?php esc_html_e( 'Alert recipient', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <input type="email" id="ago-alerts-email" name="alerts_email" class="regular-text" value="<?php echo esc_attr( $settings['alerts_email'] ?? get_option( 'admin_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                                    </td>
                                </tr>
                            </table>

                            <p class="submit">
                                <button type="submit" class="button button-primary">
                                    <?php esc_html_e( 'Save settings', 'ago-mail-pilot' ); ?>
                                </button>
                            </p>
                        </form>
                    </div>

                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Send test email', 'ago-mail-pilot' ); ?></h2>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: %s: current user email wrapped in <strong> */
                                    __( 'Send a test message to %s to verify that the SMTP configuration works end to end.', 'ago-mail-pilot' ),
                                    '<strong>' . esc_html( wp_get_current_user()->user_email ) . '</strong>'
                                )
                            );
                            ?>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'agomp_test' ); ?>
                            <input type="hidden" name="action" value="agomp_test">
                            <p>
                                <button type="submit" class="button button-secondary">
                                    <?php esc_html_e( 'Send test email', 'ago-mail-pilot' ); ?>
                                </button>
                            </p>
                        </form>
                    </div>

                    <div class="card ago-card" id="dns">
                        <h2><?php esc_html_e( 'DNS health check (SPF, DKIM, DMARC)', 'ago-mail-pilot' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Most email delivery problems come from broken sender authentication. Run this check against your sending domain to confirm SPF, DKIM and DMARC are correctly set. The check is read-only (DNS queries only); it never modifies your DNS records.', 'ago-mail-pilot' ); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'agomp_dns_audit' ); ?>
                            <input type="hidden" name="action" value="agomp_dns_audit">
                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><label for="ago-dns-domain"><?php esc_html_e( 'Domain to check', 'ago-mail-pilot' ); ?></label></th>
                                    <td><input type="text" id="ago-dns-domain" name="dns_domain" class="regular-text" placeholder="<?php echo esc_attr( $from_dom ); ?>" value="<?php echo esc_attr( $dns_data['domain'] ?? '' ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-dns-selector"><?php esc_html_e( 'DKIM selector', 'ago-mail-pilot' ); ?></label></th>
                                    <td>
                                        <input type="text" id="ago-dns-selector" name="dns_selector" class="small-text" value="<?php echo esc_attr( $dns_data['selector'] ?? 'default' ); ?>">
                                        <span class="description"><?php esc_html_e( 'Common selectors: default, google, s1, selector1, brevo, sendgrid.', 'ago-mail-pilot' ); ?></span>
                                    </td>
                                </tr>
                            </table>
                            <p>
                                <button type="submit" class="button button-secondary">
                                    <?php esc_html_e( 'Run DNS check', 'ago-mail-pilot' ); ?>
                                </button>
                            </p>
                        </form>

                        <?php if ( ! empty( $dns_data['result'] ) ) : ?>
                            <h3><?php
                                printf(
                                    /* translators: %s: domain checked */
                                    esc_html__( 'Results for %s', 'ago-mail-pilot' ),
                                    esc_html( $dns_data['domain'] )
                                );
                            ?></h3>
                            <table class="widefat striped">
                                <tbody>
                                    <?php foreach ( $dns_data['result'] as $kind => $r ) : ?>
                                        <tr>
                                            <td style="width:70px;font-weight:600"><?php echo esc_html( strtoupper( $kind ) ); ?></td>
                                            <td style="width:90px">
                                                <?php if ( $r['pass'] ) : ?>
                                                    <span style="color:#1a7f37;font-weight:600">✓ <?php esc_html_e( 'OK', 'ago-mail-pilot' ); ?></span>
                                                <?php else : ?>
                                                    <span style="color:#cf222e;font-weight:600">✗ <?php esc_html_e( 'Missing', 'ago-mail-pilot' ); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo esc_html( $r['message'] ); ?>
                                                <?php if ( $r['record'] ) : ?>
                                                    <div style="font-family:monospace;font-size:11px;color:#555;margin-top:4px;word-break:break-all"><?php echo esc_html( $r['record'] ); ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div class="card ago-card" id="log">
                        <h2 style="display:flex;justify-content:space-between;align-items:center">
                            <?php esc_html_e( 'Recent email log', 'ago-mail-pilot' ); ?>
                            <?php $entries = Logger::entries(); if ( $entries ) : ?>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                                    <?php wp_nonce_field( 'agomp_clear_log' ); ?>
                                    <input type="hidden" name="action" value="agomp_clear_log">
                                    <button type="submit" class="button button-link-delete button-small" onclick="return confirm('<?php echo esc_attr__( 'Clear all log entries?', 'ago-mail-pilot' ); ?>')">
                                        <?php esc_html_e( 'Clear log', 'ago-mail-pilot' ); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </h2>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %d: max log entries */
                                esc_html__( 'Shows the last %d outgoing emails. The log lives in the WordPress options table; no extra database table is created.', 'ago-mail-pilot' ),
                                (int) Plugin::LOG_MAX
                            );
                            ?>
                        </p>
                        <?php if ( empty( $entries ) ) : ?>
                            <p style="color:#888"><?php esc_html_e( 'No emails sent yet. Try the test email above.', 'ago-mail-pilot' ); ?></p>
                        <?php else : ?>
                            <table class="widefat striped ago-log-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'To', 'ago-mail-pilot' ); ?></th>
                                        <th><?php esc_html_e( 'Subject', 'ago-mail-pilot' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'ago-mail-pilot' ); ?></th>
                                        <th><?php esc_html_e( 'When', 'ago-mail-pilot' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $entries as $e ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $e['to'] ); ?></td>
                                            <td><?php echo esc_html( $e['subject'] ); ?></td>
                                            <td>
                                                <?php if ( 'ok' === $e['status'] ) : ?>
                                                    <span style="color:#1a7f37"><?php esc_html_e( 'Sent', 'ago-mail-pilot' ); ?></span>
                                                <?php else : ?>
                                                    <span style="color:#cf222e" title="<?php echo esc_attr( $e['error'] ?? '' ); ?>"><?php esc_html_e( 'Failed', 'ago-mail-pilot' ); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo esc_html( human_time_diff( (int) $e['time'], time() ) . ' ' . __( 'ago', 'ago-mail-pilot' ) ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="ago-sidebar">

                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'Quick links', 'ago-mail-pilot' ); ?></h3>
                        <ul class="ago-features" style="list-style:none;padding:0;margin:0">
                            <li><a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank" rel="noopener"><?php esc_html_e( 'Generate WordPress secret keys', 'ago-mail-pilot' ); ?></a></li>
                            <li><a href="https://www.mail-tester.com/" target="_blank" rel="noopener"><?php esc_html_e( 'Test deliverability (mail-tester)', 'ago-mail-pilot' ); ?></a></li>
                            <li><a href="https://mxtoolbox.com/SuperTool.aspx" target="_blank" rel="noopener"><?php esc_html_e( 'DNS lookup (MXToolbox)', 'ago-mail-pilot' ); ?></a></li>
                        </ul>
                    </div>

                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-mail-pilot' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'Send WordPress emails via SMTP. 8 provider presets with a step-by-step credentials wizard, DNS health check, encrypted password storage and failure alerts. Completely free.', 'ago-mail-pilot' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><?php
                                printf(
                                    /* translators: %d: number of provider presets */
                                    esc_html__( '%d provider presets with step-by-step credentials wizard', 'ago-mail-pilot' ),
                                    count( $presets )
                                );
                            ?></li>
                            <li><?php esc_html_e( 'TLS / SSL / no encryption', 'ago-mail-pilot' ); ?></li>
                            <li><?php esc_html_e( 'Test email with detailed error feedback', 'ago-mail-pilot' ); ?></li>
                            <li><?php esc_html_e( 'DNS auditor for SPF, DKIM, DMARC', 'ago-mail-pilot' ); ?></li>
                            <li><?php esc_html_e( 'Encrypted password storage (AES-256-CBC)', 'ago-mail-pilot' ); ?></li>
                            <li><?php esc_html_e( 'Failure rate alerts to the admin', 'ago-mail-pilot' ); ?></li>
                            <li><?php esc_html_e( 'No external API calls. No telemetry.', 'ago-mail-pilot' ); ?></li>
                        </ul>
                    </div>

                    <div class="card ago-card">
                        <h3 style="margin-top:0"><?php esc_html_e( 'Other aGo Lab plugins', 'ago-mail-pilot' ); ?></h3>
                        <p style="font-size:13px;color:#666;margin-top:0">
                            <?php esc_html_e( 'Free WordPress plugins from the same team. No upsell pressure.', 'ago-mail-pilot' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><strong>aGo AI Chatbot</strong>, <?php esc_html_e( 'AI customer support widget for your site.', 'ago-mail-pilot' ); ?></li>
                            <li><strong>aGo Legal</strong>, <?php esc_html_e( 'GDPR / LGPD / Chile Law 21.719 compliance toolkit.', 'ago-mail-pilot' ); ?></li>
                            <li><strong>aGo Cleanup</strong>, <?php esc_html_e( 'Remove WordPress bloat and front-end clutter.', 'ago-mail-pilot' ); ?></li>
                            <li><strong>aGo Harden</strong>, <?php esc_html_e( 'Lightweight security hardening toggles.', 'ago-mail-pilot' ); ?></li>
                        </ul>
                        <p>
                            <a href="https://ago.cl/herramientas/" target="_blank" rel="noopener" class="button button-secondary" style="width:100%;text-align:center">
                                <?php esc_html_e( 'Browse aGo Lab plugins', 'ago-mail-pilot' ); ?>
                            </a>
                        </p>
                    </div>

                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support open source', 'ago-mail-pilot' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'If this plugin saves you time, consider buying us a coffee.', 'ago-mail-pilot' ); ?>
                        </p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-coffee" style="margin-right:6px"></span>
                            <?php esc_html_e( 'Buy us a coffee', 'ago-mail-pilot' ); ?>
                        </a>
                    </div>

                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo">
                            <img src="<?php echo esc_url( AGOMP_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto">
                        </a>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: %1$s: heart icon HTML, %2$s: link to ago.cl */
                                    __( 'Developed with %1$s by %2$s', 'ago-mail-pilot' ),
                                    '<span style="color:#e25555">&#10084;</span>',
                                    '<a href="https://ago.cl" target="_blank" rel="noopener"><strong>aGo Lab</strong></a>'
                                )
                            );
                            ?>
                        </p>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    private static function pop_notice(): ?array {
        $key    = 'agomp_notice_' . get_current_user_id();
        $notice = get_transient( $key );
        if ( $notice ) {
            delete_transient( $key );
            return is_array( $notice ) ? $notice : null;
        }
        return null;
    }

    private static function pop_dns_result(): array {
        $key  = 'agomp_dns_result_' . get_current_user_id();
        $data = get_transient( $key );
        if ( $data && is_array( $data ) ) {
            delete_transient( $key );
            return $data;
        }
        return [];
    }

    private static function default_domain( array $settings ): string {
        $from = (string) ( $settings['from_email'] ?? '' );
        if ( $from && str_contains( $from, '@' ) ) {
            return trim( substr( strrchr( $from, '@' ), 1 ) );
        }
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        return $host ? preg_replace( '/^www\./', '', $host ) : 'example.com';
    }
}
