<?php
define( 'WP_CACHE', true );

/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung 
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt, 
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define( 'DB_NAME', 'food' );
/** Username của database */
define( 'DB_USER', 'root' );
/** Mật khẩu của database */
define( 'DB_PASSWORD', '' );
/** Hostname của database */
define( 'DB_HOST', 'localhost');
/** Database charset sử dụng để tạo bảng database. */
define( 'DB_CHARSET', 'utf8mb4' );
/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');
/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'tg E!`Ip4Jr JiNO~Bt0HZ7F-:T;0)~L<a8t/e*)G$!XqxxhdpUSu=QjsZn.5GDT' );
define( 'SECURE_AUTH_KEY',  '[YP8Ud2wpO{uSjIG]/inFs8:v354}VHc,|BSrx5 A&V<+W{?3dr)lDw(V0-[,!.f' );
define( 'LOGGED_IN_KEY',    '?|;?+~VVct%;f|6!rQ1BXGh7os7W}aC>8dYft*hp>w.huJ{-J{)d7jYzb.@^gxcA' );
define( 'NONCE_KEY',        '(9*k1RtQred8a#Dgp<!]3I5bI,7>//vG};P:V[Ko@yq]$jgBXL)CDYJu9@d1l,^.' );
define( 'AUTH_SALT',        'EMu~Nz<<LPb5oYUf#51Q&J0Wt52Pg`CG8QBnEk.u+Tz6*{SWq@izP&xP>~XeL~!e' );
define( 'SECURE_AUTH_SALT', '7/;8MpmMxC%!@`d*}L.Pkk[,vE}2GZJeNVjQnp-9xkkErt+$G#w8{b&4M#9NwBu#' );
define( 'LOGGED_IN_SALT',   'RaXbw[1/1@])OYU}==rv!.E~tzUc%.2)C#,,Nir|P{6,+St>o|G}qirAFh{s[5LS' );
define( 'NONCE_SALT',       'b)l-E$,1,=0y8iCz3K9L~P%8B]H2I^bK&#P]-$/fmGveDYwW[jNxSL:JIa)j=Nw#' );
/**#@-*/
/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix = 'wp_';
/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);
define('SAVEQUERIES', true);
define('WP_MEMORY_LIMIT', '256M');
/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */
/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');
