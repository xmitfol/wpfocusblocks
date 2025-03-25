<?php
/**
 * Класс для динамической генерации стилей
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для генерации динамических стилей блоков
 */
class WPFocusBlocks_Dynamic_Styles {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_Dynamic_Styles
     */
    private static $instance = null;

    /**
     * Список блоков, используемых на странице
     *
     * @var array
     */
    private $used_blocks = array();

    /**
     * Настройки плагина
     *
     * @var array
     */
    private $settings = array();

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_Dynamic_Styles
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
        // Действия для фронтенда
        add_action('wp', array($this, 'analyze_content'));
        add_action('wp_head', array($this, 'output_dynamic_styles'), 100);
        
        // Очистка кэша при сохранении записей
        add_action('save_post', array($this, 'clear_post_cache'), 10, 2);
        
        // Очистка кэша при сохранении настроек
        add_action('update_option_wpfocusblocks_settings', array($this, 'clear_all_caches'));
    }

    /**
     * Анализирует контент для определения используемых блоков
     */
    public function analyze_content() {
        // Пропускаем, если не просматривается отдельная запись
        if (!is_singular()) {
            return;
        }

        global $post;
        
        // Проверяем наличие записи
        if (!$post instanceof WP_Post) {
            return;
        }

        // Проверяем кэш
        $cache_enabled = isset($this->settings['cache_enabled']) ? (bool) $this->settings['cache_enabled'] : true;
        
        if ($cache_enabled) {
            $cache_key = 'wpfb_blocks_' . $post->ID . '_' . md5($post->post_modified);
            $cached_blocks = get_transient($cache_key);
            
            if (false !== $cached_blocks) {
                $this->used_blocks = $cached_blocks;
                return;
            }
        }

        // Анализируем контент
        $this->scan_content_for_blocks($post->post_content);

        // Кэшируем результат
        if ($cache_enabled) {
            $cache_time = isset($this->settings['cache_time']) ? intval($this->settings['cache_time']) : 3600;
            set_transient($cache_key, $this->used_blocks, $cache_time);
        }
    }

    /**
     * Сканирует контент на наличие блоков и шорткодов
     *
     * @param string $content Содержимое для анализа
     */
    private function scan_content_for_blocks($content) {
        // Получаем список всех блоков
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        foreach ($all_blocks as $block_id => $block_data) {
            // Пропускаем отключенные блоки
            if (!$core->is_block_enabled($block_id)) {
                continue;
            }
            
            // Проверяем наличие Gutenberg блоков
            if (function_exists('has_block') && has_block('wpfocusblocks/' . $block_id, $content)) {
                $this->used_blocks[] = $block_id;
                continue;
            }
            
            // Проверяем наличие шорткодов
            if (strpos($content, '[' . $block_id . ']') !== false) {
                $this->used_blocks[] = $block_id;
            }
        }
        
        // Убираем дубликаты
        $this->used_blocks = array_unique($this->used_blocks);
    }

    /**
     * Выводит динамические стили в head
     */
    public function output_dynamic_styles() {
        // Если блоки не найдены, ничего не делаем
        if (empty($this->used_blocks)) {
            return;
        }

        // Проверяем кэш стилей
        $cache_enabled = isset($this->settings['cache_enabled']) ? (bool) $this->settings['cache_enabled'] : true;
        
        if ($cache_enabled) {
            global $post;
            
            if ($post instanceof WP_Post) {
                $cache_key = 'wpfb_styles_' . $post->ID . '_' . md5(implode(',', $this->used_blocks) . '_' . $post->post_modified);
                $cached_styles = get_transient($cache_key);
                
                if (false !== $cached_styles) {
                    echo '<style id="wpfocusblocks-dynamic-styles">' . $cached_styles . '</style>';
                    return;
                }
            }
        }

        // Генерируем стили
        $styles = $this->generate_styles();
        
        // Кэшируем результат
        if ($cache_enabled && isset($post) && $post instanceof WP_Post) {
            $cache_time = isset($this->settings['cache_time']) ? intval($this->settings['cache_time']) : 3600;
            set_transient($cache_key, $styles, $cache_time);
        }
        
        // Выводим стили
        echo '<style id="wpfocusblocks-dynamic-styles">' . $styles . '</style>';
    }

    /**
     * Генерирует стили для используемых блоков
     *
     * @return string CSS-стили
     */
    private function generate_styles() {
        $styles = '';
        
        // Добавляем общие стили для всех блоков
        $styles .= $this->get_common_styles();
        
        // Добавляем стили для каждого используемого блока
        foreach ($this->used_blocks as $block_id) {
            $styles .= $this->get_block_styles($block_id);
        }
        
        // Добавляем адаптивные стили
        $styles .= $this->get_responsive_styles();
        
        return $styles;
    }

    /**
     * Возвращает общие стили для всех блоков
     *
     * @return string CSS-стили
     */
    private function get_common_styles() {
        $styles = '
/* WPFocusBlocks - Общие стили */
.ostorozhno1, 
.okey1, 
.vnimanie1, 
.vopros1, 
.kstati1, 
.palec_vverh1, 
.palec_vniz1, 
.quote, 
.pro {
    padding: 4% 4% 3% 4%;
    margin: 3% 0;
    position: relative;
    border-radius: 10px;
}
.ostorozhno1 p, 
.ostorozhno1 > img, 
.ostorozhno1 > div, 
.okey1 p, 
.okey1 > img, 
.okey1 > div, 
.vnimanie1 p, 
.vnimanie1 > img, 
.vnimanie1 > div, 
.vopros1 p, 
.vopros1 > img, 
.vopros1 > div, 
.kstati1 p, 
.kstati1 > img, 
.kstati1 > div, 
.palec_vverh1 p, 
.palec_vverh1 > img, 
.palec_vverh1 > div, 
.palec_vniz1 p, 
.palec_vniz1 > img, 
.palec_vniz1 > div, 
.quote p, 
.quote > img, 
.quote > div, 
.pro p,
.pro > img,
.pro > div
{
    margin: 0 0 1% 10%;
    font-size: 120%;
}
.ostorozhno1 a, 
.okey1 a, 
.vnimanie1 a, 
.vopros1 a, 
.kstati1 a, 
.palec_vverh1 a, 
.palec_vniz1 a, 
.quote a, 
.pro a {
    color: #000000;
    text-decoration: underline;
}
.ostorozhno1::before,
.okey1::before,
.vnimanie1::before,
.vopros1::before,
.kstati1::before,
.palec_vverh1::before,
.palec_vniz1::before,
.quote::before,
.pro::before
{
    font-family: sans-serif;
    font-weight: 900;
    font-size: 3em;
    position: absolute;
    top: 50%;
    margin-top: -30px;
    left: 3.5%;
    width: 50px;
    height: 50px;
}';
        
        return $styles;
    }

    /**
     * Возвращает стили для конкретного блока
     *
     * @param string $block_id Идентификатор блока
     * @return string CSS-стили
     */
    private function get_block_styles($block_id) {
        $styles = '';
        
        switch ($block_id) {
            case 'red':
                $styles .= '
.ostorozhno1 {
    background: linear-gradient(45deg, #FFE3DB, #FFEBD8);
}
.ostorozhno1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%23e74c3c\' d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'green':
                $styles .= '
.okey1 {
    background: linear-gradient(45deg, #DEF9E5, #EFFBCE);
}
.okey1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%232ecc71\' d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'yellow':
                $styles .= '
.vnimanie1 {
    background: linear-gradient(45deg, #FFF4D4, #FFEADC);
}
.vnimanie1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%23f39c12\' d=\'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'blue':
                $styles .= '
.vopros1 {
    background: linear-gradient(45deg, #E3F1F4, #E3EDFF);
}
.vopros1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%233498db\' d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2h-2c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'purple':
                $styles .= '
.kstati1 {
    background: linear-gradient(45deg, #EFE2F6, #F6E2F0);
}
.kstati1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%239b59b6\' d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'tup':
                $styles .= '
.palec_vverh1 {
    background: linear-gradient(45deg, #DEF9E5, #EFFBCE);
}
.palec_vverh1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%232ecc71\' d=\'M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01L23 10z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'tdown':
                $styles .= '
.palec_vniz1 {
    background: linear-gradient(45deg, #FFE3DB, #FFEBD8);
}
.palec_vniz1::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%23e74c3c\' d=\'M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v1.91l.01.01L1 14c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z\'/%3E%3C/svg%3E");
}';
                break;
                
            case 'quote':
                $styles .= '
.quote {
    background: linear-gradient(45deg, #E5FEFF, #DDF5F6);
    font-size: 100%;
    border: none;
    font-style: normal;
}
.quote::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%231abc9c\' d=\'M6 17h3l2-4V7H5v6h3l-2 4zm8 0h3l2-4V7h-6v6h3l-2 4z\'/%3E%3C/svg%3E");
    z-index: 1;
}
.quote p {
    color: #444444;
    margin-left: 4%;
}
.quote p:before {
    content: \'\';
}';
                break;
                
            case 'pro':
                $styles .= '
.pro {
    border: 1px solid #B5D043;
    background-color: rgba(128,128,128,0.05);
    background-image: linear-gradient(135deg, rgba(255, 255, 255, 1) 25%, transparent 0, transparent 50%, rgba(255, 255, 255, 1) 0, rgba(255, 255, 255, 1) 75%, transparent 0);
    background-size: 50px 50px;
    animation: bg-animate 5s linear infinite;
}

@keyframes bg-animate {
    0% {background-position: 0 0;}
    100% {background-position: 100px 0;}
}

.pro::before {
    content: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' width=\'50\' height=\'50\'%3E%3Cpath fill=\'%23B5D043\' d=\'M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z\'/%3E%3C/svg%3E");
}
.pro a:hover:before,
.pro a:focus:before {
    width: 0%;
}
.pro a:hover,
.pro a:focus {
    color: #B5D043;
}';
                break;
                
            // Маркеры и подчеркивания
            case 'myellow':
                $styles .= '
.myellow {
    padding: 2px 2px;
    background: rgba(243,156,18,0.15);
}';
                break;
                
            case 'mred':
                $styles .= '
.mred {
    padding: 2px 2px;
    background: rgba(231,76,60,0.15);
}';
                break;
                
            case 'mgreen':
                $styles .= '
.mgreen {
    padding: 2px 2px;
    background: rgba(46,204,113,0.15);
}';
                break;
                
            case 'msilver':
                $styles .= '
.msilver {
    padding: 2px 2px;
    background: rgba(127,140,141,0.15);
}';
                break;
                
            case 'lblue':
                $styles .= '
.lblue {
    padding-bottom: 1px;
    border-bottom: 1px solid #3498DB;
}';
                break;
                
            case 'lred':
                $styles .= '
.lred {
    padding-bottom: 1px;
    border-bottom: 1px solid #e74c3c;
}';
                break;
                
            case 'lgreen':
                $styles .= '
.lgreen {
    padding-bottom: 1px;
    border-bottom: 1px solid #2ecc71;
}';
                break;
                
            case 'lyellow':
                $styles .= '
.lyellow {
    padding-bottom: 1px;
    border-bottom: 1px solid #f39c12;
}';
                break;
        }
        
        return $styles;
    }
    
    /**
     * Возвращает адаптивные стили для мобильных устройств
     *
     * @return string CSS-стили
     */
    private function get_responsive_styles() {
        return '
@media (max-width: 770px) {
    .ostorozhno1::before,
    .okey1::before,
    .vnimanie1::before,
    .vopros1::before,
    .kstati1::before,
    .palec_vverh1::before,
    .palec_vniz1::before,
    .quote::before,
    .pro::before {
        content: none;
    }
    
    .ostorozhno1 p, 
    .okey1 p, 
    .vnimanie1 p, 
    .vopros1 p, 
    .kstati1 p, 
    .palec_vverh1 p, 
    .palec_vniz1 p, 
    .quote p, 
    .pro p {
        margin: 0;
    }
}';
    }
    
    /**
     * Очищает кэш стилей для конкретной записи
     *
     * @param int $post_id ID записи
     * @param WP_Post $post Объект записи
     */
    public function clear_post_cache($post_id, $post) {
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Удаляем кэши для этой записи
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_wpfb_blocks_' . $post_id . '_%',
            '_transient_wpfb_styles_' . $post_id . '_%'
        ));
    }
    
    /**
     * Очищает все кэши стилей
     */
    public function clear_all_caches() {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wpfb_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_wpfb_%'");
    }
}

// Инициализация
WPFocusBlocks_Dynamic_Styles::get_instance();