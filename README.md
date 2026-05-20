# MIS Project
# CodeIgniter 3 to CodeIgniter 4 Migration Checklist

## Document Control

| Item | Details |
| --- | --- |
| Document Title | CodeIgniter 3 to CodeIgniter 4 Migration Checklist |
| Project | MIS |
| Prepared For | Development, QA, DevOps, Release Management |
| Repository Context | `C:\xampp\htdocs\MIS` |
| Current Runtime Snapshot | CodeIgniter 4 application structure with hybrid CI3-era coding patterns still present in selected modules |
| Target Framework | CodeIgniter 4 |
| Current PHP Requirement | `^8.2` as defined in `composer.json` |
| Document Type | Internal migration and deployment checklist |
| Status | Ready for project planning, execution, and sign-off |

## Project Snapshot

| Area | Current Observation | Migration Relevance |
| --- | --- | --- |
| Application Structure | `app`, `public`, `system`, `tests`, `writable` folders already exist | Confirms CI4 bootstrap layout is present |
| Controllers | `Home`, `Agent`, `DuplicateEntries`, `CalcDaht`, `Mod2` | Main business modules to validate during migration |
| Models | `Model_common`, `Model_datatable` | `Model_common` still behaves like a utility/service and should be reviewed for CI4 modeling standards |
| Custom Helpers | `app/Helpers/secure_helper.php` | Needs helper and redirect behavior validation |
| Sessions | File-based sessions via `WRITEPATH . 'session'` | Production permissions and timeout behavior must be verified |
| Uploads | `writable/uploads` | File permission and file-handling regression checks required |
| Third-Party Code | `app/ThirdParty/PhpSpreadsheet` | Candidate for Composer-based dependency management |
| Routing | Route definitions exist in `app/Config/Routes.php` | URL compatibility and legacy endpoint validation required |
| Testing | `tests/` structure exists | Expand beyond skeleton tests for module-level regression coverage |

## Migration Scope

This document is intended for a controlled migration of the MIS application from CodeIgniter 3 development patterns to CodeIgniter 4 standards, deployment model, and runtime behavior.

Although this repository already uses a CodeIgniter 4 project skeleton, parts of the codebase still reflect legacy CI3 conventions such as utility-style models, procedural access control helpers, route naming carried forward from CI3, and direct SQL/query patterns that should be reviewed during migration hardening. This checklist therefore supports two goals:

1. Complete migration from CI3 conventions to CI4-compliant implementation patterns.
2. Validate that the migrated application is production-ready across development, testing, and deployment environments.

---

## 1. Introduction

### 1.1 Purpose of Migration

The purpose of migrating from CodeIgniter 3 to CodeIgniter 4 is to modernize the application architecture, align the project with current PHP standards, improve maintainability, and reduce operational risk associated with legacy framework patterns.

### 1.2 Benefits of CodeIgniter 4 Over CodeIgniter 3

| Benefit Area | CodeIgniter 3 | CodeIgniter 4 |
| --- | --- | --- |
| PHP Standards | Limited PSR alignment | Modern PSR-based architecture and autoloading |
| Namespaces | Not native by default | First-class namespace support |
| Environment Management | Manual config editing | `.env` driven configuration support |
| Routing | Simpler legacy routing | Improved routing, filters, and route grouping |
| Security | Basic protections | Better CSRF, filters, validation, and security features |
| Testing | Limited project structure | Built-in testing support and cleaner test organization |
| Dependency Management | Often manual | Composer-first workflow |
| Error Handling | Simpler exception flow | Improved exceptions, logging, and debug tooling |
| Deployment | More manual adjustment | Clear `public` and `writable` separation |

### 1.3 Intended Audience

| Audience | Primary Responsibilities |
| --- | --- |
| Developers | Refactor code, migrate modules, update libraries, fix compatibility issues |
| Testers | Execute functional, regression, negative, security, and UAT test cycles |
| Deployment Team | Prepare infrastructure, configure web server, manage permissions, deploy safely, support rollback |

### 1.4 Migration Principles

| Principle | Expected Practice |
| --- | --- |
| Preserve business behavior | URLs, reports, calculations, and user access should remain functionally correct |
| Migrate incrementally | Convert configuration, routes, controllers, models, views, and libraries in controlled phases |
| Prefer CI4-native patterns | Use namespaces, services, filters, validation, Composer, and environment variables |
| Validate every module | Treat each business module as a separate migration checkpoint |
| Keep rollback ready | Migration is not complete unless rollback has been tested |

---

## 2. Pre-Migration Checklist

### 2.1 Pre-Migration Readiness Table

| Checkpoint | Action | Owner | Status | Evidence/Notes |
| --- | --- | --- | --- | --- |
| Code freeze window approved | Freeze feature changes during migration | Project Manager | [ ] |  |
| Production backup completed | Full database and application backup taken | DevOps/DBA | [ ] |  |
| Staging environment ready | Match production PHP, DB, extensions, and web server | DevOps | [ ] |  |
| Migration branch created | Create dedicated branch for CI4 migration hardening | Development | [ ] |  |
| Risk review completed | Review known module and deployment risks | Tech Lead | [ ] |  |
| Rollback owner assigned | Confirm decision-maker during release window | Release Manager | [ ] |  |

### 2.2 Backup Database and Project Files

| Item | Required Action | Validation |
| --- | --- | --- |
| Database backup | Export full production database before any schema or code deployment | Restore test completed in non-production environment |
| Application files | Archive current project files, uploads, config, and web server settings | Archive checksum or file count verified |
| `.env` or secret config | Backup environment-specific configuration securely | Secrets accessible only to approved team |
| Scheduled jobs | Export cron/scheduler definitions if applicable | Scheduler inventory reviewed |

#### Backup Steps

1. Export the full database including schema, triggers, procedures, and seed/reference data.
2. Back up the entire application directory including `app`, `public`, `writable`, custom scripts, and uploaded files.
3. Capture server configuration such as Apache virtual host or Nginx server block definitions.
4. Store backups in a secured location with timestamp and release identifier.
5. Validate that backup restoration works before migration begins.

### 2.3 PHP Version Compatibility

| Checkpoint | Current/Target Guidance | Status |
| --- | --- | --- |
| Composer PHP requirement reviewed | This repository currently requires `PHP ^8.2` | [ ] |
| Development machines upgraded | Developers use same major PHP version as target environment | [ ] |
| Staging server upgraded | Staging matches production target runtime | [ ] |
| Required extensions installed | `intl`, `mbstring`, database driver, `openssl`, `fileinfo`, `curl` if used | [ ] |
| Deprecated PHP behavior reviewed | Dynamic properties, legacy functions, and old encryption behavior reviewed | [ ] |

### 2.4 Server Requirements

| Component | Minimum Expectation | Project Notes |
| --- | --- | --- |
| PHP | PHP 8.2 or higher for this repository | Confirm CLI and web PHP versions match |
| Web Server | Apache 2.4+ or Nginx equivalent | `public` folder must be web root |
| Database | MySQL/MariaDB compatible with `MySQLi` | Validate SQL modes and collation |
| File Permissions | Web server write access to `writable` only | Prevent write access to code folders |
| Composer | Available in build or deployment pipeline | Needed for dependency management |

### 2.5 Environment Setup

| Checkpoint | Action | Owner | Status |
| --- | --- | --- | --- |
| Local environment prepared | Install Composer dependencies and configure `.env` | Development | [ ] |
| Staging environment prepared | Match production as closely as possible | DevOps | [ ] |
| Debug configuration reviewed | Disable debug in production, enable only where required | DevOps | [ ] |
| Log retention configured | Ensure `writable/logs` retention is defined | DevOps | [ ] |
| Session storage verified | Confirm `writable/session` exists and is writable | DevOps | [ ] |

### 2.6 Existing Third-Party Libraries and Modules Review

| Library/Module | Current Observation | Required Action | Status |
| --- | --- | --- | --- |
| PhpSpreadsheet | Present under `app/ThirdParty/PhpSpreadsheet` | Prefer Composer-managed dependency or validate manual autoload strategy | [ ] |
| Custom helper `secure_helper.php` | Used for access restriction and encryption helpers | Review redirect flow, encryption method, and CI4 helper loading | [ ] |
| Agent/User management | Routed through `Agent` controller | Validate CRUD, validation, role logic | [ ] |
| Reporting modules | `Home`, `DuplicateEntries`, `CalcDaht`, `Mod2` | Validate query output, Excel export, filters, route behavior | [ ] |
| Upload/download flows | `upload_new_daht`, export actions | Validate uploaded file security and download headers | [ ] |

### 2.7 Pre-Migration Technical Review Questions

| Question | Decision Needed |
| --- | --- |
| Are all legacy CI3 libraries identified and classified as replace, refactor, or retain? |  |
| Are any controllers still depending on CI3-style super-object assumptions? |  |
| Are direct SQL strings safe, parameterized, and compatible with CI4 conventions? |  |
| Are all business URLs documented for regression testing? |  |
| Are Excel, upload, and session-heavy workflows included in the test scope? |  |

---

## 3. Folder Structure Changes

### 3.1 CI3 vs CI4 Directory Structure

| CI3 Structure | CI4 Structure | Migration Notes |
| --- | --- | --- |
| `application/` | `app/` | Main business code now lives in `app/` |
| `system/` | `system/` | Still framework core, but managed differently |
| `index.php` in project root | `public/index.php` | Web root must point to `public/` |
| `application/config` | `app/Config` | Namespaced config classes replace PHP arrays |
| `application/controllers` | `app/Controllers` | Controllers are namespaced classes |
| `application/models` | `app/Models` | Prefer extending `CodeIgniter\Model` |
| `application/views` | `app/Views` | Views loaded with `view()` |
| No `writable/` separation | `writable/` for logs, cache, sessions, uploads | Must be writable by the web server |
| No Composer-first `vendor/` use in many projects | `vendor/` created by Composer | External packages should be managed through Composer |

### 3.2 CI4 Folder Explanations

| Folder | Purpose | Team Responsibility |
| --- | --- | --- |
| `app/` | Application code including controllers, models, views, helpers, config | Development |
| `public/` | Front controller and public assets only | DevOps and Development |
| `writable/` | Logs, cache, sessions, debug output, uploads | DevOps |
| `vendor/` | Composer-managed dependencies | Development/Build Pipeline |

### 3.3 Public Folder Configuration on Server

| Item | Requirement | Status |
| --- | --- | --- |
| Apache `DocumentRoot` | Must point to `.../MIS/public` | [ ] |
| Nginx `root` | Must point to `.../MIS/public` | [ ] |
| Direct access to `app/` | Must be blocked | [ ] |
| Direct access to `writable/` | Must be blocked | [ ] |
| Asset paths | CSS/JS/images must resolve correctly from `public/` | [ ] |

### 3.4 Folder Migration Checkpoints

| Checkpoint | Action | Status |
| --- | --- | --- |
| Legacy file includes removed | Eliminate hard-coded references to CI3 root layout | [ ] |
| Upload paths reviewed | Confirm uploads target `WRITEPATH` or safe public path | [ ] |
| Log and cache folders reviewed | Remove legacy assumptions about writable locations | [ ] |
| Server symlinks reviewed | Validate no old paths still referenced by deployment scripts | [ ] |

---

## 4. Configuration Migration

### 4.1 Base URL

| CI3 | CI4 |
| --- | --- |
| `$config['base_url'] = 'http://example.com/app/';` | `public string $baseURL = 'http://example.com/';` or `.env` override |

#### Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| Base URL defined | Set in `app/Config/App.php` or `.env` | [ ] |
| Trailing slash validated | CI4 expects a trailing slash when explicitly configured | [ ] |
| Asset URLs verified | CSS, JS, image, and download links tested | [ ] |
| Reverse proxy scenario verified | `allowedHostnames` and proxy settings reviewed if applicable | [ ] |

### 4.2 Database Configuration

| CI3 | CI4 |
| --- | --- |
| `application/config/database.php` array | `app/Config/Database.php` class and/or `.env` variables |

#### Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| Connection group reviewed | Confirm `default` group values | [ ] |
| Database credentials externalized | Move environment-specific values to `.env` | [ ] |
| Character set validated | Prefer `utf8mb4` | [ ] |
| `DBDebug` reviewed | Disable in production | [ ] |
| Test database configured | Separate test DB or in-memory database verified | [ ] |

### 4.3 Environment Variables (`.env`)

| Recommended Variable | Purpose |
| --- | --- |
| `CI_ENVIRONMENT` | `development`, `testing`, or `production` |
| `app.baseURL` | Environment-specific base URL |
| `database.default.hostname` | Database host |
| `database.default.database` | Database name |
| `database.default.username` | Database user |
| `database.default.password` | Database password |
| `session.driver` | Session storage override if needed |
| `session.savePath` | Session location if not using default writable path |

#### Example

```dotenv
CI_ENVIRONMENT = production
app.baseURL = 'https://mis.example.com/'
database.default.hostname = localhost
database.default.database = mis
database.default.username = mis_user
database.default.password = change_me
```

### 4.4 Autoload Configuration

| CI3 | CI4 |
| --- | --- |
| `$autoload['libraries']`, `$autoload['helper']` | `app/Config/Autoload.php`, `BaseController::$helpers`, Composer PSR-4 |

#### Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| Helper autoload reviewed | Move common helpers to `BaseController::$helpers` if globally needed | [ ] |
| Namespace mappings reviewed | Ensure custom namespaces resolve correctly | [ ] |
| Third-party loading reviewed | Prefer Composer over manual includes | [ ] |
| Legacy library autoload removed | Remove CI3-specific autoload assumptions | [ ] |

### 4.5 Routes Migration

| CI3 Example | CI4 Example |
| --- | --- |
| `$route['login.html'] = 'home/index';` | `$routes->add('login.html', 'Home::index');` |

#### Current Project Note

The current project already defines routes in `app/Config/Routes.php`, including legacy-style URL patterns such as `login.html`, `home.html`, `auto-daht.html`, and report/export endpoints. These URLs should be treated as backward compatibility requirements during testing.

#### Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| All legacy routes inventoried | Create route-to-feature map before release | [ ] |
| Route HTTP methods reviewed | Use `get`, `post`, or grouped routes where possible | [ ] |
| 404 handling validated | Confirm unknown routes and moved routes behave correctly | [ ] |
| Route filters applied | Authentication and CSRF coverage reviewed | [ ] |

---

## 5. Controller Migration

### 5.1 Namespace Usage

CI4 controllers must be namespaced and typically live under `App\Controllers`.

```php
// CI3
class Home extends CI_Controller
{
    public function index()
    {
        $this->load->view('log_in');
    }
}
```

```php
// CI4
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('log_in');
    }
}
```

### 5.2 Extending `BaseController`

| CI3 | CI4 |
| --- | --- |
| Extend `CI_Controller` | Extend `App\Controllers\BaseController` |

#### Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| All controllers namespaced | Verify `namespace App\Controllers;` present | [ ] |
| All controllers extend `BaseController` where appropriate | Standardize shared behavior | [ ] |
| Shared helpers centralized | Move common helpers into `BaseController::$helpers` if justified | [ ] |
| Controller constructors reviewed | Avoid excessive manual service loading when services can be injected or requested cleanly | [ ] |

### 5.3 Request/Response Handling

| CI3 Pattern | CI4 Pattern |
| --- | --- |
| `$this->input->post('field')` | `$this->request->getPost('field')` |
| `echo` and exit-heavy responses | `return`, `$this->response`, redirects, JSON responses |

#### Example

```php
// CI3
$dateFrom = $this->input->post('date_from');
redirect('home');
```

```php
// CI4
$dateFrom = $this->request->getPost('date_from');
return redirect()->to('home.html');
```

### 5.4 Input Class Replacement

| CI3 API | CI4 Replacement |
| --- | --- |
| `$this->input->post()` | `$this->request->getPost()` |
| `$this->input->get()` | `$this->request->getGet()` |
| `$this->input->post_get()` | `$this->request->getVar()` |
| `$this->input->is_ajax_request()` | `$this->request->isAJAX()` |

### 5.5 Helper Loading

| CI3 | CI4 |
| --- | --- |
| `$this->load->helper('url');` | `helper('url');` or preload via `BaseController::$helpers` |

### 5.6 Controller Migration Checklist

| Checkpoint | Action | Project Focus |
| --- | --- | --- |
| Namespaces added | Add namespace declarations in all controllers | All controllers |
| Responses normalized | Replace raw echo/exit where possible with `return` or response objects | `Home`, `CalcDaht`, `DuplicateEntries`, `Mod2` |
| Access control reviewed | Move helper-based access checks toward filters or consistent response flow | `secure_helper.php`, login-protected routes |
| Validation integrated | Replace manual checks with CI4 validation services | Form submission actions |
| File handling modernized | Use uploaded file APIs and response downloads | Upload/export flows |

### 5.7 Project-Specific Controller Notes

| Controller | Migration Observation | Action |
| --- | --- | --- |
| `Home` | Large multi-responsibility controller | Consider splitting by domain over time |
| `CalcDaht` | Uses CI4 request/session/db, but contains legacy-style flow and direct SQL strings | Validate logic, refactor safely, add tests |
| `DuplicateEntries` | Large reporting controller with multiple export/report actions | Regression-test outputs and memory usage |
| `Agent` | CRUD flow and role-sensitive operations | Validate form validation and route protection |
| `Mod2` | QC/reporting functions | Review route exposure and testing coverage |

---

## 6. Model Migration

### 6.1 CI3 Model Conversion to CI4 Model

```php
// CI3
class User_model extends CI_Model
{
    public function getUser($id)
    {
        return $this->db->get_where('users', ['id' => $id])->row_array();
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
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email'];

    public function getUser(int $id): ?array
    {
        return $this->where('id', $id)->first();
    }
}
```

### 6.2 Query Builder Changes

| CI3 | CI4 |
| --- | --- |
| `$this->db->get_where('users', ['id' => $id])` | `$this->db->table('users')->where('id', $id)->get()` |
| `$query->row_array()` | `$query->getRowArray()` |
| `$query->result_array()` | `$query->getResultArray()` |

### 6.3 Database Connection Handling

| Approach | Guidance |
| --- | --- |
| `\Config\Database::connect()` | Acceptable when used intentionally |
| Extend `CodeIgniter\Model` | Preferred for table-based data access |
| Reusable service-style classes | Acceptable if class is not truly an ORM-style model |

### 6.4 Validation Integration

CI4 models support validation rules directly through model configuration or controller/service-level validation.

```php
protected $validationRules = [
    'email' => 'required|valid_email',
    'name'  => 'required|min_length[3]',
];
```

### 6.5 Model Migration Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| Legacy models identified | List all model and utility data-access classes | [ ] |
| Proper inheritance reviewed | Convert table-backed models to extend `CodeIgniter\Model` | [ ] |
| Allowed fields set | Prevent mass assignment issues | [ ] |
| Query builder syntax verified | Update result and builder methods | [ ] |
| Validation rules centralized | Add model or request validation where applicable | [ ] |

### 6.6 Project-Specific Model Notes

| Model/Class | Observation | Recommendation |
| --- | --- | --- |
| `Model_datatable` | Already extends `Model` | Validate builder and escaping logic |
| `Model_common` | Acts as a generic DB utility, not a standard CI4 model | Decide whether to keep as a service/repository or convert parts into dedicated models |

---

## 7. View Migration

### 7.1 View Loading Changes

| CI3 | CI4 |
| --- | --- |
| `$this->load->view('header');` | `echo view('header');` or `return view('header');` |

#### Example

```php
// CI3
$data['users'] = $users;
$this->load->view('user_list', $data);
```

```php
// CI4
return view('user_list', ['users' => $users]);
```

### 7.2 Passing Data to Views

| Checkpoint | Action | Status |
| --- | --- | --- |
| Array payloads reviewed | Replace implicit variable assumptions with explicit data arrays | [ ] |
| Escaping reviewed | Use `esc()` where untrusted data is rendered | [ ] |
| Repeated partials standardized | Use shared headers/footers/sections | [ ] |

### 7.3 Layout Handling

CI4 supports cleaner layout composition through multiple `view()` calls or view layouts/sections.

```php
echo view('inc_l/hd');
echo view('form_daht_report', $data);
echo view('inc_l/ftr');
```

Where practical, migrate high-reuse pages to structured layouts and sections for easier maintenance.

### 7.4 View Migration Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| All views accessible from `app/Views` | Confirm path references are correct | [ ] |
| Header/footer includes verified | Test pages with partial rendering | [ ] |
| Form CSRF tokens added | Ensure protected forms render tokens where enabled | [ ] |
| Validation errors displayed | Standardize feedback rendering | [ ] |

---

## 8. Library and Helper Migration

### 8.1 Custom Libraries Conversion

| Legacy Pattern | CI4 Approach |
| --- | --- |
| CI3 library loaded via `$this->load->library()` | Use namespaced classes, services, Composer packages, or dedicated helper/service classes |

### 8.2 Helper Compatibility

| Checkpoint | Action | Status |
| --- | --- | --- |
| All custom helpers reviewed | Confirm function naming, redirect flow, and side effects | [ ] |
| Global helper collisions checked | Ensure function names do not conflict with CI4/system helpers | [ ] |
| Autoload strategy defined | Explicit `helper()` calls or shared helper list | [ ] |

### 8.3 Session Handling Changes

| CI3 | CI4 |
| --- | --- |
| `$this->session->userdata('user_logged')` | `session()->get('user_logged')` |
| Manual session loading | Session service/config-driven session usage |

#### Example

```php
// CI3
$user = $this->session->userdata('user_logged');
```

```php
// CI4
$user = session()->get('user_logged');
```

### 8.4 Email Library Updates

| CI3 | CI4 |
| --- | --- |
| `$this->load->library('email');` | `$email = service('email');` |

#### Example

```php
$email = service('email');
$email->setTo('user@example.com');
$email->setSubject('Password Reset');
$email->setMessage('Your reset link');
$email->send();
```

### 8.5 Project-Specific Notes

| Item | Observation | Recommendation |
| --- | --- | --- |
| `secure_helper.php` | Uses redirect and exit pattern for access control | Consider moving login checks to CI4 filters for consistency |
| `PhpSpreadsheet` | Embedded under `app/ThirdParty` | Prefer Composer package management if project policy allows |
| Session config | FileHandler with `WRITEPATH/session` | Validate permissions and session cleanup strategy |

---

## 9. Security Updates

### 9.1 Security Checklist

| Checkpoint | Action | Status |
| --- | --- | --- |
| CSRF enabled where required | Validate form protection and AJAX compatibility | [ ] |
| XSS output escaping reviewed | Escape output in views and reports | [ ] |
| Validation rules applied | Replace manual request trust with validation | [ ] |
| Authentication flow reviewed | Harden login, logout, and session fixation controls | [ ] |
| Session security reviewed | Cookie settings, regeneration, timeout, and storage path validated | [ ] |
| File upload security reviewed | MIME validation, extension whitelist, storage path review | [ ] |

### 9.2 CSRF Handling

| CI3 | CI4 |
| --- | --- |
| Config-driven with manual view handling in many projects | Filter-based support with helper/form integration |

### 9.3 XSS Filtering

CI4 does not encourage blanket input filtering as a substitute for output escaping. Use validation plus contextual output escaping such as `esc($value)`.

### 9.4 Validation Rules

Prefer centralized validation rules instead of scattered manual `if` conditions.

### 9.5 Authentication and Session Security

| Item | Recommended Practice |
| --- | --- |
| Session regeneration | Confirm `timeToUpdate` and `regenerateDestroy` meet policy |
| Timeout behavior | Validate forced logout after inactivity |
| Role validation | Re-test administrator-only actions such as DAHT posting and user management |
| Secure cookies | Use secure cookie settings in HTTPS environments |

---

## 10. Migration Risks and Mitigations

| Risk | Impact | Likelihood | Mitigation | Owner |
| --- | --- | --- | --- | --- |
| Legacy URL breakage | Users cannot access known pages | Medium | Maintain current route aliases and test all published URLs | Development/QA |
| Session path or cookie misconfiguration | Login loops, forced logout, role errors | High | Validate session driver, save path, cookie name, and timeout in every environment | DevOps/QA |
| Public folder misconfiguration | 404 errors, asset failures, security exposure | High | Point web root to `public/` and test rewritten URLs before go-live | DevOps |
| Direct SQL incompatibility or regression | Reports or CRUD results become incorrect | High | Compare row counts and sample outputs before and after release | Development/QA |
| Third-party library loading issues | Excel export/import fails | Medium | Standardize PhpSpreadsheet loading and verify export/download flows | Development |
| Writable permission problems | Sessions, uploads, cache, logs fail | High | Pre-validate `writable` ownership and permissions | DevOps |
| Mixed CI3/CI4 coding patterns | Hard-to-trace runtime defects | Medium | Use code review checklist and targeted refactoring for shared patterns | Development |
| Performance regressions in large reports | Slow dashboards or export timeouts | Medium | Benchmark duplicate-entry, live QC, shadow, and DAHT reports in staging | Development/QA |

---

## 11. Server Deployment Checklist

### 11.1 Deployment Readiness Table

| Checkpoint | Action | Owner | Status | Evidence |
| --- | --- | --- | --- | --- |
| Release package built | Install dependencies and package deployment artifact | DevOps | [ ] |  |
| Environment file ready | Production `.env` prepared securely | DevOps | [ ] |  |
| Database backup completed | Backup timestamp recorded | DBA | [ ] |  |
| Maintenance window approved | Stakeholders informed | Release Manager | [ ] |  |
| Rollback package ready | Previous stable release archived | DevOps | [ ] |  |

### 11.2 Apache/Nginx Configuration

| Item | Apache | Nginx |
| --- | --- | --- |
| Web root | `DocumentRoot /path/to/MIS/public` | `root /path/to/MIS/public;` |
| Front controller | `public/index.php` | `try_files $uri $uri/ /index.php?$query_string;` |
| Protected directories | Deny direct access to `app`, `system`, `writable`, `tests` | Do not expose these directories under web root |

### 11.3 Rewrite Rules

| Checkpoint | Action | Status |
| --- | --- | --- |
| `.htaccess` reviewed | Confirm Apache rewrite rules support clean URLs | [ ] |
| Nginx fallback reviewed | Confirm `try_files` sends unresolved routes to `index.php` | [ ] |
| `index.php` visibility confirmed | Hide `index.php` where intended and test both normal and edge routes | [ ] |

### 11.4 Public Directory Mapping

| Checkpoint | Action | Status |
| --- | --- | --- |
| `public/` mapped as web root | Required | [ ] |
| Asset references validated | CSS, JS, images, downloads tested | [ ] |
| Old root entry points removed | Prevent accidental routing to wrong document root | [ ] |

### 11.5 File Permissions

| Path | Required State | Status |
| --- | --- | --- |
| Application code | Read-only for runtime user where possible | [ ] |
| `writable/logs` | Writable | [ ] |
| `writable/cache` | Writable | [ ] |
| `writable/session` | Writable | [ ] |
| `writable/uploads` | Writable | [ ] |

### 11.6 Environment Setup on Production

| Checkpoint | Action | Status |
| --- | --- | --- |
| `CI_ENVIRONMENT=production` | Set and verify | [ ] |
| Production base URL set | Verify HTTPS and canonical host | [ ] |
| DB credentials applied | Verify with smoke test | [ ] |
| Debug disabled | Ensure stack traces are not public | [ ] |
| Cron/jobs revalidated | Re-enable only after smoke tests pass | [ ] |

### 11.7 Deployment Execution Checklist

| Step | Action | Owner | Status |
| --- | --- | --- | --- |
| 1 | Place application in maintenance mode if required | DevOps | [ ] |
| 2 | Take final pre-release backup | DBA/DevOps | [ ] |
| 3 | Deploy code artifact | DevOps | [ ] |
| 4 | Install Composer dependencies | DevOps | [ ] |
| 5 | Apply environment file | DevOps | [ ] |
| 6 | Validate permissions on `writable/` | DevOps | [ ] |
| 7 | Run smoke tests | QA/Development | [ ] |
| 8 | Release traffic | Release Manager | [ ] |

---

## 12. Testing Checklist

### 12.1 Test Execution Template

| Test ID | Module | Scenario | Expected Result | Tester | Status | Evidence |
| --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  | [ ] Pass / [ ] Fail |  |

### 12.2 Login Module

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| Login page loads | Open `login.html` | Page renders without asset or route errors | [ ] |
| Valid credentials | Login with valid user | Redirect to dashboard/home page | [ ] |
| Invalid credentials | Login with wrong password | Controlled validation error shown | [ ] |
| Unauthorized direct access | Open protected URL without login | User redirected to login with message | [ ] |
| Logout | Trigger logout action | Session cleared and login page shown | [ ] |
| Session persistence | Refresh after login | User remains logged in until timeout/logout | [ ] |

### 12.3 Dashboard

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| Dashboard route | Open `home.html` as valid user | Dashboard loads | [ ] |
| Role-sensitive menus | Login as different user roles | Correct menus/options shown | [ ] |
| Data widgets | Dashboard counters/reports load | Counts match database samples | [ ] |
| Navigation | Menu links open correct routes | No broken links or 404s | [ ] |

### 12.4 CRUD Operations

| Area | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| Agent/User create | Add new user/agent | Record saved and visible in list | [ ] |
| Agent/User update | Edit existing user/agent | Changes persist correctly | [ ] |
| Agent/User delete/deactivate | Remove or deactivate record | Expected state change occurs without side effects | [ ] |
| Task create | Add task/subcategory | Record stored with correct validation | [ ] |
| Task edit | Edit existing task | Update reflected everywhere used | [ ] |
| Restore | Restore soft-deleted or hidden record where applicable | Record becomes available again | [ ] |
| Validation | Submit blank/invalid data | Validation errors displayed | [ ] |

### 12.5 Reports

| Report/Feature | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| User statistics report | Run report for sample date range | Totals and grouping match legacy output | [ ] |
| Duplicate entries report | Generate duplicate entries list | Data set and filters behave correctly | [ ] |
| Live QC report | Run live QC report | Report returns expected grouped records | [ ] |
| Shadow entries report | Run shadow report | Output matches baseline sample | [ ] |
| DAHT calculation report | Generate Auto DAHT report | Calculation, totals, variation, and export data are correct | [ ] |
| Report filters | Test category/date/user filters | Filters apply accurately | [ ] |

### 12.6 File Upload/Download

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| DAHT upload page | Open upload page | Form renders correctly | [ ] |
| Valid upload | Upload allowed file | File accepted and processed successfully | [ ] |
| Invalid upload | Upload unsupported file | Validation error shown | [ ] |
| Large upload | Test near max size | Defined behavior observed | [ ] |
| Excel export | Export report to XLS/XLSX | File downloads correctly and opens | [ ] |
| Download headers | Inspect filename and content type | Browser handles download correctly | [ ] |

### 12.7 API Integration

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| External endpoint availability | Execute integration call if applicable | Response handled without fatal errors | [ ] |
| Authentication | Validate API credentials/tokens | Secure and functional | [ ] |
| Error handling | Simulate timeout/failure | Application shows controlled fallback | [ ] |
| Logging | Verify failures are logged | Support team can diagnose issues | [ ] |

### 12.8 Email Functionality

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| SMTP/config load | Test email configuration | No configuration error | [ ] |
| Functional email | Trigger email workflow | Email sent and delivered | [ ] |
| Invalid recipient handling | Use failing address | Error logged and user-facing handling remains controlled | [ ] |
| Template rendering | Review message body | Correct formatting and links | [ ] |

### 12.9 Role/Permission Testing

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| Admin-only actions | Access DAHT posting, user management as non-admin | Access blocked | [ ] |
| Standard user actions | Verify normal menu access | User sees only authorized features | [ ] |
| Hidden URLs | Directly open protected routes | Unauthorized user redirected or denied | [ ] |

### 12.10 Session Timeout Testing

| Checkpoint | Test Scenario | Expected Result | Status |
| --- | --- | --- | --- |
| Idle timeout | Leave session idle beyond configured limit | User must re-authenticate | [ ] |
| Active session | Continue using application within timeout window | Session remains valid | [ ] |
| Concurrent tabs | Open multiple tabs and logout | All protected tabs require re-authentication after next request | [ ] |
| Session storage | Inspect `writable/session` behavior | Session files created and cleaned up correctly | [ ] |

### 12.11 Recommended Module Regression Matrix for This Project

| Module | Key Routes/Functions | Mandatory Regression Scope |
| --- | --- | --- |
| Authentication | `login.html`, `chk.html`, `logout.html` | Login, logout, invalid login, session timeout |
| Dashboard/Home | `home.html`, menu/report entry points | Navigation, widgets, role-based menu visibility |
| Agent Management | `save_agent`, `user_list`, `edit_agent`, `delete_agent`, `deactive_agent` | Full CRUD and role restrictions |
| Task Management | `task-selection.html`, `task-create.html`, edit/restore routes | Create, edit, hide, restore, validation |
| Reporting | `report.html`, `get-repo`, Excel export actions | Data accuracy and export download |
| Duplicate/Live QC/Shadow | `find-duplicate-entries.html`, related endpoints | Filters, grouping, export, performance |
| DAHT | `auto-daht.html`, `get-daht_calc`, `gen-excel-daht`, `post-daht` | Calculation accuracy, admin restriction, export, posting |
| Uploads | `upload-new-daht.html`, related save/check actions | Valid/invalid upload, file security, persistence |

---

## 13. Common Migration Errors and Fixes

| Error/Issue | Likely Cause | Recommended Fix |
| --- | --- | --- |
| Undefined property errors | CI3-style dynamic properties or missing service/model initialization | Explicitly declare properties and initialize dependencies in constructor or `initController()` |
| Namespace issues | Missing or incorrect `namespace` and `use` statements | Add correct namespace declarations and import classes explicitly |
| Base URL problems | Incorrect `baseURL`, missing trailing slash, wrong document root | Fix `.env` or `App.php`, verify `public/` mapping |
| 404 routing issues | Routes not migrated or web server rewrite misconfigured | Review `Routes.php`, Apache/Nginx rewrite rules, and route methods |
| Session issues | Wrong session save path, unwritable session folder, cookie mismatch | Verify `Session.php`, folder permissions, cookie settings, and environment values |
| Query Builder syntax differences | Using CI3 result/builder methods in CI4 | Replace with CI4 query methods like `getRowArray()` and `getResultArray()` |
| Writable folder permission issues | `writable/` not owned/writable by runtime user | Correct ownership/permissions for `logs`, `cache`, `session`, `uploads` |
| Redirect behavior inconsistency | Mixing helper `redirect()->to()->send()` with raw output flow | Standardize controller returns and review helper-based exits |
| CSRF validation failures | Missing tokens in forms or AJAX requests | Include CSRF tokens and configure AJAX headers appropriately |
| Download/export failures | Third-party library autoloading or output buffering problems | Validate dependency loading and response headers before output |

### 13.1 Quick Diagnostic Checklist

| Question | Yes/No |
| --- | --- |
| Is the web server pointing to `public/`? |  |
| Is `writable/session` writable? |  |
| Is `baseURL` correct for the environment? |  |
| Are namespaces present on all controllers/models/libraries? |  |
| Are routes defined for all legacy URLs users still access? |  |
| Are required PHP extensions enabled on the server? |  |

---

## 14. Post-Migration Validation

### 14.1 Validation Checklist

| Validation Area | Action | Owner | Status |
| --- | --- | --- | --- |
| Error log monitoring | Review `writable/logs` after deployment and during first business cycle | DevOps/Development | [ ] |
| User acceptance testing | Business users validate critical workflows | Business/QA | [ ] |
| Database verification | Compare key record counts and sample transactions | DBA/QA | [ ] |
| Performance benchmarking | Compare major reports and page loads with baseline | Development/QA | [ ] |
| Security testing | Validate auth, session, CSRF, upload, and access restrictions | QA/Security | [ ] |

### 14.2 Database Verification Points

| Area | Validation Method | Status |
| --- | --- | --- |
| User/agent data | Compare record counts and sample edit history | [ ] |
| Task/category data | Validate lookup tables and joins | [ ] |
| DAHT tables | Compare posted/generated data for sample ranges | [ ] |
| Report totals | Compare old vs new totals for known date ranges | [ ] |

### 14.3 Performance Benchmarking Points

| Feature | Metric | Baseline | Actual | Status |
| --- | --- | --- | --- | --- |
| Login | Page load/auth response time |  |  | [ ] |
| Dashboard | Initial render |  |  | [ ] |
| Duplicate report | Query + render time |  |  | [ ] |
| Live QC report | Query + render time |  |  | [ ] |
| DAHT export | Export generation time |  |  | [ ] |

---

## 15. Rollback Plan

### 15.1 Backup Restore Steps

| Step | Action | Owner | Status |
| --- | --- | --- | --- |
| 1 | Announce rollback decision and stop new deployment actions | Release Manager | [ ] |
| 2 | Place application in maintenance mode | DevOps | [ ] |
| 3 | Restore previous application package | DevOps | [ ] |
| 4 | Restore previous `.env` and server configuration if changed | DevOps | [ ] |
| 5 | Restore database backup if schema/data change requires it | DBA | [ ] |
| 6 | Clear cache/session artifacts if necessary | DevOps | [ ] |
| 7 | Perform smoke tests on restored version | QA/Development | [ ] |
| 8 | Re-open traffic after approval | Release Manager | [ ] |

### 15.2 Rollback Deployment Procedure

| Checkpoint | Action | Status |
| --- | --- | --- |
| Previous release package stored | Confirm location and version identifier | [ ] |
| Restore instructions documented | No ad hoc rollback commands during incident | [ ] |
| Rollback database impact reviewed | Determine whether data written during failed release must be preserved or reverted | [ ] |
| Rollback smoke test list ready | Login, dashboard, reports, upload, and admin flows | [ ] |

### 15.3 Emergency Recovery Checklist

| Item | Action | Status |
| --- | --- | --- |
| Stakeholder communication | Notify support, business owner, and deployment team | [ ] |
| Incident logging | Capture failure symptoms, timestamps, and affected modules | [ ] |
| Production logs archived | Save error logs before cleanup | [ ] |
| Root cause review scheduled | Create corrective action item after stabilization | [ ] |

---

## 16. Best Practices

### 16.1 Use Environment Files

| Best Practice | Why It Matters |
| --- | --- |
| Keep environment-specific values in `.env` | Avoid hard-coded production values in source code |
| Separate secrets from repository defaults | Improve security and release consistency |
| Use environment-specific overrides | Reduce deployment errors between local, staging, and production |

### 16.2 Maintain Coding Standards

| Best Practice | Why It Matters |
| --- | --- |
| Use namespaces consistently | Required for maintainable CI4 code |
| Prefer CI4 services and models | Reduces hybrid-pattern technical debt |
| Split large controllers over time | Improves readability and testability |
| Avoid direct SQL string concatenation where possible | Improves security and maintainability |

### 16.3 Logging and Monitoring

| Best Practice | Why It Matters |
| --- | --- |
| Monitor `writable/logs` after every deployment | Catch runtime issues early |
| Add structured logging around critical workflows | Speeds support and incident response |
| Retain logs through migration window | Helps compare pre- and post-release behavior |

### 16.4 Version Control Recommendations

| Best Practice | Why It Matters |
| --- | --- |
| Use dedicated migration branch | Keeps migration work isolated and reviewable |
| Tag pre-migration and post-migration releases | Simplifies rollback and comparison |
| Use pull request review checklist | Enforces namespace, route, security, and testing reviews |
| Link commits to test evidence | Improves auditability for release approval |

### 16.5 Recommended Implementation Standards for This Project

| Area | Recommendation |
| --- | --- |
| Access control | Replace helper-driven redirects with CI4 filters where practical |
| Data access | Move utility-style data access into dedicated model/service classes |
| Third-party packages | Prefer Composer-managed dependencies over manual `ThirdParty` copies |
| Reports | Add regression test datasets for duplicate, live QC, shadow, and DAHT outputs |
| Deployment | Standardize on `public/` web root and locked-down `writable/` permissions |

---

## Final Sign-Off Template

| Team | Name | Date | Status | Remarks |
| --- | --- | --- | --- | --- |
| Development |  |  | [ ] Approved |  |
| QA |  |  | [ ] Approved |  |
| DevOps/Deployment |  |  | [ ] Approved |  |
| Business/UAT |  |  | [ ] Approved |  |

## Conclusion

This checklist is designed to support a controlled, auditable, and low-risk migration from CodeIgniter 3 practices to CodeIgniter 4 standards for the MIS project. It should be used as a working document during planning, execution, testing, deployment, and post-release validation. Teams should update the status columns and evidence fields throughout the migration cycle so the document becomes the formal record of readiness and sign-off.
