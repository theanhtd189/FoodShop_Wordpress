<?php
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
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'tFPN^V54&w CSH]0*Oqur$+re]XhY[dQ22Er(}iSO`Vm(~=%6P@XyPAv@pGE) EB' );
define( 'SECURE_AUTH_KEY',  'L^Q!x]xO7iv(lKvKCf#lia8tn0_`T<8l-F;TF^|_e4{D>UYZ3i7>zT94>+lJF6}[' );
define( 'LOGGED_IN_KEY',    'r@8p9fy|[8g^C}7o:MUg&FT6Y*v7}.KV;E&<2UcD6IR7vM-Fc9k(nigxlsQ%*+<b' );
define( 'NONCE_KEY',        'yr/#1FShd6Vu%s1G&]]gt<v~1?V_M+&N*GmJBeJz}9Q;>f[nPTuU>yAn73t}t) k' );
define( 'AUTH_SALT',        'XDpU>vFliR!RFQG<{Rj6G<ke+-nK`H;6VL#f}lEpSLQl,dslJ3t<2o u#Hs9!)<k' );
define( 'SECURE_AUTH_SALT', 'Qc$f!_S )/Qc%Ph~<@C2c:+aWLPeX$J4.$rI/qdRd@`_[6F?_~WMs`Z,jpr}>R%R' );
define( 'LOGGED_IN_SALT',   'eN?A6~);1L-r.f`l@!E9e|s4-ToJd:}`Uk_^W3k3IOd5QZpv[S+YL.E?9wS[I-:%' );
define( 'NONCE_SALT',       ' T{gdo`6>dB8aLZ@C)jLoS[xL&_2jBjOoxFk}dP&IF*^xe68]dHE?6-F&Q,e;3gl' );

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
define('WP_DEBUG', false);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');
