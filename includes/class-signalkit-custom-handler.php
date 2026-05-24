<?php
/**
 * SignalKit Custom Banner AJAX Handler
 * Handles form submissions, webhook integration, and local storage
 * 
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Banner Submission Handler
 */
class SignalKit_Custom_Handler {
    
    /**
     * Table name for local storage
     * 
     * @var string
     */
    private $table_name;
    
    /**
     * Settings
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        
        $this->table_name = $wpdb->prefix . 'signalkit_submissions';
        $this->settings = get_option('signalkit_settings', array());
        
        // AJAX hooks
        add_action('wp_ajax_signalkit_custom_submit', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_signalkit_custom_submit', array($this, 'handle_submission'));
        
        // Admin hooks for viewing submissions
        add_action('admin_menu', array($this, 'add_submissions_menu'), 20);
        
        // Handle CSV export early, BEFORE any output starts
        add_action('admin_init', array($this, 'handle_csv_export_early'), 1);

        // Enqueue admin styles properly
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * Handle form submission via AJAX
     */
    public function handle_submission() {
        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'] ?? '')), 'signalkit_custom_submit')) {
            wp_send_json_error(array('message' => __('Security check failed', 'signalkit')));
        }
        
        // Rate limiting
        if (!$this->check_rate_limit()) {
            wp_send_json_error(array('message' => __('Too many submissions. Please try again later.', 'signalkit')));
        }
        
        // Sanitize input
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $banner_type = sanitize_key(wp_unslash($_POST['banner_type'] ?? 'newsletter'));
        
        // Validate email
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address', 'signalkit')));
        }
        
        // Check for duplicate within last 24 hours
        if ($this->is_duplicate_submission($email)) {
            // Still return success (don't reveal if email exists)
            wp_send_json_success(array('message' => $this->settings['custom_success_message'] ?? __('Thank you for subscribing!', 'signalkit')));
        }
        
        // Prepare submission data
        $submission = array(
            'email' => $email,
            'name' => $name,
            'banner_type' => $banner_type,
            'page_url' => esc_url_raw(wp_unslash($_POST['page_url'] ?? wp_get_referer())),
            'user_agent' => sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')),
            'ip_address' => $this->anonymize_ip($this->get_client_ip()), // PRIVACY: Anonymized IP
            'submitted_at' => current_time('mysql'),
        );
        
        $success = true;
        $errors = array();
        
        // Store locally if enabled
        if (!empty($this->settings['custom_store_locally'])) {
            $stored = $this->store_submission($submission);
            if (!$stored) {
                $errors[] = 'local_storage';
            }
        }
        
        // Send to webhook if configured
        if (!empty($this->settings['custom_webhook_url'])) {
            $webhook_result = $this->send_to_webhook($submission);
            if (!$webhook_result) {
                $errors[] = 'webhook';
            }
        }
        
        // Send admin notification
        $this->maybe_send_notification($submission);
        
        // Track submission in analytics
        if (class_exists('SignalKit_Analytics')) {
            SignalKit_Analytics::track_submission();
        }
        
        // Always return success to user (even if webhook failed)
        wp_send_json_success(array(
            'message' => $this->settings['custom_success_message'] ?? __('Thank you for subscribing!', 'signalkit'),
            'redirect' => $this->settings['custom_redirect_url'] ?? ''
        ));
    }
    
    /**
     * Store submission in database
     * 
     * @param array $submission Submission data
     * @return bool
     */
    private function store_submission($submission) {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'email' => $submission['email'],
                'name' => $submission['name'],
                'banner_type' => $submission['banner_type'],
                'page_url' => $submission['page_url'],
                'ip_address' => $submission['ip_address'],
                'submitted_at' => $submission['submitted_at'],
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Send submission to webhook
     * 
     * PRIVACY COMPLIANCE: Only sends data if admin has explicitly consented
     * Required by CodeCanyon/Envato standards
     * 
     * @param array $submission Submission data
     * @return bool
     */
    private function send_to_webhook($submission) {
        $webhook_url = $this->settings['custom_webhook_url'];
        
        if (empty($webhook_url)) {
            return true;
        }
        
        // PRIVACY: Check for explicit webhook consent (CodeCanyon requirement)
        if (empty($this->settings['custom_webhook_consent'])) {
            // Silently skip if admin hasn't explicitly consented to data transmission
            return true;
        }
        
        // Prepare payload
        $payload = array(
            'email' => $submission['email'],
            'name' => $submission['name'],
            'source' => 'signalkit',
            'banner_type' => $submission['banner_type'],
            'page_url' => $submission['page_url'],
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'timestamp' => $submission['submitted_at'],
        );
        
        $response = wp_remote_post($webhook_url, array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($payload),
        ));
        
        // Check response
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        return $response_code >= 200 && $response_code < 300;
    }
    
    /**
     * Maybe send admin notification
     * 
     * @param array $submission Submission data
     */
    private function maybe_send_notification($submission) {
        // Could add admin notification email setting
        // For now, skip email notification to avoid spam
    }
    
    /**
     * Check rate limit
     * 
     * @return bool
     */
    private function check_rate_limit() {
        $ip = $this->get_client_ip();
        $transient_key = 'signalkit_rate_' . md5($ip);
        
        $count = get_transient($transient_key);
        
        if ($count === false) {
            set_transient($transient_key, 1, HOUR_IN_SECONDS);
            return true;
        }
        
        if ($count >= 10) {
            return false;
        }
        
        set_transient($transient_key, $count + 1, HOUR_IN_SECONDS);
        return true;
    }
    
    /**
     * Check for duplicate submission
     * 
     * @param string $email Email address
     * @return bool
     */
    private function is_duplicate_submission($email) {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM `" . esc_sql($this->table_name) . "` 
             WHERE email = %s 
             AND submitted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            $email
        ));
        
        return (int) $count > 0;
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])))[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        
        return sanitize_text_field(trim($ip));
    }
    
    /**
     * Anonymize IP address for privacy compliance
     * Required by CodeCanyon/Envato for GDPR compliance
     * 
     * @param string $ip IP address to anonymize
     * @return string Anonymized IP (hashed)
     */
    private function anonymize_ip($ip) {
        // Return anonymized hash instead of raw IP
        // Using hash_hmac with WordPress salt for security
        return hash_hmac('sha256', $ip, wp_salt('auth'));
    }
    
    /**
     * Add submissions menu
     */
    public function add_submissions_menu() {
        add_submenu_page(
            'signalkit',
            __('Submissions', 'signalkit'),
            __('Submissions', 'signalkit'),
            'manage_options',
            'signalkit-submissions',
            array($this, 'render_submissions_page')
        );
    }
    
    /**
     * Handle CSV export early (before any output)
     * This runs on admin_init before headers are sent
     * 
     * @return void
     */
    public function handle_csv_export_early() {
        // Only run on our submissions page
        if (!isset($_GET['page']) || $_GET['page'] !== 'signalkit-submissions') {
            return;
        }
        
        // Check for export action
        if (!isset($_GET['action']) || $_GET['action'] !== 'export') {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'signalkit_export')) {
            return;
        }
        
        // Check capability
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Perform the export (this will exit after sending the file)
        $this->export_submissions();
    }
    
    /**
     * Render submissions page
     */
    public function render_submissions_page() {
        global $wpdb;
        
        // Handle delete
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'] ?? '')), 'signalkit_delete_submission')) {
            $id = absint($_POST['submission_id'] ?? 0);
            if ($id > 0) {
                $wpdb->delete($this->table_name, array('id' => $id), array('%d'));
                echo '<div class="notice notice-success"><p>' . esc_html__('Submission deleted.', 'signalkit') . '</p></div>';
            }
        }
        
        // Pagination
        $per_page = 20;
        $current_page = max(1, absint($_GET['paged'] ?? 1));
        $offset = ($current_page - 1) * $per_page;
        
        // Get total count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
        $total = $wpdb->get_var("SELECT COUNT(*) FROM `" . esc_sql($this->table_name) . "`");
        $total_pages = ceil($total / $per_page);
        
        // Get submissions
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, email, name, banner_type, page_url, submitted_at FROM `" . esc_sql($this->table_name) . "` ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        ?>
        <div class="wrap signalkit-submissions-page">
            <h1><?php esc_html_e('SignalKit Submissions', 'signalkit'); ?></h1>
            
            <div class="signalkit-submissions-header">
                <?php /* translators: %d: total submission count */ ?>
                <p><?php printf(esc_html__('Total submissions: %d', 'signalkit'), absint($total)); ?></p>
                
                <?php if ($total > 0): ?>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=signalkit-submissions&action=export'), 'signalkit_export')); ?>" 
                       class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export CSV', 'signalkit'); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($submissions)): ?>
                <div class="signalkit-empty-state">
                    <span class="dashicons dashicons-email-alt"></span>
                    <h3><?php esc_html_e('No submissions yet', 'signalkit'); ?></h3>
                    <p><?php esc_html_e('Submissions from your custom banner will appear here.', 'signalkit'); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Email', 'signalkit'); ?></th>
                            <th><?php esc_html_e('Name', 'signalkit'); ?></th>
                            <th><?php esc_html_e('Banner Type', 'signalkit'); ?></th>
                            <th><?php esc_html_e('Page', 'signalkit'); ?></th>
                            <th><?php esc_html_e('Date', 'signalkit'); ?></th>
                            <th><?php esc_html_e('Actions', 'signalkit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        /**
                         * SECURITY: All database values are late-escaped before output
                         * 
                         * Escaping functions used per column:
                         * - Email: esc_attr() for href, esc_html() for display
                         * - Name: esc_html()
                         * - Banner Type: esc_html()
                         * - Page URL: esc_url() for href, esc_html() for path display
                         * - DateTime: esc_html() (WordPress wp_date output)
                         * - ID: esc_attr() for hidden input value
                         */
                        foreach ($submissions as $sub): 
                        ?>
                            <tr>
                                <td><a href="mailto:<?php echo esc_attr($sub->email); ?>"><?php echo esc_html($sub->email); ?></a></td>
                                <td><?php echo esc_html($sub->name ?: '—'); ?></td>
                                <td><span class="signalkit-badge"><?php echo esc_html(ucfirst($sub->banner_type)); ?></span></td>
                                <td><?php 
                                    if ($sub->page_url) {
                                        $path = wp_parse_url($sub->page_url, PHP_URL_PATH);
                                        echo '<a href="' . esc_url($sub->page_url) . '" target="_blank">' . esc_html($path ?: '/') . '</a>';
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                                <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($sub->submitted_at))); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('signalkit_delete_submission'); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="submission_id" value="<?php echo esc_attr($sub->id); ?>">
                                        <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Delete this submission?', 'signalkit'); ?>');">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo wp_kses_post(paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            )));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Export submissions as CSV
     * Properly clears output buffers and sends correct headers for file download
     */
    private function export_submissions() {
        global $wpdb;
        
        // Get all submissions
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be prepared, esc_sql is used
        $submissions = $wpdb->get_results("SELECT id, email, name, banner_type, page_url, submitted_at FROM `" . esc_sql($this->table_name) . "` ORDER BY submitted_at DESC");
        
        // Generate filename
        $filename = 'signalkit-submissions-' . gmdate('Y-m-d-His') . '.csv';
        
        // Clean any existing output buffers to prevent content mixing
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Check if headers already sent (debugging)
        if (headers_sent($file, $line)) {
            wp_die(esc_html__('Export failed: Headers already sent. Please contact support.', 'signalkit'));
        }
        
        // Set headers for file download
        nocache_headers();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        if ($output === false) {
            wp_die(esc_html__('Export failed: Could not open output stream.', 'signalkit'));
        }
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Header row
        fputcsv($output, array('Email', 'Name', 'Banner Type', 'Page URL', 'Date'));
        
        // Data rows
        foreach ($submissions as $sub) {
            fputcsv($output, array(
                $sub->email,
                $sub->name,
                $sub->banner_type,
                $sub->page_url,
                $sub->submitted_at
            ));
        }
        
        if (is_resource($output)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
            fclose($output);
        }
        
        // Exit to prevent any further output
        exit;
    }
    
    
    /**
     * Create submissions table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) DEFAULT '',
            banner_type varchar(50) DEFAULT 'newsletter',
            page_url varchar(500) DEFAULT '',
            ip_address varchar(64) DEFAULT '' COMMENT 'Anonymized IP hash for privacy compliance',
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY banner_type (banner_type),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Enqueue admin styles
     * 
     * @param string $hook The current admin page hook
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'signalkit_page_signalkit-submissions') {
            return;
        }
        
        wp_enqueue_style(
            'signalkit-submissions',
            SIGNALKIT_PLUGIN_URL . 'admin/css/signalkit-submissions.css',
            array(),
            SIGNALKIT_VERSION
        );
    }
}


