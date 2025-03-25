<?php
/**
 * Plugin Name: WPFocusBlocks
 * Plugin URI: https://wpfocusblocks.com
 * Description: Красивые блоки внимания и выделения текста без влияния на скорость сайта.
 * Version: 1.0.0
 * Author: WPFocusBlocks Team
 * Author URI: https://wpfocusblocks.com
 * Text Domain: wpfocusblocks
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы плагина
define('WPFOCUSBLOCKS_VERSION', '1.0.0');
define('WPFOCUSBLOCKS_PATH', plugin_dir_path(__FILE__));
define('WPFOCUSBLOCKS_URL', plugin_dir_url(__FILE__));
define('WPFOCUSBLOCKS_BASENAME', plugin_basename(__FILE__));

/**
 * Основной класс плагина
 */
final class WPFocusBlocks {
    /**
     * Экземпляр класса (паттерн Singleton)
     *
     * @var WPFocusBlocks
     */
    private static $instance = null;

    /**
     * Получаем единственный экземпляр класса
     *
     * @return WPFocusBlocks
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Конструктор класса
     */
    private function __construct() {
        $this->load_dependencies();
        $this->setup_hooks();
    }

    /**
     * Загружаем необходимые зависимости
     */
	private function load_dependencies() {
		// Проверяем, существуют ли файлы перед их подключением

		// Основные компоненты
		$core_file = WPFOCUSBLOCKS_PATH . 'includes/class-core.php';
		$dynamic_styles_file = WPFOCUSBLOCKS_PATH . 'includes/class-dynamic-styles.php';
		$shortcodes_file = WPFOCUSBLOCKS_PATH . 'includes/class-shortcodes.php';
		$tinymce_file = WPFOCUSBLOCKS_PATH . 'includes/class-tinymce.php';
		$gutenberg_file = WPFOCUSBLOCKS_PATH . 'includes/class-gutenberg.php';
		$admin_file = WPFOCUSBLOCKS_PATH . 'admin/class-admin.php';
		
		// Выводим сообщения для диагностики (включить при отладке)
		/*
		error_log('Core file exists: ' . (file_exists($core_file) ? 'Yes' : 'No'));
		error_log('Dynamic Styles file exists: ' . (file_exists($dynamic_styles_file) ? 'Yes' : 'No'));
		error_log('Shortcodes file exists: ' . (file_exists($shortcodes_file) ? 'Yes' : 'No'));
		error_log('TinyMCE file exists: ' . (file_exists($tinymce_file) ? 'Yes' : 'No'));
		error_log('Gutenberg file exists: ' . (file_exists($gutenberg_file) ? 'Yes' : 'No'));
		error_log('Admin file exists: ' . (file_exists($admin_file) ? 'Yes' : 'No'));
		*/
		
		// Загружаем только существующие файлы
		if (file_exists($core_file)) {
			require_once $core_file;
		}
		
		if (file_exists($dynamic_styles_file)) {
			require_once $dynamic_styles_file;
		}
		
		if (file_exists($shortcodes_file)) {
			require_once $shortcodes_file;
		}
		
		if (file_exists($tinymce_file)) {
			require_once $tinymce_file;
		}
		
		// Проверяем наличие Gutenberg
		if (function_exists('register_block_type') && file_exists($gutenberg_file)) {
			require_once $gutenberg_file;
		}
		
		// Административный интерфейс (только в админке)
		if (is_admin() && file_exists($admin_file)) {
			require_once $admin_file;
		}
	}

    /**
     * Настраиваем хуки WordPress
     */
    private function setup_hooks() {
        // Хуки активации/деактивации плагина
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Загрузка текстового домена
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Действия при активации плагина
     */
    public function activate() {
        // Устанавливаем дефолтные настройки
        $default_settings = array(
            'cache_enabled' => true,
            'cache_time' => 3600, // 1 час в секундах
            'blocks_enabled' => array(
                'red' => true,
                'green' => true,
                'yellow' => true,
                'blue' => true,
                'purple' => true,
                'tup' => true,
                'tdown' => true,
                'quote' => true,
                'pro' => true,
                'myellow' => true,
                'mred' => true,
                'mgreen' => true,
                'msilver' => true,
                'lblue' => true,
                'lred' => true,
                'lyellow' => true,
                'lgreen' => true
            )
        );
        
        // Добавляем настройки только если их еще нет
        if (!get_option('wpfocusblocks_settings')) {
            add_option('wpfocusblocks_settings', $default_settings);
        }
        
        // Очистка всех кэшей, если они есть
        $this->clear_caches();
    }

    /**
     * Действия при деактивации плагина
     */
    public function deactivate() {
        // Очистка всех кэшей
        $this->clear_caches();
    }
    
    /**
     * Очистка кэшей
     */
    private function clear_caches() {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wpfb_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_wpfb_%'");
    }

    /**
     * Загружаем текстовый домен для переводов
     */
    public function load_textdomain() {
        load_plugin_textdomain('wpfocusblocks', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Инициализация плагина
function wpfocusblocks() {
    return WPFocusBlocks::instance();
}

// Запускаем плагин
wpfocusblocks();