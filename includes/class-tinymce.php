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
        
        // Настраиваем стили для контента TinyMCE
        add_filter('tiny_mce_before_init', array($this, 'setup_tinymce_content_css'));
        
        // Передаем URL иконок для JavaScript
        add_action('admin_enqueue_scripts', array($this, 'add_icons_url_to_script'), 9);
        
        // Добавляем стили для редактора
        add_action('admin_enqueue_scripts', array($this, 'add_editor_styles'));
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
    }

/**
     * Добавляем JS-файл с плагином
     *
     * @param array $plugins Массив плагинов
     * @return array Обновленный массив плагинов
     */
    public function add_tinymce_plugin($plugins) {
        $plugins['wpfocusblocks'] = WPFOCUSBLOCKS_URL . 'admin/js/tinymce-plugin.js';
        return $plugins;
    }
    
    /**
     * Добавляем URL иконок в JavaScript
     */
    public function add_icons_url_to_script() {
        // Регистрируем и подключаем скрипт только на страницах с редактором
        if ($this->is_editor_page()) {
            wp_register_script('wpfocusblocks-icons-url', false);
            wp_enqueue_script('wpfocusblocks-icons-url');
            
            // Используем WPFOCUSBLOCKS_URL константу для указания правильного пути к иконкам
            $icons_url = WPFOCUSBLOCKS_URL . 'assets/icons/';
            
            wp_add_inline_script(
                'wpfocusblocks-icons-url', 
                'var wpfocusblocks_icons_url = "' . esc_url($icons_url) . '";'
            );
        }
    }

    /**
     * Регистрируем кнопки в редакторе
     *
     * @param array $buttons Массив кнопок
     * @return array Обновленный массив кнопок
     */
    public function register_tinymce_buttons($buttons) {
        // Добавляем единую кнопку для блоков внимания
        $buttons[] = 'wpfocusblocks_btn';
        
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
    
    /**
     * Настройка TinyMCE для поддержки плагина
     * 
     * @param array $mce_init Настройки TinyMCE
     * @return array Измененные настройки
     */
    public function setup_tinymce_content_css($mce_init) {
        // Проверяем текущий хук
        if (!$this->is_editor_page()) {
            return $mce_init;
        }
        
        // Добавляем стили в редактор TinyMCE
        $content_css = isset($mce_init['content_css']) ? $mce_init['content_css'] : '';
        
        // Добавляем наш CSS файл
        if (!empty($content_css)) {
            $content_css .= ',';
        }
        
        $content_css .= WPFOCUSBLOCKS_URL . 'admin/css/editor-styles.css';
        $mce_init['content_css'] = $content_css;
        
        // Добавляем класс к body редактора
        $body_class = isset($mce_init['body_class']) ? $mce_init['body_class'] : '';
        $body_class .= ' wpfocusblocks-editor';
        $mce_init['body_class'] = $body_class;
        
        // Указываем, какие элементы не должны фильтроваться
        $extended_valid = isset($mce_init['extended_valid_elements']) ? $mce_init['extended_valid_elements'] : '';
        if (!empty($extended_valid)) {
            $extended_valid .= ',';
        }
        $extended_valid .= 'blockquote[class|id|data-wpfb-type],span[class|id|data-wpfb-type]';
        $mce_init['extended_valid_elements'] = $extended_valid;
        
        // Защищаем наши элементы от автоматического форматирования
        $custom_elements = isset($mce_init['custom_elements']) ? $mce_init['custom_elements'] : '';
        if (!empty($custom_elements)) {
            $custom_elements .= ',';
        }
        $custom_elements .= 'blockquote[data-wpfb-type],span[data-wpfb-type]';
        $mce_init['custom_elements'] = $custom_elements;
        
        return $mce_init;
    }
	
	/**
     * Добавляем стили для редактора
     *
     * @param string $hook Текущая страница в админке
     */
    public function add_editor_styles($hook) {
        // Подключаем стили только на страницах редактирования
        if ($this->is_editor_page($hook)) {
            // Стили для редактора
            wp_enqueue_style(
                'wpfocusblocks-editor',
                WPFOCUSBLOCKS_URL . 'admin/css/editor-styles.css',
                array(),
                WPFOCUSBLOCKS_VERSION
            );
            
            // Стили для модального окна
            wp_enqueue_style(
                'wpfocusblocks-modal',
                WPFOCUSBLOCKS_URL . 'admin/css/modal-styles.css',
                array(),
                WPFOCUSBLOCKS_VERSION
            );
            
            // Добавляем dashicons для иконок
            wp_enqueue_style('dashicons');
        }
    }
    
    /**
     * Проверяет, находимся ли мы на странице с редактором
     *
     * @param string $hook Текущая страница в админке
     * @return bool Результат проверки
     */
    private function is_editor_page($hook = '') {
        if (empty($hook)) {
            global $pagenow;
            $hook = $pagenow;
        }
        
        return in_array($hook, array('post.php', 'post-new.php', 'admin.php'));
    }
    
    /**
     * Создает общую иконку для плагина
     *
     * @return string Путь к иконке
     */
    private function create_plugin_icon() {
        // Если иконка уже существует, возвращаем путь к ней
        $icon_path = WPFOCUSBLOCKS_PATH . 'assets/icons/wpfocusblocks-icon.svg';
        $icon_url = WPFOCUSBLOCKS_URL . 'assets/icons/wpfocusblocks-icon.svg';
        
        if (file_exists($icon_path)) {
            return $icon_url;
        }
        
        // Проверяем наличие директории
        $icons_dir = WPFOCUSBLOCKS_PATH . 'assets/icons';
        if (!file_exists($icons_dir)) {
            wp_mkdir_p($icons_dir);
        }
        
        // Создаем простую SVG иконку
        $svg_content = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
            <rect x="2" y="2" width="16" height="16" rx="2" ry="2" fill="#4d3bfe" />
            <path d="M6 7 L14 7 L14 9 L6 9 Z" fill="white" />
            <path d="M6 11 L14 11 L14 13 L6 13 Z" fill="white" />
        </svg>';
        
        // Записываем SVG в файл
        file_put_contents($icon_path, $svg_content);
        
        return $icon_url;
    }
}

// Инициализация
WPFocusBlocks_TinyMCE::get_instance();