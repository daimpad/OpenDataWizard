/**
 * Open Data Wizard — Datenkatalog (Filterformular)
 *
 * Vanilla JS, keine Abhängigkeiten. Ohne dieses Skript funktioniert das
 * Formular vollständig, nur schlichter:
 *
 * - Schlagworte: Aus dem nativen Mehrfach-Auswahlfeld (select[multiple]
 *   [data-odw-multiselect]) wird ein Aufklappfeld mit Suche und Kästchen.
 *   Das Auswahlfeld bleibt verborgen im Formular und wird mitgeschickt; die
 *   Kästchen setzen nur seine Optionen. Beschriftungen kommen übersetzt aus
 *   den data-Attributen.
 * - Auf schmalen Bildschirmen wird der Bereich „Filter und Suche"
 *   (details.odw-catalog__toggle) eingeklappt, damit die Liste gleich zu
 *   sehen ist. Die aktiven Filter stehen als Chips darunter.
 */
(function () {
    'use strict';

    var NARROW = '(max-width: 40em)';
    var counter = 0;

    function make(tag, attrs, text) {
        var node = document.createElement(tag);
        Object.keys(attrs || {}).forEach(function (name) {
            node.setAttribute(name, attrs[name]);
        });
        if (text) {
            node.textContent = text;
        }
        return node;
    }

    /**
     * Mehrfach-Auswahlfeld → Aufklappfeld.
     *
     * @param {HTMLSelectElement} select Das native Auswahlfeld.
     */
    function enhanceMultiselect(select) {
        var field = select.parentNode;
        var id = select.id || 'odw-ms-' + (++counter);
        var label = field.querySelector('label[for="' + id + '"]');
        var hint = document.getElementById(select.getAttribute('aria-describedby') || '');
        var data = select.dataset;

        var valueId = id + '-value';
        var panelId = id + '-panel';
        if (label && !label.id) {
            label.id = id + '-label';
        }

        var button = make('button', {
            type: 'button',
            id: id + '-button',
            'class': 'odw-ms__button',
            'aria-expanded': 'false',
            'aria-controls': panelId,
            'aria-labelledby': (label ? label.id + ' ' : '') + valueId
        });
        var value = make('span', { id: valueId });
        button.appendChild(value);

        var panel = make('div', { id: panelId, 'class': 'odw-ms__panel' });
        panel.hidden = true;

        var search = make('input', {
            type: 'search',
            'class': 'odw-ms__search',
            id: id + '-search',
            placeholder: data.labelSearch || '',
            'aria-label': data.labelSearch || '',
            autocomplete: 'off'
        });
        var list = make('fieldset', { 'class': 'odw-ms__list' });
        list.appendChild(make('legend', { 'class': 'odw-sr-only' }, label ? label.textContent : ''));

        Array.prototype.forEach.call(select.options, function (option, index) {
            var row = make('label', { 'data-text': option.value.toLowerCase() });
            var box = make('input', { type: 'checkbox', id: id + '-opt-' + index });
            box.checked = option.selected;
            box.addEventListener('change', function () {
                option.selected = box.checked;
                summarize();
            });
            row.appendChild(box);
            row.appendChild(document.createTextNode(' ' + option.text));
            list.appendChild(row);
        });

        var foot = make('p', { 'class': 'odw-ms__foot' });
        var clear = make('button', { type: 'button', 'class': 'odw-ms__clear' }, data.labelClear || '');
        var done = make('button', { type: 'button', 'class': 'odw-ms__done' }, data.labelDone || '');
        foot.appendChild(clear);
        foot.appendChild(done);

        panel.appendChild(search);
        panel.appendChild(list);
        if (data.hint) {
            panel.appendChild(make('p', { 'class': 'odw-ms__hint' }, data.hint));
        }
        panel.appendChild(foot);

        function chosen() {
            return Array.prototype.filter.call(select.options, function (option) {
                return option.selected;
            }).map(function (option) {
                return option.value;
            });
        }

        function summarize() {
            var names = chosen();
            if (!names.length) {
                value.textContent = data.labelNone || '';
            } else if (names.length <= 2) {
                value.textContent = names.join(', ');
            } else {
                value.textContent = (data.labelMany || '%d').replace('%d', String(names.length));
            }
        }

        function open(state, focusButton) {
            panel.hidden = !state;
            button.setAttribute('aria-expanded', state ? 'true' : 'false');
            if (state) {
                search.focus();
            } else if (focusButton) {
                button.focus();
            }
        }

        button.addEventListener('click', function () {
            open(panel.hidden, false);
        });
        done.addEventListener('click', function () {
            open(false, true);
        });
        clear.addEventListener('click', function () {
            Array.prototype.forEach.call(select.options, function (option) {
                option.selected = false;
            });
            Array.prototype.forEach.call(list.querySelectorAll('input'), function (box) {
                box.checked = false;
            });
            summarize();
        });
        search.addEventListener('input', function () {
            var needle = search.value.trim().toLowerCase();
            Array.prototype.forEach.call(list.querySelectorAll('label'), function (row) {
                row.hidden = needle !== '' && row.getAttribute('data-text').indexOf(needle) === -1;
            });
        });
        // Enter im Suchfeld soll nicht das ganze Formular abschicken.
        search.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
        panel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                open(false, true);
            }
        });
        document.addEventListener('click', function (event) {
            if (!panel.hidden && !field.contains(event.target)) {
                open(false, false);
            }
        });

        if (label) {
            label.htmlFor = button.id;
        }
        select.hidden = true;
        if (hint) {
            hint.hidden = true;
        }
        field.classList.add('odw-ms');
        field.appendChild(button);
        field.appendChild(panel);
        summarize();
    }

    /**
     * Auf schmalen Bildschirmen einklappen, auf breiten immer offen. Ein
     * Hinweis zu einer ungültigen Eingabe bleibt sichtbar.
     *
     * @param {HTMLDetailsElement} details Der Bereich um das Formular.
     */
    function collapse(details) {
        if (!window.matchMedia) {
            return;
        }
        var query = window.matchMedia(NARROW);
        var keepOpen = !!details.querySelector('[aria-invalid="true"]');

        function apply() {
            details.open = keepOpen || !query.matches;
        }

        apply();
        if (query.addEventListener) {
            query.addEventListener('change', apply);
        } else if (query.addListener) {
            query.addListener(apply);
        }
    }

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('select[data-odw-multiselect]'), enhanceMultiselect);
        Array.prototype.forEach.call(document.querySelectorAll('details.odw-catalog__toggle'), collapse);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
