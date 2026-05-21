# Botlogs CodeIgniter 3 to CodeIgniter 4 Migration Checklist

## Document Control

| Item | Details |
| --- | --- |
| Document title | Botlogs CodeIgniter 3 to CodeIgniter 4 Migration Checklist |
| Intended audience | Developers, QA/Testers, DevOps/Deployment Teams, Technical Leads |
| Usage | Internal migration planning, execution, validation, deployment, rollback |
| Target application | Botlogs |
| Repository reference state | Current repository already follows a CodeIgniter 4 structure, but still contains legacy CI3-style coding patterns and migration-sensitive behaviors |
| Framework baseline observed | CodeIgniter 4.7.0 |
| Local PHP baseline observed | PHP 8.2.12 via `C:\xampp\php\php.exe` |
| Primary web root | `public/` |
| Primary runtime config | `.env` and `app/Config/*` |
| Runtime write paths | `writable/` |
| Main dependencies observed | PhpSpreadsheet under `app/ThirdParty` |
| Important note | Do not copy live passwords, database secrets, or encryption values into documents, tickets, chat logs, or screenshots |

---

## 1. Introduction

### 1.1 Purpose of Migration

This document provides a project-specific migration runbook for Botlogs while moving from a legacy CodeIgniter 3 implementation style to a stable, supportable, and secure CodeIgniter 4 operating model.

Botlogs already uses a CI4 folder layout, namespaced controllers, `.env`, and `app/Config/*`. However, the application still contains CI3-era behaviors and migration-sensitive patterns, including:

- Legacy authentication logic using `md5()`
- Custom helper-based access control
- Manual report export headers instead of standardized response downloads
- Broad controller responsibilities concentrated mainly in `Home.php`
- Global CSRF filtering disabled
- One model referencing `otherdb` even though that DB group is not currently configured
- A current runtime permission issue where `spark routes` fails because `writable/cache` is not writable

The migration effort is successful only when the application is not just running on CI4, but is also deployable, testable, supportable, and safe across local, test, UAT, and production systems.

### 1.2 Benefits of CodeIgniter 4 Over CodeIgniter 3

| Area | CodeIgniter 3 | CodeIgniter 4 | Botlogs Migration Benefit |
| --- | --- | --- | --- |
| PHP support | Older PHP compatibility | Modern PHP support | Aligns Botlogs with PHP 8.2+ and supported runtime behavior |
| Architecture | Minimal namespaces | PSR-4 namespaces and stronger structure | Easier maintenance of `Home`, `Agent`, models, and helpers |
| Configuration | File/array-heavy | `.env` and typed config classes | Cleaner separation for local, UAT, and production |
| Routing | Simpler routing model | Explicit route definitions and filters | Better control of Botlogs' route surface |
| Security | Older defaults | Improved filters, sessions, and request handling | Better protection for login, admin, and reporting flows |
| Testing | Limited built-in scaffolding | Stronger CLI and test structure | Easier regression testing and deployment validation |
| Deployment | Often root-level exposure | `public/` entrypoint and `writable/` separation | Safer hosting model |

### 1.3 Migration Objective

The Botlogs migration should be considered complete when:

1. All functional modules operate correctly under CI4 conventions with no business regression.
2. Environment configuration is reproducible and separated per system.
3. Login, reports, CRUD, exports, sessions, and admin flows are validated.
4. Security-sensitive legacy patterns are remediated or formally accepted with risk sign-off.
5. Deployment and rollback procedures are documented and testable.

---

## 2. Pre-Migration Checklist

### 2.1 Backup Requirements

| Checkpoint | Required Action | Owner | Evidence |
| --- | --- | --- | --- |
| Project backup | Back up the full project directory including `app/`, `public/`, `system/`, `tests/`, `writable/`, `.env`, and server config | DevOps / Developer | Backup archive name and timestamp |
| Database backup | Back up the Botlogs application database | DBA / DevOps | SQL dump name and timestamp |
| Restore validation | Restore backup into a non-production environment and confirm startup | DevOps / QA | Restore log and smoke test result |
| Config snapshot | Capture current vhost/site config, rewrite config, and PHP version | DevOps | Configuration export |
| Change freeze | Freeze schema and risky code changes during the cutover window | Technical Lead | Approved change window |

### 2.2 PHP Version Compatibility

| Item | Requirement | Botlogs Note | Validation |
| --- | --- | --- | --- |
| PHP runtime | 8.2+ | `composer.json` requires `^8.2` | Validate on all environments |
| PHP CLI | Available for `spark` and tests | `C:\xampp\php\php.exe` is available locally | Standardize CLI command usage |
| Dynamic properties | Avoid | BaseController notes PHP 8.2 dynamic property deprecation | Static review and runtime testing |
| Deprecated patterns | Review | Legacy CI3-style code still exists in login and reporting flows | Log review and UAT |

### 2.3 Server Requirements

| Requirement | Minimum Expectation | Botlogs Relevance |
| --- | --- | --- |
| Web server | Apache or Nginx | `public/.htaccess` exists and expects rewrite support |
| PHP | 8.2+ | Required by current framework baseline |
| Extensions | `intl`, `mbstring`, `mysqli`, `json`, `fileinfo` | Required for framework and database access |
| Spreadsheet support | PhpSpreadsheet dependency availability | Used for Excel export generation |
| File permissions | Read access to project, write access to `writable/` | Required for cache, session, and logs |
| Database access | Reachable MySQL instance | Required for login, task, and reporting data |

### 2.4 Environment Setup

| Item | Required Action | Botlogs Note |
| --- | --- | --- |
| `.env` | Maintain separate values per local, test, UAT, and production | Already in use |
| `CI_ENVIRONMENT` | Set correctly per stage | Local environment is currently configured as development |
| Base URL | Set per environment | Local config currently points to `/botlogs/public/` |
| Logging | Lower log verbosity in production | Local logger threshold is verbose |
| Cache/session paths | Confirm writable absolute runtime paths | Important because current cache write issue is already visible |

### 2.5 Existing Third-Party Libraries and Modules Review

| Dependency / Module | Current Location | Purpose | Migration Check |
| --- | --- | --- | --- |
| PhpSpreadsheet | `app/ThirdParty/PhpSpreadsheet` | Excel report export | Validate PSR-4 mapping, export generation, and file download behavior |
| Custom helper | `app/Helpers/secure_helper.php` | Login restriction and redirect handling | Confirm CI4 compatibility and redirect behavior |
| Custom models | `app/Models/Model_common.php`, `Model_datatable.php` | Shared DB and datatable logic | Validate Query Builder behavior and SQL safety |
| Legacy controller | `app/Controllers/Home_v0.php` | Old code reference | Decide whether to retain, archive, or remove |

### 2.6 Project-Specific Pre-Migration Risks

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Passwords still use `md5()` | Weak authentication security | Prioritize password hashing modernization |
| `writable/cache` not writable | CLI/runtime failures | Fix permissions before UAT and production deployment |
| CSRF globally disabled | Form submission risk | Decide and validate global or route-level CSRF |
| `otherdb` referenced but not configured | Runtime failure if legacy code path is used | Remove dead path or define the DB group explicitly |
| Legacy export responses use raw headers | Output/download fragility | Standardize response handling during migration |
| Sensitive values copied into docs | Security incident | Use placeholders and secure vault references only |

---

## 3. Folder Structure Changes

### 3.1 CI3 vs CI4 Directory Structure

| CI3 Structure | CI4 Structure | Botlogs Current State | Migration Note |
| --- | --- | --- | --- |
| `application/` | `app/` | Present | Already migrated structurally |
| `application/config/` | `app/Config/` plus `.env` | Present | Environment separation should be enforced |
| `application/controllers/` | `app/Controllers/` | Present | Main logic sits in `Home.php` and `Agent.php` |
| `application/models/` | `app/Models/` | Present | Shared wrapper models remain |
| `application/views/` | `app/Views/` | Present | Smaller view surface than larger tracker apps |
| Root `index.php` | `public/index.php` | Present | Server must target `public/` |
| Mixed runtime writes | `writable/` | Present | Permissions must be fixed and validated |
| Composer-managed `vendor/` | `vendor/` | Not present in project root | Botlogs currently relies on `app/ThirdParty` |

### 3.2 Explanation of Key CI4 Folders

| Folder | Purpose | Botlogs Consideration |
| --- | --- | --- |
| `app/` | Controllers, models, views, helpers, config | Primary business code |
| `public/` | Front controller and web-accessible assets | Must be the only public entrypoint |
| `writable/` | Logs, cache, sessions | Current permissions must be validated |
| `vendor/` | Composer-managed dependencies | Standard CI4 pattern, but not currently present |

### 3.3 Public Folder Configuration on Server

Use the following checkpoints:

1. Confirm the web server document root points to `.../botlogs/public`.
2. Confirm `app/`, `system/`, `writable/`, `.env`, and other non-public paths are not browser-accessible.
3. Confirm `public/.htaccess` or equivalent Nginx rewrite rules are active.
4. Confirm `index.php` is removed from URLs only after rewrite validation.
5. Confirm CSS, JS, images, and generated report links resolve correctly.

---

## 4. Configuration Migration

### 4.1 Configuration Migration Table

| Area | CI3 Typical Pattern | CI4 Target | Botlogs Current State | Validation |
| --- | --- | --- | --- | --- |
| Base URL | `application/config/config.php` | `.env` and `Config\App` | `.env` in use | Open login page and assets |
| Index page | `index.php` config | `.env` / `Config\App::$indexPage` | Empty in local `.env` | Confirm clean URLs |
| Database config | `application/config/database.php` | `app/Config/Database.php` plus `.env` | `default` defined | Validate DB connectivity |
| Autoload | `application/config/autoload.php` | `app/Config/Autoload.php` | Custom PSR-4 mapping for PhpSpreadsheet | Validate autoloading |
| Routes | `application/config/routes.php` | `app/Config/Routes.php` | 53 explicit routes, auto-route disabled | Smoke-test route map |
| Session config | Config arrays | `app/Config/Session.php` | File-based sessions | Validate login persistence |
| Security config | Hooks/config arrays | `Security.php` and `Filters.php` | CSRF alias exists, global CSRF disabled | Security review required |

### 4.2 Base URL

Checklist:

1. Set `app.baseURL` separately for each environment.
2. Do not hardcode local URLs in controllers, views, or JavaScript.
3. Confirm `base_url()` and `site_url()` resolve correctly for:
   - `login.html`
   - `home.html`
   - `report.html`
   - report export endpoints such as `gen-excl` and `gen-excl1`
4. In production, users should access the site without `/public/` in the final URL.

### 4.3 Database Configuration

Botlogs-specific points:

- The main configured DB group is `default`.
- `Model_datatable` contains a method that references `otherdb`, but `otherdb` is not currently configured in `Database.php`.

Validation steps:

1. Confirm `default` database credentials, charset, and connectivity per environment.
2. Identify whether the `otherdb` code path is still required.
3. If required, create and validate an `otherdb` group before go-live.
4. If not required, remove or retire the dead path to reduce runtime risk.

### 4.4 Environment Variables (`.env`)

Best-practice checkpoints:

- Store environment-specific settings in `.env` or a secure secret store.
- Do not expose passwords in migration documents.
- Use masked examples only.

Example:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://botlogs.example.com/'
app.indexPage = ''
database.default.hostname = db-host
database.default.database = botlogs
database.default.username = botlogs_user
database.default.password = ********
logger.threshold = 1
```

### 4.5 Autoload Configuration

Botlogs currently uses:

- App namespace mapping
- PSR-4 mapping for `PhpOffice\PhpSpreadsheet`
- Helper autoload entries for `url`, `form`, and `secure`

Validation steps:

1. Open report screens that trigger spreadsheet export.
2. Confirm helper functions load without manual `helper()` calls.
3. Confirm no class-not-found errors occur in `Home.php`.

### 4.6 Routes Migration

Botlogs route review must verify:

- Route path parity with legacy URLs
- Admin, login, and reporting URLs still resolve
- AJAX endpoints like `check-task`, `check-cat`, `subcat-menu`, `get-cat`, and datatable endpoints still work
- Export endpoints still return valid files
- Route coverage is complete before controller methods change

Current project observations:

- `setAutoRoute(false)` is already enabled, which is preferable for production safety
- 53 route definitions are currently declared

### 4.7 CI3 vs CI4 Example: Routes Configuration

```php
// CI3
$route['login.html'] = 'home/index';
$route['report.html'] = 'home/task_report';

// CI4
$routes->add('login.html', 'Home::index');
$routes->add('report.html', 'Home::task_report');
```

---

## 5. Controller Migration

### 5.1 Controller Conversion Checklist

| Topic | CI3 Pattern | CI4 Pattern | Botlogs Note |
| --- | --- | --- | --- |
| Namespace | Often absent | `namespace App\Controllers;` | Already in use |
| Base class | `CI_Controller` | `BaseController` | Already in use |
| Input handling | `$this->input` | `$this->request` | Largely migrated |
| Output | `load->view()`, raw output | `return view()`, response objects | Mixed usage remains |
| Redirects | `redirect()` or raw headers | `return redirect()->to(...)` | Mixed; export flows still use raw headers |
| Session | Loaded library | `session()` / typed property | Widely used |

### 5.2 Namespace and BaseController Example

```php
// CI3
class Home extends CI_Controller
{
    public function index()
    {
        $this->load->view('login');
    }
}

// CI4
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('login');
    }
}
```

### 5.3 Request/Response Handling

Controller review must confirm:

- POST requests use `$this->request->getPost()`
- Query-string requests use `$this->request->getGet()`
- File downloads use CI4 response patterns where possible
- Redirects do not rely on legacy CI3 helpers or raw header usage unnecessarily
- AJAX endpoints return consistent plain-text, JSON, or HTML fragments

### 5.4 Input Class Replacement

| CI3 Usage | CI4 Replacement | Botlogs Relevance |
| --- | --- | --- |
| `$this->input->post('field')` | `$this->request->getPost('field')` | Used across login, create, update, and reporting |
| `$this->input->get('field')` | `$this->request->getGet('field')` | Used in lookup and validation endpoints |
| `$this->input->server()` | `$this->request->getServer()` | Use if server-aware logic is introduced |
| `$this->input->is_ajax_request()` | `$this->request->isAJAX()` | Useful for tightening AJAX-only endpoints |

### 5.5 Helper Loading

```php
// CI3
$this->load->helper(['url', 'form']);

// CI4
helper(['url', 'form']);
```

Botlogs note:

- `Config\Autoload` already autoloads `url`, `form`, and `secure`.
- Keep helper usage centralized and minimize helper-side business logic growth.

---

## 6. Model Migration

### 6.1 Model Conversion Strategy

Botlogs currently uses:

- `Model_common`
- `Model_datatable`

These are shared utility/wrapper models rather than fully structured domain models.

Recommended approach:

1. Preserve current behavior first.
2. Validate all SQL/datatable behavior before style refactoring.
3. Convert stable areas to `CodeIgniter\Model` subclasses only where it adds clear value.
4. Add stricter validation and field protection after regression coverage is in place.

### 6.2 CI3 vs CI4 Example: Generic Model Conversion

```php
// CI3
class Task_model extends CI_Model
{
    public function getTasks()
    {
        return $this->db->get('task')->result_array();
    }
}

// CI4
namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'task';
    protected $returnType = 'array';

    public function getTasks(): array
    {
        return $this->findAll();
    }
}
```

### 6.3 Query Builder Changes

Checklist:

- Re-test `select()`, `where()`, `insert()`, `update()`, and `delete()`
- Re-test raw SQL used in datatable rendering
- Review string-built SQL carefully for search and ordering
- Re-test affected-row behavior because some wrapper methods treat zero affected rows as failure

### 6.4 Database Connection Handling

| Item | Botlogs Note | Validation |
| --- | --- | --- |
| Default DB | Primary configured group | Validate login, report, and CRUD operations |
| `otherdb` reference | Present in `Model_datatable`, not configured | Remove or configure explicitly |
| SQL safety | Datatable methods build SQL strings dynamically | Validate search inputs and escaping behavior |
| Test DB group | `tests` group exists | Review if automated tests are to be expanded |

### 6.5 Validation Integration

At minimum, validate:

- Login credentials
- User creation and password reset fields
- Bot, task, and trigger creation forms
- Report filter values
- Export request parameters

---

## 7. View Migration

### 7.1 View Loading Changes

```php
// CI3
$this->load->view('header');
$this->load->view('report', $data);
$this->load->view('footer');

// CI4
return view('inc_l/hd')
    . view('form_report', $data)
    . view('inc_l/ftr');
```

Botlogs note:

- This CI4 view-composition pattern is already common in `Home.php` and `Agent.php`.

### 7.2 Passing Data to Views

| CI3 Pattern | CI4 Pattern | Botlogs Usage |
| --- | --- | --- |
| `$this->load->view('x', $data)` | `return view('x', $data)` | Standard for edit and report screens |
| View state via session | Still possible | Used heavily for flash messages and logged-in user state |

### 7.3 Layout Handling

Botlogs commonly uses:

- `inc_l/hd`
- page-specific view
- `inc_l/ftr`

Checklist:

1. Confirm header/footer partials render correctly after route or config changes.
2. Confirm assets under `public/` load on all pages.
3. Confirm report/export forms continue to submit correctly.

### 7.4 View-Specific Validation

Validate the following:

- Form action URLs
- Export form URLs
- Session flash messages
- Admin-only screen rendering
- CSRF token placement if CSRF protection is enabled

---

## 8. Library and Helper Migration

### 8.1 Custom Libraries Conversion

| Component | Current Location | Review Required |
| --- | --- | --- |
| Custom helper | `app/Helpers/secure_helper.php` | Redirect handling, session access, encryption helper behavior |
| Spreadsheet integration | `app/ThirdParty/PhpSpreadsheet` | Export compatibility and memory usage |
| Shared wrappers | `app/Models/*` | Return behavior and SQL patterns |

### 8.2 Helper Compatibility

Checklist:

- Confirm helper functions do not depend on CI3 super-object behavior
- Confirm access-control helpers behave correctly in CI4 request flow
- Review encryption helper functions and whether they are still needed
- Confirm redirect helpers do not create double-output or header timing issues

### 8.3 Session Handling Changes

| Topic | CI3 | CI4 | Botlogs Note |
| --- | --- | --- | --- |
| Session access | Loaded library | `session()` / typed session property | Already in use |
| Flashdata | Supported | Supported | Used for login, create, update, and error messages |
| Storage | Configurable | File handler by default | Permission validation required |

### 8.4 Email Library Updates

Current repo observation:

- No dedicated email-sending module or mail library integration was observed in the active Botlogs codebase.

Migration action:

1. Treat email testing as not applicable unless email functionality exists outside the current repository or is added later.
2. If email is introduced, store SMTP settings in `.env`, not in code.

---

## 9. Security Updates

### 9.1 CSRF Handling

Current Botlogs observations:

- `Config\Filters` defines the `csrf` alias
- Global CSRF filtering is commented out
- Forms reviewed do not currently show `csrf_field()`

Required actions:

1. Decide whether to enable global CSRF or apply it route-by-route.
2. Add `csrf_field()` to forms if CSRF is enabled.
3. Re-test login, create, edit, and export flows after enabling.

### 9.2 XSS Filtering

Use:

- Output escaping in views
- Validation at input boundaries
- Careful review of dynamic report and datatable output

### 9.3 Validation Rules

Priority validation areas:

- Login
- User creation and password reset
- Bot/task/trigger creation
- Report filters and export criteria

### 9.4 Authentication and Session Security

Priority actions:

- Replace `md5()` with `password_hash()` / `password_verify()`
- Confirm logout fully clears session state
- Confirm session regeneration policy
- Confirm unauthorized users cannot access admin-only functions

---

## 10. Project-Specific Migration Hotspots

| Hotspot | Current Observation | Required Action |
| --- | --- | --- |
| Authentication | Passwords use `md5()` | Prioritize password hardening |
| Route safety | `setAutoRoute(false)` already set | Preserve explicit-route discipline |
| CSRF | Alias exists, not globally enabled | Define final protection model |
| Cache/runtime permissions | `spark routes` currently fails because `writable/cache` is not writable | Fix filesystem permissions before testing and deployment |
| Legacy DB reference | `otherdb` referenced in model, not configured | Remove dead code or add config |
| Export handling | Excel downloads use raw header output | Standardize on response-based downloads where practical |
| Legacy controller copy | `Home_v0.php` still exists | Decide retain/archive/remove |

---

## 11. Server Deployment Checklist

### 11.1 Deployment Checklist

| Checkpoint | Apache | Nginx | Validation |
| --- | --- | --- | --- |
| Document root points to `public/` | Required | Required | Homepage loads without exposing project root |
| Rewrite rules active | `public/.htaccess` | `try_files` equivalent | Clean URLs resolve |
| `index.php` removed from URLs | Yes | Yes | Route smoke test |
| `writable/` writable | Yes | Yes | Cache, session, and log test |
| `.env` secured | Yes | Yes | Config audit |
| PHP 8.2+ available | Yes | Yes | CLI and web validation |
| Required extensions enabled | Yes | Yes | Extension inventory |

### 11.2 Apache Configuration Notes

1. Point the virtual host document root to `.../botlogs/public`.
2. Enable `mod_rewrite`.
3. Confirm `.htaccess` is honored.
4. Disable directory listing.
5. Confirm server does not expose non-public paths.

### 11.3 Nginx Configuration Notes

Example:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Additional checks:

- Point `root` to `public/`
- Pass PHP requests correctly
- Deny access to non-public application paths

### 11.4 Public Directory Mapping

Mandatory controls:

- Do not expose `app/`, `system/`, `writable/`, or `.env`
- Do not rely on `/public/` in user-facing production URLs
- Configure the site correctly at the web-server level

### 11.5 File Permissions

| Path | Expected Access | Why |
| --- | --- | --- |
| `public/` | Readable by web server | Public entrypoint and assets |
| `writable/` | Writable by PHP runtime | Cache, logs, sessions |
| `.env` | Readable by app, not web-exposed | Environment configuration |

### 11.6 Production Environment Setup

1. Set `CI_ENVIRONMENT = production`.
2. Set the correct production `app.baseURL`.
3. Lower the log threshold.
4. Disable development-only debugging output.
5. Validate report export after permission and memory checks.

---

## 12. Testing Checklist

### 12.1 Module Test Matrix

| Module Area | Controllers / Modules | Core Tests |
| --- | --- | --- |
| Login module | `Home` | Valid login, invalid login, logout, deactivated user, session persistence |
| Dashboard / landing | `Home::home_1` and shared layout | Page load, access control, navigation |
| CRUD operations | `Home`, `Agent` | Create bot, task, trigger, record edit, hide/delete/deactivate, password reset |
| Reports | `Home` | Filter accuracy, datatable output, Excel export |
| File download | `Home` export endpoints | Excel file download, filename, content integrity |
| Email functionality | Not observed as a dedicated module | Mark as not applicable unless external functionality exists |
| Session timeout | Shared session behavior | Forced re-login after timeout, protected route denial |

### 12.2 Login Module Checklist

| Test Case | Expected Result |
| --- | --- |
| Valid login | User reaches `home.html` |
| Invalid password | Error shown, no session created |
| Deactivated user | Access denied with correct message |
| Logout | Session cleared and redirected safely |
| Session persistence | User remains authenticated across pages |
| Session timeout | User must authenticate again after expiry |

### 12.3 Dashboard Checklist

| Test Case | Expected Result |
| --- | --- |
| Home page load after login | Page renders without PHP errors |
| Header/footer render | Shared layout loads correctly |
| Menu navigation | All links resolve correctly |
| Access control | Unauthenticated users are redirected to login |

### 12.4 CRUD Operations Checklist

| Test Case | Modules | Expected Result |
| --- | --- | --- |
| Create bot | `Home` | Record saved and success shown |
| Create triggered-by | `Home` | Record saved and success shown |
| Create task | `Home` | Record saved and success shown |
| Create user | `Agent` | Record saved and admin feedback shown |
| Edit user/record | `Agent`, `Home` | Changes persist accurately |
| Hide/delete/deactivate | `Home`, `Agent` | Authorized action succeeds and messages are correct |

### 12.5 Reports Checklist

| Test Case | Expected Result |
| --- | --- |
| Report page load | No PHP errors |
| Filter by bot/client/category/date | Result set matches DB query |
| Datatable search/order | Filtering and ordering behave correctly |
| Empty result set | Graceful no-data output |
| Excel export | File downloads and matches report content |

### 12.6 File Upload/Download Checklist

Current repo observation:

- No dedicated upload module was observed.
- Report export download is present and must be tested.

| Test Case | Expected Result |
| --- | --- |
| Export existing report | Excel file downloads successfully |
| Export with empty results | Graceful output or empty export without crash |
| Browser download headers | Filename and content type behave correctly |

### 12.7 Email Functionality Checklist

| Test Case | Expected Result |
| --- | --- |
| Email feature existence review | Confirm whether any external or hidden email flow exists |
| If email is added later | SMTP settings must be environment-driven and tested in non-production |

### 12.8 Session Timeout Testing

| Test Case | Expected Result |
| --- | --- |
| Idle session expiry | User is redirected to login |
| Post-timeout form access | Protected actions are blocked |
| Flashdata after redirect | Messages render correctly |
| Admin route protection | Non-admin users remain blocked |

### 12.9 Suggested Test Evidence Template

| Test ID | Module | Scenario | Input | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- | --- | --- |
| BOT-MIG-001 | Login | Valid login | Valid user | Redirect to `home.html` |  |  |  |
| BOT-MIG-002 | Report | Excel export | Date range | `.xls` download |  |  |  |
| BOT-MIG-003 | Agent | Create user | Valid data | User created |  |  |  |

---

## 13. Common Migration Errors and Fixes

| Error | Likely Cause | Fix | Validation |
| --- | --- | --- | --- |
| Undefined property | Missing property declaration or initialization | Declare typed properties and initialize in controller/model setup | Reload affected page |
| Namespace issue | Missing namespace or bad PSR-4 mapping | Correct namespace and autoload configuration | Load class/module |
| Base URL issue | Bad `.env` or incorrect web root | Fix `app.baseURL` and `public/` mapping | Asset and route test |
| 404 routing issue | Missing route or rewrite problem | Add/update route and validate rewrite rules | Route smoke test |
| Session issue | Misconfigured session path or permissions | Fix `writable/` permissions and session config | Login/logout test |
| Query Builder difference | Legacy SQL assumption or wrapper logic | Review SQL and affected-row handling | Compare DB results |
| Writable folder permission issue | PHP cannot write cache/log/session files | Correct permissions | `spark`, login, and cache validation |
| Missing `otherdb` group | Legacy code references unconfigured DB group | Add config or remove unused path | Targeted smoke test |

---

## 14. Post-Migration Validation

### 14.1 Error Log Monitoring

Monitor:

- PHP warnings and deprecations
- Route not found errors
- Session write failures
- Cache write failures
- Spreadsheet export failures
- Database query errors

### 14.2 User Acceptance Testing

UAT must include:

1. Authentication
2. At least one create/edit/delete flow
3. Core report filters
4. Excel export
5. Admin-only user management

### 14.3 Database Verification

Checklist:

- Confirm created/updated/deactivated rows match UI behavior
- Compare report totals and counts with direct DB queries
- Validate no unexpected data loss occurred during migration

### 14.4 Performance Benchmarking

Benchmark:

- Login response time
- Home/dashboard response time
- Report generation time
- Excel export generation time

### 14.5 Security Testing

Include:

- Auth bypass attempts
- CSRF testing after final configuration
- Session timeout and logout verification
- Direct URL access attempts to admin-only pages

---

## 15. Rollback Plan

### 15.1 Backup Restore Steps

1. Announce rollback and freeze changes.
2. Preserve the failed deployment state for investigation.
3. Restore the previous stable application package.
4. Restore the previous `.env` and web-server configuration if changed.
5. Restore the database backup if required by the failed release.
6. Re-run smoke tests on the restored build.

### 15.2 Rollback Deployment Procedure

| Step | Action | Evidence |
| --- | --- | --- |
| 1 | Stop or isolate the failed release | Incident record |
| 2 | Restore last stable code package | Deployment log |
| 3 | Restore previous config and environment values | Config audit |
| 4 | Validate `public/` mapping and permissions | Smoke test |
| 5 | Validate login, reports, and admin module | QA sign-off |

### 15.3 Emergency Recovery Checklist

| Trigger | Action | Owner | ETA |
| --- | --- | --- | --- |
| Login failure | Restore previous application/config and re-test auth | DevOps + Developer | Immediate |
| Report/export failure | Roll back code and validate runtime permissions | DevOps | Immediate |
| Session/cache failure | Restore stable environment settings and permissions | DevOps | Immediate |
| Widespread route issues | Revert deployment and verify rewrite config | DevOps | Immediate |

---

## 16. Best Practices

### 16.1 Use Environment Files

- Maintain separate `.env` values per environment
- Keep secrets out of source control when possible
- Use masked examples in documentation

### 16.2 Maintain Coding Standards

- Use namespaces consistently
- Avoid dynamic properties under PHP 8.2
- Standardize request, response, redirect, and session handling
- Continue reducing controller bloat over time

### 16.3 Logging and Monitoring

- Log failures with useful technical context
- Never log secrets or sensitive values
- Keep UAT logging more verbose than production

### 16.4 Version Control Recommendations

- Use a dedicated migration/hardening branch
- Keep commits small and reviewable
- Tag the pre-cutover release
- Tag the production cutover release
- Preserve a clear rollback target

---

## Appendix A: Botlogs Quick Reference

| Area | Current Botlogs Observation | Migration Focus |
| --- | --- | --- |
| Framework layout | CI4 structure already in place | Validate and harden legacy behaviors |
| Routes | 53 explicit routes | Maintain parity and verify all links |
| Controllers | `Home.php`, `Agent.php`, plus legacy `Home_v0.php` | Focus on login, CRUD, and reports |
| Models | Shared wrapper models | Validate SQL safety and behavior |
| Views | Small view set | Check forms, flash messages, and CSRF readiness |
| Database | `default` group configured | Validate and resolve stray `otherdb` reference |
| Exports | Excel export via PhpSpreadsheet | Test download behavior and output integrity |
| Runtime writes | `writable/` required | Fix cache/session/log permissions |

## Appendix B: CI3 vs CI4 Quick Reference

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
| Runtime files | Mixed | `writable/` |
