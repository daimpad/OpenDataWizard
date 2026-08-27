/**
 * Open Data Wizard — Tab Navigation
 *
 * Vanilla JS, keine jQuery-Abhängigkeit.
 * Nutzt Carbon Fields' native Tab-Struktur (CF 3.6:
 * ul.cf-container__tabs-list > li.cf-container__tabs-item > button,
 * aktiver Tab: li.cf-container__tabs-item--current) und sessionStorage
 * für den Tab-Zustand. role/aria-selected verwaltet Carbon Fields selbst.
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
        var tabs = document.querySelectorAll('.cf-container__tabs-list .cf-container__tabs-item');

        if (tabs.length > 0) {
            callback(tabs);
        } else if (attempts < 20) {
            setTimeout(function () {
                waitForTabs(callback, attempts + 1);
            }, 200);
        }
    }

    /**
     * Der klickbare Button innerhalb eines Tab-Listenelements.
     */
    function tabButton(tab) {
        return tab.querySelector('button');
    }

    /**
     * Sichtbarer Tab-Name (Button-Text).
     */
    function tabLabel(tab) {
        var btn = tabButton(tab);
        return (btn ? btn.textContent : tab.textContent).trim();
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
            var btn = tabButton(tab);
            if (btn && tabLabel(tab) === savedLabel) {
                btn.click();
            }
        });
    }

    /**
     * Speichert aktiven Tab-Namen in sessionStorage beim Klick
     * (Klicks auf den Button bubbeln zum li).
     */
    function persistTab(tabs) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                storage.set(SESSION_KEY, tabLabel(tab));
            });
        });
    }

    /**
     * Keyboard navigation für Tabs (Accessibility):
     * Pfeiltasten wechseln, Home/End springen an Anfang/Ende.
     */
    function addKeyboardNav(tabs) {
        tabs.forEach(function (tab, idx) {
            var btn = tabButton(tab);
            if (!btn) {
                return;
            }

            btn.addEventListener('keydown', function (e) {
                var newIdx = -1;
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    newIdx = (idx + 1) % tabs.length;
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    newIdx = (idx - 1 + tabs.length) % tabs.length;
                } else if (e.key === 'Home') {
                    newIdx = 0;
                } else if (e.key === 'End') {
                    newIdx = tabs.length - 1;
                }

                if (newIdx >= 0) {
                    e.preventDefault();
                    var target = tabButton(tabs[newIdx]);
                    if (target) {
                        target.click();
                        target.focus();
                    }
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
