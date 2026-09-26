const eslint = require('@eslint/js');
const tseslint = require('typescript-eslint');
const angular = require('angular-eslint');

module.exports = tseslint.config(
  {
    ignores: ['../htdocs/js/ng-zxart/**', 'coverage/**', 'node_modules/**'],
  },
  {
    files: ['src/**/*.ts'],
    extends: [
      eslint.configs.recommended,
      ...tseslint.configs.recommended,
      ...angular.configs.tsRecommended,
    ],
    processor: angular.processInlineTemplates,
    rules: {
      '@typescript-eslint/no-unused-vars': ['error', {
        argsIgnorePattern: '^_',
        ignoreRestSiblings: true,
      }],
    },
  },
  {
    files: ['src/**/*.html'],
    extends: [
      ...angular.configs.templateRecommended,
    ],
  },
  {
    files: ['src/app/shared/ui/zx-tabs/zx-tabs.component.html'],
    rules: {
      '@angular-eslint/template/eqeqeq': 'off',
    },
  },
  {
    files: [
      'src/app/entities/zx-collaborators-section/zx-collaborators-section.component.ts',
      'src/app/shared/ui/zx-input/zx-input.component.ts',
    ],
    rules: {
      '@angular-eslint/no-output-native': 'off',
    },
  },
  {
    files: [
      'src/app/features/player/components/legacy-play-button/legacy-play-button.component.ts',
      'src/app/shared/ui/zx-vote/zx-vote.component.ts',
    ],
    rules: {
      '@angular-eslint/no-input-rename': 'off',
    },
  },
  {
    files: [
      'src/app/features/prods-browser/services/prods-browser.service.ts',
      'src/app/pages/firstpage/firstpage.component.ts',
      'src/app/shared/components/viewport-loader/viewport-loader.component.ts',
      'src/app/shared/services/playlist.service.ts',
    ],
    rules: {
      '@typescript-eslint/no-explicit-any': 'off',
    },
  },
  {
    files: [
      'src/app/features/emulator/engines/mame-loader.ts',
      'src/app/features/emulator/engines/mame.engine.ts',
    ],
    rules: {
      '@typescript-eslint/no-unused-expressions': 'off',
    },
  },
  {
    files: ['src/set-public-path.ts'],
    rules: {
      '@typescript-eslint/no-unused-vars': 'off',
      'no-var': 'off',
    },
  },
);
