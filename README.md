# QualityMIS CodeIgniter 3 to CodeIgniter 4 Migration Checklist

| Document Control | Details |
|---|---|
| Project | QualityMIS |
| Document Type | Internal migration checklist and runbook |
| Target Migration | CodeIgniter 3 to CodeIgniter 4 |
| Primary Audience | Developers, QA/Testers, DevOps/Deployment Team |
| Suggested Owner | Technical Lead / Release Manager |
| Suggested Usage | Planning, execution, validation, deployment, rollback readiness |

## 1. Introduction

### 1.1 Purpose of Migration

This document provides a controlled migration checklist for moving the QualityMIS application from CodeIgniter 3 (CI3) to CodeIgniter 4 (CI4). It is intended to reduce migration risk, preserve business functionality, and provide a shared operational reference for engineering, testing, and deployment teams.

The checklist is designed for internal company usage and can be used as:

- a planning document before migration starts
- an execution checklist during implementation
- a validation checklist during SIT/UAT
- a deployment and rollback runbook for release day

### 1.2 Benefits of CodeIgniter 4 Over CodeIgniter 3

| Area | CodeIgniter 3 | CodeIgniter 4 Benefit |
|---|---|---|
| PHP support | Built for older PHP versions | Native support for modern PHP, strong compatibility with PHP 8.x |
| Architecture | Limited namespacing and legacy loading patterns | Namespaces, PSR-4 autoloading, modern project structure |
| Configuration | Array/config-file centric | Improved config classes and `.env` environment separation |
| Security | Older security defaults | Better CSRF, filters, validation, and secure request handling |
| Routing | Simpler route handling | More structured routes, filters, grouped routes, improved routing control |
| Testing | Minimal built-in testing structure | Better PHPUnit support and app/test separation |
| CLI tooling | Limited | `spark` commands for migrations, cache clear, route listing, etc. |
| Maintainability | Higher technical debt in large legacy controllers | Cleaner separation of controllers, models, views, services |
| Deployment | Often mixed web root/application root | Safer `public/` web root model and dedicated `writable/` folder |

### 1.3 Current QualityMIS Observations

The current QualityMIS repository already uses a CI4-style skeleton, but the migration checklist remains relevant because several business modules still reflect CI3-era coding patterns and should be reviewed during migration hardening.

| Observed Area | Current QualityMIS Pattern | Migration Relevance |
|---|---|---|
| Controllers | Large controllers such as `Home`, `DuplicateEntries`, and `Mod2` | Review for CI4 request handling, validation, and maintainability |
| Authentication/session guard | Custom `secure_helper` with session-based redirects | Revalidate session handling and filter-based access control |
| Models | Mix of `CodeIgniter\Model` and custom DB-wrapper classes | Normalize model usage and validation strategy |
| Reporting | Multiple raw SQL report builders and Excel exports | Validate query compatibility and output generation |
| File uploads | Upload flow in DA/HT import | Recheck upload rules, MIME validation, and writable/public paths |
| Multi-database support | Multiple database groups configured through environment settings | Verify all secondary connections in CI4 production |
| Third-party code | PhpSpreadsheet loaded via custom PSR-4 mapping | Decide whether to keep manual autoloading or move to Composer-managed dependency |

### 1.4 Recommended Migration Strategy

Use a phased migration approach instead of a big-bang rewrite.

| Phase | Goal | Exit Criteria |
|---|---|---|
| Phase 1 | Baseline inventory and backup | All modules, routes, DB connections, libraries, cron jobs, and reports documented |
| Phase 2 | Core framework migration | App boots in CI4, login works, routing resolves, primary DB connects |
| Phase 3 | Module migration | CRUD, reports, uploads, exports, and admin flows working in CI4 |
| Phase 4 | Security and performance hardening | Validation, CSRF, logging, permissions, and benchmarks approved |
| Phase 5 | UAT and production release | Business sign-off, rollback readiness, deployment approval |

### 1.5 Migration Risk Register

| Risk | Impact | Likelihood | Preventive Action | Owner |
|---|---|---|---|---|
| Legacy helper/session logic behaves differently in CI4 | Login failures or unauthorized access | Medium | Retest all protected routes and session timeout scenarios | Dev + QA |
| Route mismatches after migration | 404 errors or wrong module load | High | Freeze route inventory and test every business URL | Dev |
| Raw SQL incompatibility | Report failures or incorrect results | High | Validate each query and compare outputs with CI3 baseline | Dev + QA |
| Multi-database connection issues | Client-specific data not available | High | Test each configured DB group in staging before production | DevOps + Dev |
| File permission errors in `writable/` and `public/` | Upload/export failures and log write issues | High | Pre-validate server permissions and release-day ownership | DevOps |
| Missing third-party dependencies | Excel/report export failures | Medium | Verify PhpSpreadsheet loading path and Composer dependencies | Dev |
| Environment secrets exposed or misconfigured | Security incident or broken production | High | Use environment-specific secret management and do not commit real credentials | DevOps |

## 2. Pre-Migration Checklist

### 2.1 Pre-Migration Readiness Checklist

| Checkpoint | Description | Owner | Evidence Required | Status |
|---|---|---|---|---|
| Project backup completed | Full source-code backup taken from CI3 baseline | DevOps | Backup archive path and timestamp | `Pending` |
| Database backup completed | Full SQL dump taken for all relevant databases | DBA/DevOps | Backup filenames and restore test note | `Pending` |
| Restore test completed | Backup restoration tested on non-production environment | DBA/DevOps | Restore validation result | `Pending` |
| Module inventory created | All user-facing and background modules listed | Dev Lead | Approved inventory sheet | `Pending` |
| Route inventory created | All CI3 URLs mapped to CI4 routes | Dev | Route mapping document | `Pending` |
| Database dependency inventory created | All DB groups, external schemas, and tables documented | Dev + DBA | Connection matrix | `Pending` |
| Third-party library review completed | Libraries/helpers/plugins reviewed for CI4 compatibility | Dev | Dependency assessment | `Pending` |
| Rollback plan approved | Restore steps reviewed before implementation | Release Manager | Signed rollback checklist | `Pending` |

### 2.2 Backup Database and Project Files

Step-by-step:

1. Freeze active migration-related changes in source control.
2. Export the primary QualityMIS database.
3. Export all client-specific or secondary databases used by the application.
4. Archive the entire CI3 project directory, including hidden files.
5. Archive web server virtual host configuration and rewrite rules.
6. Archive cron job definitions, scheduled tasks, and deployment scripts if used.
7. Perform at least one restore drill on a staging or sandbox environment.

### 2.3 PHP Version Compatibility

| Item | CI3 Consideration | CI4 Requirement/Recommendation | Action |
|---|---|---|---|
| PHP runtime | Legacy CI3 apps often run on older PHP | CI4 should run on supported modern PHP; current QualityMIS codebase targets PHP 8.2 | Confirm dev, QA, and prod PHP versions are aligned |
| Dynamic properties | Often tolerated in older PHP | Deprecated in PHP 8.2 | Declare controller/model properties explicitly |
| Deprecated functions | Legacy helpers may use outdated functions | Must be cleaned or isolated | Run PHP deprecation review |
| Error handling | Hidden notices may exist in CI3 | CI4 + PHP 8.x reveals more issues | Enable verbose logging in non-production |

### 2.4 Server Requirements

| Requirement | Validation Question | Status |
|---|---|---|
| PHP version supported | Is the target server on the approved PHP version for CI4? | `Pending` |
| Extensions installed | Are `intl`, `mbstring`, `mysqli`, `json`, `openssl`, `fileinfo`, `curl` installed where needed? | `Pending` |
| Web server rewrite enabled | Is Apache `mod_rewrite` or equivalent Nginx rewrite active? | `Pending` |
| Writable directories available | Can the app write to `writable/logs`, `writable/cache`, `writable/session`, upload temp paths? | `Pending` |
| Timezone configured | Is server/application timezone aligned with business expectations? | `Pending` |
| SSL/TLS available | Is HTTPS correctly configured for production? | `Pending` |

### 2.5 Environment Setup

| Item | Action |
|---|---|
| Local development | Prepare a CI4-compatible local stack with matching PHP and extensions |
| QA/Staging | Create a staging environment structurally identical to production |
| Production | Keep production environment-specific configuration outside source-controlled defaults |
| Secrets | Move DB credentials, SMTP credentials, and other secrets to `.env` or secret store |
| Logging | Confirm per-environment logging thresholds |
| Caching | Disable or clear stale cache during migration validation |

### 2.6 Existing Third-Party Libraries/Modules Review

QualityMIS-specific review points:

| Dependency/Pattern | Observed Use | Migration Check |
|---|---|---|
| PhpSpreadsheet | Excel exports/imports in reporting and DA/HT upload flow | Confirm autoloading, class paths, memory usage, and export/import validation |
| Custom helper | `secure_helper` for access control and redirect behavior | Replace or reinforce with CI4 filters where practical |
| Custom model wrapper | `Model_common` style DB wrapper | Decide whether to keep as utility or refactor into CI4 models/services |
| DataTables server-side responses | JSON builders in model layer | Verify request parameter handling and XSS-safe output |
| Multi-database switching | Several named DB groups | Validate each group connection and fallback behavior |

## 3. Folder Structure Changes

### 3.1 CI3 vs CI4 Directory Structure

| CI3 Structure | CI4 Structure | Notes |
|---|---|---|
| `application/` | `app/` | Main application code moves into `app/` |
| `system/` | `system/` or Composer-managed framework | Avoid business logic changes inside framework folder |
| `index.php` at web root | `public/index.php` | Public entry point should be inside `public/` |
| `assets/` often at root | `public/` | Publicly accessible assets belong in `public/` |
| `cache/`, `logs/`, uploads mixed across app | `writable/` | Runtime-generated files belong in `writable/` |
| No standard `vendor/` usage in many CI3 apps | `vendor/` | Composer dependencies should live here in recommended CI4 setups |

### 3.2 Key CI4 Folders Explanation

| Folder | Purpose | QualityMIS Migration Note |
|---|---|---|
| `app/` | Controllers, Models, Views, Config, Helpers, Language, Filters | Existing business logic belongs here |
| `public/` | Only web-accessible files such as `index.php`, CSS, JS, images | Server document root must point here |
| `writable/` | Logs, sessions, cache, uploads, temp files | Validate write permissions in every environment |
| `vendor/` | Composer-installed packages | Recommended location for managed third-party libraries |

### 3.3 Public Folder Configuration on Server

Deployment checkpoint:

| Item | Required State | Status |
|---|---|---|
| Web root | Mapped to `public/` only | `Pending` |
| Direct app access blocked | `app/`, `writable/`, `.env`, and other sensitive paths not web-accessible | `Pending` |
| Asset URLs updated | CSS/JS/image references resolve from `public/` | `Pending` |
| Upload strategy reviewed | Confirm whether uploaded files belong in `public/` or protected storage under `writable/` | `Pending` |

## 4. Configuration Migration

### 4.1 Configuration Mapping

| CI3 Area | CI3 Location | CI4 Location | Migration Action |
|---|---|---|---|
| Base URL | `application/config/config.php` | `app/Config/App.php` or `.env` | Move to environment-specific config |
| Database | `application/config/database.php` | `app/Config/Database.php` and `.env` | Use named groups and secrets in `.env` |
| Autoload | `application/config/autoload.php` | `app/Config/Autoload.php` | Replace legacy loading with namespaces/helpers |
| Routes | `application/config/routes.php` | `app/Config/Routes.php` | Rebuild explicit routes and filters |
| Environment | Usually manual constants | `.env` + environment bootstrapping | Standardize per environment |

### 4.2 Base URL

Checklist:

- [ ] Set `app.baseURL` correctly in `.env`.
- [ ] Remove accidental double `/public/public/` or missing trailing slash issues.
- [ ] Confirm `app.indexPage = ''` if rewrite rules are working.
- [ ] Validate generated links, redirects, and AJAX endpoints.

Example:

```dotenv
app.baseURL = 'https://qualitymis.company.example/'
app.indexPage = ''
```

### 4.3 Database Configuration

Checklist:

- [ ] Move all credentials out of hard-coded PHP config into `.env`.
- [ ] Validate default DB group connection.
- [ ] Validate all secondary DB groups used for client-specific lookups.
- [ ] Confirm charset, collation, and port settings.
- [ ] Confirm CI4 test environment uses an isolated test database.

QualityMIS note:

The application uses multiple named database groups. This is a high-risk area because even if the main application works, client-specific report/category lookups can still fail if a single secondary connection is missing or misnamed.

### 4.4 Environment Variables (`.env`)

Best-practice checkpoints:

- [ ] Store only environment-specific values in `.env`.
- [ ] Do not commit production credentials to version control.
- [ ] Maintain separate `.env` values for local, QA, staging, and production.
- [ ] Keep `CI_ENVIRONMENT` aligned with target environment.
- [ ] Review debug and logging thresholds before production go-live.

### 4.5 Autoload Configuration

CI3:

```php
$autoload['helper'] = array('url', 'form');
$autoload['libraries'] = array('session');
```

CI4:

```php
public $helpers = ['url', 'form', 'secure'];
public $psr4 = [
    APP_NAMESPACE => APPPATH,
];
```

Checklist:

- [ ] Replace legacy library autoloading with CI4 services/helpers.
- [ ] Register custom namespaces in `app/Config/Autoload.php`.
- [ ] Review whether manually mapped third-party libraries should be Composer-managed.

### 4.6 Routes Migration

CI3:

```php
$route['login.html'] = 'home/index';
$route['default_controller'] = 'home';
```

CI4:

```php
$routes->get('/', 'Home::index');
$routes->add('login.html', 'Home::index');
$routes->add('validate', 'Home::do_job');
```

Checklist:

- [ ] Convert every CI3 route to explicit CI4 route definitions.
- [ ] Keep `setAutoRoute(false)` unless there is a strong reason to enable it.
- [ ] Validate custom URI patterns and route parameters.
- [ ] Add route filters for protected modules where appropriate.
- [ ] Test all legacy bookmarked URLs used by users.

## 5. Controller Migration

### 5.1 Namespace Usage

CI3 controllers are typically non-namespaced. CI4 controllers must use namespaces.

CI3:

```php
class Users extends CI_Controller
{
    public function index()
    {
        $this->load->view('users/list');
    }
}
```

CI4:

```php
namespace App\Controllers;

class Users extends BaseController
{
    public function index()
    {
        return view('users/list');
    }
}
```

### 5.2 Extending `BaseController`

Checklist:

- [ ] All controllers extend `BaseController`.
- [ ] Common helpers are declared once in `BaseController` or config.
- [ ] Shared session/service initialization is standardized.
- [ ] Access-control logic is reviewed for possible filter migration.

### 5.3 Request/Response Handling

CI3:

```php
$data = $this->input->post();
echo json_encode($data);
```

CI4:

```php
$data = $this->request->getPost();
return $this->response->setJSON($data);
```

Checklist:

- [ ] Replace direct `$_POST`, `$_GET`, and legacy input usage with request methods.
- [ ] Return `Response` objects for JSON, redirects, and downloads where practical.
- [ ] Standardize AJAX responses and content types.
- [ ] Avoid mixed `echo` + redirect + header output flows when refactoring.

### 5.4 Input Class Replacement

| CI3 Pattern | CI4 Replacement |
|---|---|
| `$this->input->post('field')` | `$this->request->getPost('field')` |
| `$this->input->get('field')` | `$this->request->getGet('field')` |
| `$this->input->method()` | `$this->request->getMethod()` |
| Raw `$_FILES` access | `$this->request->getFile('field')` |

### 5.5 Helper Loading

CI3:

```php
$this->load->helper('url');
```

CI4:

```php
helper('url');
```

QualityMIS note:

The current codebase already autoloads `url`, `form`, and `secure` helpers. During migration cleanup, confirm that all helper dependencies are intentional and documented.

### 5.6 Controller Migration Checkpoints

| Checkpoint | Status |
|---|---|
| All controllers namespaced | `Pending` |
| All controllers extend `BaseController` | `Pending` |
| No CI3 `Input` class usage remains | `Pending` |
| All redirects use CI4 redirect responses | `Pending` |
| Protected routes verified for session/auth behavior | `Pending` |
| JSON and download responses tested | `Pending` |

## 6. Model Migration

### 6.1 CI3 Model Conversion to CI4 Model

CI3:

```php
class User_model extends CI_Model
{
    public function getUsers()
    {
        return $this->db->get('users')->result_array();
    }
}
```

CI4:

```php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'email'];

    public function getUsers(): array
    {
        return $this->findAll();
    }
}
```

### 6.2 Query Builder Changes

Checklist:

- [ ] Review query builder chains for CI4 compatibility.
- [ ] Validate `getRowArray()`, `getResultArray()`, `getRow()`, and `getNumRows()` usage.
- [ ] Review raw SQL for quoting, date handling, and DB portability concerns.
- [ ] Refactor repeated raw SQL into reusable model/service methods where feasible.

### 6.3 Database Connection Handling

Checklist:

- [ ] Standardize `\Config\Database::connect()` usage.
- [ ] Document each named DB connection.
- [ ] Validate connection failure handling and fallback behavior.
- [ ] Avoid storing active DB connection objects in session.

### 6.4 Validation Integration

Instead of validating only at the controller level, use CI4 validation rules in models or centralized validation config where appropriate.

Example:

```php
protected $validationRules = [
    'name'  => 'required|min_length[3]',
    'email' => 'required|valid_email',
];
```

### 6.5 QualityMIS Model Refactoring Notes

| Current Pattern | Recommendation |
|---|---|
| Utility-style DB wrapper classes | Keep only if clearly documented and tested |
| Large raw SQL blocks | Move repeated reporting logic into dedicated service/model classes |
| Mixed escaping strategies | Standardize output encoding in views and parameter handling in queries |
| Manual DataTables JSON building | Keep if stable, but validate search/order inputs carefully |

## 7. View Migration

### 7.1 View Loading Changes

CI3:

```php
$this->load->view('header');
$this->load->view('users/list', $data);
$this->load->view('footer');
```

CI4:

```php
echo view('header');
echo view('users/list', $data);
echo view('footer');
```

or:

```php
return view('users/list', $data);
```

### 7.2 Passing Data to Views

Checklist:

- [ ] Replace implicit variable reliance with explicit data arrays.
- [ ] Escape output using `esc()` where appropriate.
- [ ] Remove dependency on controller-global state where possible.

### 7.3 Layout Handling

CI4 supports layout sections that are cleaner than manual header/footer assembly.

Example:

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
    <h1>User List</h1>
<?= $this->endSection() ?>
```

Checklist:

- [ ] Decide whether to keep include-style header/footer views or adopt layouts incrementally.
- [ ] Ensure all asset paths resolve from `public/`.
- [ ] Validate flash messages and session-driven UI messages.

## 8. Library and Helper Migration

### 8.1 Custom Libraries Conversion

| CI3 Style | CI4 Replacement Strategy |
|---|---|
| `application/libraries/MyLib.php` | Move to `app/Libraries/MyLib.php` or `app/Services/` |
| `$this->load->library('mylib')` | Use namespaces and instantiate/service-load class |

### 8.2 Helper Compatibility

Checklist:

- [ ] Review each custom helper for CI4-compatible redirects, services, and request access.
- [ ] Remove hard dependency on global state where possible.
- [ ] Add unit tests for helper functions that control access or encryption/decryption behavior.

### 8.3 Session Handling Changes

Checklist:

- [ ] Replace CI3 session usage with CI4 `session()` service.
- [ ] Validate login session creation, refresh, and removal.
- [ ] Validate flashdata behavior after redirects.
- [ ] Confirm session storage path/permissions under `writable/session`.
- [ ] Review session fixation and regeneration behavior after login.

### 8.4 Email Library Updates

Checklist:

- [ ] Review whether email sending is active in the CI3 implementation.
- [ ] Configure `app/Config/Email.php` or `.env` for SMTP.
- [ ] Validate sender identity, encryption, timeout, and delivery logs.
- [ ] Run functional email tests in staging.

QualityMIS note:

Current repository review shows email configuration exists, but email sending flows should still be explicitly tested during migration because configuration-only presence does not guarantee working integration.

## 9. Security Updates

### 9.1 Security Checklist

| Area | Required Validation | Status |
|---|---|---|
| CSRF | Forms and AJAX endpoints validated under CI4 CSRF settings | `Pending` |
| XSS filtering | Output escaping confirmed in views and JSON generation | `Pending` |
| Validation | Server-side validation applied to all critical forms | `Pending` |
| Authentication | Session creation, role checks, and logout flows verified | `Pending` |
| Session security | Regeneration, cookie flags, and timeout behavior verified | `Pending` |
| Upload security | File type validation, file name sanitization, and storage path review completed | `Pending` |
| Secrets management | No real credentials committed in deployable artifacts | `Pending` |

### 9.2 CSRF Handling

Checklist:

- [ ] Confirm global CSRF policy.
- [ ] Verify all POST forms include CSRF token support.
- [ ] Verify AJAX requests send token correctly if CSRF is enforced.
- [ ] Confirm exclusions, if any, are documented and approved.

### 9.3 XSS Filtering

Checklist:

- [ ] Escape all user-controlled values in views.
- [ ] Review report/export data that may include special characters.
- [ ] Review DataTables JSON output and HTML generation.

### 9.4 Validation Rules

Checklist:

- [ ] Add or confirm validation for login, user creation, report filters, uploads, and QC forms.
- [ ] Validate date formats, numeric fields, enum fields, and mandatory dropdown selections.
- [ ] Prevent invalid data from reaching query builders or raw SQL.

### 9.5 Authentication and Session Security

Checklist:

- [ ] Regenerate session ID after successful login.
- [ ] Validate logout clears all sensitive session keys.
- [ ] Confirm role-based access for admin-only routes.
- [ ] Confirm direct URL access is blocked for unauthorized users.

## 10. QualityMIS Module Migration Map

This section is recommended for execution planning because QualityMIS contains multiple business modules with different migration risk levels.

| Module | Primary Controller/Area | Migration Focus |
|---|---|---|
| Login and password reset | `Home` | Session creation, flashdata, redirects, password hashing review |
| Admin dashboard | `Home` | Role checks, menu rendering, restricted navigation |
| User/Agent management | `Agent` | CRUD, validation, status/deactivation behavior |
| Task entry and task maintenance | `Home` | Form submission, date conversions, validation, restore/edit flows |
| Reports and Excel export | `Home` | Query accuracy, export headers, performance |
| Duplicate/Multiple entries | `DuplicateEntries` | Query correctness, filters, Excel export, admin access |
| QC module | `Mod2` | Complex forms, scoring logic, report exports |
| File upload/import | `Home::save_new_daht()` | Upload validation, file parsing, storage path, duplicate prevention |
| Multi-client DB lookup | `Home::db_config()` and related methods | Secondary DB connection integrity |

## 11. Server Deployment Checklist

### 11.1 Deployment Readiness Table

| Deployment Item | Required State | Owner | Status |
|---|---|---|---|
| Apache/Nginx config updated | Server points to `public/` document root | DevOps | `Pending` |
| Rewrite rules applied | Clean URLs work without `index.php` if desired | DevOps | `Pending` |
| Public directory mapped | Only `public/` is web-accessible | DevOps | `Pending` |
| File permissions reviewed | Application files readable by web server | DevOps | `Pending` |
| `writable/` permissions reviewed | Logs, cache, session, temp, uploads writable | DevOps | `Pending` |
| Production `.env` prepared | Base URL, DB groups, mail, debug, logging set correctly | DevOps | `Pending` |
| Maintenance window approved | Business and support teams informed | Release Manager | `Pending` |
| Rollback package ready | Previous release and backups available | Release Manager | `Pending` |

### 11.2 Apache/Nginx Configuration

Checklist:

- [ ] Update virtual host/site configuration to point to `public/`.
- [ ] Confirm PHP handler/FPM pool is using approved PHP version.
- [ ] Confirm HTTPS certificates and redirect rules.
- [ ] Confirm large upload and execution limits if import/export files are used.

### 11.3 Rewrite Rules

Checklist:

- [ ] Verify CI4 rewrite rules are present and active.
- [ ] Test direct access to key routes without manual `index.php`.
- [ ] Test 404 behavior for invalid routes.

### 11.4 Public Directory Mapping

Checklist:

- [ ] Application root is not exposed directly.
- [ ] `.env` is inaccessible from the web.
- [ ] `writable/` is inaccessible from the web unless a deliberate protected download design exists.

### 11.5 File Permissions

Checklist:

- [ ] Web server user can read app files.
- [ ] Deployment user ownership and ACL strategy documented.
- [ ] No overly broad `777` style permission workaround is used.

### 11.6 Writable Folder Permissions

Checklist:

- [ ] `writable/logs` writable
- [ ] `writable/cache` writable
- [ ] `writable/session` writable
- [ ] Any temporary import/export folders writable

### 11.7 Environment Setup on Production

Checklist:

- [ ] `CI_ENVIRONMENT=production`
- [ ] Production base URL set
- [ ] All production DB groups validated
- [ ] Error display disabled, logging enabled
- [ ] Secrets loaded from secure source

## 12. Testing Checklist

### 12.1 Testing Execution Rules

Use the following approach for each module:

1. Capture the CI3 baseline behavior before migration.
2. Execute the same scenario in CI4.
3. Compare output, data written, logs generated, and user-visible messages.
4. Record pass/fail with evidence such as screenshot, export file, log reference, or SQL result.

### 12.2 Login Module

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Valid login | User lands on correct home/dashboard page | Screenshot/session check | `Pending` |
| Invalid password | Error message shown, no session created | Screenshot/log | `Pending` |
| Deactivated user login | Access blocked with correct message | Screenshot | `Pending` |
| Already logged-in redirect | Existing session redirects to home page | Screenshot | `Pending` |
| Logout | Session removed and protected routes blocked | Screenshot/log | `Pending` |
| Password reset flow | Password updated and usable on next login | DB check and login evidence | `Pending` |

### 12.3 Dashboard

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Admin dashboard load | Admin sees dashboard without error | Screenshot | `Pending` |
| Normal user redirection | Normal user redirected to allowed module | Screenshot | `Pending` |
| Menu rendering | Only authorized menu items displayed | Screenshot | `Pending` |
| Flash messages | Success/error messages display after redirects | Screenshot | `Pending` |

### 12.4 CRUD Operations

Use for user/agent records, task maintenance, restore/edit flows, and any admin-maintained master data.

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Create record | Record inserted successfully | DB row and UI confirmation | `Pending` |
| Edit record | Updated values saved correctly | Before/after DB comparison | `Pending` |
| Delete/deactivate record | Correct record state change applied | DB check and UI result | `Pending` |
| Validation failure | Invalid data blocked with correct message | Screenshot | `Pending` |
| Unauthorized access | Non-admin user cannot access protected CRUD endpoints | Screenshot/log | `Pending` |

### 12.5 Reports

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Standard report load | Results match CI3 baseline | SQL/result comparison | `Pending` |
| Date filter report | Filtered records are accurate | Export/UI comparison | `Pending` |
| User-based report | Correct user data returned | UI/export evidence | `Pending` |
| Duplicate entries report | Duplicate grouping is correct | Export evidence | `Pending` |
| Multiple entries report | Grouping and counts are correct | Export evidence | `Pending` |
| QC report | QC score/error percentage match expected logic | SQL/manual calculation | `Pending` |

### 12.6 File Upload/Download

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Valid upload file | Accepted and processed successfully | UI + DB verification | `Pending` |
| Invalid extension | Upload blocked with error | Screenshot | `Pending` |
| Duplicate upload condition | Duplicate protection message shown | Screenshot/DB check | `Pending` |
| Excel export download | File downloads successfully and opens correctly | File open validation | `Pending` |
| Large file handling | Application handles within accepted limits | Log/performance note | `Pending` |

### 12.7 Email Functionality

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| SMTP connectivity | Application connects to SMTP successfully | Log/test result | `Pending` |
| Test email send | Email delivered to test inbox | Mail evidence | `Pending` |
| Error handling | Failures logged clearly without breaking UX | Log evidence | `Pending` |

### 12.8 Session Timeout Testing

| Test Case | Expected Result | Evidence | Status |
|---|---|---|---|
| Idle timeout | Session expires after configured period | Screenshot/log | `Pending` |
| Post-timeout protected access | User redirected to login | Screenshot | `Pending` |
| Flashdata after timeout | Correct message shown | Screenshot | `Pending` |
| Concurrent session scenarios | Behavior matches business rule | QA notes | `Pending` |

### 12.9 Additional QualityMIS Project Module Checks

| Module | Validation Focus | Status |
|---|---|---|
| Multi-client category lookup | Correct secondary DB selected and category list loaded | `Pending` |
| Task entry submission | Date conversions, category/subcategory, counts, and comments saved correctly | `Pending` |
| QC form save | All scoring fields saved and calculated correctly | `Pending` |
| DA/HT import | Sheet names, data parsing, duplicate protection, and insert logic verified | `Pending` |

### 12.10 Test Execution Template

| Test ID | Module | Scenario | Preconditions | Steps | Expected Result | Actual Result | Evidence | Tester | Status |
|---|---|---|---|---|---|---|---|---|---|
| `TBD` | `TBD` | `TBD` | `TBD` | `TBD` | `TBD` | `TBD` | `TBD` | `TBD` | `Pending` |

## 13. Common Migration Errors and Fixes

| Error / Issue | Likely Cause | Fix |
|---|---|---|
| Undefined property errors | PHP 8.2 dynamic properties no longer tolerated | Declare properties explicitly in controllers/models |
| Namespace issues | Class moved to CI4 structure without namespace/use statements | Add correct namespace and imports |
| Base URL problems | Incorrect `app.baseURL` or wrong public mapping | Fix `.env`, document root, and asset paths |
| 404 routing issues | Missing explicit routes or route method mismatch | Add/update route definitions and verify HTTP methods |
| Session issues | Wrong session path, missing writable permission, or changed session behavior | Fix `writable/session` permissions and retest auth flow |
| Query Builder syntax differences | CI3 query pattern not fully compatible | Review query builder methods and raw SQL execution |
| Writable folder permission issues | Server cannot write logs/cache/sessions | Correct ownership and writable permissions |
| Download/export headers fail | Output started before headers or mixed echo/header flow | Return response cleanly and avoid pre-output whitespace |
| Upload path issues | Invalid target path or moved file logic | Validate target directory and use CI4 upload APIs correctly |
| CSRF token mismatch | Missing token in forms or AJAX | Update forms/scripts to send valid token |

## 14. Post-Migration Validation

### 14.1 Validation Checklist

| Validation Area | Action | Owner | Status |
|---|---|---|---|
| Error log monitoring | Review logs for warnings, notices, SQL failures, and missing classes | Dev + DevOps | `Pending` |
| User acceptance testing | Business users execute approved UAT scenarios | Business + QA | `Pending` |
| Database verification | Compare critical record counts and sample transactions | DBA + QA | `Pending` |
| Performance benchmarking | Compare response times and export durations vs baseline | DevOps + Dev | `Pending` |
| Security testing | Verify auth, CSRF, uploads, and direct URL access | QA + Security | `Pending` |

### 14.2 Error Log Monitoring

Checklist:

- [ ] Review application logs after deployment.
- [ ] Review web server error logs.
- [ ] Review PHP logs/FPM logs.
- [ ] Track recurring warnings, not only fatal errors.

### 14.3 User Acceptance Testing

Checklist:

- [ ] Admin users validate core flows.
- [ ] Normal users validate daily task entry flow.
- [ ] Reporting users validate exports and filters.
- [ ] QC users validate scoring and report generation.

### 14.4 Database Verification

Checklist:

- [ ] Validate record creation in primary transactional tables.
- [ ] Validate no unexpected null/default values introduced.
- [ ] Validate imported/exported data counts.
- [ ] Validate secondary DB lookups for all supported client groups.

### 14.5 Performance Benchmarking

Measure at minimum:

- login response time
- dashboard load time
- common report execution time
- Excel export generation time
- file import duration

### 14.6 Security Testing

Checklist:

- [ ] Unauthorized URL access blocked
- [ ] Session expires correctly
- [ ] Uploaded file validation enforced
- [ ] Sensitive files not publicly accessible
- [ ] Production debug output disabled

## 15. Rollback Plan

### 15.1 Backup Restore Steps

| Step | Action | Owner |
|---|---|---|
| 1 | Stop or place application in maintenance mode | DevOps |
| 2 | Confirm rollback decision and release approval | Release Manager |
| 3 | Restore previous application package | DevOps |
| 4 | Restore database backup if data rollback is required | DBA |
| 5 | Restore web server and environment configuration if changed | DevOps |
| 6 | Clear caches and restart services | DevOps |
| 7 | Run smoke tests on restored version | QA + DevOps |

### 15.2 Rollback Deployment Procedure

Checklist:

- [ ] Previous stable package/tag available
- [ ] Database restore point identified
- [ ] Rollback owner assigned
- [ ] Business communication template prepared
- [ ] Smoke test checklist ready

### 15.3 Emergency Recovery Checklist

| Item | Status |
|---|---|
| Production backup verified | `Pending` |
| Restore command/script documented | `Pending` |
| DBA available during release | `Pending` |
| DevOps available during release | `Pending` |
| Support contact list ready | `Pending` |
| Release bridge/war room defined | `Pending` |

## 16. Best Practices

### 16.1 Use Environment Files

- Keep secrets out of source-controlled PHP files.
- Maintain separate environment values per deployment tier.
- Document required environment variables for new team members.

### 16.2 Maintain Coding Standards

- Use namespaces consistently.
- Prefer thin controllers and reusable services/models.
- Avoid adding business logic to framework core files.
- Standardize naming, validation, and response patterns.

### 16.3 Logging and Monitoring

- Keep application logging enabled in production at an appropriate level.
- Monitor logs after deployment for at least the first business cycle.
- Add alerts for fatal errors, repeated DB failures, and failed imports/exports where possible.

### 16.4 Version Control Recommendations

- Use a dedicated migration branch.
- Tag the last stable CI3 release before migration.
- Tag the first stable CI4 release after sign-off.
- Keep deployment scripts and environment templates versioned separately from secrets.

### 16.5 Additional CI4 Best Practices for QualityMIS

- Prefer Composer-managed dependencies over manually copied third-party code where practical.
- Replace MD5-based password handling with modern password hashing when business constraints allow.
- Introduce route filters for role-protected modules.
- Refactor very large controllers into services or smaller domain-focused controllers over time.
- Normalize report and export logic so that business rules are reusable and testable.

## Final Sign-Off Checklist

| Area | Sign-Off By | Date | Status |
|---|---|---|---|
| Development migration complete | Dev Lead |  | `Pending` |
| QA/SIT complete | QA Lead |  | `Pending` |
| UAT complete | Business Owner |  | `Pending` |
| Deployment readiness approved | DevOps Lead |  | `Pending` |
| Rollback readiness approved | Release Manager |  | `Pending` |
| Production release approved | Project Sponsor |  | `Pending` |

