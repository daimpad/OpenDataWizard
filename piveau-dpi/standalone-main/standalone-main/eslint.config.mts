import antfu from '@antfu/eslint-config'

export default antfu({
  ignores: [
    '**/__tests__/**/*.json',
    '**/__tests__/*.json',
    'tsconfig.*.json',
  ],
}, {
  rules: {
    'vue/order-in-components': 'off',
  },
})
