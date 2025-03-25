(function() {
    // Создаем плагин для TinyMCE
    tinymce.create('tinymce.plugins.wpfocusblocks', {
        init: function(ed, url) {
            // Используем глобальную переменную для URL иконок, или путь относительно url как запасной вариант
            var iconsUrl = (typeof wpfocusblocks_icons_url !== 'undefined') ? 
                wpfocusblocks_icons_url : 
                url + '/../../assets/icons/';
            
            console.log("Using icons URL:", iconsUrl);
            
            // Красный блок
            ed.addButton('red', {
                title: 'Красный блок',
                image: iconsUrl + 'block_red.svg',
                onclick: function() {
                    ed.selection.setContent('[red]' + ed.selection.getContent() + '[/red]');
                }
            });
            
            // Зелёный блок
            ed.addButton('green', {
                title: 'Зелёный блок',
                image: iconsUrl + 'block_green.svg',
                onclick: function() {
                    ed.selection.setContent('[green]' + ed.selection.getContent() + '[/green]');
                }
            });
            
            // Оранжевый блок
            ed.addButton('yellow', {
                title: 'Оранжевый блок',
                image: iconsUrl + 'block_yellow.svg',
                onclick: function() {
                    ed.selection.setContent('[yellow]' + ed.selection.getContent() + '[/yellow]');
                }
            });
            
            // Синий блок
            ed.addButton('blue', {
                title: 'Синий блок',
                image: iconsUrl + 'block_blue.svg',
                onclick: function() {
                    ed.selection.setContent('[blue]' + ed.selection.getContent() + '[/blue]');
                }
            });
            
            // Сиреневый блок
            ed.addButton('purple', {
                title: 'Сиреневый блок',
                image: iconsUrl + 'block_purple.svg',
                onclick: function() {
                    ed.selection.setContent('[purple]' + ed.selection.getContent() + '[/purple]');
                }
            });
            
            // Палец вверх
            ed.addButton('tup', {
                title: 'Палец вверх',
                image: iconsUrl + 'block_tup.svg',
                onclick: function() {
                    ed.selection.setContent('[tup]' + ed.selection.getContent() + '[/tup]');
                }
            });
            
            // Палец вниз
            ed.addButton('tdown', {
                title: 'Палец вниз',
                image: iconsUrl + 'block_tdown.svg',
                onclick: function() {
                    ed.selection.setContent('[tdown]' + ed.selection.getContent() + '[/tdown]');
                }
            });
            
            // Блок цитаты
            ed.addButton('quote', {
                title: 'Блок цитаты',
                image: iconsUrl + 'block_quote.svg',
                onclick: function() {
                    ed.selection.setContent('[quote]' + ed.selection.getContent() + '[/quote]');
                }
            });
            
            // Блок PRO
            ed.addButton('pro', {
                title: 'Блок PRO',
                image: iconsUrl + 'block_pro.svg',
                onclick: function() {
                    ed.selection.setContent('[pro]' + ed.selection.getContent() + '[/pro]');
                }
            });
            
            // Выделение жёлтым
            ed.addButton('myellow', {
                title: 'Выделение жёлтым',
                image: iconsUrl + 'block_marker.svg',
                onclick: function() {
                    ed.selection.setContent('[myellow]' + ed.selection.getContent() + '[/myellow]');
                }
            });
            
            // Выделение красным
            ed.addButton('mred', {
                title: 'Выделение красным',
                image: iconsUrl + 'block_marker_red.svg',
                onclick: function() {
                    ed.selection.setContent('[mred]' + ed.selection.getContent() + '[/mred]');
                }
            });
            
            // Выделение зелёным
            ed.addButton('mgreen', {
                title: 'Выделение зелёным',
                image: iconsUrl + 'block_marker_green.svg',
                onclick: function() {
                    ed.selection.setContent('[mgreen]' + ed.selection.getContent() + '[/mgreen]');
                }
            });
            
            // Выделение серым
            ed.addButton('msilver', {
                title: 'Выделение серым',
                image: iconsUrl + 'block_marker_silver.svg',
                onclick: function() {
                    ed.selection.setContent('[msilver]' + ed.selection.getContent() + '[/msilver]');
                }
            });
            
            // Подчёркивание синим
            ed.addButton('lblue', {
                title: 'Подчёркивание синим',
                image: iconsUrl + 'block_line_blue.svg',
                onclick: function() {
                    ed.selection.setContent('[lblue]' + ed.selection.getContent() + '[/lblue]');
                }
            });
            
            // Подчёркивание красным
            ed.addButton('lred', {
                title: 'Подчёркивание красным',
                image: iconsUrl + 'block_line_red.svg',
                onclick: function() {
                    ed.selection.setContent('[lred]' + ed.selection.getContent() + '[/lred]');
                }
            });
            
            // Подчёркивание жёлтым
            ed.addButton('lyellow', {
                title: 'Подчёркивание жёлтым',
                image: iconsUrl + 'block_line_yellow.svg',
                onclick: function() {
                    ed.selection.setContent('[lyellow]' + ed.selection.getContent() + '[/lyellow]');
                }
            });
            
            // Подчёркивание зелёным
            ed.addButton('lgreen', {
                title: 'Подчёркивание зелёным',
                image: iconsUrl + 'block_line_green.svg',
                onclick: function() {
                    ed.selection.setContent('[lgreen]' + ed.selection.getContent() + '[/lgreen]');
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
})();