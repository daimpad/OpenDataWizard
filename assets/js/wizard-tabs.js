/**
 * Open Data Wizard — Tab Navigation
 *
 * Vanilla JS, keine jQuery-Abhängigkeit.
 * Nutzt Carbon Fields native Tab-Struktur und sessionStorage für Tab-Zustand.
 */
(function () {
    'use strict';

    // Post-ID aus URL für post-spezifischen Storage-Schlüssel.
    var postId = (function () {
        var match = window.location.search.match(/[?&]post=(\d+)/);
        return match ? match[1] : 'new';
    })();

    var SESSION_KEY = 'odw_active_tab_' + postId;

    /**
     * sessionStorage-Wrapper mit Fehlerbehandlung für Private-Browsing-Modus.
     */
    var storage = {
        get: function (key) {
            try {
                return sessionStorage.getItem(key);
            } catch (e) {
                return null;
            }
        },
        set: function (key, value) {
            try {
                sessionStorage.setItem(key, value);
            } catch (e) {
                // Quota exceeded oder Private-Browsing — stumm ignorieren.
            }
        }
    };

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
        var savedLabel = storage.get(SESSION_KEY);
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
     * Speichert aktiven Tab-Namen in sessionStorage beim Klick.
     */
    function persistTab(tabs) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var labelEl = tab.querySelector('.cf-tab__label');
                if (labelEl) {
                    storage.set(SESSION_KEY, labelEl.textContent.trim());
                }
            });
        });
    }

    /**
     * Setzt aria-selected Attribut via MutationObserver.
     * Gibt den Observer zurück damit er beim Entladen getrennt werden kann.
     */
    function enhanceActiveStyle(tabs) {
        var observer = new MutationObserver(function () {
            tabs.forEach(function (tab) {
                tab.setAttribute('aria-selected', tab.classList.contains('cf-tab--active') ? 'true' : 'false');
            });
        });

        tabs.forEach(function (tab) {
            observer.observe(tab, { attributes: true, attributeFilter: ['class'] });
        });

        return observer;
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
            var observer = enhanceActiveStyle(tabsArray);
            addKeyboardNav(tabsArray);
            restoreTab(tabsArray);

            // Observer beim Verlassen der Seite trennen (Speicherleck vermeiden).
            window.addEventListener('beforeunload', function () {
                observer.disconnect();
            }, { once: true });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
