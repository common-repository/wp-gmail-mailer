<?php
/*
Plugin Name: WP Gmail Mailer
Version: 1.0.2
Plugin URI: https://noorsplugin.com/gmail-mailer-plugin-for-wordpress/
Author: naa986
Author URI: https://noorsplugin.com/
Description: Send email using your Gmail account in WordPress
Text Domain: wp-gmail-mailer
Domain Path: /languages 
 */

if (!defined('ABSPATH')){
    exit;
}

class GMAIL_MAILER {
    
    var $plugin_version = '1.0.2';
    var $phpmailer_version = '6.5.0';
    var $plugin_url;
    var $plugin_path;
    
    function __construct() {
        define('GMAIL_MAILER_VERSION', $this->plugin_version);
        define('GMAIL_MAILER_SITE_URL', site_url());
        define('GMAIL_MAILER_HOME_URL', home_url());
        define('GMAIL_MAILER_URL', $this->plugin_url());
        define('GMAIL_MAILER_PATH', $this->plugin_path());
        $this->plugin_includes();
        $this->loader_operations();
    }

    function plugin_includes() {

    }

    function loader_operations() {
        if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        add_action('admin_menu', array($this, 'options_menu'));
        add_action('admin_notices', 'gmail_mailer_admin_notice');
        add_filter('pre_wp_mail', 'gmail_mailer_pre_wp_mail', 11, 2);
    }
    
    function plugins_loaded_handler()
    {
        load_plugin_textdomain('wp-gmail-mailer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
    }

    function plugin_url() {
        if ($this->plugin_url)
            return $this->plugin_url;
        return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
    }

    function plugin_path() {
        if ($this->plugin_path)
            return $this->plugin_path;
        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }

    function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
            $links[] = '<a href="options-general.php?page=gmail-mailer-settings">'.__('Settings', 'wp-gmail-mailer').'</a>';
        }
        return $links;
    }
    
    function options_menu() {
        add_options_page(__('Gmail Mailer', 'wp-gmail-mailer'), __('Gmail Mailer', 'wp-gmail-mailer'), 'manage_options', 'gmail-mailer-settings', array($this, 'options_page'));
    }

    function options_page() {
        $plugin_tabs = array(
            'gmail-mailer-settings' => __('General', 'wp-gmail-mailer'),
            'gmail-mailer-settings&action=test-email' => __('Test Email', 'wp-gmail-mailer'),
            'gmail-mailer-settings&action=server-info' => __('Server Info', 'wp-gmail-mailer'),
        );
        $url = "https://noorsplugin.com/gmail-mailer-plugin-for-wordpress/";
        $link_text = sprintf(wp_kses(__('Please visit the <a target="_blank" href="%s">Gmail Mailer</a> documentation page for usage instructions.', 'wp-gmail-mailer'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
        echo '<div class="wrap"><h2>Gmail Mailer v' . GMAIL_MAILER_VERSION . '</h2>';
        echo '<div class="update-nag">'.$link_text.'</div>';
        if (isset($_GET['page'])) {
            $current = $_GET['page'];
            if (isset($_GET['action'])) {
                $current .= "&action=" . $_GET['action'];
            }
        }
        $content = '';
        $content .= '<h2 class="nav-tab-wrapper">';
        foreach ($plugin_tabs as $location => $tabname) {
            if ($current == $location) {
                $class = ' nav-tab-active';
            } else {
                $class = '';
            }
            $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
        }
        $content .= '</h2>';
        echo $content;
                
        if(isset($_GET['action']) && $_GET['action'] == 'test-email'){            
            $this->test_email_settings();
        }
        else if(isset($_GET['action']) && $_GET['action'] == 'server-info'){            
            $this->server_info_settings();
        }
        else{
            $this->general_settings();
        }
        echo '</div>';
    }
    
    function test_email_settings(){
        if(isset($_POST['gmail_mailer_send_test_email'])){
            $to = '';
            if(isset($_POST['gmail_mailer_to_email']) && !empty($_POST['gmail_mailer_to_email'])){
                $to = sanitize_text_field($_POST['gmail_mailer_to_email']);
            }
            $subject = '';
            if(isset($_POST['gmail_mailer_email_subject']) && !empty($_POST['gmail_mailer_email_subject'])){
                $subject = sanitize_text_field($_POST['gmail_mailer_email_subject']);
            }
            $message = '';
            if(isset($_POST['gmail_mailer_email_body']) && !empty($_POST['gmail_mailer_email_body'])){
                $message = sanitize_text_field($_POST['gmail_mailer_email_body']);
            }
            wp_mail($to, $subject, $message);           
        }
        ?>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('gmail_mailer_test_email'); ?>

            <table class="form-table">

                <tbody>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_mailer_to_email"><?php _e('To', 'wp-gmail-mailer');?></label></th>
                        <td><input name="gmail_mailer_to_email" type="text" id="gmail_mailer_to_email" value="" class="regular-text">
                            <p class="description"><?php _e('Email address of the recipient', 'wp-gmail-mailer');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_mailer_email_subject"><?php _e('Subject', 'wp-gmail-mailer');?></label></th>
                        <td><input name="gmail_mailer_email_subject" type="text" id="gmail_mailer_email_subject" value="" class="regular-text">
                            <p class="description"><?php _e('Subject of the email', 'wp-gmail-mailer');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_mailer_email_body"><?php _e('Message', 'wp-gmail-mailer');?></label></th>
                        <td><textarea name="gmail_mailer_email_body" id="gmail_mailer_email_body" rows="6"></textarea>
                            <p class="description"><?php _e('Email body', 'wp-gmail-mailer');?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="gmail_mailer_send_test_email" id="gmail_mailer_send_test_email" class="button button-primary" value="<?php _e('Send Email', 'wp-gmail-mailer');?>"></p>
        </form>
        
        <?php
    }   
    
    function server_info_settings()
    {
        $server_info = '';
        $server_info .= sprintf('OS: %s%s', php_uname(), PHP_EOL);
        $server_info .= sprintf('PHP version: %s%s', PHP_VERSION, PHP_EOL);
        $server_info .= sprintf('WordPress version: %s%s', get_bloginfo('version'), PHP_EOL);
        $server_info .= sprintf('WordPress multisite: %s%s', (is_multisite() ? 'Yes' : 'No'), PHP_EOL);
        $openssl_status = 'Available';
        $openssl_text = '';
        if(!extension_loaded('openssl') && !defined('OPENSSL_ALGO_SHA1')){
            $openssl_status = 'Not available';
            $openssl_text = ' (openssl extension is required in order to use any kind of encryption like TLS or SSL)';
        }      
        $server_info .= sprintf('openssl: %s%s%s', $openssl_status, $openssl_text, PHP_EOL);
        $server_info .= sprintf('allow_url_fopen: %s%s', (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled'), PHP_EOL);
        $stream_socket_client_status = 'Not Available';
        $fsockopen_status = 'Not Available';
        $socket_enabled = false;
        if(function_exists('stream_socket_client')){
            $stream_socket_client_status = 'Available';
            $socket_enabled = true;
        }
        if(function_exists('fsockopen')){
            $fsockopen_status = 'Available';
            $socket_enabled = true;
        }
        $socket_text = '';
        if(!$socket_enabled){
            $socket_text = ' (In order to make a SMTP connection your server needs to have either stream_socket_client or fsockopen)';
        }
        $server_info .= sprintf('stream_socket_client: %s%s', $stream_socket_client_status, PHP_EOL);
        $server_info .= sprintf('fsockopen: %s%s%s', $fsockopen_status, $socket_text, PHP_EOL);
        ?>
        <textarea rows="10" cols="50" class="large-text code"><?php echo $server_info;?></textarea>
        <?php
    }

    function general_settings() {
        
        if (isset($_POST['gmail_mailer_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'gmail_mailer_general_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $smtp_username = '';
            if(isset($_POST['smtp_username']) && !empty($_POST['smtp_username'])){
                $smtp_username = sanitize_text_field($_POST['smtp_username']);
            }
            $smtp_password = '';
            if(isset($_POST['smtp_password']) && !empty($_POST['smtp_password'])){             
                $smtp_password = sanitize_text_field($_POST['smtp_password']);
                $smtp_password = wp_unslash($smtp_password); // This removes slash (automatically added by WordPress) from the password when apostrophe is present
                $smtp_password = base64_encode($smtp_password);
            }
            $type_of_encryption = '';
            if(isset($_POST['type_of_encryption']) && !empty($_POST['type_of_encryption'])){
                $type_of_encryption = sanitize_text_field($_POST['type_of_encryption']);
            }
            $smtp_port = '';
            if(isset($_POST['smtp_port']) && !empty($_POST['smtp_port'])){
                $smtp_port = sanitize_text_field($_POST['smtp_port']);
            }
            $from_email = '';
            if(isset($_POST['from_email']) && !empty($_POST['from_email'])){
                $from_email = sanitize_email($_POST['from_email']);
            }
            $from_name = '';
            if(isset($_POST['from_name']) && !empty($_POST['from_name'])){
                $from_name = sanitize_text_field(stripslashes($_POST['from_name']));
            }
            $disable_ssl_verification = '';
            if(isset($_POST['disable_ssl_verification']) && !empty($_POST['disable_ssl_verification'])){
                $disable_ssl_verification = sanitize_text_field($_POST['disable_ssl_verification']);
            }
            $options = array();
            $options['smtp_username'] = $smtp_username;
            if(!empty($smtp_password)){
                $options['smtp_password'] = $smtp_password;
            }
            $options['type_of_encryption'] = $type_of_encryption;
            $options['smtp_port'] = $smtp_port;
            $options['from_email'] = $from_email;
            $options['from_name'] = $from_name;
            $options['disable_ssl_verification'] = $disable_ssl_verification;
            gmail_mailer_update_option($options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved!', 'wp-gmail-mailer');
            echo '</strong></p></div>';
        }       
        
        $options = gmail_mailer_get_option();
        if(!is_array($options)){
            $options = array();
            $options['smtp_username'] = '';
            $options['smtp_password'] = '';
            $options['type_of_encryption'] = '';
            $options['smtp_port'] = '';
            $options['from_email'] = '';
            $options['from_name'] = '';
            $options['disable_ssl_verification'] = '';
        }
        
        // Avoid warning notice since this option was added later
        if(!isset($options['disable_ssl_verification'])){
            $options['disable_ssl_verification'] = '';
        }
        
        $smtp_password = '';
        if(isset($options['smtp_password']) && !empty($options['smtp_password'])){
            $smtp_password = base64_decode($options['smtp_password']);
        }
        ?>

        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('gmail_mailer_general_settings'); ?>

            <table class="form-table">

                <tbody>                   
                    
                    <tr valign="top">
                        <th scope="row"><label for="smtp_username"><?php _e('Gmail Username', 'wp-gmail-mailer');?></label></th>
                        <td><input name="smtp_username" type="text" id="smtp_username" value="<?php echo $options['smtp_username']; ?>" class="regular-text code">
                            <p class="description"><?php _e('Your Gmail Account Username.', 'wp-gmail-mailer');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="smtp_password"><?php _e('Gmail Password', 'wp-gmail-mailer');?></label></th>
                        <td><input name="smtp_password" type="password" id="smtp_password" value="" class="regular-text code">
                            <p class="description"><?php _e('Your Gmail Account Password. The saved password is not shown for security reasons. If you do not want to update the saved password, you can leave this field empty when updating other options.', 'wp-gmail-mailer');?></p></td>
                    </tr>
                    
                    <tr>
                    <th scope="row"><label for="type_of_encryption"><?php _e('Type of Encryption', 'wp-gmail-mailer');?></label></th>
                    <td>
                        <select name="type_of_encryption" id="type_of_encryption">
                            <option value="tls" <?php echo selected( $options['type_of_encryption'], 'tls', false );?>><?php _e('TLS', 'wp-gmail-mailer');?></option>
                            <option value="ssl" <?php echo selected( $options['type_of_encryption'], 'ssl', false );?>><?php _e('SSL', 'wp-gmail-mailer');?></option>
                            <option value="none" <?php echo selected( $options['type_of_encryption'], 'none', false );?>><?php _e('No Encryption', 'wp-gmail-mailer');?></option>
                        </select>
                        <p class="description"><?php _e('The encryption which will be used when sending an email (recommended: TLS).', 'wp-gmail-mailer');?></p>
                    </td>
                    </tr>                   
                    
                    <tr valign="top">
                        <th scope="row"><label for="smtp_port"><?php _e('SMTP Port', 'wp-gmail-mailer');?></label></th>
                        <td><input name="smtp_port" type="text" id="smtp_port" value="<?php echo $options['smtp_port']; ?>" class="regular-text code">
                            <p class="description"><?php _e('The port which will be used when sending an email (587/465/25). If you choose TLS it should be set to 587. For SSL use port 465 instead.', 'wp-gmail-mailer');?></p></td>
                    </tr>                                      
                    
                    <tr valign="top">
                        <th scope="row"><label for="from_email"><?php _e('From Email Address', 'wp-gmail-mailer');?></label></th>
                        <td><input name="from_email" type="text" id="from_email" value="<?php echo $options['from_email']; ?>" class="regular-text code">
                            <p class="description"><?php _e('The email address which will be used as the From Address if it is not supplied to the mail function.', 'wp-gmail-mailer');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="from_name"><?php _e('From Name', 'wp-gmail-mailer');?></label></th>
                        <td><input name="from_name" type="text" id="from_name" value="<?php echo $options['from_name']; ?>" class="regular-text code">
                            <p class="description"><?php _e('The name which will be used as the From Name if it is not supplied to the mail function.', 'wp-gmail-mailer');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="disable_ssl_verification"><?php _e('Disable SSL Certificate Verification', 'wp-gmail-mailer');?></label></th>
                        <td><input name="disable_ssl_verification" type="checkbox" id="disable_ssl_verification" <?php checked($options['disable_ssl_verification'], 1); ?> value="1">
                            <p class="description"><?php _e('As of PHP 5.6 you will get a warning/error if the SSL certificate on the server is not properly configured. You can check this option to disable that default behaviour. Please note that PHP 5.6 made this change for a good reason. So you should get your host to fix the SSL configurations instead of bypassing it', 'wp-gmail-mailer');?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="gmail_mailer_update_settings" id="gmail_mailer_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'wp-gmail-mailer')?>"></p>
        </form>
        
        <?php
        }           
}

function gmail_mailer_get_option(){
    $options = get_option('gmail_mailer_options');
    return $options;
}

function gmail_mailer_update_option($options){
    update_option('gmail_mailer_options', $options);
}

function gmail_mailer_admin_notice() {        
    if(!is_gmail_mailer_configured()){
        ?>
        <div class="error">
            <p><?php _e('Gmail Mailer plugin cannot send email until you enter your credentials in the settings.', 'wp-gmail-mailer'); ?></p>
        </div>
        <?php
    }
}

function is_gmail_mailer_configured() {
    $options = gmail_mailer_get_option();
    $smtp_configured = true;
    if(!isset($options['smtp_username']) || empty($options['smtp_username'])){
        $smtp_configured = false;
    }
    if(!isset($options['smtp_password']) || empty($options['smtp_password'])){
        $smtp_configured = false;
    }
    if(!isset($options['type_of_encryption']) || empty($options['type_of_encryption'])){
        $smtp_configured = false;
    }
    if(!isset($options['smtp_port']) || empty($options['smtp_port'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_email']) || empty($options['from_email'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_name']) || empty($options['from_name'])){
        $smtp_configured = false;
    }
    return $smtp_configured;
}

$GLOBALS['gmail_mailer'] = new GMAIL_MAILER();

function gmail_mailer_pre_wp_mail($null, $atts)
{
    if ( isset( $atts['to'] ) ) {
            $to = $atts['to'];
    }

    if ( ! is_array( $to ) ) {
            $to = explode( ',', $to );
    }

    if ( isset( $atts['subject'] ) ) {
            $subject = $atts['subject'];
    }

    if ( isset( $atts['message'] ) ) {
            $message = $atts['message'];
    }

    if ( isset( $atts['headers'] ) ) {
            $headers = $atts['headers'];
    }

    if ( isset( $atts['attachments'] ) ) {
            $attachments = $atts['attachments'];
    }

    if ( ! is_array( $attachments ) ) {
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
    }
    
    $options = gmail_mailer_get_option();
    
    global $phpmailer;

    // (Re)create it, if it's gone missing.
    if ( ! ( $phpmailer instanceof PHPMailer\PHPMailer\PHPMailer ) ) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
            $phpmailer = new PHPMailer\PHPMailer\PHPMailer( true );

            $phpmailer::$validator = static function ( $email ) {
                    return (bool) is_email( $email );
            };
    }

    // Headers.
    $cc       = array();
    $bcc      = array();
    $reply_to = array();

    if ( empty( $headers ) ) {
            $headers = array();
    } else {
            if ( ! is_array( $headers ) ) {
                    // Explode the headers out, so this function can take
                    // both string headers and an array of headers.
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                    $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents.
            if ( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers.
                    foreach ( (array) $tempheaders as $header ) {
                            if ( strpos( $header, ':' ) === false ) {
                                    if ( false !== stripos( $header, 'boundary=' ) ) {
                                            $parts    = preg_split( '/boundary=/i', trim( $header ) );
                                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                                    }
                                    continue;
                            }
                            // Explode them out.
                            list( $name, $content ) = explode( ':', trim( $header ), 2 );

                            // Cleanup crew.
                            $name    = trim( $name );
                            $content = trim( $content );

                            switch ( strtolower( $name ) ) {
                                    // Mainly for legacy -- process a "From:" header if it's there.
                                    case 'from':
                                            $bracket_pos = strpos( $content, '<' );
                                            if ( false !== $bracket_pos ) {
                                                    // Text before the bracketed email is the "From" name.
                                                    if ( $bracket_pos > 0 ) {
                                                            $from_name = substr( $content, 0, $bracket_pos - 1 );
                                                            $from_name = str_replace( '"', '', $from_name );
                                                            $from_name = trim( $from_name );
                                                    }

                                                    $from_email = substr( $content, $bracket_pos + 1 );
                                                    $from_email = str_replace( '>', '', $from_email );
                                                    $from_email = trim( $from_email );

                                                    // Avoid setting an empty $from_email.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $from_email = trim( $content );
                                            }
                                            break;
                                    case 'content-type':
                                            if ( strpos( $content, ';' ) !== false ) {
                                                    list( $type, $charset_content ) = explode( ';', $content );
                                                    $content_type                   = trim( $type );
                                                    if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                                            $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                                    } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                                            $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                                            $charset  = '';
                                                    }

                                                    // Avoid setting an empty $content_type.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $content_type = trim( $content );
                                            }
                                            break;
                                    case 'cc':
                                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                            break;
                                    case 'bcc':
                                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                            break;
                                    case 'reply-to':
                                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                                            break;
                                    default:
                                            // Add it to our grand headers array.
                                            $headers[ trim( $name ) ] = trim( $content );
                                            break;
                            }
                    }
            }
    }

    // Empty out the values that may be set.
    $phpmailer->clearAllRecipients();
    $phpmailer->clearAttachments();
    $phpmailer->clearCustomHeaders();
    $phpmailer->clearReplyTos();

    // Set "From" name and email.

    // If we don't have a name from the input headers.
    if ( ! isset( $from_name ) ) {
            $from_name = $options['from_name'];//'WordPress';
    }

    /*
     * If we don't have an email from the input headers, default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist,
     * but there's no easy alternative. Defaulting to admin_email might appear to be
     * another option, but some hosts may refuse to relay mail from an unknown domain.
     * See https://core.trac.wordpress.org/ticket/5007.
     */
    if ( ! isset( $from_email ) ) {
            // Get the site domain and get rid of www.
            $sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
            if ( 'www.' === substr( $sitename, 0, 4 ) ) {
                    $sitename = substr( $sitename, 4 );
            }

            $from_email = $options['from_email'];//'wordpress@' . $sitename;
    }

    /**
     * Filters the email address to send from.
     *
     * @since 2.2.0
     *
     * @param string $from_email Email address to send from.
     */
    $from_email = apply_filters( 'wp_mail_from', $from_email );

    /**
     * Filters the name to associate with the "from" email address.
     *
     * @since 2.3.0
     *
     * @param string $from_name Name associated with the "from" email address.
     */
    $from_name = apply_filters( 'wp_mail_from_name', $from_name );

    try {
            $phpmailer->setFrom( $from_email, $from_name, false );
    } catch ( PHPMailer\PHPMailer\Exception $e ) {
            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This filter is documented in wp-includes/pluggable.php */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
    }

    // Set mail's subject and body.
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;

    // Set destination addresses, using appropriate methods for handling addresses.
    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

    foreach ( $address_headers as $address_header => $addresses ) {
            if ( empty( $addresses ) ) {
                    continue;
            }

            foreach ( (array) $addresses as $address ) {
                    try {
                            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                            $recipient_name = '';

                            if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                                    if ( count( $matches ) == 3 ) {
                                            $recipient_name = $matches[1];
                                            $address        = $matches[2];
                                    }
                            }

                            switch ( $address_header ) {
                                    case 'to':
                                            $phpmailer->addAddress( $address, $recipient_name );
                                            break;
                                    case 'cc':
                                            $phpmailer->addCc( $address, $recipient_name );
                                            break;
                                    case 'bcc':
                                            $phpmailer->addBcc( $address, $recipient_name );
                                            break;
                                    case 'reply_to':
                                            $phpmailer->addReplyTo( $address, $recipient_name );
                                            break;
                            }
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    // Tell PHPMailer to use SMTP
    $phpmailer->isSMTP(); //$phpmailer->isMail();
    // Set the hostname of the mail server
    $phpmailer->Host = 'smtp.gmail.com';
    // Whether to use SMTP authentication
    $phpmailer->SMTPAuth = true;
    // SMTP username
    $phpmailer->Username = $options['smtp_username'];
    // SMTP password
    $phpmailer->Password = base64_decode($options['smtp_password']);
    // Whether to use encryption
    $type_of_encryption = $options['type_of_encryption'];
    if($type_of_encryption=="none"){
        $type_of_encryption = '';  
    }
    $phpmailer->SMTPSecure = $type_of_encryption;
    // SMTP port
    $phpmailer->Port = $options['smtp_port'];  

    // Whether to enable TLS encryption automatically if a server supports it
    $phpmailer->SMTPAutoTLS = false;
    //enable debug when sending a test mail
    if(isset($_POST['gmail_mailer_send_test_email'])){
        $phpmailer->SMTPDebug = 4;
        // Ask for HTML-friendly debug output
        $phpmailer->Debugoutput = 'html';
    }

    //disable ssl certificate verification if checked
    if(isset($options['disable_ssl_verification']) && !empty($options['disable_ssl_verification'])){
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
    // Set Content-Type and charset.

    // If we don't have a content-type from the input headers.
    if ( ! isset( $content_type ) ) {
            $content_type = 'text/plain';
    }

    /**
     * Filters the wp_mail() content type.
     *
     * @since 2.3.0
     *
     * @param string $content_type Default wp_mail() content type.
     */
    $content_type = apply_filters( 'wp_mail_content_type', $content_type );

    $phpmailer->ContentType = $content_type;

    // Set whether it's plaintext, depending on $content_type.
    if ( 'text/html' === $content_type ) {
            $phpmailer->isHTML( true );
    }

    // If we don't have a charset from the input headers.
    if ( ! isset( $charset ) ) {
            $charset = get_bloginfo( 'charset' );
    }

    /**
     * Filters the default wp_mail() charset.
     *
     * @since 2.3.0
     *
     * @param string $charset Default email charset.
     */
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

    // Set custom headers.
    if ( ! empty( $headers ) ) {
            foreach ( (array) $headers as $name => $content ) {
                    // Only add custom headers not added automatically by PHPMailer.
                    if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ), true ) ) {
                            try {
                                    $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                            } catch ( PHPMailer\PHPMailer\Exception $e ) {
                                    continue;
                            }
                    }
            }

            if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
                    $phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
            }
    }

    if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                    try {
                            $phpmailer->addAttachment( $attachment );
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    /**
     * Fires after PHPMailer is initialized.
     *
     * @since 2.2.0
     *
     * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
     */
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

    // Send!
    try {
            return $phpmailer->send();
    } catch ( PHPMailer\PHPMailer\Exception $e ) {

            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a PHPMailer\PHPMailer\Exception is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
    }

}
