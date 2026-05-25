<?php
/**
 * Settings page functionality.
 *
 * @package SignalKit
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalKit_Settings {
    
    /**
     * The ID of this plugin.
     */
    private $plugin_name;
    
    /**
     * The version of this plugin.
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        signalkit_log('SignalKit_Settings initialized');
    }
    
    /**
     * Section callbacks.
     */
    public function general_section_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<p>' . esc_html__('Configure basic plugin settings.', 'signalkit') . '</p>';
    }
    
    public function content_section_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<p>' . esc_html__('Customize the text and links displayed in your banner.', 'signalkit') . '</p>';
    }
    
    public function design_section_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<p>' . esc_html__('Customize the visual appearance of your banner.', 'signalkit') . '</p>';
    }
    
    public function display_section_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<p>' . esc_html__('Control when and where the banner is displayed.', 'signalkit') . '</p>';
    }
    
    public function analytics_section_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $analytics = SignalKit_Analytics::get_analytics();
        ?>
        <div class="signalkit-analytics-dashboard">
            <p><?php esc_html_e('Track the performance of your banner.', 'signalkit'); ?></p>
            <div class="signalkit-stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['impressions'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['clicks'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html($analytics['ctr']); ?>%</div>
                    <div class="stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['dismissals'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                </div>
            </div>
            <button type="button" class="button button-secondary" id="signalkit-reset-analytics">
                <?php esc_html_e('Reset Analytics', 'signalkit'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Field callbacks.
     */
    public function checkbox_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : false;
        ?>
        <label>
            <input type="checkbox" name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" value="1" <?php checked($value, true); ?>>
            <?php if (!empty($args['description'])): ?>
                <span class="description"><?php echo esc_html($args['description']); ?></span>
            <?php endif; ?>
        </label>
        <?php
    }
    
    public function text_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : '';
        ?>
        <input type="text" 
               name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo esc_attr($args['placeholder']); ?>"<?php endif; ?>>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public function textarea_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : '';
        ?>
        <textarea name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" 
                  rows="3" 
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public function url_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : '';
        ?>
        <input type="url" 
               name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" 
               value="<?php echo esc_url($value); ?>" 
               class="regular-text"
               <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo esc_attr($args['placeholder']); ?>"<?php endif; ?>>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public function color_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : '#4285f4';
        ?>
        <input type="text" 
               name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="signalkit-color-picker">
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public function select_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : '';
        ?>
        <select name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]">
            <?php foreach ($args['options'] as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    public function number_field_callback($args) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('signalkit_settings', array());
        $value = isset($settings[$args['field']]) ? $settings[$args['field']] : 0;
        $min = isset($args['min']) ? $args['min'] : 0;
        $max = isset($args['max']) ? $args['max'] : 999;
        ?>
        <input type="number" 
               name="signalkit_settings[<?php echo esc_attr($args['field']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               min="<?php echo esc_attr($min); ?>"
               max="<?php echo esc_attr($max); ?>"
               class="small-text">
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized access', 'signalkit'));
        }
        
        // Check if we just saved
        if (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated'])) === 'true') {
            add_settings_error(
                'signalkit_messages',
                'signalkit_message',
                __('Settings saved successfully!', 'signalkit'),
                'success'
            );
            
            signalkit_log('Settings: Settings saved successfully via options.php');
        }
        
        // Handle analytics reset
        if (isset($_POST['signalkit_reset_analytics']) && check_admin_referer('signalkit_reset_analytics_nonce')) {
            SignalKit_Analytics::reset_analytics();
            add_settings_error('signalkit_messages', 'signalkit_message', __('Analytics reset successfully!', 'signalkit'), 'updated');
        }
        
        settings_errors('signalkit_messages');
        
        // Get settings for template
        $settings = get_option('signalkit_settings', array());
        
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }
    
    /**
     * Render the analytics page.
     */
    public function render_analytics_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized access', 'signalkit'));
        }
        
        // Check if analytics file exists
        $analytics_page = SIGNALKIT_PLUGIN_DIR . 'admin/partials/analytics-page.php';
        if (file_exists($analytics_page)) {
            require_once $analytics_page;
        } else {
            // Fallback: Display analytics inline if template doesn't exist
            $this->render_analytics_fallback();
        }
    }
    
    /**
     * Fallback analytics display if template file doesn't exist.
     */
    private function render_analytics_fallback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $follow_analytics = SignalKit_Analytics::get_analytics('follow');
        $preferred_analytics = SignalKit_Analytics::get_analytics('preferred');
        $all_analytics = SignalKit_Analytics::get_analytics('all');
        
        ?>
        <div class="wrap signalkit-analytics-page">
            <h1><?php esc_html_e('SignalKit Analytics', 'signalkit'); ?></h1>
            
            <div class="signalkit-analytics-container">
                
                <!-- Combined Analytics -->
                <div class="signalkit-analytics-card signalkit-combined-analytics">
                    <div class="signalkit-card-header">
                        <h2><?php esc_html_e('Combined Performance', 'signalkit'); ?></h2>
                    </div>
                    <div class="signalkit-stats-grid">
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-value">
                                <?php echo esc_html(number_format(
                                    ($follow_analytics['impressions'] ?? 0) + ($preferred_analytics['impressions'] ?? 0)
                                )); ?>
                            </div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Total Impressions', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-value">
                                <?php echo esc_html(number_format(
                                    ($follow_analytics['clicks'] ?? 0) + ($preferred_analytics['clicks'] ?? 0)
                                )); ?>
                            </div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Total Clicks', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box signalkit-stat-highlight">
                            <div class="signalkit-stat-value">
                                <?php
                                $total_impressions = ($follow_analytics['impressions'] ?? 0) + ($preferred_analytics['impressions'] ?? 0);
                                $total_clicks = ($follow_analytics['clicks'] ?? 0) + ($preferred_analytics['clicks'] ?? 0);
                                $combined_ctr = $total_impressions > 0 ? round(($total_clicks / $total_impressions) * 100, 2) : 0;
                                echo esc_html($combined_ctr);
                                ?>%
                            </div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Combined CTR', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-value">
                                <?php echo esc_html(number_format(
                                    ($follow_analytics['dismissals'] ?? 0) + ($preferred_analytics['dismissals'] ?? 0)
                                )); ?>
                            </div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Total Dismissals', 'signalkit'); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Follow Banner Analytics -->
                <div class="signalkit-analytics-card">
                    <div class="signalkit-card-header">
                        <h2><?php esc_html_e('Follow Banner', 'signalkit'); ?></h2>
                        <button type="button" 
                                class="button button-secondary signalkit-reset-analytics" 
                                data-banner-type="follow">
                            <?php esc_html_e('Reset', 'signalkit'); ?>
                        </button>
                    </div>
                    <div class="signalkit-stats-grid">
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($follow_analytics['impressions'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon">👆</div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($follow_analytics['clicks'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box signalkit-stat-highlight">
                            <div class="signalkit-stat-icon">📈</div>
                            <div class="signalkit-stat-value"><?php echo esc_html($follow_analytics['ctr'] ?? 0); ?>%</div>
                            <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($follow_analytics['dismissals'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                        </div>
                    </div>
                    <?php if (isset($follow_analytics['last_updated'])): ?>
                        <div class="signalkit-last-updated">
                            <?php
                            printf(
                                /* translators: %s: Last updated date/time */
                                esc_html__('Last updated: %s', 'signalkit'),
                                esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($follow_analytics['last_updated'])))
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Preferred Source Banner Analytics -->
                <div class="signalkit-analytics-card">
                    <div class="signalkit-card-header">
                        <h2><?php esc_html_e('Preferred Source Banner', 'signalkit'); ?></h2>
                        <button type="button" 
                                class="button button-secondary signalkit-reset-analytics" 
                                data-banner-type="preferred">
                            <?php esc_html_e('Reset', 'signalkit'); ?>
                        </button>
                    </div>
                    <div class="signalkit-stats-grid">
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($preferred_analytics['impressions'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Impressions', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon">👆</div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($preferred_analytics['clicks'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Clicks', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box signalkit-stat-highlight">
                            <div class="signalkit-stat-icon">📈</div>
                            <div class="signalkit-stat-value"><?php echo esc_html($preferred_analytics['ctr'] ?? 0); ?>%</div>
                            <div class="signalkit-stat-label"><?php esc_html_e('CTR', 'signalkit'); ?></div>
                        </div>
                        <div class="signalkit-stat-box">
                            <div class="signalkit-stat-icon" aria-hidden="true"><span class="dashicons dashicons-dismiss"></span></div>
                            <div class="signalkit-stat-value"><?php echo esc_html(number_format($preferred_analytics['dismissals'] ?? 0)); ?></div>
                            <div class="signalkit-stat-label"><?php esc_html_e('Dismissals', 'signalkit'); ?></div>
                        </div>
                    </div>
                    <?php if (isset($preferred_analytics['last_updated'])): ?>
                        <div class="signalkit-last-updated">
                            <?php
                            printf(
                                /* translators: %s: Last updated date/time */
                                esc_html__('Last updated: %s', 'signalkit'),
                                esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($preferred_analytics['last_updated'])))
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        <?php
    }
}