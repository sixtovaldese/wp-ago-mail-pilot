<?php

namespace AgoLab\Smtp\Admin;

use AgoLab\Smtp\Plugin;
use AgoLab\Smtp\Presets;
use AgoLab\Smtp\Logger;
use AgoLab\Smtp\Mailer;

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
                <img src="<?php echo esc_url( AGO_SMTP_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo SMTP', 'ago-smtp' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGO_SMTP_VERSION ); ?></span>
            </h1>

            <?php if ( $notice ) : ?>
                <div class="notice notice-<?php echo esc_attr( $notice['status'] ); ?> is-dismissible" style="margin:15px 0">
                    <p><?php echo wp_kses_post( $notice['message'] ); ?></p>
                </div>
            <?php endif; ?>

            <div class="ago-layout">
                <div class="ago-main">

                    <!-- SMTP Configuration -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'SMTP configuration', 'ago-smtp' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Pick a provider preset to auto-fill host, port and encryption. The credentials wizard opens automatically and shows you, step by step, how to obtain a username and password for that provider.', 'ago-smtp' ); ?>
                        </p>

                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'ago_smtp_save' ); ?>
                            <input type="hidden" name="action" value="ago_smtp_save">

                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><label for="ago-preset"><?php esc_html_e( 'Provider preset', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <select id="ago-preset" name="preset" class="regular-text">
                                            <option value="custom"><?php esc_html_e( 'Custom (enter manually)', 'ago-smtp' ); ?></option>
                                            <?php foreach ( $presets as $key => $p ) : ?>
                                                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['preset'] ?? '', $key ); ?>>
                                                    <?php echo esc_html( $p['label'] ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">
                                            <a href="#" id="ago-show-guide" style="display:none">
                                                <?php esc_html_e( 'Show step-by-step guide for this provider', 'ago-smtp' ); ?>
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
                                                    <?php esc_html_e( 'Hide guide', 'ago-smtp' ); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th><label for="ago-host"><?php esc_html_e( 'SMTP host', 'ago-smtp' ); ?></label></th>
                                    <td><input type="text" id="ago-host" name="host" class="regular-text" value="<?php echo esc_attr( $settings['host'] ?? '' ); ?>" placeholder="smtp.example.com"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-port"><?php esc_html_e( 'Port', 'ago-smtp' ); ?></label></th>
                                    <td><input type="number" id="ago-port" name="port" class="small-text" value="<?php echo esc_attr( $settings['port'] ?? 587 ); ?>" min="1" max="65535"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-encryption"><?php esc_html_e( 'Encryption', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <select id="ago-encryption" name="encryption">
                                            <option value="none" <?php selected( $settings['encryption'] ?? '', 'none' ); ?>><?php esc_html_e( 'None', 'ago-smtp' ); ?></option>
                                            <option value="ssl" <?php selected( $settings['encryption'] ?? '', 'ssl' ); ?>>SSL</option>
                                            <option value="tls" <?php selected( $settings['encryption'] ?? 'tls', 'tls' ); ?>>TLS</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-username"><?php esc_html_e( 'Username', 'ago-smtp' ); ?></label></th>
                                    <td><input type="text" id="ago-username" name="username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings['username'] ?? '' ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-password"><?php esc_html_e( 'Password', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <input type="password" id="ago-password" name="password" class="regular-text" autocomplete="new-password" placeholder="<?php echo ! empty( $settings['password'] ) ? esc_attr__( 'Leave blank to keep current password', 'ago-smtp' ) : ''; ?>">
                                        <p class="description">
                                            <?php
                                            if ( $is_aes ) {
                                                esc_html_e( 'Stored encrypted (AES-256-CBC) tied to your WordPress AUTH_KEY.', 'ago-smtp' );
                                            } else {
                                                echo wp_kses_post( __( 'For higher security, define <code>AGO_SMTP_PASSWORD</code> in <code>wp-config.php</code>. When defined, the constant overrides this field.', 'ago-smtp' ) );
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-from-name"><?php esc_html_e( 'From name', 'ago-smtp' ); ?></label></th>
                                    <td><input type="text" id="ago-from-name" name="from_name" class="regular-text" value="<?php echo esc_attr( $settings['from_name'] ?? '' ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-from-email"><?php esc_html_e( 'From email', 'ago-smtp' ); ?></label></th>
                                    <td><input type="email" id="ago-from-email" name="from_email" class="regular-text" value="<?php echo esc_attr( $settings['from_email'] ?? '' ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"></td>
                                </tr>
                            </table>

                            <h3 style="margin-top:24px"><?php esc_html_e( 'Failure rate alerts', 'ago-smtp' ); ?></h3>
                            <p class="description">
                                <?php esc_html_e( 'Get an email when too many recent emails fail. Useful to detect provider outages early. Throttled to one alert per 24 hours.', 'ago-smtp' ); ?>
                            </p>
                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><?php esc_html_e( 'Alerts', 'ago-smtp' ); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="alerts_enabled" value="1" <?php checked( ! empty( $settings['alerts_enabled'] ) ); ?>>
                                            <?php esc_html_e( 'Notify the admin if the failure rate exceeds the threshold.', 'ago-smtp' ); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-threshold"><?php esc_html_e( 'Threshold (%)', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <input type="number" id="ago-alerts-threshold" name="alerts_threshold" class="small-text" min="1" max="100" value="<?php echo esc_attr( $settings['alerts_threshold'] ?? 30 ); ?>"> %
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-min"><?php esc_html_e( 'Minimum sample size', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <input type="number" id="ago-alerts-min" name="alerts_min_count" class="small-text" min="1" value="<?php echo esc_attr( $settings['alerts_min_count'] ?? 5 ); ?>">
                                        <span class="description"><?php esc_html_e( 'Do not alert until at least this many emails have been sent.', 'ago-smtp' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="ago-alerts-email"><?php esc_html_e( 'Alert recipient', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <input type="email" id="ago-alerts-email" name="alerts_email" class="regular-text" value="<?php echo esc_attr( $settings['alerts_email'] ?? get_option( 'admin_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                                    </td>
                                </tr>
                            </table>

                            <p class="submit">
                                <button type="submit" class="button button-primary">
                                    <?php esc_html_e( 'Save settings', 'ago-smtp' ); ?>
                                </button>
                            </p>
                        </form>
                    </div>

                    <!-- Test Email -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Send test email', 'ago-smtp' ); ?></h2>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: %s: current user email wrapped in <strong> */
                                    __( 'Send a test message to %s to verify that the SMTP configuration works end to end.', 'ago-smtp' ),
                                    '<strong>' . esc_html( wp_get_current_user()->user_email ) . '</strong>'
                                )
                            );
                            ?>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'ago_smtp_test' ); ?>
                            <input type="hidden" name="action" value="ago_smtp_test">
                            <p>
                                <button type="submit" class="button button-secondary">
                                    <?php esc_html_e( 'Send test email', 'ago-smtp' ); ?>
                                </button>
                            </p>
                        </form>
                    </div>

                    <!-- DNS Auditor -->
                    <div class="card ago-card" id="dns">
                        <h2><?php esc_html_e( 'DNS health check (SPF, DKIM, DMARC)', 'ago-smtp' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Most email delivery problems come from broken sender authentication. Run this check against your sending domain to confirm SPF, DKIM and DMARC are correctly set. The check is read-only (DNS queries only); it never modifies your DNS records.', 'ago-smtp' ); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( 'ago_smtp_dns_audit' ); ?>
                            <input type="hidden" name="action" value="ago_smtp_dns_audit">
                            <table class="form-table ago-form-table">
                                <tr>
                                    <th><label for="ago-dns-domain"><?php esc_html_e( 'Domain to check', 'ago-smtp' ); ?></label></th>
                                    <td><input type="text" id="ago-dns-domain" name="dns_domain" class="regular-text" placeholder="<?php echo esc_attr( $from_dom ); ?>" value="<?php echo esc_attr( $dns_data['domain'] ?? '' ); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="ago-dns-selector"><?php esc_html_e( 'DKIM selector', 'ago-smtp' ); ?></label></th>
                                    <td>
                                        <input type="text" id="ago-dns-selector" name="dns_selector" class="small-text" value="<?php echo esc_attr( $dns_data['selector'] ?? 'default' ); ?>">
                                        <span class="description"><?php esc_html_e( 'Common selectors: default, google, s1, selector1, brevo, sendgrid.', 'ago-smtp' ); ?></span>
                                    </td>
                                </tr>
                            </table>
                            <p>
                                <button type="submit" class="button button-secondary">
                                    <?php esc_html_e( 'Run DNS check', 'ago-smtp' ); ?>
                                </button>
                            </p>
                        </form>

                        <?php if ( ! empty( $dns_data['result'] ) ) : ?>
                            <h3><?php
                                printf(
                                    /* translators: %s: domain checked */
                                    esc_html__( 'Results for %s', 'ago-smtp' ),
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
                                                    <span style="color:#1a7f37;font-weight:600">✓ <?php esc_html_e( 'OK', 'ago-smtp' ); ?></span>
                                                <?php else : ?>
                                                    <span style="color:#cf222e;font-weight:600">✗ <?php esc_html_e( 'Missing', 'ago-smtp' ); ?></span>
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

                    <!-- Email Log -->
                    <div class="card ago-card" id="log">
                        <h2 style="display:flex;justify-content:space-between;align-items:center">
                            <?php esc_html_e( 'Recent email log', 'ago-smtp' ); ?>
                            <?php $entries = Logger::entries(); if ( $entries ) : ?>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                                    <?php wp_nonce_field( 'ago_smtp_clear_log' ); ?>
                                    <input type="hidden" name="action" value="ago_smtp_clear_log">
                                    <button type="submit" class="button button-link-delete button-small" onclick="return confirm('<?php echo esc_attr__( 'Clear all log entries?', 'ago-smtp' ); ?>')">
                                        <?php esc_html_e( 'Clear log', 'ago-smtp' ); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </h2>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %d: max log entries */
                                esc_html__( 'Shows the last %d outgoing emails. The log lives in the WordPress options table; no extra database table is created.', 'ago-smtp' ),
                                (int) Plugin::LOG_MAX
                            );
                            ?>
                        </p>
                        <?php if ( empty( $entries ) ) : ?>
                            <p style="color:#888"><?php esc_html_e( 'No emails sent yet. Try the test email above.', 'ago-smtp' ); ?></p>
                        <?php else : ?>
                            <table class="widefat striped ago-log-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'To', 'ago-smtp' ); ?></th>
                                        <th><?php esc_html_e( 'Subject', 'ago-smtp' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'ago-smtp' ); ?></th>
                                        <th><?php esc_html_e( 'When', 'ago-smtp' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $entries as $e ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $e['to'] ); ?></td>
                                            <td><?php echo esc_html( $e['subject'] ); ?></td>
                                            <td>
                                                <?php if ( 'ok' === $e['status'] ) : ?>
                                                    <span style="color:#1a7f37"><?php esc_html_e( 'Sent', 'ago-smtp' ); ?></span>
                                                <?php else : ?>
                                                    <span style="color:#cf222e" title="<?php echo esc_attr( $e['error'] ?? '' ); ?>"><?php esc_html_e( 'Failed', 'ago-smtp' ); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo esc_html( human_time_diff( (int) $e['time'], time() ) . ' ' . __( 'ago', 'ago-smtp' ) ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- SIDEBAR -->
                <div class="ago-sidebar">

                    <!-- Quick links -->
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'Quick links', 'ago-smtp' ); ?></h3>
                        <ul class="ago-features" style="list-style:none;padding:0;margin:0">
                            <li><a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank" rel="noopener"><?php esc_html_e( 'Generate WordPress secret keys', 'ago-smtp' ); ?></a></li>
                            <li><a href="https://www.mail-tester.com/" target="_blank" rel="noopener"><?php esc_html_e( 'Test deliverability (mail-tester)', 'ago-smtp' ); ?></a></li>
                            <li><a href="https://mxtoolbox.com/SuperTool.aspx" target="_blank" rel="noopener"><?php esc_html_e( 'DNS lookup (MXToolbox)', 'ago-smtp' ); ?></a></li>
                        </ul>
                    </div>

                    <!-- About -->
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-smtp' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'Send WordPress emails via SMTP. 8 provider presets with a step-by-step credentials wizard, DNS health check, encrypted password storage and failure alerts. Completely free.', 'ago-smtp' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><?php
                                printf(
                                    /* translators: %d: number of provider presets */
                                    esc_html__( '%d provider presets with step-by-step credentials wizard', 'ago-smtp' ),
                                    count( $presets )
                                );
                            ?></li>
                            <li><?php esc_html_e( 'TLS / SSL / no encryption', 'ago-smtp' ); ?></li>
                            <li><?php esc_html_e( 'Test email with detailed error feedback', 'ago-smtp' ); ?></li>
                            <li><?php esc_html_e( 'DNS auditor for SPF, DKIM, DMARC', 'ago-smtp' ); ?></li>
                            <li><?php esc_html_e( 'Encrypted password storage (AES-256-CBC)', 'ago-smtp' ); ?></li>
                            <li><?php esc_html_e( 'Failure rate alerts to the admin', 'ago-smtp' ); ?></li>
                            <li><?php esc_html_e( 'No external API calls. No telemetry.', 'ago-smtp' ); ?></li>
                        </ul>
                    </div>

                    <!-- Cross-sell aGo Lab plugins -->
                    <div class="card ago-card">
                        <h3 style="margin-top:0"><?php esc_html_e( 'Other aGo Lab plugins', 'ago-smtp' ); ?></h3>
                        <p style="font-size:13px;color:#666;margin-top:0">
                            <?php esc_html_e( 'Free WordPress plugins from the same team. No upsell pressure.', 'ago-smtp' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><strong>aGo AI Chatbot</strong> — <?php esc_html_e( 'AI customer support widget for your site.', 'ago-smtp' ); ?></li>
                            <li><strong>aGo Legal</strong> — <?php esc_html_e( 'GDPR / LGPD / Chile Law 21.719 compliance toolkit.', 'ago-smtp' ); ?></li>
                            <li><strong>aGo Cleanup</strong> — <?php esc_html_e( 'Remove WordPress bloat and front-end clutter.', 'ago-smtp' ); ?></li>
                            <li><strong>aGo Harden</strong> — <?php esc_html_e( 'Lightweight security hardening toggles.', 'ago-smtp' ); ?></li>
                        </ul>
                        <p>
                            <a href="https://ago.cl/herramientas/" target="_blank" rel="noopener" class="button button-secondary" style="width:100%;text-align:center">
                                <?php esc_html_e( 'Browse aGo Lab plugins', 'ago-smtp' ); ?>
                            </a>
                        </p>
                    </div>

                    <!-- Donation -->
                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support open source', 'ago-smtp' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'If this plugin saves you time, consider buying us a coffee.', 'ago-smtp' ); ?>
                        </p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-coffee" style="margin-right:6px"></span>
                            <?php esc_html_e( 'Buy us a coffee', 'ago-smtp' ); ?>
                        </a>
                    </div>

                    <!-- Footer with logo -->
                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo">
                            <img src="<?php echo esc_url( AGO_SMTP_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto">
                        </a>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: %1$s: heart icon HTML, %2$s: link to ago.cl */
                                    __( 'Developed with %1$s by %2$s', 'ago-smtp' ),
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
        $key    = 'ago_smtp_notice_' . get_current_user_id();
        $notice = get_transient( $key );
        if ( $notice ) {
            delete_transient( $key );
            return is_array( $notice ) ? $notice : null;
        }
        return null;
    }

    private static function pop_dns_result(): array {
        $key  = 'ago_smtp_dns_result_' . get_current_user_id();
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
