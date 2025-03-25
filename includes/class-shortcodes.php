<?php
/**
 * Класс для обработки шорткодов
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для регистрации и обработки шорткодов
 */
class WPFocusBlocks_Shortcodes {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_Shortcodes
     */
    private static $instance = null;

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_Shortcodes
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
        $this->register_shortcodes();
    }

    /**
     * Регистрация всех шорткодов
     */
    private function register_shortcodes() {
        // Получаем список всех блоков
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        // Регистрируем шорткоды для каждого блока
        foreach ($all_blocks as $block_id => $block_data) {
            // Пропускаем отключенные блоки
            if (!$core->is_block_enabled($block_id)) {
                continue;
            }
            
            add_shortcode($block_id, array($this, 'render_shortcode'));
        }
    }

	/**
	 * Обработка шорткода
	 */
	public function render_shortcode($atts, $content = null, $tag = '') {
		// Получаем данные о блоке
		$core = WPFocusBlocks_Core::get_instance();
		$all_blocks = $core->get_available_blocks();
		
		// Проверяем, существует ли такой блок
		if (!isset($all_blocks[$tag])) {
			return do_shortcode($content);
		}
		
		$block_data = $all_blocks[$tag];
		
		// Обрабатываем атрибуты
		$attributes = shortcode_atts(array(
			'title' => '',
			'class' => '',
		), $atts, $tag);
		
		// Формируем CSS-классы
		$css_classes = $block_data['class'];
		
		if (!empty($attributes['class'])) {
			$css_classes .= ' ' . esc_attr($attributes['class']);
		}
		
		// Различная обработка для блоков и текстовых выделений
		if (in_array($tag, array('myellow', 'mred', 'mgreen', 'msilver', 'lblue', 'lred', 'lyellow', 'lgreen'))) {
			// Текстовые выделения (маркеры и подчеркивания)
			return '<span class="' . esc_attr($css_classes) . '">' . do_shortcode($content) . '</span>';
		} else {
			// Блоки внимания
			$output = '<div class="' . esc_attr($css_classes) . '">';
			
			// Добавляем заголовок, если указан
			if (!empty($attributes['title'])) {
				$output .= '<h4>' . esc_html($attributes['title']) . '</h4>';
			}
			
			$output .= '<p>' . do_shortcode($content) . '</p>';
			$output .= '</div>';
			
			return $output;
		}
	}
}

// Инициализация
WPFocusBlocks_Shortcodes::get_instance();