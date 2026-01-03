# Translations

## Adding a New Language

1. **Admin Panel Method**:
   - Go to `/admin/languages`.
   - Use the "Import / Upload JSON" section.
   - Enter the language code (e.g., `fr`) and upload a valid JSON file.
   - The system will create the file and add the language to the database.

2. **Manual Method**:
   - Create a new directory in `locales/` (e.g., `locales/fr/`).
   - Create `messages.json` inside that directory.
   - Run the migration or manually insert into `languages` table.
   - (Or just visit the Admin Panel, which syncs the directory structure on load).

## Translation Keys

All UI strings should be in `locales/en/messages.json`. Use the `__('key')` helper function in PHP.

### Placeholders
You can use placeholders in your strings:
```json
"welcome": "Welcome, :name"
```

Usage in PHP:
```php
echo __('welcome', ['name' => $userName]);
```

## Missing Keys
The Admin Language Manager reports how many keys are missing in secondary languages compared to English.

## Storage
- **Files**: JSON files in `locales/{code}/messages.json`.
- **Database**: `languages` table stores the list of available languages and their enabled status.
- **User Preference**: Stored in `users` table (`language` column) and `app_lang` cookie.
