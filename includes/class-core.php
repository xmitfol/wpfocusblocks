<?php
/**
 * Основной класс плагина WPFocusBlocks
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для основной функциональности плагина
 */
class WPFocusBlocks_Core {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_Core
     */
    private static $instance = null;

    /**
     * Настройки плагина
     *
     * @var array
     */
    private $settings;

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_Core
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Конструктор
     */
    private function __construct() {
        $this->settings = get_option('wpfocusblocks_settings', array());
        $this->init_hooks();
    }

    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        // Хуки для внешнего интерфейса
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        
        // Хуки для админ-интерфейса
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        }
        
        // Инициализация компонентов
        $this->init_components();
    }

    /**
     * Регистрация скриптов и стилей
     */
    public function register_assets() {
        // Регистрируем пустой CSS файл для динамических стилей
        wp_register_style(
            'wpfocusblocks-dynamic', 
            false
        );
        
        // Подключаем его
        wp_enqueue_style('wpfocusblocks-dynamic');
    }

    /**
     * Регистрация скриптов и стилей для админки
     */
    public function register_admin_assets($hook) {
        // Подключаем стили только на странице настроек плагина
        if ('settings_page_wpfocusblocks' === $hook) {
            wp_enqueue_style(
                'wpfocusblocks-admin',
                WPFOCUSBLOCKS_URL . 'admin/css/admin-styles.css',
                array(),
                WPFOCUSBLOCKS_VERSION
            );
            
            wp_enqueue_script(
                'wpfocusblocks-admin',
                WPFOCUSBLOCKS_URL . 'admin/js/admin-script.js',
                array('jquery', 'wp-color-picker'),
                WPFOCUSBLOCKS_VERSION,
                true
            );
            
            // Подключаем палитру цветов WordPress
            wp_enqueue_style('wp-color-picker');
        }
    }

    /**
     * Инициализация компонентов плагина
     */
	private function init_components() {
		// Сначала загружаем все классы, затем инициализируем их
		
		// 1. Получаем экземпляры только после полной загрузки всех классов
		$dynamic_styles_class = class_exists('WPFocusBlocks_Dynamic_Styles') 
			? WPFocusBlocks_Dynamic_Styles::get_instance() 
			: null;
		
		$shortcodes_class = class_exists('WPFocusBlocks_Shortcodes') 
			? WPFocusBlocks_Shortcodes::get_instance() 
			: null;
		
		$tinymce_class = class_exists('WPFocusBlocks_TinyMCE') 
			? WPFocusBlocks_TinyMCE::get_instance() 
			: null;
		
		// Gutenberg инициализировать только если доступен
		$gutenberg_class = (function_exists('register_block_type') && class_exists('WPFocusBlocks_Gutenberg'))
			? WPFocusBlocks_Gutenberg::get_instance()
			: null;
		
		// Admin интерфейс инициализировать только в админке
		$admin_class = (is_admin() && class_exists('WPFocusBlocks_Admin'))
			? WPFocusBlocks_Admin::get_instance()
			: null;
	}

    /**
     * Получение настроек плагина
     *
     * @return array Настройки плагина
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Обновление настроек плагина
     *
     * @param array $new_settings Новые настройки
     * @return bool Успешность обновления
     */
    public function update_settings($new_settings) {
        $this->settings = array_merge($this->settings, $new_settings);
        return update_option('wpfocusblocks_settings', $this->settings);
    }

    /**
     * Получение списка доступных блоков
     *
     * @return array Список всех блоков
     */
    public function get_available_blocks() {
        return array(
            'red' => array(
                'name' => __('Красный блок', 'wpfocusblocks'),
                'class' => 'ostorozhno1',
                'icon' => 'block_red.svg',
                'description' => __('Используется для предупреждений и важной информации', 'wpfocusblocks')
            ),
            'green' => array(
                'name' => __('Зелёный блок', 'wpfocusblocks'),
                'class' => 'okey1',
                'icon' => 'block_green.svg',
                'description' => __('Используется для успешных операций и рекомендаций', 'wpfocusblocks')
            ),
            'yellow' => array(
                'name' => __('Оранжевый блок', 'wpfocusblocks'),
                'class' => 'vnimanie1',
                'icon' => 'block_yellow.svg',
                'description' => __('Используется для информации требующей внимания', 'wpfocusblocks')
            ),
            'blue' => array(
                'name' => __('Синий блок', 'wpfocusblocks'),
                'class' => 'vopros1',
                'icon' => 'block_blue.svg',
                'description' => __('Используется для информационных сообщений', 'wpfocusblocks')
            ),
            'purple' => array(
                'name' => __('Сиреневый блок', 'wpfocusblocks'),
                'class' => 'kstati1',
                'icon' => 'block_purple.svg',
                'description' => __('Используется для дополнительной информации', 'wpfocusblocks')
            ),
            'tup' => array(
                'name' => __('Палец вверх', 'wpfocusblocks'),
                'class' => 'palec_vverh1',
                'icon' => 'block_tup.svg',
                'description' => __('Используется для положительных моментов', 'wpfocusblocks')
            ),
            'tdown' => array(
                'name' => __('Палец вниз', 'wpfocusblocks'),
                'class' => 'palec_vniz1',
                'icon' => 'block_tdown.svg',
                'description' => __('Используется для отрицательных моментов', 'wpfocusblocks')
            ),
            'quote' => array(
                'name' => __('Блок цитаты', 'wpfocusblocks'),
                'class' => 'quote',
                'icon' => 'block_quote.svg',
                'description' => __('Используется для выделения цитат', 'wpfocusblocks')
            ),
            'pro' => array(
                'name' => __('Блок PRO', 'wpfocusblocks'),
                'class' => 'pro',
                'icon' => 'block_pro.svg',
                'description' => __('Используется для выделения premium-контента', 'wpfocusblocks')
            ),
            // Маркеры
            'myellow' => array(
                'name' => __('Выделение жёлтым', 'wpfocusblocks'),
                'class' => 'myellow',
                'icon' => 'block_marker.svg',
                'description' => __('Маркерное выделение текста жёлтым', 'wpfocusblocks')
            ),
            'mred' => array(
                'name' => __('Выделение красным', 'wpfocusblocks'),
                'class' => 'mred',
                'icon' => 'block_marker_red.svg',
                'description' => __('Маркерное выделение текста красным', 'wpfocusblocks')
            ),
            'mgreen' => array(
                'name' => __('Выделение зелёным', 'wpfocusblocks'),
                'class' => 'mgreen',
                'icon' => 'block_marker_green.svg',
                'description' => __('Маркерное выделение текста зелёным', 'wpfocusblocks')
            ),
            'msilver' => array(
                'name' => __('Выделение серым', 'wpfocusblocks'),
                'class' => 'msilver',
                'icon' => 'block_marker_silver.svg',
                'description' => __('Маркерное выделение текста серым', 'wpfocusblocks')
            ),
            // Подчеркивания
            'lblue' => array(
                'name' => __('Подчёркивание синим', 'wpfocusblocks'),
                'class' => 'lblue',
                'icon' => 'block_line_blue.svg',
                'description' => __('Подчёркивание текста синим', 'wpfocusblocks')
            ),
            'lred' => array(
                'name' => __('Подчёркивание красным', 'wpfocusblocks'),
                'class' => 'lred',
                'icon' => 'block_line_red.svg',
                'description' => __('Подчёркивание текста красным', 'wpfocusblocks')
            ),
            'lyellow' => array(
                'name' => __('Подчёркивание жёлтым', 'wpfocusblocks'),
                'class' => 'lyellow',
                'icon' => 'block_line_yellow.svg',
                'description' => __('Подчёркивание текста жёлтым', 'wpfocusblocks')
            ),
            'lgreen' => array(
                'name' => __('Подчёркивание зелёным', 'wpfocusblocks'),
                'class' => 'lgreen',
                'icon' => 'block_line_green.svg',
                'description' => __('Подчёркивание текста зелёным', 'wpfocusblocks')
            )
        );
    }

    /**
     * Проверяет, включен ли блок
     *
     * @param string $block_id Идентификатор блока
     * @return bool Включен ли блок
     */
    public function is_block_enabled($block_id) {
        if (!isset($this->settings['blocks_enabled'])) {
            return true; // По умолчанию все блоки включены
        }
        
        if (!isset($this->settings['blocks_enabled'][$block_id])) {
            return true;
        }
        
        return (bool) $this->settings['blocks_enabled'][$block_id];
    }
}

// Инициализация
WPFocusBlocks_Core::get_instance();