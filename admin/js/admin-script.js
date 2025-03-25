/**
 * JavaScript для административного интерфейса WPFocusBlocks
 */
(function($) {
    'use strict';
    
    // Инициализация скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка показа/скрытия опции времени кэширования
        const $cacheEnabledCheckbox = $('input[name="wpfocusblocks_settings[cache_enabled]"]');
        const $cacheTimeField = $('input[name="wpfocusblocks_settings[cache_time]"]').closest('tr');
        
        // Функция для переключения видимости поля времени кэширования
        function toggleCacheTimeField() {
            if ($cacheEnabledCheckbox.is(':checked')) {
                $cacheTimeField.show();
            } else {
                $cacheTimeField.hide();
            }
        }
        
        // Применяем состояние при загрузке
        toggleCacheTimeField();
        
        // Добавляем обработчик изменения
        $cacheEnabledCheckbox.on('change', toggleCacheTimeField);
        
        // Выделение всех/снятие выделения со всех блоков
        $('#wpfocusblocks-toggle-all-blocks').on('click', function(e) {
            e.preventDefault();
            
            const $checkboxes = $('input[name^="wpfocusblocks_settings[blocks_enabled]"]');
            const allChecked = $checkboxes.length === $checkboxes.filter(':checked').length;
            
            $checkboxes.prop('checked', !allChecked);
        });
    });
})(jQuery);