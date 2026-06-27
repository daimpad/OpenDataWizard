import * as z from 'zod'

const keycloakSchema = z.object({
  realm: z.string().default('piveau'),
  url: z.string().url().optional(),
  clientId: z.string().default('piveau-hub-ui'),
  'ssl-required': z.string().default('external'),
  'public-client': z.boolean().default(true),
  'verify-token-audience': z.boolean().default(true),
  'use-resource-role-mappings': z.boolean().default(true),
  'confidential-port': z.number().default(0)
})

const keycloakInitSchema = z.object({
  pkceMethod: z.string().default(''),
  useNone: z.boolean().default(true),
  adapter: z.string().default('default'),
  onLoad: z.enum(['check-sso', 'login-required']).default('login-required'),
  token: z.string().optional(),
  refreshToken: z.string().optional(),
  idToken: z.string().optional(),
  timeSkew: z.number().optional(),
  checkLoginIframe: z.boolean().default(true),
  checkLoginIframeInterval: z.number().default(5),
  responseMode: z.enum(['query', 'fragment', 'form_post']).default('fragment'),
  flow: z.enum(['standard', 'implicit', 'hybrid']).default('standard'),
  scope: z.string().default('openid'),
  redirectUri: z.string().optional(),
  silentCheckSsoRedirectUri: z.string().optional(),
}).passthrough()

const authMiddlewareSchema = z.object({
  enable: z.boolean().default(true),
  baseUrl: z.string().default(""),
  loginRedirectUrl: z.string().default(""),
  logoutRedirectUrl: z.string().default("")
});

export const authenticationSchema = z.object({
  useService: z.boolean().default(false),
  login: z.object({
    useLogin: z.boolean().default(true),
    loginTitle: z.string().default('Login'),
    loginURL: z.string().default('/login'),
    loginRedirectUri: z.string().default('/'),
    logoutTitle: z.string().default('Logout'),
    logoutURL: z.string().default('/logout'),
    logoutRedirectUri: z.string().default('/'),
  }).default({}),
  keycloak: keycloakSchema.default({}),
  keycloakInit: keycloakInitSchema.default({}),
  rtp: z.object({
    grand_type: z.string().default('urn:ietf:params:oauth:grant-type:uma-ticket'),
    audience: z.string().default('piveau-hub-repo'),
  }).default({}),
  authToken: z.string().default(''),
  authMiddleware: authMiddlewareSchema.default({})
}).passthrough().default({})
