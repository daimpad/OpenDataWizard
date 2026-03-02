/**
 * Open Data Wizard — Tab Navigation
 *
 * Vanilla JS, keine jQuery-Abhängigkeit.
 * Nutzt Carbon Fields native Tab-Struktur und sessionStorage für Tab-Zustand.
 */
(function () {
    'use strict';

    var SESSION_KEY = 'odw_active_tab';

    /**
     * Warte auf Carbon Fields Tab-Rendering.
     */
    function waitForTabs(callback, attempts) {
        attempts = attempts || 0;
        var tabs = document.querySelectorAll('.cf-container__tabs-nav li');

        if (tabs.length > 0) {
            callback(tabs);
        } else if (attempts < 20) {
            setTimeout(function () {
                waitForTabs(callback, attempts + 1);
            }, 200);
        }
    }

    /**
     * Initialisiert Tab-Zustand aus sessionStorage.
     */
    function restoreTab(tabs) {
        var savedLabel = sessionStorage.getItem(SESSION_KEY);
        if (!savedLabel) {
            return;
        }

        tabs.forEach(function (tab) {
            var labelEl = tab.querySelector('.cf-tab__label');
            if (labelEl && labelEl.textContent.trim() === savedLabel) {
                tab.click();
            }
        });
    }

    /**
     * Speichert aktiven Tab-Namen in sessionStorage.
     */
    function persistTab(tabs) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var labelEl = tab.querySelector('.cf-tab__label');
                if (labelEl) {
                    sessionStorage.setItem(SESSION_KEY, labelEl.textContent.trim());
                }
            });
        });
    }

    /**
     * Fügt aktive Klasse für visuelle Hervorhebung hinzu.
     */
    function enhanceActiveStyle(tabs) {
        var observer = new MutationObserver(function () {
            tabs.forEach(function (tab) {
                var isActive = tab.classList.contains('cf-tab--active');
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        });

        tabs.forEach(function (tab) {
            observer.observe(tab, { attributes: true, attributeFilter: ['class'] });
        });
    }

    /**
     * Keyboard navigation für Tabs (Accessibility).
     */
    function addKeyboardNav(tabs) {
        tabs.forEach(function (tab, idx) {
            tab.setAttribute('tabindex', '0');
            tab.setAttribute('role', 'tab');

            tab.addEventListener('keydown', function (e) {
                var newIdx = -1;
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    newIdx = (idx + 1) % tabs.length;
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    newIdx = (idx - 1 + tabs.length) % tabs.length;
                }

                if (newIdx >= 0) {
                    e.preventDefault();
                    tabs[newIdx].click();
                    tabs[newIdx].focus();
                }
            });
        });
    }

    /**
     * Init.
     */
    function init() {
        waitForTabs(function (tabs) {
            var tabsArray = Array.prototype.slice.call(tabs);
            persistTab(tabsArray);
            enhanceActiveStyle(tabsArray);
            addKeyboardNav(tabsArray);
            restoreTab(tabsArray);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
