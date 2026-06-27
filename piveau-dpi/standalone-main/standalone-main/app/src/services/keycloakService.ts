// @ts-nocheck
/* eslint-disable */

// Allow usage of this.$keycloak in components
declare module '@vue/runtime-core' {
  interface ComponentCustomProperties  {
    $keycloak: VueKeycloakInstance
  }
}

import { reactive } from 'vue';
import Keycloak from 'keycloak-js';
import qs from 'qs';
import axios from 'axios';
import {
  store
} from '@piveau/piveau-hub-ui-modules';

let installed = false;
let rtpToken = null;

export default {
  install(app, params = {}) {
    if (installed) return;
    installed = true;

    const defaultParams = {
      config: window.__BASEURL__ ? `${window.__BASEURL__}/config` : '/config',
      init: { onLoad: 'login-required' },
    };
    const options = Object.assign({}, defaultParams, params);
    if (assertOptions(options).hasError) throw new Error(`Invalid options given: ${assertOptions(options).error}`);

    const watch = reactive({
      ready: false,
      authenticated: false,
      userName: null,
      fullName: null,
      token: null,
      rtpToken: null,
      tokenParsed: null,
      logoutFn: null,
      loginFn: null,
      login: null,
      createLoginUrl: null,
      createLogoutUrl: null,
      createRegisterUrl: null,
      register: null,
      accountManagement: null,
      createAccountUrl: null,
      loadUserProfile: null,
      loadUserInfo: null,
      subject: null,
      idToken: null,
      idTokenParsed: null,
      realmAccess: null,
      resourceAccess: null,
      refreshToken: null,
      refreshTokenParsed: null,
      timeSkew: null,
      responseMode: null,
      responseType: null,
      hasRealmRole: null,
      hasResourceRole: null,
      getRtpToken: null,
    });

    getConfig(options.config)
    .then((config) => {
      init(config, watch, options);
      Object.defineProperty(app.config.globalProperties, '$keycloak', {
        get() {
          return watch;
        },
      });
    })
    .catch((err) => {
      console.error(err);
    });
  },
};

function init(config, watch, options) {
  const ctor = sanitizeConfig(config);
  const keycloak = new Keycloak(ctor);

  keycloak.onReady = function (authenticated) {
    updateWatchVariables(authenticated);
    watch.ready = true;
    typeof options.onReady === 'function' && options.onReady(keycloak, watch);
  };

  keycloak.onAuthSuccess = function () {
    // Check token validity every 10 seconds (10 000 ms) and, if necessary, update the token.
    // Refresh token if it's valid for less then 60 seconds
    const updateTokenInterval = setInterval(() => keycloak.updateToken(60)
    .then((hasRefreshed) => {
      if (hasRefreshed) {
        // When the auth token refreshes, 'invalidate' the stored rtpToken
        // to force getting a new rtpToken the next time
        rtpToken = null;
      }
    })
    .catch(() => {
      rtpToken = null;
      keycloak.clearToken();
    }), 10000);

    watch.logoutFn = () => {
      clearInterval(updateTokenInterval);
    };
  };

  keycloak.onAuthRefreshSuccess = function () {
    updateWatchVariables(true);
  };

  keycloak.init(options.init)
  .catch((err) => {
    typeof options.onInitError === 'function' && options.onInitError(err);
  });

  let updateTokenTimeout = null;

  function getRtpToken({ autoRefresh = false, refreshToken = null} = {}) {
    const rtpConfig = options.config.rtp;
    const baseUrl = options.config.url;
    const realm = options.config.realm;
    const token = keycloak.token;
    const endpoint = `${baseUrl}/realms/${realm}/protocol/openid-connect/token`;
    const requestBody = {
      grant_type: rtpConfig.grand_type,
      audience: rtpConfig.audience,
      ...refreshToken ? { refresh_token: refreshToken } : {},
    };

    return new Promise((resolve, reject) => {
      if (rtpToken && !refreshToken) {
        resolve(rtpToken)
        return;
      }

      axios.post(endpoint, qs.stringify(requestBody), {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/x-www-form-urlencoded',
        },
      }).then((response) => {
        rtpToken = response?.data?.access_token;
        const refreshInterval = response?.data?.expires_in * 1000 * 0.8;

        if (autoRefresh) {
          if (updateTokenTimeout) clearTimeout(updateTokenTimeout);
          updateTokenTimeout = setTimeout(async () => {
            await getRtpToken({ autoRefresh: true, refreshToken: response.data.refresh_token });
          }, refreshInterval);
        }
        updateWatchVariables(true);
        resolve(rtpToken);
      }).catch((error) => {
        reject(error);
      });
    });
  }

  function loginFn(options) {
    keycloak.login(options)
    .then(() => {
      store.dispatch('auth/setKeycloak', keycloak);
      store.dispatch('auth/authLogin', keycloak.authenticated);
    })
    .catch((err) => {
      console.error(`Error keycloak login: ${JSON.stringify(err)}`);
    });
  }

  function logoutFn(options) {
    keycloak.logout(options);
  }

  function updateWatchVariables(isAuthenticated = false) {
    watch.authenticated = isAuthenticated;
    watch.loginFn = loginFn;
    watch.login = keycloak.login;
    watch.createLoginUrl = keycloak.createLoginUrl;
    watch.logoutFn = logoutFn;
    watch.logout = keycloak.logout;
    watch.createLogoutUrl = keycloak.createLogoutUrl;
    watch.createRegisterUrl = keycloak.createRegisterUrl;
    watch.register = keycloak.register;
    if (isAuthenticated) {
      watch.accountManagement = keycloak.accountManagement;
      watch.createAccountUrl = keycloak.createAccountUrl;
      watch.hasRealmRole = keycloak.hasRealmRole;
      watch.hasResourceRole = keycloak.hasResourceRole;
      watch.loadUserProfile = keycloak.loadUserProfile;
      watch.loadUserInfo = keycloak.loadUserInfo;
      watch.token = keycloak.token;
      watch.rtpToken = rtpToken;
      watch.subject = keycloak.subject;
      watch.idToken = keycloak.idToken;
      watch.idTokenParsed = keycloak.idTokenParsed;
      watch.realmAccess = keycloak.realmAccess;
      watch.resourceAccess = keycloak.resourceAccess;
      watch.refreshToken = keycloak.refreshToken;
      watch.refreshTokenParsed = keycloak.refreshTokenParsed;
      watch.timeSkew = keycloak.timeSkew;
      watch.responseMode = keycloak.responseMode;
      watch.responseType = keycloak.responseType;
      watch.tokenParsed = keycloak.tokenParsed;
      watch.userName = keycloak.tokenParsed.preferred_username;
      watch.fullName = keycloak.tokenParsed.name;
      watch.getRtpToken = getRtpToken
    }
  }
}

function assertOptions(options) {
  const {
    config, init, onReady, onInitError,
  } = options;
  if (typeof config !== 'string' && !_isObject(config)) {
    return { hasError: true, error: `'config' option must be a string or an object. Found: '${config}'` };
  }
  if (!_isObject(init) || typeof init.onLoad !== 'string') {
    return { hasError: true, error: `'init' option must be an object with an 'onLoad' property. Found: '${init}'` };
  }
  if (onReady && typeof onReady !== 'function') {
    return { hasError: true, error: `'onReady' option must be a function. Found: '${onReady}'` };
  }
  if (onInitError && typeof onInitError !== 'function') {
    return { hasError: true, error: `'onInitError' option must be a function. Found: '${onInitError}'` };
  }
  return {
    hasError: false,
    error: null,
  };
}

function _isObject(obj) {
  return obj !== null && typeof obj === 'object' && Object.prototype.toString.call(obj) !== '[object Array]';
}

function getConfig(config) {
  if (_isObject(config)) return Promise.resolve(config);
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', config);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          resolve(JSON.parse(xhr.responseText));
        } else {
          reject(Error(xhr.statusText));
        }
      }
    };
    xhr.send();
  });
}

function sanitizeConfig(config) {
  const renameProp = (oldProp, newProp, { [oldProp]: old, ...others }) => ({
    [newProp]: old,
    ...others,
  });
  return Object.keys(config).reduce((previous, key) => {
    if (['authRealm', 'authUrl', 'authClientId'].includes(key)) {
      const cleaned = key.replace('auth', '');
      const newKey = cleaned.charAt(0).toLowerCase() + cleaned.slice(1);
      return renameProp(key, newKey, previous);
    }
    return previous;
  }, config);
}
