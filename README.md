# Taylor Project
# CodeIgniter 3 to CodeIgniter 4 Migration Checklist

## Document Control

| Item | Details |
| --- | --- |
| Document title | Taylor CodeIgniter 3 to CodeIgniter 4 Migration Checklist |
| Intended audience | Developers, QA/Testers, DevOps/Deployment Teams, Technical Leads |
| Usage | Internal migration planning, execution, validation, and rollback |
| Target application | Taylor |
| Current reference state | Repository currently reflects a CodeIgniter 4 layout and configuration model |
| Primary web root | `public/` |
| Primary runtime config | `.env` and `app/Config/*` |
| Important note | Do not copy live secrets, passwords, or encryption values into working documents or tickets |

## 1. Introduction

### 1.1 Purpose of Migration

This document provides a formal migration runbook for moving the Taylor application from a legacy CodeIgniter 3 implementation model to a CodeIgniter 4 target architecture. It is designed to support planning, execution, verification, deployment, and rollback across development, testing, and operations teams.

The checklist is tailored to the Taylor project and its current application surface, including:

- Authentication and session handling in `Home` and `Loginperform`
- Dashboards and reports in `Prodreport`, `AutoProduction`, and `Viewmisrec`
- CRUD-heavy business modules in `Agent`, `Editrec`, `Custcomp`, `Processimprovement`, `Revenue`, and `Pccase`
- Email, import, and task assignment flows in `Editmail` and `Import`
- Dual database connectivity through `default` and `otherdb`
- Custom helpers, libraries, and third-party packages loaded from `app/Helpers`, `app/Libraries`, and `app/ThirdParty`

### 1.2 Benefits of CodeIgniter 4 Over CodeIgniter 3

| Area | CodeIgniter 3 | CodeIgniter 4 | Taylor Migration Benefit |
| --- | --- | --- | --- |
| PHP support | Older PHP compatibility | Modern PHP support, currently PHP 8.2+ in this repository | Aligns Taylor with supported PHP versions and modern language features |
| Architecture | Limited namespace usage | Namespaces, PSR-4 autoloading, stronger structure | Improves maintainability of large controller and module set |
| Configuration | Array/file-heavy config | `.env` plus typed config classes | Cleaner environment separation for development, UAT, and production |
| Routing | Simpler routing model | Explicit route definitions and filter support | Better control over Taylor's large route surface |
| Security | Older defaults | Improved CSRF, filters, headers, validation patterns | Better protection for session-heavy and form-heavy modules |
| Request handling | Input class pattern | Request/response abstraction | Standardizes GET, POST, file upload, and API handling |
| Testing | Limited built-in structure | Better test scaffolding and CLI support | Easier regression testing for reports, CRUD, and imports |
| Deployment | Root-level entry assumptions common in CI3 | Secure `public/` entrypoint and `writable/` separation | Safer hosting model for Taylor |

### 1.3 Migration Objective

The migration is considered successful when:

- All business modules operate under CodeIgniter 4 conventions without functional regression
- Configuration is environment-driven and reproducible
- Route mappings, sessions, uploads, exports, and database operations are validated
- Security controls are rechecked under CI4
- Deployment and rollback procedures are documented and testable

## 2. Pre-Migration Checklist

### 2.1 Readiness Checklist

| Checkpoint | Team | Action | Evidence Required | Status | Blocker |
| --- | --- | --- | --- | --- | --- |
| Full project backup completed | DevOps | Backup application code, uploads, generated assets, and server config | Backup job log and archive path | Pending |  |
| Full database backup completed | DBA/DevOps | Backup all Taylor databases, including `default` and `otherdb` source data if needed | SQL dump names and restore test evidence | Pending |  |
| PHP version validated | DevOps | Confirm target servers run PHP 8.2 or higher | `php -v` output captured in deployment record | Pending |  |
| Required PHP extensions validated | DevOps | Confirm `intl`, `mbstring`, `mysqli`, `json`, `curl`, `fileinfo` and other needed extensions | Extension inventory | Pending |  |
| Server web root approach confirmed | DevOps | Ensure virtual host or site root points to `public/` | Web server config review | Pending |  |
| Environment file strategy approved | DevOps/Lead | Define `.env` values for dev, UAT, and prod without exposing secrets | Approved configuration matrix | Pending |  |
| Third-party package review completed | Developers | Review `PhpSpreadsheet`, `php-imap`, `PHPMailer`, custom helpers/libraries | Dependency assessment table | Pending |  |
| Session strategy validated | Developers/QA | Confirm CI4 session settings, storage path, and timeout behavior | Session test results | Pending |  |
| Upload and export paths reviewed | Developers/DevOps | Confirm `uploads/` and `writable/` usage, ownership, and retention | Path review record | Pending |  |
| Regression test scope approved | QA/Lead | Approve module-wise test coverage for all major flows | Test plan sign-off | Pending |  |

### 2.2 Backup Requirements

Perform the following in order:

1. Back up the complete project directory, including `app/`, `public/`, `uploads/`, `writable/`, environment files, and server-specific configuration.
2. Back up all application databases required by Taylor.
3. Verify that the backup can be restored into a non-production environment.
4. Record exact backup timestamps, file names, storage location, and owner.
5. Freeze schema changes during the migration window unless approved by the technical lead.

### 2.3 PHP Version Compatibility

| Item | Requirement | Taylor Note | Validation |
| --- | --- | --- | --- |
| PHP runtime | 8.2+ | `composer.json` requires `^8.2` | Validate on each environment |
| Dynamic property usage | Avoid | PHP 8.2 deprecates dynamic properties | Check controllers, libraries, and models |
| Deprecated functions | Review | Legacy CI3-style code may rely on older behavior | Static review and runtime testing |

### 2.4 Server Requirements

| Requirement | Minimum Expectation | Taylor Relevance |
| --- | --- | --- |
| Web server | Apache or Nginx | Apache rewrite file exists under `public/.htaccess` |
| PHP extensions | `intl`, `mbstring`, `mysqli`, `json` | Needed for framework and database access |
| File permissions | Read access to project, write access to `writable/` | Required for logs, cache, sessions |
| Upload storage | Writable upload path | Taylor uses `uploads/` for file import and file handling |
| Database connectivity | Access to required DB hosts | Taylor uses `default` and `otherdb` connections |

### 2.5 Environment Setup

| Item | Required Action | Taylor Note |
| --- | --- | --- |
| `.env` creation | Maintain separate environment values for each stage | Current repo uses `.env` for app URL, DB, and logger settings |
| `CI_ENVIRONMENT` | Set appropriately per environment | Use `development`, `testing`, `production` as appropriate |
| Base URL | Set environment-specific application URL | CI4 layout expects `public/`-based serving |
| Logging | Set threshold per environment | Lower in production unless troubleshooting |
| Session path | Confirm absolute writable path | CI4 session path defaults to `WRITEPATH . 'session'` |

### 2.6 Existing Third-Party Libraries and Modules Review

| Dependency | Current Location | Purpose | Migration Check |
| --- | --- | --- | --- |
| `PhpSpreadsheet` | `app/ThirdParty/PhpSpreadsheet` | Excel import/export | Validate PSR-4 mapping and PHP 8.2 compatibility |
| `php-imap` | `app/ThirdParty/php-imap` | Email/IMAP processing | Validate namespace loading and mail server connectivity |
| `PHPMailer` | `app/ThirdParty/PHPMailer` | Email sending | Validate SMTP/TLS config and error handling |
| Custom helper `secure_helper` | `app/Helpers/secure_helper.php` | Access/security helper logic | Verify helper functions and CI4 compatibility |
| Custom libraries | `app/Libraries` | Business-specific utility logic | Confirm namespace, constructor, and service usage |
| Custom models | `app/Models` | Common DB logic | Review non-extended model patterns and query behavior |

### 2.7 Pre-Migration Risks

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Secrets copied into migration documents | Security incident | Use placeholders and secure credential vault references only |
| Server still points to project root instead of `public/` | Application exposure and routing issues | Validate vhost/site root before deployment |
| Legacy CI3 assumptions remain in modules | Runtime errors and regressions | Use conversion checklist and module regression testing |
| File permissions missing on `writable/` or `uploads/` | Session, log, or upload failures | Validate permissions before UAT and production cutover |

## 3. Folder Structure Changes

### 3.1 CI3 vs CI4 Directory Structure

| CI3 Structure | CI4 Structure | Purpose of Change | Taylor Note |
| --- | --- | --- | --- |
| `application/` | `app/` | Application code container | Taylor already uses `app/` |
| `application/config/` | `app/Config/` plus `.env` | Typed config classes and environment overrides | Taylor uses both |
| `application/controllers/` | `app/Controllers/` | Namespaced controllers | Taylor controllers extend `BaseController` |
| `application/models/` | `app/Models/` | Namespaced models | Taylor models exist, some do not extend `CodeIgniter\Model` |
| `application/views/` | `app/Views/` | View storage | Taylor has many module-specific views |
| Root `index.php` common in CI3 hosting | `public/index.php` | Safer public entrypoint | Must be enforced on server |
| Cache/log/session mixed usage | `writable/` | Controlled runtime writes | Required for logs, cache, sessions |
| Composer optional in many CI3 apps | `vendor/` | Dependency management | Standard CI4 pattern; Taylor also uses custom `app/ThirdParty` |

### 3.2 Explanation of Key CI4 Folders

| Folder | Purpose | Taylor Consideration |
| --- | --- | --- |
| `app/` | Business logic, controllers, models, config, helpers, views | Main application codebase |
| `public/` | Front controller, public assets, rewrite rules | Must be web root |
| `writable/` | Logs, cache, sessions, temporary writable data | Must remain writable in all environments |
| `vendor/` | Composer-managed packages | Standard CI4 dependency area |

### 3.3 Public Folder Configuration on Server

Use the following checkpoints:

1. Confirm the web server document root points to `.../Taylor/public`.
2. Confirm users never access framework files through the browser.
3. Confirm `public/.htaccess` or equivalent Nginx rules are active.
4. Confirm `index.php` can be removed from URLs only after rewrite validation.
5. Confirm asset URLs generated via `base_url()` resolve correctly.

## 4. Configuration Migration

### 4.1 Configuration Migration Table

| CI3 Area | CI3 Typical Location | CI4 Target | Taylor Note | Validation Step |
| --- | --- | --- | --- | --- |
| Base URL | `application/config/config.php` | `.env` `app.baseURL` and `Config\App::$baseURL` | Current repo uses `.env` for base URL | Open site and validate asset and route links |
| Index page | `config.php` | `.env` `app.indexPage` and `Config\App::$indexPage` | Taylor sets empty index page in `.env` | Confirm clean URLs work |
| Database config | `application/config/database.php` | `app/Config/Database.php` and `.env` | Taylor has `default` and `otherdb` groups | Validate both DB connections |
| Routes | `application/config/routes.php` | `app/Config/Routes.php` | Taylor has a large explicit route set | Smoke-test named routes by module |
| Autoload | `application/config/autoload.php` | `app/Config/Autoload.php` | Taylor maps helpers and third-party namespaces | Validate all custom classes load |
| Session config | `application/config/config.php` or library config | `app/Config/Session.php` | File-based sessions in `writable/session` | Validate login, timeout, logout |
| Security config | Mixed in config/hooks | `app/Config/Security.php` and `Filters.php` | CSRF exists but global filter is not enabled by default | Review and test before production |
| Email config | Library-based settings | `app/Config/Email.php` plus `.env` where needed | Email and task flows depend on this | Test mail send in non-prod |

### 4.2 Base URL

Checklist:

1. Set `app.baseURL` per environment.
2. Do not hardcode localhost or UAT URLs into controllers or views.
3. Confirm `base_url()` and `site_url()` resolve correctly for:
   - Login pages
   - Reports
   - AJAX endpoints such as `get-prod-repo`
   - Download/export links

### 4.3 Database Configuration

Taylor-specific points:

- `Config\Database` contains `default` and `otherdb` groups.
- The migration document must instruct teams to validate credentials, hostnames, port, charset, and collation outside source control.
- Do not document real usernames, passwords, or encryption keys.

### 4.4 Environment Variables (`.env`)

Best-practice checkpoints:

- Store environment-specific values in `.env` or secure deployment secrets.
- Keep `.env` out of shared documentation exports when it contains secrets.
- Use masked examples only, for example:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://example.company.com/'
database.default.hostname = db-host
database.default.database = app_db
database.default.username = app_user
database.default.password = ********
```

### 4.5 Autoload Configuration

Taylor currently uses:

- App namespace mapping
- PSR-4 mappings for `PhpOffice\PhpSpreadsheet`
- PSR-4 mappings for `Webklex\PHPIMAP`
- PSR-4 mappings for `PHPMailer\PHPMailer`
- Helper autoload entries for `url`, `form`, and `secure`

Validation steps:

1. Load representative controllers that use helpers.
2. Run import/export workflows.
3. Run email-related screens and mail send tests.
4. Confirm no class-not-found or namespace resolution errors occur.

### 4.6 Routes Migration

Taylor uses an explicit `app/Config/Routes.php` with a high number of route definitions. Migration review must verify:

- Route path parity with legacy URLs
- AJAX endpoints continue to resolve
- Export and download endpoints accept expected parameters
- Route changes do not break bookmarks or external references
- Any filtered or restricted routes are explicitly protected

### 4.7 CI3 vs CI4 Example: Routes Configuration

```php
// CI3: application/config/routes.php
$route['default_controller'] = 'home';
$route['login.html'] = 'home/index';
$route['prod-report.html'] = 'prodreport/index';
$route['get-prod-repo'] = 'prodreport/get_report_table_prod';
```

```php
// CI4: app/Config/Routes.php
$routes->setDefaultController('Home');
$routes->add('login.html', 'Home::index');
$routes->add('prod-report.html', 'Prodreport::index');
$routes->add('get-prod-repo', 'Prodreport::get_report_table_prod');
```

### 4.8 CI3 vs CI4 Example: Environment and Config Migration

```php
// CI3: application/config/config.php
$config['base_url'] = 'http://example.local/';
```

```php
// CI3: application/config/database.php
$db['default'] = [
    'hostname' => 'localhost',
    'username' => 'app_user',
    'password' => 'secret',
    'database' => 'app_db',
    'dbdriver' => 'mysqli',
];
```

```ini
# CI4: .env
app.baseURL = 'https://example.company.com/'
database.default.hostname = db-host
database.default.database = app_db
database.default.username = app_user
database.default.password = ********
database.default.DBDriver = MySQLi
```

```php
// CI4: app/Config/Database.php
public array $default = [
    'hostname' => 'localhost',
    'username' => '',
    'password' => '',
    'database' => '',
    'DBDriver' => 'MySQLi',
];
```

## 5. Controller Migration

### 5.1 Controller Conversion Checklist

| Topic | CI3 Pattern | CI4 Pattern | Taylor Note |
| --- | --- | --- | --- |
| Base class | `CI_Controller` | `BaseController` | Taylor controllers already extend `BaseController` |
| Namespace | Often absent | `namespace App\Controllers;` | Required for every controller |
| Input | `$this->input` | `$this->request` | Common conversion point |
| Output | Echo/load view directly | `return view()` or response methods | Taylor commonly uses `echo view()` |
| Helpers | `$this->load->helper()` | `helper()` or autoloaded helpers | Taylor autoloads common helpers |
| Session | `$this->session` via CI3 loader | `session()` or `service('session')` | Taylor uses `session()` and stored property usage |

### 5.2 CI3 vs CI4 Example: Controller Inheritance and Namespace

```php
// CI3
class Report extends CI_Controller
{
    public function index()
    {
        $this->load->view('report_view');
    }
}
```

```php
// CI4
namespace App\Controllers;

class Report extends BaseController
{
    public function index()
    {
        return view('report_view');
    }
}
```

### 5.3 CI3 vs CI4 Example: Request Input Handling

```php
// CI3
$post = $this->input->post();
$from = $this->input->post('date_from');
$id = $this->input->get('id');
```

```php
// CI4
$post = $this->request->getPost();
$from = $this->request->getPost('date_from');
$id = $this->request->getGet('id');
```

### 5.4 Request/Response Handling

Controller review must confirm:

- POST forms use `$this->request->getPost()`
- Query-string requests use `$this->request->getGet()`
- File uploads use `$this->request->getFile()`
- Redirects use CI4 redirect responses where applicable
- AJAX responses return valid HTML, JSON, or file content consistently

### 5.5 Input Class Replacement

| CI3 Usage | CI4 Replacement | Validation |
| --- | --- | --- |
| `$this->input->post('field')` | `$this->request->getPost('field')` | Form submission test |
| `$this->input->get('field')` | `$this->request->getGet('field')` | Report filter test |
| `$this->input->server()` | `$this->request->getServer()` | Server variable test where used |
| `$this->input->is_ajax_request()` | `$this->request->isAJAX()` | AJAX endpoint test |

### 5.6 Helper Loading

```php
// CI3
$this->load->helper(['url', 'form']);
```

```php
// CI4
helper(['url', 'form']);
```

Or use autoload:

```php
public $helpers = ['url', 'form', 'secure'];
```

Taylor note:

- `Config\Autoload` already includes `url`, `form`, and `secure`.
- Review whether module-specific helpers should remain autoloaded or be loaded only where needed.

## 6. Model Migration

### 6.1 Model Conversion Strategy

Taylor currently contains model classes such as `Model_common`, `Model_datatable`, and `Import_model`. Some of these are lightweight database wrapper classes and do not extend `CodeIgniter\Model`.

Recommended review approach:

1. Identify which models can remain service-style wrappers.
2. Identify which models should be refactored into `CodeIgniter\Model` subclasses.
3. Preserve existing query behavior before performing style refactors.
4. Add validation and allowed-field protection only after regression coverage is in place.

### 6.2 CI3 vs CI4 Example: Generic Model Conversion

```php
// CI3
class User_model extends CI_Model
{
    public function getUsers()
    {
        return $this->db->get('users')->result_array();
    }
}
```

```php
// CI4
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $returnType = 'array';

    public function getUsers(): array
    {
        return $this->findAll();
    }
}
```

### 6.3 Taylor Note on Current Models

| Current Pattern | Example | Migration Consideration |
| --- | --- | --- |
| Manual DB connection in constructor | `Model_common` | Acceptable short term, but standardize if future refactor is planned |
| Query builder wrappers | `select_single_row`, `update`, `delete_row` | Regression-test query outputs carefully |
| Multiple DB groups | `Database::connect('otherdb')` | Must validate both schema compatibility and credentials |
| Import batch operations | `Import_model` | Validate insert batch handling and transaction needs |

### 6.4 Query Builder Changes

Checklist:

- Confirm `select()`, `where()`, `like()`, `orderBy()`, `insert()`, `insertBatch()`, `update()`, and `delete()` calls behave as expected.
- Re-test any raw SQL or DB-driver-specific syntax.
- Re-test date-format ordering and SQL functions used in report modules.
- Re-test affected row expectations because CI4 behavior may differ in edge cases.

### 6.5 Database Connection Handling

Checklist:

1. Confirm each model or controller uses the correct DB group.
2. Confirm `default` and `otherdb` connectivity works in all environments.
3. Confirm connection failures are logged without exposing credentials.
4. Confirm long-running reports do not exhaust resources.

### 6.6 Validation Integration

Where feasible, shift validation from controller-only checks into structured validation rules. At minimum:

- Validate required fields before insert/update
- Validate file types and upload presence
- Validate report filter dates and identifiers
- Validate email addresses and role-sensitive changes

## 7. View Migration

### 7.1 View Loading Changes

```php
// CI3
$this->load->view('header');
$this->load->view('report', $data);
$this->load->view('footer');
```

```php
// CI4
echo view('inc_l/hd');
echo view('form_dreport', $data);
echo view('inc_l/ftr');
```

Taylor note:

- Taylor already uses the CI4-style `view()` helper in many controllers.
- This pattern should be retained unless a future layout refactor is planned.

### 7.2 Passing Data to Views

| CI3 Pattern | CI4 Pattern | Taylor Usage |
| --- | --- | --- |
| `$this->load->view('x', $data)` | `return view('x', $data)` | Standard for module forms and reports |
| Extracted variables in views | Same behavior via array keys | Validate variable names carefully during migration |

### 7.3 Layout Handling

Taylor commonly composes pages through header and footer partials:

- `inc_l/hd`
- main module view
- `inc_l/ftr`

Checklist:

1. Confirm each controller renders the correct header/footer combination.
2. Confirm JavaScript assets referenced by `base_url()` still load.
3. Confirm AJAX-based report placeholders render correctly after route changes.

### 7.4 View-Specific Validation

Validate the following:

- Form action URLs
- AJAX URLs such as `get-prod-repo`
- Export button links
- Download links
- CSRF token placement if enabled
- Session message rendering for success and error states

## 8. Library and Helper Migration

### 8.1 Custom Libraries Conversion

| Component | Location | Review Required |
| --- | --- | --- |
| Custom libraries | `app/Libraries` | Namespace declaration, constructor logic, service access |
| Custom helper | `app/Helpers/secure_helper.php` | Function compatibility, global helper loading, side effects |
| Common utility logic | Models and controllers | Reduce duplicated procedural behavior over time |

### 8.2 Helper Compatibility

Checklist:

- Confirm all custom helper functions are declared safely
- Confirm helper files use CI4-compatible naming and loading
- Confirm helper functions do not rely on CI3 super-object patterns
- Confirm helper calls work from controllers and views

### 8.3 Session Handling Changes

| Topic | CI3 | CI4 | Taylor Note |
| --- | --- | --- | --- |
| Session access | Usually loaded library | `session()` or `service('session')` | Widely used across modules |
| Flash data | CI3 flashdata | Supported in CI4 | Used in Taylor for success/error messaging |
| Storage | Configurable | File handler defaults to `writable/session` | Permission validation required |

CI3 vs CI4 session example:

```php
// CI3
$this->session->set_userdata('user_logged', $userData);
$this->session->set_flashdata('success_update', 'Successfully updated');
```

```php
// CI4
$session = session();
$session->set('user_logged', $userData);
$session->setFlashdata('success_update', 'Successfully updated');
```

### 8.4 Email Library Updates

Checklist:

1. Validate `app/Config/Email.php` or equivalent runtime config.
2. Confirm SMTP host, port, crypto, sender, and timeout values per environment.
3. Re-test all email flows in `Editmail` and related task workflows.
4. Confirm failures are logged with message context but without secret disclosure.

## 9. Security Updates

### 9.1 Security Hardening Checklist

| Control | Required Action | Taylor Note | Status |
| --- | --- | --- | --- |
| CSRF | Review whether CSRF should be globally enabled in `Filters.php` | Currently available but not globally enabled | Pending |
| XSS prevention | Escape view output where appropriate and validate HTML rendering needs | Review report tables and form values | Pending |
| Validation rules | Add or confirm server-side validation for all form submissions | Important for CRUD and uploads | Pending |
| Session protection | Review timeout, regeneration, and logout behavior | Session-heavy modules exist | Pending |
| HTTPS | Force HTTPS in production where appropriate | Review proxy/load balancer setup | Pending |
| Secure headers | Consider enabling secure headers filter in production | Currently not global | Pending |
| Upload security | Restrict file types and validate file names | Required for `Import` and `Pccase` import flows | Pending |

### 9.2 CSRF Handling

Current repo observations:

- `Config\Security` is present
- `Config\Filters` defines the `csrf` alias
- Global CSRF filtering is currently commented out

Migration action:

1. Decide whether to enable global CSRF or apply route-level protection.
2. Test all forms and AJAX endpoints after enabling protection.
3. Add `csrf_field()` to forms where required.

### 9.3 XSS Filtering

CodeIgniter 4 does not rely on CI3-style global XSS cleaning patterns. Use:

- Output escaping in views
- Validation and sanitization at input boundaries
- Strict handling for HTML-rich or email body content where raw rendering is intentional

### 9.4 Validation Rules

Priority validation areas:

- Login and password reset flows
- Report filters
- CRUD create/update forms
- File upload and import screens
- Email/task assignment inputs

### 9.5 Authentication and Session Security

Validate:

- Session ID regeneration policy
- Logout behavior
- Flashdata behavior after redirects
- Session timeout behavior during long report usage
- Unauthorized access handling for protected pages

## 10. Database Migration and Validation

### 10.1 Database Validation Matrix

| Check | Scope | Owner | Evidence |
| --- | --- | --- | --- |
| SQL compatibility review | All queries, builders, raw SQL, functions | Developers/DBA | Query review log |
| Table structure validation | All business tables used by Taylor | DBA | Schema comparison report |
| Data verification | Key transactional and reporting tables | QA/DBA | Sample record reconciliation |
| Stored procedure testing | If present in source databases | DBA | Execution result log |
| Trigger testing | If present in source databases | DBA | Trigger behavior evidence |
| Dual DB connectivity | `default` and `otherdb` | Developers/DevOps | Connection test results |

### 10.2 SQL Compatibility Check

Review for:

- MySQL function usage in report ordering and filtering
- Driver-specific syntax
- Date conversions and string-to-date logic
- Batch insert behavior
- Case-sensitive table names on Linux deployments

### 10.3 Table Structure Validation

Checklist:

1. Compare production schema to expected schema.
2. Confirm nullable fields, default values, indexes, and collation.
3. Confirm import/export tables match controller expectations.
4. Confirm session-related tables if a DB session handler is ever adopted.

### 10.4 Data Verification Steps

Perform these checks:

1. Count records before and after migration.
2. Verify a sample of recent records for each core module.
3. Verify report totals and Excel exports match expected data.
4. Verify uploads still reference valid stored files.
5. Verify no unexpected encoding or date format changes.

### 10.5 Stored Procedures and Triggers Testing

Repository evidence for stored procedures and triggers is not definitive. Treat this as a mandatory database validation activity:

- Inventory all stored procedures and triggers in target databases
- Execute smoke tests for each dependent business flow
- Confirm side effects, audit behavior, and update cascades

## 11. Server Deployment Checklist

### 11.1 Deployment Checklist

| Checkpoint | Apache | Nginx | Validation |
| --- | --- | --- | --- |
| Web root points to `public/` | Required | Required | Open homepage without exposing project root |
| Rewrite rules active | Use `public/.htaccess` | Equivalent `try_files` rule | Clean URLs resolve |
| `index.php` removed from URLs where intended | Yes | Yes | Route smoke test |
| `writable/` permissions set | Yes | Yes | Session/log/cache write test |
| Upload path permissions set | Yes | Yes | Upload/import test |
| `.env` deployed securely | Yes | Yes | Environment config review |
| PHP 8.2+ available | Yes | Yes | Runtime validation |
| Required extensions enabled | Yes | Yes | Extension inventory |

### 11.2 Apache Configuration Notes

Required checks:

1. Point virtual host document root to `public/`.
2. Enable `mod_rewrite`.
3. Confirm `.htaccess` is honored.
4. Confirm authorization header forwarding if applicable.
5. Confirm directory listing is disabled.

### 11.3 Nginx Configuration Notes

Use an equivalent configuration pattern:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Additional checks:

- Point root to `public/`
- Pass PHP requests to PHP-FPM
- Deny access to non-public application paths

### 11.4 Public Directory Mapping

This is mandatory:

- Do not expose `app/`, `writable/`, `system/`, or environment files to the web
- Do not rely on URLs such as `/public/...` in production as a workaround
- Configure the site correctly at the web server level

### 11.5 File Permissions

| Path | Expected Access | Why |
| --- | --- | --- |
| `public/` | Readable by web server | Public assets and front controller |
| `writable/` | Writable by application runtime | Logs, cache, sessions |
| `uploads/` | Writable where upload/import is required | Taylor file import and related workflows |
| `.env` | Readable by app, restricted from web access | Contains environment values |

### 11.6 Production Environment Setup

Checklist:

1. Set `CI_ENVIRONMENT = production`.
2. Set correct `app.baseURL`.
3. Deploy secure DB credentials outside documentation.
4. Review log threshold.
5. Disable development-only debugging output.
6. Validate mail configuration using a production-approved test account.

## 12. Testing Checklist (Project-wise)

### 12.1 Module Test Matrix

| Module Area | Controllers/Modules | Core Tests |
| --- | --- | --- |
| Login module | `Home`, `Loginperform` | Login, logout, invalid login, flash messages, session persistence, session timeout |
| Dashboard | `Prodreport`, `AutoProduction`, `Viewmisrec`, `Pccase` dashboards | Page load, filters, totals, drill-down navigation |
| CRUD operations | `Agent`, `Editrec`, `Custcomp`, `Processimprovement`, `Revenue`, `Pccase` | Create, edit, delete/deactivate, validation, permissions |
| Reports | `Prodreport`, `Home`, `Revenue`, `Custcomp`, `Pccase`, `Loginperform` | Filter accuracy, export accuracy, row counts, date logic |
| File upload/download | `Import`, `Pccase`, download endpoints in `Custcomp` and `Editmail` | Upload validation, file storage, download access, invalid file handling |
| API/external integration | `Editmail`, any curl/IMAP integration | Connectivity, timeout handling, invalid responses |
| Email functionality | `Editmail`, `PHPMailer` flows | Send success, send failure, attachment handling, SMTP validation |
| Cron jobs | Not confirmed in repository | Verification template only, see Section 12.8 |
| Role/permission | Protected modules across controllers | Authorized access, unauthorized redirect, restricted actions |
| Session timeout | Login/session-heavy modules | Inactivity expiration, flashdata retention, re-login behavior |

### 12.2 Login Module Checklist

| Test Case | Expected Result | Status |
| --- | --- | --- |
| Valid login | User reaches correct landing page | Pending |
| Invalid password | Error message shown, no session created | Pending |
| Deactivated user | Access denied with correct message | Pending |
| Logout | Session cleared and redirected safely | Pending |
| Session persistence across modules | User remains authenticated as expected | Pending |
| Session timeout | Forced re-authentication after timeout | Pending |

### 12.3 Dashboard Checklist

| Test Case | Modules | Expected Result | Status |
| --- | --- | --- | --- |
| Dashboard page load | Reporting and dashboard controllers | Page renders without PHP errors | Pending |
| Summary counts | `Pccase` dashboards and reporting screens | Counts match DB results | Pending |
| Drill-down links | Dashboard to detail pages | Linked routes resolve and data matches | Pending |
| AJAX data blocks | Report and MIS screens | Response returns correctly and renders | Pending |

### 12.4 CRUD Operations Checklist

| Test Case | Modules | Expected Result | Status |
| --- | --- | --- | --- |
| Create record | `Agent`, `Custcomp`, `Processimprovement`, `Revenue`, `Pccase` | Record saved, success message shown | Pending |
| Edit record | `Editrec`, `Revenue`, `Pccase`, `Processimprovement` | Updates persist accurately | Pending |
| Delete or deactivate | `Agent`, `Custcomp`, other applicable modules | Action restricted and recorded correctly | Pending |
| Validation failure | All create/update forms | Form errors handled safely | Pending |

### 12.5 Reports and Export Flows

| Test Case | Modules | Expected Result | Status |
| --- | --- | --- | --- |
| Filter by date or criteria | `Prodreport`, `Home`, `Revenue`, `Pccase`, `Custcomp`, `Loginperform` | Result set matches filter | Pending |
| Export to Excel | All export-capable modules | File downloads successfully and data matches UI | Pending |
| Large result set | Heavy reporting modules | Response completes within acceptable time | Pending |
| Empty result set | All reporting modules | Graceful no-data behavior | Pending |

### 12.6 File Upload/Download Checklist

| Test Case | Modules | Expected Result | Status |
| --- | --- | --- | --- |
| Valid file import | `Import`, `Pccase` import flow | File accepted and data inserted correctly | Pending |
| Invalid file type | Upload/import screens | Upload rejected with clear message | Pending |
| Missing file | Upload/import screens | Validation error shown | Pending |
| Download existing file | `Custcomp`, `Editmail` download routes | File downloads successfully | Pending |
| Download missing file | Download routes | Controlled failure, no unhandled error | Pending |

### 12.7 API and Email Checklist

| Test Case | Area | Expected Result | Status |
| --- | --- | --- | --- |
| IMAP or external mail read | `Editmail` and mail reader flows | Connectivity succeeds and records load | Pending |
| SMTP send test | Email send flows | Email sent and logged correctly | Pending |
| External failure handling | Curl/IMAP/SMTP | User-safe error handling without secret leakage | Pending |

### 12.8 Cron Jobs Verification Template

Repository evidence for custom cron or CI CLI job scheduling is limited. Do not assume an implementation exists. Use this template during migration:

| Check | Required Action | Owner | Status |
| --- | --- | --- | --- |
| Scheduled job inventory | Confirm whether any cron, Windows Task Scheduler, or server jobs call Taylor endpoints or scripts | DevOps | Pending |
| Trigger mapping | Map each job to module/business purpose | Lead/DevOps | Pending |
| Runtime compatibility | Confirm target job path still works in CI4 deployment layout | Developers/DevOps | Pending |
| Post-cutover execution test | Run each scheduled job in non-prod and production validation window | DevOps/QA | Pending |

### 12.9 Role and Permission Testing

Validate:

- Restricted pages cannot be opened without authentication
- Role-sensitive actions such as updates, reassignment, and exports are controlled
- Redirect behavior after denial is correct
- Session-based access remains intact after navigation and refresh

## 13. Common Migration Errors and Fixes

| Error | Likely Cause | Fix | Validation |
| --- | --- | --- | --- |
| Undefined property errors | Dynamic properties or missing initialized dependencies | Declare properties and initialize in controller/model constructors or `initController()` | Reload affected module |
| Namespace issues | Missing `namespace` or incorrect PSR-4 mapping | Add proper namespace and confirm `Autoload.php` mapping | Run controller/module load test |
| Base URL problems | Incorrect `.env` or `Config\App` value | Set correct `app.baseURL` and confirm trailing slash behavior | Open assets and AJAX routes |
| 404 routing issues | Missing or changed route definitions | Add/update explicit route in `app/Config/Routes.php` | Route smoke test |
| Session issues | Invalid `writable/session` permissions or misconfigured session settings | Fix path and permissions, review session config | Login/logout/session timeout test |
| Query Builder syntax differences | Legacy query assumptions | Update builder usage or raw SQL carefully | Compare DB results |
| Writable folder permission issues | Web server cannot write logs/cache/sessions | Correct ownership/permissions | Write log and session test |

### 13.1 Example: View Loading Conversion

```php
// CI3
$this->load->view('my_view', $data);
```

```php
// CI4
return view('my_view', $data);
```

### 13.2 Example: File Upload Conversion

```php
// CI3
$config['upload_path'] = './uploads/';
$config['allowed_types'] = 'xls|xlsx';
$this->load->library('upload', $config);
$this->upload->do_upload('uploadFile');
```

```php
// CI4
$file = $this->request->getFile('uploadFile');

if ($file && $file->isValid()) {
    $newName = $file->getRandomName();
    $file->move(ROOTPATH . 'uploads/', $newName);
}
```

## 14. Post-Migration Validation

### 14.1 Validation Checklist

| Check | Team | Expected Result | Status |
| --- | --- | --- | --- |
| Error log monitoring | DevOps/Developers | No recurring critical application errors | Pending |
| User acceptance testing | Business/QA | Business-critical flows approved | Pending |
| Database verification | DBA/QA | Record counts and sample records match | Pending |
| Performance benchmarking | Developers/DevOps | Response times within approved threshold | Pending |
| Security testing | Security/QA | No critical findings introduced by migration | Pending |

### 14.2 Error Log Monitoring

Monitor:

- PHP warnings and deprecations
- Route not found errors
- Session write failures
- Upload move failures
- Email/IMAP/SMTP errors
- Database connection or query failures

### 14.3 User Acceptance Testing

UAT must include:

- Authentication
- At least one CRUD scenario from each major module family
- Core reports and exports
- Upload/import and download flows
- Email/task assignment flows

### 14.4 Performance Benchmarking

Benchmark:

- Login response time
- Heavy report generation time
- Excel export generation time
- Dashboard load time
- Import processing time

### 14.5 Security Testing

Include:

- Auth bypass attempts
- CSRF testing after final configuration
- Session hijack/session fixation checks where applicable
- File upload abuse scenarios
- Direct access attempts to non-public paths

## 15. Rollback Plan

### 15.1 Rollback Checklist

| Trigger | Action | Owner | ETA |
| --- | --- | --- | --- |
| Critical login failure | Revert deployment and restore last stable config | DevOps | Immediate |
| Major reporting data mismatch | Revert deployment, freeze changes, validate DB state | Lead/DBA/DevOps | Immediate |
| Upload or export failure across core modules | Roll back application build and restore previous runtime paths/config | DevOps | Immediate |
| Widespread route/session issues | Revert code and environment changes | DevOps | Immediate |

### 15.2 Backup Restore Steps

1. Announce rollback decision and freeze user-impacting changes.
2. Take a fresh backup of the failed state for investigation.
3. Restore previous application package.
4. Restore previous environment and web server configuration if changed.
5. Restore database backup only if database changes were part of the migration and data integrity is impacted.
6. Re-run smoke tests on the restored version.

### 15.3 Rollback Deployment Procedure

| Step | Action | Evidence |
| --- | --- | --- |
| 1 | Stop or isolate the failed deployment | Incident record |
| 2 | Restore last known stable build | Deployment log |
| 3 | Restore previous `.env` and server config | Config audit |
| 4 | Validate web root and permissions | Smoke test evidence |
| 5 | Validate login, reports, and key CRUD | QA or support sign-off |

### 15.4 Emergency Recovery Checklist

| Item | Required Action | Status |
| --- | --- | --- |
| Stakeholder communication | Notify support, business owner, and lead | Pending |
| Access restoration | Confirm users can log in again | Pending |
| Data integrity verification | Confirm no partial corruption | Pending |
| Incident log | Record root cause, timeline, and next steps | Pending |

## 16. Best Practices

### 16.1 Use Environment Files

- Maintain separate `.env` values per environment
- Keep secrets out of source control where possible
- Use masked examples in documentation and tickets

### 16.2 Maintain Coding Standards

- Use namespaces consistently
- Avoid dynamic properties under PHP 8.2
- Standardize request, response, session, and validation handling
- Keep controllers focused and move reusable logic into models, helpers, or services over time

### 16.3 Logging and Monitoring

- Log technical failures with actionable context
- Avoid logging secrets or sensitive payloads
- Enable enough logging in UAT to catch regressions before production cutover

### 16.4 Version Control Recommendations

- Use a dedicated migration branch
- Keep migration commits small and reviewable
- Tag the pre-cutover release
- Tag the production cutover release
- Preserve a clearly identified rollback target

## Appendix A: CI3 vs CI4 Quick Reference

| Area | CI3 | CI4 |
| --- | --- | --- |
| Controller base | `CI_Controller` | `BaseController` |
| Model base | `CI_Model` | `CodeIgniter\Model` or service-style class |
| POST input | `$this->input->post()` | `$this->request->getPost()` |
| GET input | `$this->input->get()` | `$this->request->getGet()` |
| View loading | `$this->load->view()` | `view()` |
| Helper loading | `$this->load->helper()` | `helper()` or autoload |
| Config | `application/config/*` | `.env` and `app/Config/*` |
| Web root | Often project root | `public/` |
| Writable runtime files | Mixed | `writable/` |

## Appendix B: Final Sign-Off Checklist

| Checkpoint | Developers | QA | DevOps | Sign-Off |
| --- | --- | --- | --- | --- |
| Code migration completed |  |  |  |  |
| Config migration completed |  |  |  |  |
| Database validation completed |  |  |  |  |
| Module regression testing completed |  |  |  |  |
| Security review completed |  |  |  |  |
| Deployment validation completed |  |  |  |  |
| Rollback readiness confirmed |  |  |  |  |
| Production go-live approved |  |  |  |  |
