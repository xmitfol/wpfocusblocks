<?php
/**
 * Класс для административного интерфейса
 *
 * @package WPFocusBlocks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для управления административным интерфейсом
 */
class WPFocusBlocks_Admin {
    /**
     * Экземпляр класса
     *
     * @var WPFocusBlocks_Admin
     */
    private static $instance = null;

    /**
     * Получение экземпляра класса
     *
     * @return WPFocusBlocks_Admin
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
        // Добавление страницы в меню
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Регистрация настроек
        add_action('admin_init', array($this, 'register_settings'));
        
        // Добавление ссылки на настройки на странице плагинов
        add_filter('plugin_action_links_' . WPFOCUSBLOCKS_BASENAME, array($this, 'add_settings_link'));
    }
	
	/**
	 * Регистрация скриптов и стилей для админки
	 */
	public function register_admin_assets($hook) {
		// Подключаем стили только на странице настроек плагина или страницах редактирования
		if ('settings_page_wpfocusblocks' === $hook || in_array($hook, array('post.php', 'post-new.php'))) {
			wp_enqueue_style(
				'wpfocusblocks-admin',
				WPFOCUSBLOCKS_URL . 'admin/css/admin-styles.css',
				array(),
				WPFOCUSBLOCKS_VERSION
			);
			
			// Стили для предпросмотра блоков в редакторе
			if (in_array($hook, array('post.php', 'post-new.php'))) {
				wp_enqueue_style(
					'wpfocusblocks-editor',
					WPFOCUSBLOCKS_URL . 'admin/css/editor-styles.css',
					array(),
					WPFOCUSBLOCKS_VERSION
				);
			}
			
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
     * Добавление страницы в административное меню
     */
    public function add_admin_menu() {
        add_options_page(
            __('WPFocusBlocks - Настройки', 'wpfocusblocks'),
            __('WPFocusBlocks', 'wpfocusblocks'),
            'manage_options',
            'wpfocusblocks',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Регистрация настроек плагина
     */
    public function register_settings() {
        register_setting(
            'wpfocusblocks_settings',
            'wpfocusblocks_settings',
            array($this, 'sanitize_settings')
        );
        
        // Секция "Общие настройки"
        add_settings_section(
            'wpfocusblocks_general',
            __('Общие настройки', 'wpfocusblocks'),
            array($this, 'render_general_section'),
            'wpfocusblocks'
        );
        
        // Поле для настройки кэширования
        add_settings_field(
            'cache_enabled',
            __('Кэширование стилей', 'wpfocusblocks'),
            array($this, 'render_cache_enabled_field'),
            'wpfocusblocks',
            'wpfocusblocks_general'
        );
        
        // Поле для времени кэширования
        add_settings_field(
            'cache_time',
            __('Время кэширования (секунды)', 'wpfocusblocks'),
            array($this, 'render_cache_time_field'),
            'wpfocusblocks',
            'wpfocusblocks_general'
        );
        
        // Секция "Доступные блоки"
        add_settings_section(
            'wpfocusblocks_blocks',
            __('Доступные блоки', 'wpfocusblocks'),
            array($this, 'render_blocks_section'),
            'wpfocusblocks'
        );
        
        // Добавляем поля для каждого блока
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        foreach ($all_blocks as $block_id => $block_data) {
            add_settings_field(
                'block_' . $block_id,
                $block_data['name'],
                array($this, 'render_block_enabled_field'),
                'wpfocusblocks',
                'wpfocusblocks_blocks',
                array('block_id' => $block_id, 'description' => $block_data['description'])
            );
        }
    }

    /**
     * Санитизация настроек
     *
     * @param array $input Входные данные
     * @return array Санитизированные данные
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        
        // Общие настройки
        $sanitized_input['cache_enabled'] = isset($input['cache_enabled']) ? (bool) $input['cache_enabled'] : false;
        $sanitized_input['cache_time'] = isset($input['cache_time']) ? absint($input['cache_time']) : 3600;
        
        // Ограничиваем минимальное время кэширования 60 секундами
        if ($sanitized_input['cache_time'] < 60) {
            $sanitized_input['cache_time'] = 60;
        }
        
        // Настройки блоков
        $sanitized_input['blocks_enabled'] = array();
        
        $core = WPFocusBlocks_Core::get_instance();
        $all_blocks = $core->get_available_blocks();
        
        foreach (array_keys($all_blocks) as $block_id) {
            $sanitized_input['blocks_enabled'][$block_id] = isset($input['blocks_enabled'][$block_id]) ? 
                (bool) $input['blocks_enabled'][$block_id] : false;
        }
        
        // Очищаем кэш после сохранения настроек
        $dynamic_styles = WPFocusBlocks_Dynamic_Styles::get_instance();
        $dynamic_styles->clear_all_caches();
        
        return $sanitized_input;
    }

    /**
     * Добавление ссылки на настройки на странице плагинов
     *
     * @param array $links Существующие ссылки
     * @return array Обновленные ссылки
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=wpfocusblocks') . '">' . 
            __('Настройки', 'wpfocusblocks') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Рендеринг административной страницы
     */
    public function render_admin_page() {
        // Проверяем права пользователя
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="wpfocusblocks-admin-content">
                <div class="wpfocusblocks-admin-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('wpfocusblocks_settings');
                        do_settings_sections('wpfocusblocks');
                        submit_button();
                        ?>
                    </form>
                </div>
                
                <div class="wpfocusblocks-admin-sidebar">
                    <div class="wpfocusblocks-widget">
                        <h2><?php _e('О плагине', 'wpfocusblocks'); ?></h2>
                        <p>
                            <?php _e('WPFocusBlocks добавляет красивые блоки внимания и выделения текста без влияния на скорость сайта.', 'wpfocusblocks'); ?>
                        </p>
                        <p>
                            <strong><?php _e('Версия:', 'wpfocusblocks'); ?></strong> <?php echo WPFOCUSBLOCKS_VERSION; ?>
                        </p>
                    </div>
                    
                    <div class="wpfocusblocks-widget">
                        <h2><?php _e('Как использовать', 'wpfocusblocks'); ?></h2>
                        <p>
                            <?php _e('Для добавления блока в классическом редакторе:', 'wpfocusblocks'); ?>
                        </p>
                        <ol>
                            <li><?php _e('Выделите текст в редакторе', 'wpfocusblocks'); ?></li>
                            <li><?php _e('Нажмите на соответствующую кнопку в панели инструментов', 'wpfocusblocks'); ?></li>
                        </ol>
                        <p>
                            <?php _e('Для добавления блока в редакторе Gutenberg:', 'wpfocusblocks'); ?>
                        </p>
                        <ol>
                            <li><?php _e('Добавьте блок "Классический редактор"', 'wpfocusblocks'); ?></li>
                            <li><?php _e('Используйте кнопки как в классическом редакторе', 'wpfocusblocks'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Рендеринг секции общих настроек
     */
    public function render_general_section() {
        echo '<p>' . __('Основные настройки плагина WPFocusBlocks.', 'wpfocusblocks') . '</p>';
    }

    /**
     * Рендеринг поля включения кэширования
     */
    public function render_cache_enabled_field() {
        $options = get_option('wpfocusblocks_settings');
        $cache_enabled = isset($options['cache_enabled']) ? (bool) $options['cache_enabled'] : true;
        
        ?>
        <label>
            <input type="checkbox" name="wpfocusblocks_settings[cache_enabled]" value="1" <?php checked($cache_enabled, true); ?>>
            <?php _e('Включить кэширование стилей (рекомендуется)', 'wpfocusblocks'); ?>
        </label>
        <p class="description">
            <?php _e('Кэширование стилей ускоряет работу плагина и снижает нагрузку на сервер.', 'wpfocusblocks'); ?>
        </p>
        <?php
    }

    /**
     * Рендеринг поля времени кэширования
     */
    public function render_cache_time_field() {
        $options = get_option('wpfocusblocks_settings');
        $cache_time = isset($options['cache_time']) ? absint($options['cache_time']) : 3600;
        
        ?>
        <input type="number" name="wpfocusblocks_settings[cache_time]" value="<?php echo esc_attr($cache_time); ?>" min="60" step="1">
        <p class="description">
            <?php _e('Время в секундах, на которое будут кэшироваться стили. По умолчанию: 3600 (1 час).', 'wpfocusblocks'); ?>
        </p>
        <?php
    }

    /**
     * Рендеринг секции блоков
     */
    public function render_blocks_section() {
        echo '<p>' . __('Укажите, какие блоки должны быть доступны в редакторе.', 'wpfocusblocks') . '</p>';
    }

    /**
     * Рендеринг поля включения блока
     *
     * @param array $args Аргументы поля
     */
    public function render_block_enabled_field($args) {
        $options = get_option('wpfocusblocks_settings');
        $block_id = $args['block_id'];
        $block_enabled = isset($options['blocks_enabled'][$block_id]) ? 
            (bool) $options['blocks_enabled'][$block_id] : true;
        
        ?>
        <label>
            <input type="checkbox" name="wpfocusblocks_settings[blocks_enabled][<?php echo esc_attr($block_id); ?>]" 
                   value="1" <?php checked($block_enabled, true); ?>>
            <?php _e('Включено', 'wpfocusblocks'); ?>
        </label>
        <p class="description">
            <?php echo esc_html($args['description']); ?>
        </p>
        <?php
    }
}

// Инициализация
WPFocusBlocks_Admin::get_instance();