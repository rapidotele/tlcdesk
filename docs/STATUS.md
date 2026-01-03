# Status

## Phase 01: Foundation

### Progress
- [x] Project Structure Created
- [x] Database Schema Defined
- [x] Core Classes (Database, Model, Controller, Router)
- [x] Installer Implemented (/install)
- [x] Auth System (Login/Register/Logout)
- [x] Multi-tenancy Support (Middleware & Model Scope)
- [x] Onboarding Wizard (Placeholder Flow)
- [x] Dashboard (Basic View)
- [x] Documentation (INSTALL.md)

### Dependencies
- PHP 7.4+ or 8.x
- MySQL/MariaDB
- PDO Extension
- JSON Extension

### Notes
- No external frameworks used.
- Custom autoloader implemented in `public/index.php`.
- Installer handles database setup and first admin creation.
- Tenant isolation is enforced in `App\Core\Model` base class.
- Registration supports both Drivers and Fleet Managers.
