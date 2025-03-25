(function() {
    // Создаем плагин для TinyMCE
    tinymce.create('tinymce.plugins.wpfocusblocks', {
        init: function(editor, url) {
            var iconsUrl = typeof wpfocusblocks_icons_url !== 'undefined' ? 
                wpfocusblocks_icons_url : 
                url + '/../../assets/icons/';
            
            // Добавляем фильтры для преобразования контента
            // Преобразуем шорткоды в HTML при загрузке контента в редактор
            editor.on('BeforeSetContent', function(e) {
                if (e.content) {
                    // Преобразуем шорткоды в HTML
                    e.content = convertShortcodesToHTML(e.content);
                }
            });
            
            // Преобразуем HTML обратно в шорткоды при сохранении
            editor.on('GetContent', function(e) {
                if (e.content) {
                    // Преобразуем HTML обратно в шорткоды
                    e.content = convertHTMLToShortcodes(e.content);
                }
            });
            
            // Единая кнопка "Добавить блок внимания"
            editor.addButton('wpfocusblocks_btn', {
                title: 'Добавить блок внимания',
                image: iconsUrl + 'wpfocusblocks-icon.svg', // Общая иконка
                onclick: function() {
                    var selectedContent = editor.selection.getContent({format: 'html'});
                    var modalHeight = selectedContent.length === 0 ? 250 : 100;
                    
                    // HTML для модального окна с выбором типа блока
                    var modalHTML = '<div class="wpfocusblocks-modal-content">' +
                        '<div class="wpfocusblocks-block-icons">' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--red" data-type="red" title="Красный блок"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--green" data-type="green" title="Зелёный блок"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--yellow" data-type="yellow" title="Оранжевый блок"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--blue" data-type="blue" title="Синий блок"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--purple" data-type="purple" title="Сиреневый блок"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--tup" data-type="tup" title="Палец вверх"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--tdown" data-type="tdown" title="Палец вниз"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--quote" data-type="quote" title="Блок цитаты"></span>' +
                            '<span class="wpfocusblocks-block wpfocusblocks-block--pro" data-type="pro" title="Блок PRO"></span>' +
                        '</div>';
                    
                    // Если нет выделенного текста, добавляем поле для ввода
                    if (selectedContent.length === 0) {
                        modalHTML += '<div class="wpfocusblocks-block-textarea">' +
                            '<textarea placeholder="Введите текст"></textarea>' +
                        '</div>';
                    }
                    
                    modalHTML += '</div>';
                    
                    // Открываем модальное окно
                    editor.windowManager.open({
                        title: 'Вставить блок внимания',
                        body: [
                            {
                                type: 'container',
                                html: modalHTML
                            }
                        ],
                        width: 500,
                        height: modalHeight,
                        buttons: [
                            {
                                text: 'Вставить',
                                subtype: 'primary',
                                onclick: function() {
                                    var selectedBlock = jQuery('.wpfocusblocks-block.active');
                                    
                                    if (selectedBlock.length === 0) {
                                        alert('Пожалуйста, выберите тип блока');
                                        return;
                                    }
                                    
                                    var blockType = selectedBlock.data('type');
                                    var content = selectedContent;
                                    
                                    // Если контент не был выделен, берем из текстового поля
                                    if (content.length === 0) {
                                        content = jQuery('.wpfocusblocks-block-textarea textarea').val();
                                    }
                                    
                                    // Формируем HTML-структуру блока
                                    var cssClass = getCssClassForBlockType(blockType);
                                    var blockHTML = '<blockquote class="wpfocusblocks-' + blockType + ' ' + cssClass + '" data-wpfb-type="' + blockType + '">' +
                                        '<p>' + content + '</p>' +
                                    '</blockquote>';
                                    
                                    // Вставляем HTML в редактор
                                    editor.execCommand('mceInsertContent', false, blockHTML);
                                    editor.windowManager.close();
                                }
                            },
                            {
                                text: 'Отмена',
                                onclick: 'close'
                            }
                        ],
                        onOpen: function() {
                            // Добавляем обработчики событий для выбора блока
                            jQuery('.wpfocusblocks-block').on('click', function() {
                                jQuery('.wpfocusblocks-block').removeClass('active');
                                jQuery(this).addClass('active');
                            });
                        }
                    });
                }
            });
            
            // Добавляем индивидуальные кнопки для каждого типа блока (для обратной совместимости)
            // Красный блок
            editor.addButton('red', {
                title: 'Красный блок',
                image: iconsUrl + 'block_red.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'red');
                }
            });
            
            // Зелёный блок
            editor.addButton('green', {
                title: 'Зелёный блок',
                image: iconsUrl + 'block_green.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'green');
                }
            });
            
            // Оранжевый блок
            editor.addButton('yellow', {
                title: 'Оранжевый блок',
                image: iconsUrl + 'block_yellow.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'yellow');
                }
            });
            
            // Синий блок
            editor.addButton('blue', {
                title: 'Синий блок',
                image: iconsUrl + 'block_blue.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'blue');
                }
            });
            
            // Сиреневый блок
            editor.addButton('purple', {
                title: 'Сиреневый блок',
                image: iconsUrl + 'block_purple.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'purple');
                }
            });
            
            // Палец вверх
            editor.addButton('tup', {
                title: 'Палец вверх',
                image: iconsUrl + 'block_tup.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'tup');
                }
            });
            
            // Палец вниз
            editor.addButton('tdown', {
                title: 'Палец вниз',
                image: iconsUrl + 'block_tdown.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'tdown');
                }
            });
            
            // Блок цитаты
            editor.addButton('quote', {
                title: 'Блок цитаты',
                image: iconsUrl + 'block_quote.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'quote');
                }
            });
            
            // Блок PRO
            editor.addButton('pro', {
                title: 'Блок PRO',
                image: iconsUrl + 'block_pro.svg',
                onclick: function() {
                    insertBlockHTML(editor, 'pro');
                }
            });
            
            // Выделение жёлтым
            editor.addButton('myellow', {
                title: 'Выделение жёлтым',
                image: iconsUrl + 'block_marker.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'myellow');
                }
            });
            
            // Выделение красным
            editor.addButton('mred', {
                title: 'Выделение красным',
                image: iconsUrl + 'block_marker_red.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'mred');
                }
            });
            
            // Выделение зелёным
            editor.addButton('mgreen', {
                title: 'Выделение зелёным',
                image: iconsUrl + 'block_marker_green.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'mgreen');
                }
            });
            
            // Выделение серым
            editor.addButton('msilver', {
                title: 'Выделение серым',
                image: iconsUrl + 'block_marker_silver.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'msilver');
                }
            });
            
            // Подчёркивание синим
            editor.addButton('lblue', {
                title: 'Подчёркивание синим',
                image: iconsUrl + 'block_line_blue.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'lblue');
                }
            });
            
            // Подчёркивание красным
            editor.addButton('lred', {
                title: 'Подчёркивание красным',
                image: iconsUrl + 'block_line_red.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'lred');
                }
            });
            
            // Подчёркивание жёлтым
            editor.addButton('lyellow', {
                title: 'Подчёркивание жёлтым',
                image: iconsUrl + 'block_line_yellow.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'lyellow');
                }
            });
            
            // Подчёркивание зелёным
            editor.addButton('lgreen', {
                title: 'Подчёркивание зелёным',
                image: iconsUrl + 'block_line_green.svg',
                onclick: function() {
                    insertInlineHTML(editor, 'lgreen');
                }
            });
        },
        
        createControl: function(n, cm) {
            return null;
        },
        
        getInfo: function() {
            return {
                longname: 'WPFocusBlocks Buttons',
                author: 'WPFocusBlocks Team',
                authorurl: 'https://wpfocusblocks.com',
                infourl: 'https://wpfocusblocks.com',
                version: '1.0.0'
            };
        }
    });
    
    // Добавляем плагин в TinyMCE
    tinymce.PluginManager.add('wpfocusblocks', tinymce.plugins.wpfocusblocks);
    
    // Вспомогательные функции
    
    // Функция вставки блочного HTML
    function insertBlockHTML(editor, blockType) {
        var content = editor.selection.getContent({format: 'html'});
        var cssClass = getCssClassForBlockType(blockType);
        
        var blockHTML = '<blockquote class="wpfocusblocks-' + blockType + ' ' + cssClass + '" data-wpfb-type="' + blockType + '">' +
            '<p>' + content + '</p>' +
        '</blockquote>';
        
        editor.execCommand('mceInsertContent', false, blockHTML);
    }
    
    // Функция вставки строчного HTML (для маркеров и подчеркиваний)
    function insertInlineHTML(editor, inlineType) {
        var content = editor.selection.getContent({format: 'html'});
        var inlineHTML = '<span class="wpfocusblocks-' + inlineType + ' ' + inlineType + '" data-wpfb-type="' + inlineType + '">' + 
            content + 
        '</span>';
        
        editor.execCommand('mceInsertContent', false, inlineHTML);
    }
    
    // Получение CSS-класса для типа блока
    function getCssClassForBlockType(blockType) {
        var classes = {
            'red': 'ostorozhno1',
            'green': 'okey1',
            'yellow': 'vnimanie1',
            'blue': 'vopros1',
            'purple': 'kstati1',
            'tup': 'palec_vverh1',
            'tdown': 'palec_vniz1',
            'quote': 'quote',
            'pro': 'pro',
            'myellow': 'myellow',
            'mred': 'mred',
            'mgreen': 'mgreen',
            'msilver': 'msilver',
            'lblue': 'lblue',
            'lred': 'lred',
            'lyellow': 'lyellow',
            'lgreen': 'lgreen'
        };
        
        return classes[blockType] || '';
    }
    
	// Преобразование шорткодов в HTML
		function convertShortcodesToHTML(content) {
			// Шаблоны для блочных элементов
			var blockShortcodes = [
				{pattern: /\[red\]([\s\S]*?)\[\/red\]/g, replacement: '<blockquote class="wpfocusblocks-red ostorozhno1" data-wpfb-type="red"><p>$1</p></blockquote>'},
				{pattern: /\[green\]([\s\S]*?)\[\/green\]/g, replacement: '<blockquote class="wpfocusblocks-green okey1" data-wpfb-type="green"><p>$1</p></blockquote>'},
				{pattern: /\[yellow\]([\s\S]*?)\[\/yellow\]/g, replacement: '<blockquote class="wpfocusblocks-yellow vnimanie1" data-wpfb-type="yellow"><p>$1</p></blockquote>'},
				{pattern: /\[blue\]([\s\S]*?)\[\/blue\]/g, replacement: '<blockquote class="wpfocusblocks-blue vopros1" data-wpfb-type="blue"><p>$1</p></blockquote>'},
				{pattern: /\[purple\]([\s\S]*?)\[\/purple\]/g, replacement: '<blockquote class="wpfocusblocks-purple kstati1" data-wpfb-type="purple"><p>$1</p></blockquote>'},
				{pattern: /\[tup\]([\s\S]*?)\[\/tup\]/g, replacement: '<blockquote class="wpfocusblocks-tup palec_vverh1" data-wpfb-type="tup"><p>$1</p></blockquote>'},
				{pattern: /\[tdown\]([\s\S]*?)\[\/tdown\]/g, replacement: '<blockquote class="wpfocusblocks-tdown palec_vniz1" data-wpfb-type="tdown"><p>$1</p></blockquote>'},
				{pattern: /\[quote\]([\s\S]*?)\[\/quote\]/g, replacement: '<blockquote class="wpfocusblocks-quote quote" data-wpfb-type="quote"><p>$1</p></blockquote>'},
				{pattern: /\[pro\]([\s\S]*?)\[\/pro\]/g, replacement: '<blockquote class="wpfocusblocks-pro pro" data-wpfb-type="pro"><p>$1</p></blockquote>'}
			];
			
			// Шаблоны для строчных элементов (маркеры и подчеркивания)
			var inlineShortcodes = [
				{pattern: /\[myellow\]([\s\S]*?)\[\/myellow\]/g, replacement: '<span class="wpfocusblocks-myellow myellow" data-wpfb-type="myellow">$1</span>'},
				{pattern: /\[mred\]([\s\S]*?)\[\/mred\]/g, replacement: '<span class="wpfocusblocks-mred mred" data-wpfb-type="mred">$1</span>'},
				{pattern: /\[mgreen\]([\s\S]*?)\[\/mgreen\]/g, replacement: '<span class="wpfocusblocks-mgreen mgreen" data-wpfb-type="mgreen">$1</span>'},
				{pattern: /\[msilver\]([\s\S]*?)\[\/msilver\]/g, replacement: '<span class="wpfocusblocks-msilver msilver" data-wpfb-type="msilver">$1</span>'},
				{pattern: /\[lblue\]([\s\S]*?)\[\/lblue\]/g, replacement: '<span class="wpfocusblocks-lblue lblue" data-wpfb-type="lblue">$1</span>'},
				{pattern: /\[lred\]([\s\S]*?)\[\/lred\]/g, replacement: '<span class="wpfocusblocks-lred lred" data-wpfb-type="lred">$1</span>'},
				{pattern: /\[lyellow\]([\s\S]*?)\[\/lyellow\]/g, replacement: '<span class="wpfocusblocks-lyellow lyellow" data-wpfb-type="lyellow">$1</span>'},
				{pattern: /\[lgreen\]([\s\S]*?)\[\/lgreen\]/g, replacement: '<span class="wpfocusblocks-lgreen lgreen" data-wpfb-type="lgreen">$1</span>'}
			];
			
			// Применяем замену для блочных элементов
			blockShortcodes.forEach(function(shortcode) {
				content = content.replace(shortcode.pattern, shortcode.replacement);
			});
			
			// Применяем замену для строчных элементов
			inlineShortcodes.forEach(function(shortcode) {
				content = content.replace(shortcode.pattern, shortcode.replacement);
			});
			
			return content;
		}
		// Преобразование HTML обратно в шорткоды
    function convertHTMLToShortcodes(content) {
        if (!content) {
            return content;
        }
        
        // Создаем временный div для работы с DOM
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        
        // Преобразуем блочные элементы
        var blockquotes = tempDiv.querySelectorAll('blockquote[data-wpfb-type]');
        blockquotes.forEach(function(blockquote) {
            var blockType = blockquote.getAttribute('data-wpfb-type');
            var blockContent = '';
            
            // Собираем содержимое блока
            var paragraphs = blockquote.querySelectorAll('p');
            if (paragraphs.length > 0) {
                paragraphs.forEach(function(p) {
                    blockContent += p.innerHTML;
                });
            } else {
                blockContent = blockquote.innerHTML;
            }
            
            // Создаем шорткод
            var shortcode = '[' + blockType + ']' + blockContent + '[/' + blockType + ']';
            
            // Заменяем блок на шорткод
            var tempElement = document.createElement('div');
            tempElement.innerHTML = shortcode;
            blockquote.parentNode.replaceChild(tempElement.firstChild, blockquote);
        });
        
        // Преобразуем строчные элементы
        var spans = tempDiv.querySelectorAll('span[data-wpfb-type]');
        spans.forEach(function(span) {
            var inlineType = span.getAttribute('data-wpfb-type');
            var inlineContent = span.innerHTML;
            
            // Создаем шорткод
            var shortcode = '[' + inlineType + ']' + inlineContent + '[/' + inlineType + ']';
            
            // Заменяем строчный элемент на шорткод
            var tempElement = document.createElement('div');
            tempElement.innerHTML = shortcode;
            span.parentNode.replaceChild(tempElement.firstChild, span);
        });
        
        return tempDiv.innerHTML;
    }
})();