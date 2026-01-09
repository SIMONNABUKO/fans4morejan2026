# Internationalization (i18n) Implementation Tasks

## How to Use
- When a task is completed, replace the `[ ]` with `[✅]` to mark it as complete.

---

## Frontend (Vue.js)

### 1. Install and Configure vue-i18n
- [✅] Install `vue-i18n` package
- [✅] Update `main.js` to set up i18n
  - **Update:** `fans/src/main.js`

### 2. Extract and Organize Translation Strings
- [ ] Create `locales/` directory for translation files
  - **Add:** `fans/src/locales/en.json`, `fans/src/locales/fr.json`, etc.
- [ ] Move all UI strings from components/views to translation files
  - **Update:** All `.vue` files in `fans/src/views/`, `fans/src/components/`, `fans/src/layouts/`

### 3. Replace Hardcoded Strings with $t()
- [ ] Refactor templates and scripts to use `$t('key')`
  - **Update:** All `.vue` files in `fans/src/views/`, `fans/src/components/`, `fans/src/layouts/`

### 4. Language Switcher Integration
- [ ] Update language switcher to change i18n locale
  - **Update:** `fans/src/views/settings/DisplaySettingsView.vue` (and any other language selector components)

### 5. API Integration: Send Language Header
- [ ] Update Axios instance to send `Accept-Language` header
  - **Update:** `fans/src/axios.js` (or wherever Axios is configured)

---

## Backend (Laravel)

### 1. Add Middleware to Set Locale
- [ ] Create `SetLocale` middleware to read `Accept-Language` header
  - **Add:** `app/Http/Middleware/SetLocale.php`
- [ ] Register middleware in `app/Http/Kernel.php`
  - **Update:** `app/Http/Kernel.php`

### 2. Update API Responses to Use Translations
- [ ] Refactor controllers and responses to use `__('key')` for messages
  - **Update:** All relevant controllers in `app/Http/Controllers/`

### 3. Add/Update Translation Files
- [ ] Add translation files for all supported languages
  - **Add/Update:** `resources/lang/en/messages.php`, `resources/lang/fr/messages.php`, etc.

### 4. Validation and Error Messages
- [ ] Ensure validation and error messages use translation files
  - **Update:** Validation logic in controllers/requests

---

## Completion
- [ ] Review and test all language switching and translation features
- [ ] Mark each completed task with a green tick `[✅]` 