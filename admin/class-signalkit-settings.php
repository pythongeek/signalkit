<?php
/**
 * Settings page functionality.
 *
 * @package SignalKit_For_Google
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
     * Add settings page to admin menu.
     */
    public function add_settings_page() {
        add_options_page(
            __('SignalKit for Google Settings', 'signalkit-for-google'),
            __('SignalKit for Google', 'signalkit-for-google'),
            'manage_options',
            'signalkit-settings',
            array($this, 'render_settings_page')
        );
        
        signalkit_log('Settings page added to admin menu');
    }
    
    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'signalkit_settings_group',
            'signalkit_settings',
            array($this, 'sanitize_settings')
        );
        
        // General Settings Section
        add_settings_section(
            'signalkit_general_section',
            __('General Settings', 'signalkit-for-google'),
            array($this, 'general_section_callback'),
            'signalkit-settings'
        );
        
        // Content Settings Section
        add_settings_section(
            'signalkit_content_section',
            __('Content Settings', 'signalkit-for-google'),
            array($this, 'content_section_callback'),
            'signalkit-settings'
        );
        
        // Design Settings Section
        add_settings_section(
            'signalkit_design_section',
            __('Design & Appearance', 'signalkit-for-google'),
            array($this, 'design_section_callback'),
            'signalkit-settings'
        );
        
        // Display Rules Section
        add_settings_section(
            'signalkit_display_section',
            __('Display Rules', 'signalkit-for-google'),
            array($this, 'display_section_callback'),
            'signalkit-settings'
        );
        
        // Analytics Section
        add_settings_section(
            'signalkit_analytics_section',
            __('Analytics', 'signalkit-for-google'),
            array($this, 'analytics_section_callback'),
            'signalkit-settings'
        );
        
        $this->register_all_fields();
        
        signalkit_log('All settings registered');
    }
    
    /**
     * Register all settings fields.
     */
    private function register_all_fields() {
        // Enable/Disable
        add_settings_field(
            'signalkit_enabled',
            __('Enable SignalKit', 'signalkit-for-google'),
            array($this, 'checkbox_field_callback'),
            'signalkit-settings',
            'signalkit_general_section',
            array('field' => 'enabled', 'description' => __('Turn on/off the banner display', 'signalkit-for-google'))
        );
        
        // Site Name
        add_settings_field(
            'signalkit_site_name',
            __('Site Name', 'signalkit-for-google'),
            array($this, 'text_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'site_name', 'description' => __('Your site name to display in the banner', 'signalkit-for-google'))
        );
        
        // Google News URL
        add_settings_field(
            'signalkit_google_news_url',
            __('Google News Publication URL', 'signalkit-for-google'),
            array($this, 'url_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'google_news_url', 'description' => __('Your Google News publication URL', 'signalkit-for-google'), 'placeholder' => 'https://news.google.com/publications/CAAq...')
        );
        
        // Google Preferences URL
        add_settings_field(
            'signalkit_google_preferences_url',
            __('Google Preferences URL', 'signalkit-for-google'),
            array($this, 'url_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'google_preferences_url', 'description' => __('Link to Google preferences page for your site', 'signalkit-for-google'), 'placeholder' => 'https://www.google.com/preferences/source?q=yoursite.com')
        );
        
        // Educational Post URL
        add_settings_field(
            'signalkit_educational_post_url',
            __('Educational Post URL', 'signalkit-for-google'),
            array($this, 'url_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'educational_post_url', 'description' => __('Link to your article about Google preferred sources', 'signalkit-for-google'), 'placeholder' => 'https://yoursite.com/google-preferred-sources')
        );
        
        // Button Text
        add_settings_field(
            'signalkit_button_text',
            __('Button Text', 'signalkit-for-google'),
            array($this, 'text_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'button_text', 'description' => __('Text displayed on the main CTA button', 'signalkit-for-google'))
        );
        
        // Banner Headline
        add_settings_field(
            'signalkit_banner_headline',
            __('Banner Headline', 'signalkit-for-google'),
            array($this, 'textarea_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'banner_headline', 'description' => __('Main headline text. Use [site_name] to insert site name dynamically.', 'signalkit-for-google'))
        );
        
        // Educational Text
        add_settings_field(
            'signalkit_educational_text',
            __('Educational Link Text', 'signalkit-for-google'),
            array($this, 'text_field_callback'),
            'signalkit-settings',
            'signalkit_content_section',
            array('field' => 'educational_text', 'description' => __('Text for the educational link', 'signalkit-for-google'))
        );
        
        // Primary Color
        add_settings_field(
            'signalkit_primary_color',
            __('Primary Color', 'signalkit-for-google'),
            array($this, 'color_field_callback'),
            'signalkit-settings',
            'signalkit_design_section',
            array('field' => 'primary_color', 'description' => __('Main button and accent color', 'signalkit-for-google'))
        );
        
        // Secondary Color
        add_settings_field(
            'signalkit_secondary_color',
            __('Secondary Color', 'signalkit-for-google'),
            array($this, 'color_field_callback'),
            'signalkit-settings',
            'signalkit_design_section',
            array('field' => 'secondary_color', 'description' => __('Background color', 'signalkit-for-google'))
        );
        
        // Accent Color
        add_settings_field(
            'signalkit_accent_color',
            __('Accent Color', 'signalkit-for-google'),
            array($this, 'color_field_callback'),
            'signalkit-settings',
            'signalkit_design_section',
            array('field' => 'accent_color', 'description' => __('Secondary accent color', 'signalkit-for-google'))
        );
        
        // Position
        add_settings_field(
            'signalkit_position',
            __('Banner Position', 'signalkit-for-google'),
            array($this, 'select_field_callback'),
            'signalkit-settings',
            'signalkit_design_section',
            array(
                'field' => 'position',
                'options' => array(
                    'bottom_right' => __('Bottom Right (Fixed)', 'signalkit-for-google'),
                    'bottom_left' => __('Bottom Left (Fixed)', 'signalkit-for-google'),
                    'top_right' => __('Top Right (Fixed)', 'signalkit-for-google'),
                    'top_left' => __('Top Left (Fixed)', 'signalkit-for-google'),
                    'bottom_bar' => __('Bottom Bar (Full Width)', 'signalkit-for-google'),
                    'top_bar' => __('Top Bar (Full Width)', 'signalkit-for-google'),
                ),
                'description' => __('Where to display the banner', 'signalkit-for-google')
            )
        );
        
        // Animation
        add_settings_field(
            'signalkit_animation',
            __('Animation Style', 'signalkit-for-google'),
            array($this, 'select_field_callback'),
            'signalkit-settings',
            'signalkit_design_section',
            array(
                'field' => 'animation',
                'options' => array(
                    'slide_in' => __('Slide In', 'signalkit-for-google'),
                    'fade_in' => __('Fade In', 'signalkit-for-google'),
                    'bounce' => __('Bounce', 'signalkit-for-google'),
                    'none' => __('No Animation', 'signalkit-for-google'),
                ),
                'description' => __('Entrance animation effect', 'signalkit-for-google')
            )
        );
        
        // Dismissible
        add_settings_field(
            'signalkit_dismissible',
            __('Dismissible', 'signalkit-for-google'),
            array($this, 'checkbox_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array('field' => 'dismissible', 'description' => __('Allow users to close the banner', 'signalkit-for-google'))
        );
        
        // Dismiss Duration
        add_settings_field(
            'signalkit_dismiss_duration',
            __('Dismiss Duration (Days)', 'signalkit-for-google'),
            array($this, 'number_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array('field' => 'dismiss_duration', 'description' => __('How many days to hide after dismissal', 'signalkit-for-google'), 'min' => 1, 'max' => 365)
        );
        
        // Show On
        add_settings_field(
            'signalkit_show_on',
            __('Show On', 'signalkit-for-google'),
            array($this, 'select_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array(
                'field' => 'show_on',
                'options' => array(
                    'all' => __('All Pages', 'signalkit-for-google'),
                    'homepage' => __('Homepage Only', 'signalkit-for-google'),
                    'posts' => __('Posts Only', 'signalkit-for-google'),
                    'pages' => __('Pages Only', 'signalkit-for-google'),
                ),
                'description' => __('Where to display the banner', 'signalkit-for-google')
            )
        );
        
        // Show Frequency
        add_settings_field(
            'signalkit_show_frequency',
            __('Show Frequency', 'signalkit-for-google'),
            array($this, 'select_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array(
                'field' => 'show_frequency',
                'options' => array(
                    'always' => __('Every Page Load', 'signalkit-for-google'),
                    'once_per_session' => __('Once Per Session', 'signalkit-for-google'),
                    'once_per_day' => __('Once Per Day', 'signalkit-for-google'),
                ),
                'description' => __('How often to show the banner', 'signalkit-for-google')
            )
        );
        
        // Mobile Enabled
        add_settings_field(
            'signalkit_mobile_enabled',
            __('Show on Mobile', 'signalkit-for-google'),
            array($this, 'checkbox_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array('field' => 'mobile_enabled', 'description' => __('Display banner on mobile devices', 'signalkit-for-google'))
        );
        
        // Desktop Enabled
        add_settings_field(
            'signalkit_desktop_enabled',
            __('Show on Desktop', 'signalkit-for-google'),
            array($this, 'checkbox_field_callback'),
            'signalkit-settings',
            'signalkit_display_section',
            array('field' => 'desktop_enabled', 'description' => __('Display banner on desktop devices', 'signalkit-for-google'))
        );
    }
    
    /**
     * Sanitize settings.
     */
    public function sanitize_settings($input) {
        signalkit_log('Sanitizing settings input', $input);
        
        $sanitized = array();
        
        // Boolean fields
        $boolean_fields = array('enabled', 'dismissible', 'auto_hide', 'mobile_enabled', 'desktop_enabled');
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? true : false;
        }
        
        // Text fields
        $text_fields = array('site_name', 'button_text', 'banner_headline', 'educational_text');
        foreach ($text_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? sanitize_text_field($input[$field]) : '';
        }
        
        // URL fields
        $url_fields = array('google_news_url', 'google_preferences_url', 'educational_post_url');
        foreach ($url_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? esc_url_raw($input[$field]) : '';
        }
        
        // Color fields
        $color_fields = array('primary_color', 'secondary_color', 'accent_color');
        foreach ($color_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? sanitize_hex_color($input[$field]) : '';
        }
        
        // Select fields
        $select_fields = array('position', 'animation', 'show_on', 'show_frequency');
        foreach ($select_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? sanitize_text_field($input[$field]) : '';
        }
        
        // Number fields
        $sanitized['dismiss_duration'] = isset($input['dismiss_duration']) ? absint($input['dismiss_duration']) : 7;
        $sanitized['auto_hide_delay'] = isset($input['auto_hide_delay']) ? absint($input['auto_hide_delay']) : 0;
        
        signalkit_log('Settings sanitized', $sanitized);
        
        return $sanitized;
    }
    
    /**
     * Section callbacks.
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure basic plugin settings.', 'signalkit-for-google') . '</p>';
    }
    
    public function content_section_callback() {
        echo '<p>' . esc_html__('Customize the text and links displayed in your banner.', 'signalkit-for-google') . '</p>';
    }
    
    public function design_section_callback() {
        echo '<p>' . esc_html__('Customize the visual appearance of your banner.', 'signalkit-for-google') . '</p>';
    }
    
    public function display_section_callback() {
        echo '<p>' . esc_html__('Control when and where the banner is displayed.', 'signalkit-for-google') . '</p>';
    }
    
    public function analytics_section_callback() {
        $analytics = SignalKit_Analytics::get_analytics();
        ?>
        <div class="signalkit-analytics-dashboard">
            <p><?php esc_html_e('Track the performance of your banner.', 'signalkit-for-google'); ?></p>
            <div class="signalkit-stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['impressions'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Impressions', 'signalkit-for-google'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['clicks'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Clicks', 'signalkit-for-google'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html($analytics['ctr']); ?>%</div>
                    <div class="stat-label"><?php esc_html_e('CTR', 'signalkit-for-google'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo esc_html(number_format($analytics['dismissals'])); ?></div>
                    <div class="stat-label"><?php esc_html_e('Dismissals', 'signalkit-for-google'); ?></div>
                </div>
            </div>
            <button type="button" class="button button-secondary" id="signalkit-reset-analytics">
                <?php esc_html_e('Reset Analytics', 'signalkit-for-google'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Field callbacks.
     */
    public function checkbox_field_callback($args) {
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
            return;
        }
        
        // Handle analytics reset
        if (isset($_POST['signalkit_reset_analytics']) && check_admin_referer('signalkit_reset_analytics_nonce')) {
            SignalKit_Analytics::reset_analytics();
            add_settings_error('signalkit_messages', 'signalkit_message', __('Analytics reset successfully!', 'signalkit-for-google'), 'updated');
        }
        
        settings_errors('signalkit_messages');
        
        require_once SIGNALKIT_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }
}