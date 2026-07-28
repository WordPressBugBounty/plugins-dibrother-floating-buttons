<?php
/**
 * Plugin Name:       Nút Bấm Liên Hệ Dibrother
 * Description:       Thêm các nút bấm hành động (Gọi, Zalo, Messenger, Lên đầu trang) cố định trên màn hình.
 * Version:           2.1.2
 * Author:            Dibrother
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nut-bam-lien-he-dibrother
 * Requires at least: 5.0
 * Tested up to:      6.8
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Thêm link "Cài đặt" vào danh sách plugin
function dblh_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=nut_bam_lien_he_dibrother">' . __('Cài đặt') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin_basename = plugin_basename(__FILE__);
add_filter("plugin_action_links_{$plugin_basename}", 'dblh_add_settings_link');

// TẠO MENU CÀI ĐẶT TRONG ADMIN
function dblh_add_admin_menu() {
    add_options_page('Nút Bấm Liên Hệ Dibrother', 'Nút Liên Hệ Dibrother', 'manage_options', 'nut_bam_lien_he_dibrother', 'dblh_options_page_html');
}
add_action('admin_menu', 'dblh_add_admin_menu');

// Hàm làm sạch dữ liệu cài đặt để tăng bảo mật
function dblh_options_sanitize($input) {
    $new_input = array();

    $checkboxes = ['enable', 'ripple_effect', 'enable_phone', 'enable_zalo', 'enable_messenger', 'enable_scrolltop'];
    foreach ($checkboxes as $key) {
        if (isset($input[$key]) && $input[$key] == '1') {
            $new_input[$key] = 1;
        }
    }

    if (isset($input['position']) && in_array($input['position'], ['left', 'right'])) {
        $new_input['position'] = $input['position'];
    }

    if (isset($input['phone_number'])) {
        $new_input['phone_number'] = preg_replace('/[^0-9\+]/', '', $input['phone_number']);
    }

    if (isset($input['zalo_link'])) {
        $new_input['zalo_link'] = esc_url_raw($input['zalo_link']);
    }
    if (isset($input['messenger_link'])) {
        $new_input['messenger_link'] = esc_url_raw($input['messenger_link']);
    }

    $colors = ['phone_bg_color', 'zalo_bg_color', 'messenger_bg_color', 'scrolltop_bg_color'];
    foreach ($colors as $key) {
        if (isset($input[$key]) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $input[$key])) {
            $new_input[$key] = $input[$key];
        }
    }

    return $new_input;
}

// ĐĂNG KÝ CÁC TRƯỜNG CÀI ĐẶT
function dblh_settings_init() {
    register_setting('dblh_settings_group', 'dblh_options', 'dblh_options_sanitize');

    add_settings_section('dblh_section_general', 'Cài đặt chung', null, 'nut_bam_lien_he_dibrother');
    add_settings_field('dblh_enable', 'Bật/Tắt Plugin', 'dblh_field_enable_html', 'nut_bam_lien_he_dibrother', 'dblh_section_general');
    add_settings_field('dblh_position', 'Vị trí hiển thị', 'dblh_field_position_html', 'nut_bam_lien_he_dibrother', 'dblh_section_general');
    add_settings_field('dblh_ripple', 'Hiệu ứng sóng', 'dblh_field_ripple_html', 'nut_bam_lien_he_dibrother', 'dblh_section_general');

    add_settings_section('dblh_section_phone', 'Nút Gọi điện', null, 'nut_bam_lien_he_dibrother');
    add_settings_field('dblh_enable_phone', 'Hiển thị nút Gọi điện', 'dblh_field_enable_phone_html', 'nut_bam_lien_he_dibrother', 'dblh_section_phone');
    add_settings_field('dblh_phone_number', 'Số điện thoại', 'dblh_field_phone_number_html', 'nut_bam_lien_he_dibrother', 'dblh_section_phone');
    add_settings_field('dblh_phone_color', 'Màu nền', 'dblh_field_phone_bg_color_html', 'nut_bam_lien_he_dibrother', 'dblh_section_phone');

    add_settings_section('dblh_section_zalo', 'Nút Zalo', null, 'nut_bam_lien_he_dibrother');
    add_settings_field('dblh_enable_zalo', 'Hiển thị nút Zalo', 'dblh_field_enable_zalo_html', 'nut_bam_lien_he_dibrother', 'dblh_section_zalo');
    add_settings_field('dblh_zalo_link', 'Link Zalo', 'dblh_field_zalo_link_html', 'nut_bam_lien_he_dibrother', 'dblh_section_zalo');
    add_settings_field('dblh_zalo_bg_color', 'Màu nền', 'dblh_field_zalo_bg_color_html', 'nut_bam_lien_he_dibrother', 'dblh_section_zalo');

    add_settings_section('dblh_section_messenger', 'Nút Messenger', null, 'nut_bam_lien_he_dibrother');
    add_settings_field('dblh_enable_messenger', 'Hiển thị nút Messenger', 'dblh_field_enable_messenger_html', 'nut_bam_lien_he_dibrother', 'dblh_section_messenger');
    add_settings_field('dblh_messenger_link', 'Link Messenger', 'dblh_field_messenger_link_html', 'nut_bam_lien_he_dibrother', 'dblh_section_messenger');
    add_settings_field('dblh_messenger_bg_color', 'Màu nền', 'dblh_field_messenger_bg_color_html', 'nut_bam_lien_he_dibrother', 'dblh_section_messenger');

    add_settings_section('dblh_section_scroll', 'Nút Lên đầu trang', null, 'nut_bam_lien_he_dibrother');
    add_settings_field('dblh_enable_scrolltop', 'Hiển thị nút Lên đầu trang', 'dblh_field_enable_scrolltop_html', 'nut_bam_lien_he_dibrother', 'dblh_section_scroll');
    add_settings_field('dblh_scrolltop_bg_color', 'Màu nền', 'dblh_field_scrolltop_bg_color_html', 'nut_bam_lien_he_dibrother', 'dblh_section_scroll');
}
add_action('admin_init', 'dblh_settings_init');


// CÁC HÀM CALLBACK ĐỂ RENDER HTML CHO CÁC TRƯỜNG
function dblh_field_enable_html() { $options = get_option('dblh_options'); ?> <input type="checkbox" name="dblh_options[enable]" value="1" <?php checked(isset($options['enable']), 1); ?> /> <em>Chọn để kích hoạt tất cả các nút.</em> <?php }
function dblh_field_position_html() { $options = get_option('dblh_options'); $position = isset($options['position']) ? $options['position'] : 'right'; ?> <label><input type="radio" name="dblh_options[position]" value="right" <?php checked($position, 'right'); ?> /> Góc phải dưới</label><br><label><input type="radio" name="dblh_options[position]" value="left" <?php checked($position, 'left'); ?> /> Góc trái dưới</label> <?php }
function dblh_field_ripple_html() { $options = get_option('dblh_options'); ?> <input type="checkbox" name="dblh_options[ripple_effect]" value="1" <?php checked(isset($options['ripple_effect']), 1); ?> /> <em>Tạo hiệu ứng sóng lan tỏa xung quanh các nút.</em> <?php }
function dblh_field_enable_phone_html() { $options = get_option('dblh_options'); ?><input type="checkbox" name="dblh_options[enable_phone]" value="1" <?php checked(isset($options['enable_phone']), 1); ?> /><?php }
function dblh_field_phone_number_html() { $options = get_option('dblh_options'); ?><input type="text" name="dblh_options[phone_number]" value="<?php echo esc_attr($options['phone_number'] ?? ''); ?>" placeholder="09xxxxxxxx"/><?php }
function dblh_field_phone_bg_color_html() { $options = get_option('dblh_options'); $color = isset($options['phone_bg_color']) ? $options['phone_bg_color'] : '#4CAF50'; ?><input type="color" name="dblh_options[phone_bg_color]" value="<?php echo esc_attr($color); ?>" /><?php }
function dblh_field_enable_zalo_html() { $options = get_option('dblh_options'); ?><input type="checkbox" name="dblh_options[enable_zalo]" value="1" <?php checked(isset($options['enable_zalo']), 1); ?> /><?php }
function dblh_field_zalo_link_html() { $options = get_option('dblh_options'); ?><input type="text" name="dblh_options[zalo_link]" value="<?php echo esc_attr($options['zalo_link'] ?? ''); ?>" placeholder="https://zalo.me/09xxxxxxxx"/><?php }
function dblh_field_zalo_bg_color_html() { $options = get_option('dblh_options'); $color = isset($options['zalo_bg_color']) ? $options['zalo_bg_color'] : '#0068ff'; ?><input type="color" name="dblh_options[zalo_bg_color]" value="<?php echo esc_attr($color); ?>" /><?php }
function dblh_field_enable_messenger_html() { $options = get_option('dblh_options'); ?><input type="checkbox" name="dblh_options[enable_messenger]" value="1" <?php checked(isset($options['enable_messenger']), 1); ?> /><?php }
function dblh_field_messenger_link_html() { $options = get_option('dblh_options'); ?><input type="text" name="dblh_options[messenger_link]" value="<?php echo esc_attr($options['messenger_link'] ?? ''); ?>" placeholder="https://m.me/your.facebook.page"/><?php }
function dblh_field_messenger_bg_color_html() { $options = get_option('dblh_options'); $color = isset($options['messenger_bg_color']) ? $options['messenger_bg_color'] : '#0084ff'; ?><input type="color" name="dblh_options[messenger_bg_color]" value="<?php echo esc_attr($color); ?>" /><?php }
function dblh_field_enable_scrolltop_html() { $options = get_option('dblh_options'); ?><input type="checkbox" name="dblh_options[enable_scrolltop]" value="1" <?php checked(isset($options['enable_scrolltop']), 1); ?> /><?php }
function dblh_field_scrolltop_bg_color_html() { $options = get_option('dblh_options'); $color = isset($options['scrolltop_bg_color']) ? $options['scrolltop_bg_color'] : '#607D8B'; ?><input type="color" name="dblh_options[scrolltop_bg_color]" value="<?php echo esc_attr($color); ?>" /><?php }


// TẠO GIAO DIỆN TRANG CÀI ĐẶT
function dblh_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('dblh_settings_group');
            do_settings_sections('nut_bam_lien_he_dibrother');
            submit_button('Lưu thay đổi');
            ?>
        </form>

        <hr>
        <div class="dblh-credit">
            <h2>Ghi công & Ủng hộ</h2>
            <p>Plugin được phát triển bởi <a href="https://dibrother.com/" target="_blank" rel="nofollow">Dibrother</a>. Plugin là miễn phí, bạn có thể mời tác giả 1 ly cà phê để có thêm động lực phát triển:</p>
            <ul>
                <li><strong>Vietcombank:</strong> 9935754589</li>
                <li><strong>Paypal:</strong> duyfagor@gmail.com</li>
            </ul>
        </div>
        
    </div>
    <?php
}

// ENQUEUE CSS VÀ JS CHO FRONTEND
function dblh_enqueue_scripts() {
    $options = get_option('dblh_options');
    if (!empty($options['enable'])) {
        wp_enqueue_style('dblh-styles', plugin_dir_url(__FILE__) . 'style.css', array(), '2.1.1');
        wp_enqueue_script('dblh-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), '2.1.1', true);
    }
}
add_action('wp_enqueue_scripts', 'dblh_enqueue_scripts');

// HIỂN THỊ CÁC NÚT BẤM RA NGOÀI WEBSITE
function dblh_display_buttons() {
    $options = get_option('dblh_options');
    if (empty($options['enable'])) {
        return;
    }

    $position_class = isset($options['position']) && $options['position'] === 'left' ? 'fab-container-left' : 'fab-container-right';
    $ripple_class = !empty($options['ripple_effect']) ? 'fab-ripple' : '';
    $plugin_img_path = plugin_dir_url(__FILE__) . 'img/';

    $phone_color = !empty($options['phone_bg_color']) ? $options['phone_bg_color'] : '#4CAF50';
    $zalo_color = !empty($options['zalo_bg_color']) ? $options['zalo_bg_color'] : '#0068ff';
    $messenger_color = !empty($options['messenger_bg_color']) ? $options['messenger_bg_color'] : '#0084ff';
    $scrolltop_color = !empty($options['scrolltop_bg_color']) ? $options['scrolltop_bg_color'] : '#607D8B';

    echo "<div class='fab-container " . esc_attr($position_class) . "'>";

    if (!empty($options['enable_scrolltop'])) {
        $scroll_icon_svg = '<svg style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
        echo "<a href='#' id='fab-scroll-top' class='fab-button " . esc_attr($ripple_class) . "' title='Lên đầu trang' style='background-color: " . esc_attr($scrolltop_color) . ";'>" . $scroll_icon_svg . "</a>";
    }

    if (!empty($options['enable_messenger']) && !empty($options['messenger_link'])) {
        echo "<a href='" . esc_url($options['messenger_link']) . "' target='_blank' rel='nofollow' id='fab-messenger' class='fab-button " . esc_attr($ripple_class) . "' title='Chat Messenger' style='background-color: " . esc_attr($messenger_color) . ";'><img src='" . esc_url($plugin_img_path . 'mess.png') . "' alt='Messenger'></a>";
    }
    
    if (!empty($options['enable_zalo']) && !empty($options['zalo_link'])) {
        echo "<a href='" . esc_url($options['zalo_link']) . "' target='_blank' rel='nofollow' id='fab-zalo' class='fab-button " . esc_attr($ripple_class) . "' title='Chat Zalo' style='background-color: " . esc_attr($zalo_color) . ";'><img src='" . esc_url($plugin_img_path . 'zalo.png') . "' alt='Zalo'></a>";
    }

    if (!empty($options['enable_phone']) && !empty($options['phone_number'])) {
        echo "<a href='tel:" . esc_attr($options['phone_number']) . "' id='fab-phone' class='fab-button " . esc_attr($ripple_class) . "' title='Gọi điện' style='background-color: " . esc_attr($phone_color) . ";'><img src='" . esc_url($plugin_img_path . 'phone.png') . "' alt='Phone'></a>";
    }

    echo "</div>";
}
add_action('wp_footer', 'dblh_display_buttons');

