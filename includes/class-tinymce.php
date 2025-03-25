<?php
/**
 * Класс для интеграции с TinyMCE
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для интеграции с классическим редактором TinyMCE
 */
class WPFocusBlocks_TinyMCE {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_TinyMCE
     */
    private static $instance = null;

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_TinyMCE
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
        $this->init_hooks();
    }

    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        // Добавляем кнопки в редактор, если пользователь имеет права
        add_action('admin_init', array($this, 'setup_tinymce_plugin'));
        
        // Для отладки - показываем панель кнопок в редакторе
        add_filter('tiny_mce_before_init', array($this, 'ensure_toolbar3'));
    }
    
    /**
     * Убеждаемся, что третий тулбар отображается
     * 
     * @param array $settings Настройки TinyMCE
     * @return array Обновленные настройки
     */
    public function ensure_toolbar3($settings) {
        // Принудительно включаем отображение третьей строки
        $settings['toolbar3'] = implode(',', apply_filters('mce_buttons_3', array()));
        return $settings;
    }

    /**
     * Настройка плагина TinyMCE
     */
    public function setup_tinymce_plugin() {
        // Проверяем права пользователя
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        
        // Проверяем, включен ли визуальный редактор
        if ('true' !== get_user_option('rich_editing')) {
            return;
        }
        
        // Регистрируем скрипт с плагином
        add_filter('mce_external_plugins', array($this, 'add_tinymce_plugin'));
        
        // Добавляем кнопки в третий ряд
        add_filter('mce_buttons_3', array($this, 'register_tinymce_buttons'));
        
        // Передаем URL иконок для JavaScript
        add_action('admin_enqueue_scripts', array($this, 'add_icons_url_to_script'), 9);
    }

    /**
     * Добавляем JS-файл с плагином
     *
     * @param array $plugins Массив плагинов
     * @return array Обновленный массив плагинов
     */

	public function add_tinymce_plugin($plugins) {
		// Используем константу WPFOCUSBLOCKS_URL
		$plugins['wpfocusblocks'] = WPFOCUSBLOCKS_URL . 'admin/js/tinymce-plugin.js';
		return $plugins;
	}
    
    /**
     * Добавляем URL иконок в JavaScript
     */
	public function add_icons_url_to_script() {
		// Используем wp_add_inline_script вместо прямого вывода
		wp_register_script('wpfocusblocks-icons-url', false);
		wp_enqueue_script('wpfocusblocks-icons-url');
		
		// Используем WPFOCUSBLOCKS_URL константу для указания правильного пути к иконкам
		$icons_url = WPFOCUSBLOCKS_URL . 'assets/icons/';
		
		wp_add_inline_script(
			'wpfocusblocks-icons-url', 
			'var wpfocusblocks_icons_url = "' . esc_url($icons_url) . '";
			console.log("WPFocusBlocks Icons URL:", wpfocusblocks_icons_url);'
		);
	}

    /**
     * Регистрируем кнопки в редакторе
     *
     * @param array $buttons Массив кнопок
     * @return array Обновленный массив кнопок
     */
    public function register_tinymce_buttons($buttons) {
        // Получаем список включенных блоков
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        // Добавляем кнопки для каждого блока
        foreach ($all_blocks as $block_id => $block_data) {
            // Пропускаем отключенные блоки
            if (!$core->is_block_enabled($block_id)) {
                continue;
            }
            
            $buttons[] = $block_id;
        }
        
        return $buttons;
    }
}

// Инициализация
WPFocusBlocks_TinyMCE::get_instance();