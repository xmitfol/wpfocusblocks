<?php
/**
 * Класс для интеграции с редактором Gutenberg
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поддержки блоков Gutenberg
 */
class WPFocusBlocks_Gutenberg {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_Gutenberg
     */
    private static $instance = null;

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_Gutenberg
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
        // Регистрация блоков
        add_action('init', array($this, 'register_blocks'));
        
        // Добавление категории блоков
        add_filter('block_categories_all', array($this, 'add_block_category'), 10, 2);
    }

    /**
     * Регистрация блоков Gutenberg
     */
    public function register_blocks() {
        // Проверяем функцию register_block_type
        if (!function_exists('register_block_type')) {
            return;
        }
        
        // Регистрируем скрипты и стили для редактора
        wp_register_script(
            'wpfocusblocks-editor',
            WPFOCUSBLOCKS_URL . 'assets/js/block-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            WPFOCUSBLOCKS_VERSION,
            true
        );
        
        wp_register_style(
            'wpfocusblocks-editor',
            WPFOCUSBLOCKS_URL . 'assets/css/block-editor.css',
            array(),
            WPFOCUSBLOCKS_VERSION
        );
        
        // Получаем список всех блоков
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        // Передаем данные о блоках в JavaScript
        wp_localize_script('wpfocusblocks-editor', 'wpfocusblocks', array(
            'blocks' => $all_blocks,
            'pluginUrl' => WPFOCUSBLOCKS_URL
        ));
        
        // Регистрируем блоки
        // В текущей версии мы не будем реализовывать полноценные блоки Gutenberg,
        // вместо этого мы будем использовать подход с классическим редактором
        
        /*
        // Пример регистрации блока:
        register_block_type('wpfocusblocks/alert', array(
            'editor_script' => 'wpfocusblocks-editor',
            'editor_style' => 'wpfocusblocks-editor',
            'attributes' => array(
                'content' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'blockType' => array(
                    'type' => 'string',
                    'default' => 'red'
                )
            ),
            'render_callback' => array($this, 'render_alert_block')
        ));
        */
    }

    /**
     * Добавление категории блоков
     *
     * @param array $categories Существующие категории
     * @param WP_Post $post Текущая запись
     * @return array Обновленные категории
     */
    public function add_block_category($categories, $post) {
        return array_merge($categories, array(
            array(
                'slug' => 'wpfocusblocks',
                'title' => __('WPFocusBlocks', 'wpfocusblocks'),
                'icon' => 'warning'
            )
        ));
    }

    /**
     * Рендеринг блока Alert
     *
     * @param array $attributes Атрибуты блока
     * @param string $content Содержимое блока
     * @return string HTML-код блока
     */
    public function render_alert_block($attributes, $content) {
        $block_type = isset($attributes['blockType']) ? $attributes['blockType'] : 'red';
        $block_content = isset($attributes['content']) ? $attributes['content'] : '';
        
        // Получаем класс CSS для блока
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        if (!isset($all_blocks[$block_type])) {
            $block_type = 'red'; // Используем красный блок по умолчанию
        }
        
        $css_class = $all_blocks[$block_type]['class'];
        
        // Формируем HTML блока
        $output = sprintf(
            '<div class="%s"><p>%s</p></div>',
            esc_attr($css_class),
            wp_kses_post($block_content)
        );
        
        return $output;
    }
}

// Инициализация
WPFocusBlocks_Gutenberg::get_instance();