<?php
/**
 * Plugin Name: WABot — WooCommerce Integration
 * Plugin URI: https://wabot.test
 * Description: Hubungkan WooCommerce ke WABot untuk notifikasi order otomatis via WhatsApp, Telegram, Instagram, dan channel lainnya.
 * Version: 1.0.0
 * Author: WABot
 * License: GPL-2.0+
 * Requires PHP: 7.4
 * Requires at minimum WooCommerce: 5.0
 * Text Domain: wabot-woocommerce
 */

if (!defined('ABSPATH')) exit;

define('WABOT_WC_VERSION', '1.0.0');
define('WABOT_WC_DIR', plugin_dir_path(__FILE__));
define('WABOT_WC_URL', plugin_dir_url(__FILE__));

class WABot_WooCommerce
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        // Order status change hooks
        $events = get_option('wabot_wc_events', ['processing', 'completed', 'cancelled', 'refunded']);
        if (in_array('processing', $events)) {
            add_action('woocommerce_order_status_processing', [$this, 'onOrderProcessing'], 10, 2);
        }
        if (in_array('completed', $events)) {
            add_action('woocommerce_order_status_completed', [$this, 'onOrderCompleted'], 10, 2);
        }
        if (in_array('cancelled', $events)) {
            add_action('woocommerce_order_status_cancelled', [$this, 'onOrderCancelled'], 10, 2);
        }
        if (in_array('refunded', $events)) {
            add_action('woocommerce_order_status_refunded', [$this, 'onOrderRefunded'], 10, 2);
        }
        if (in_array('pending', $events)) {
            add_action('woocommerce_order_status_pending', [$this, 'onOrderPending'], 10, 2);
        }
        if (in_array('on-hold', $events)) {
            add_action('woocommerce_order_status_on-hold', [$this, 'onOrderOnHold'], 10, 2);
        }

        // New order (any status)
        if (in_array('new_order', $events)) {
            add_action('woocommerce_new_order', [$this, 'onNewOrder'], 10, 2);
        }

        // Abandoned cart
        if (get_option('wabot_wc_abandoned_cart', false)) {
            add_action('woocommerce_add_to_cart', [$this, 'cartUpdated']);
            add_action('woocommerce_cart_item_removed', [$this, 'cartUpdated']);
            add_action('woocommerce_cart_item_restored', [$this, 'cartUpdated']);
            add_action('woocommerce_cart_updated', [$this, 'cartUpdated']);
        }
    }

    public function addAdminMenu()
    {
        add_options_page(
            'WABot WooCommerce',
            'WABot WC',
            'manage_options',
            'wabot-woocommerce',
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings()
    {
        register_setting('wabot_wc_settings', 'wabot_wc_webhook_url');
        register_setting('wabot_wc_settings', 'wabot_wc_api_key');
        register_setting('wabot_wc_settings', 'wabot_wc_events');
        register_setting('wabot_wc_settings', 'wabot_wc_abandoned_cart');
        register_setting('wabot_wc_settings', 'wabot_wc_cart_delay');
        register_setting('wabot_wc_settings', 'wabot_wc_template_processing');
        register_setting('wabot_wc_settings', 'wabot_wc_template_completed');
        register_setting('wabot_wc_settings', 'wabot_wc_template_cancelled');
        register_setting('wabot_wc_settings', 'wabot_wc_notify_phone_meta');
        register_setting('wabot_wc_settings', 'wabot_wc_notify_admin');
        register_setting('wabot_wc_settings', 'wabot_wc_admin_phone');
    }

    public function enqueueAssets($hook)
    {
        if ($hook !== 'settings_page_wabot-woocommerce') return;
        wp_enqueue_style('wabot-wc-admin', WABOT_WC_URL . 'assets/admin.css', [], WABOT_WC_VERSION);
    }

    // === Order Hooks ===

    public function onNewOrder($orderId, $order)
    {
        $this->sendNotification($order, 'new_order');
    }

    public function onOrderProcessing($orderId, $order)
    {
        $this->sendNotification($order, 'processing');
    }

    public function onOrderCompleted($orderId, $order)
    {
        $this->sendNotification($order, 'completed');
    }

    public function onOrderCancelled($orderId, $order)
    {
        $this->sendNotification($order, 'cancelled');
    }

    public function onOrderRefunded($orderId, $order)
    {
        $this->sendNotification($order, 'refunded');
    }

    public function onOrderPending($orderId, $order)
    {
        $this->sendNotification($order, 'pending');
    }

    public function onOrderOnHold($orderId, $order)
    {
        $this->sendNotification($order, 'on-hold');
    }

    // === Core ===

    private function sendNotification($order, $status)
    {
        $webhookUrl = get_option('wabot_wc_webhook_url');
        $apiKey = get_option('wabot_wc_api_key');

        if (empty($webhookUrl)) return;

        $customerPhone = $this->getCustomerPhone($order);

        $payload = [
            'event' => 'order.' . $status,
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'status' => $status,
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'payment_method' => $order->get_payment_method_title(),
            'shipping_method' => $order->get_shipping_method(),
            'customer' => [
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $customerPhone,
                'address' => $order->get_billing_address_1() . ', ' . $order->get_billing_city(),
            ],
            'items' => $this->getOrderItems($order),
            'billing' => $order->get_address('billing'),
            'shipping' => $order->get_address('shipping'),
            'timestamp' => current_time('timestamp'),
        ];

        $args = [
            'body' => json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key' => $apiKey,
            ],
            'timeout' => 15,
        ];

        $response = wp_remote_post($webhookUrl, $args);

        if (is_wp_error($response)) {
            error_log('[WABot WC] Webhook failed: ' . $response->get_error_message());
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log('[WABot WC] Webhook returned HTTP ' . $code);
        }

        // Send admin notification
        if (get_option('wabot_wc_notify_admin', false)) {
            $this->sendAdminNotification($order, $status, $webhookUrl, $apiKey);
        }
    }

    private function sendAdminNotification($order, $status, $webhookUrl, $apiKey)
    {
        $adminPhone = get_option('wabot_wc_admin_phone');
        if (empty($adminPhone)) return;

        $statusLabels = [
            'new_order' => 'Pesanan Baru',
            'pending' => 'Pending',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Refund',
            'on-hold' => 'Ditahan',
        ];

        $label = $statusLabels[$status] ?? $status;
        $message = "*{$label} #{$order->get_order_number()}*\n";
        $message .= "Pelanggan: {$order->get_billing_first_name()}\n";
        $message .= "Total: " . wc_price($order->get_total()) . "\n";
        $message .= "Pembayaran: {$order->get_payment_method_title()}\n";
        $message .= site_url("/wp-admin/post.php?post={$order->get_id()}&action=edit");

        wp_remote_post($webhookUrl, [
            'body' => json_encode([
                'event' => 'admin.notification',
                'phone' => $adminPhone,
                'message' => $message,
                'type' => 'text',
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key' => $apiKey,
            ],
            'timeout' => 15,
        ]);
    }

    private function getCustomerPhone($order)
    {
        $metaKey = get_option('wabot_wc_notify_phone_meta', 'billing_phone');
        $phone = '';

        // Try WooCommerce checkout field
        if ($metaKey === 'billing_phone') {
            $phone = $order->get_billing_phone();
        }

        // Try custom user meta
        if (empty($phone)) {
            $userId = $order->get_user_id();
            if ($userId) {
                $phone = get_user_meta($userId, $metaKey, true);
            }
        }

        // Try order meta
        if (empty($phone)) {
            $phone = $order->get_meta($metaKey);
        }

        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return $phone;
    }

    private function getOrderItems($order)
    {
        $items = [];
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $items[] = [
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total(),
                'sku' => $product ? $product->get_sku() : '',
                'image_url' => $product ? wp_get_attachment_url($product->get_image_id()) : '',
            ];
        }
        return $items;
    }

    // === Abandoned Cart ===

    public function cartUpdated()
    {
        if (!is_user_logged_in()) return;

        $userId = get_current_user_id();
        $delay = (int) get_option('wabot_wc_cart_delay', 30);
        $transientKey = 'wabot_wc_cart_' . $userId;

        if (get_transient($transientKey)) return;

        set_transient($transientKey, true, $delay * 60);

        // Schedule cart recovery check
        if (!wp_next_scheduled('wabot_wc_cart_recovery', [$userId])) {
            wp_schedule_single_event(time() + ($delay * 60), 'wabot_wc_cart_recovery', [$userId]);
        }
    }

    // === Settings Page ===

    public function renderSettingsPage()
    {
        $statuses = wc_get_order_statuses();
        $savedEvents = (array) get_option('wabot_wc_events', ['processing', 'completed']);

        ?>
        <div class="wrap wabot-wc-wrap">
            <h1>
                <img src="<?php echo WABOT_WC_URL; ?>assets/icon.svg" width="32" style="vertical-align: middle; margin-right: 8px;">
                WABot — WooCommerce Integration
            </h1>
            <p>Hubungkan WooCommerce ke WABot untuk notifikasi order otomatis via WhatsApp, Telegram, dan multi-channel.</p>

            <form method="post" action="options.php">
                <?php settings_fields('wabot_wc_settings'); ?>

                <div class="wabot-wc-card">
                    <h2>Koneksi WABot</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="wabot_wc_webhook_url">Webhook URL</label></th>
                            <td>
                                <input type="url" id="wabot_wc_webhook_url" name="wabot_wc_webhook_url"
                                    value="<?php echo esc_attr(get_option('wabot_wc_webhook_url')); ?>"
                                    class="regular-text" placeholder="https://wabot.test/webhook/store/1" required>
                                <p class="description">URL webhook dari WABot. Dapatkan di menu <strong>Integrations → E-Commerce</strong> di dashboard WABot.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wabot_wc_api_key">API Key</label></th>
                            <td>
                                <input type="password" id="wabot_wc_api_key" name="wabot_wc_api_key"
                                    value="<?php echo esc_attr(get_option('wabot_wc_api_key')); ?>"
                                    class="regular-text" placeholder="API key WABot" required>
                                <p class="description">API key untuk autentikasi. Diperlukan untuk mengirim notifikasi.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="wabot-wc-card">
                    <h2>Trigger Event</h2>
                    <p>Pilih status order mana yang akan mengirim notifikasi ke WABot:</p>
                    <div class="wabot-wc-checklist">
                        <label><input type="checkbox" name="wabot_wc_events[]" value="new_order" <?php checked(in_array('new_order', $savedEvents)); ?>> Pesanan Baru</label>
                        <?php foreach ($statuses as $key => $label): ?>
                            <?php $statusKey = str_replace('wc-', '', $key); ?>
                            <label><input type="checkbox" name="wabot_wc_events[]" value="<?php echo $statusKey; ?>" <?php checked(in_array($statusKey, $savedEvents)); ?>> <?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="wabot-wc-card">
                    <h2>Template Notifikasi</h2>
                    <p>Variabel: <code>{order_number}</code> <code>{customer_name}</code> <code>{total}</code> <code>{payment_method}</code> <code>{item_list}</code> <code>{store_name}</code></p>
                    <table class="form-table">
                        <tr>
                            <th>Processing</th>
                            <td><textarea name="wabot_wc_template_processing" class="large-text" rows="3"><?php echo esc_textarea(get_option('wabot_wc_template_processing', "Halo {customer_name},\n\nPesanan #{order_number} sedang diproses.\nTotal: {total}\n\n{store_name}")) ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Completed</th>
                            <td><textarea name="wabot_wc_template_completed" class="large-text" rows="3"><?php echo esc_textarea(get_option('wabot_wc_template_completed', "Halo {customer_name},\n\nPesanan #{order_number} telah selesai! 🎉\n\n{store_name}")) ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Cancelled</th>
                            <td><textarea name="wabot_wc_template_cancelled" class="large-text" rows="3"><?php echo esc_textarea(get_option('wabot_wc_template_cancelled', "Halo {customer_name},\n\nPesanan #{order_number} telah dibatalkan. Hubungi kami jika ada pertanyaan.\n\n{store_name}")) ?></textarea></td>
                        </tr>
                    </table>
                </div>

                <div class="wabot-wc-card">
                    <h2>Pengaturan Lanjutan</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="wabot_wc_notify_phone_meta">Field Nomor HP</label></th>
                            <td>
                                <input type="text" id="wabot_wc_notify_phone_meta" name="wabot_wc_notify_phone_meta"
                                    value="<?php echo esc_attr(get_option('wabot_wc_notify_phone_meta', 'billing_phone')); ?>" class="regular-text">
                                <p class="description">Meta key untuk nomor telepon customer. Default: <code>billing_phone</code>. Untuk plugin custom field, gunakan meta key-nya.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Notifikasi Admin</th>
                            <td>
                                <label><input type="checkbox" name="wabot_wc_notify_admin" value="1" <?php checked(get_option('wabot_wc_notify_admin')); ?>> Kirim notifikasi ke admin juga</label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wabot_wc_admin_phone">Nomor Admin</label></th>
                            <td>
                                <input type="text" id="wabot_wc_admin_phone" name="wabot_wc_admin_phone"
                                    value="<?php echo esc_attr(get_option('wabot_wc_admin_phone')); ?>" class="regular-text">
                                <p class="description">Nomor WhatsApp admin untuk menerima notifikasi pesanan baru.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Abandoned Cart</th>
                            <td>
                                <label><input type="checkbox" name="wabot_wc_abandoned_cart" value="1" <?php checked(get_option('wabot_wc_abandoned_cart')); ?>> Aktifkan abandoned cart recovery</label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wabot_wc_cart_delay">Delay Cart (menit)</label></th>
                            <td>
                                <input type="number" id="wabot_wc_cart_delay" name="wabot_wc_cart_delay"
                                    value="<?php echo esc_attr(get_option('wabot_wc_cart_delay', 30)); ?>" min="5" step="5" class="small-text">
                                <p class="description">Kirim pengingat setelah X menit cart ditinggalkan.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button('Simpan Pengaturan'); ?>
            </form>

            <div class="wabot-wc-card wabot-wc-help">
                <h2>Cara Setup</h2>
                <ol>
                    <li>Di dashboard WABot, buka <strong>Integrations → E-Commerce</strong></li>
                    <li>Klik <strong>Add Store</strong>, pilih platform <strong>WooCommerce</strong></li>
                    <li>Copy <strong>Webhook URL</strong> dan <strong>API Key</strong> dari WABot</li>
                    <li>Paste di form di atas, pilih event, lalu <strong>Simpan</strong></li>
                    <li>Selesai! Setiap order baru/status berubah akan mengirim notifikasi ke WABot → WhatsApp / Telegram / multi-channel</li>
                </ol>
            </div>
        </div>
        <?php
    }
}

// Init
add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        WABot_WooCommerce::instance();
    }
});

// Activation
register_activation_hook(__FILE__, function () {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('WABot WooCommerce membutuhkan WooCommerce yang aktif. Silakan install dan aktifkan WooCommerce terlebih dahulu.');
    }
});
