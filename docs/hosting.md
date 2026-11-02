# Hosting and operations plan

An independent portfolio prototype, run locally with DDEV. This document sets out how the tracker would be hosted, deployed, backed up and maintained for real use. It is a plan: no production environment has been set up, and no hosting provider is endorsed or contracted.

## 1. What has to be hosted
| Component | Detail |
|---|---|
| Application | Drupal 11.4 on PHP 8.3, served by nginx with PHP-FPM (the same stack DDEV runs locally) |
| Database | A MySQL/MariaDB-compatible database (the local DDEV default); holds content, users, configuration state |
| Files | User-uploaded files and the generated public files directory |
| Code and configuration | This repository: custom module, theme, exported configuration (`web/config/sync`), build scripts, Composer lockfile |
| Data sources | CSV files in `data/`, loaded by the scripts in `web/scripts` |

The site is read-mostly: visitors read pages and downloads; a small number of contributors and editors write. That favours aggressive caching and modest server size.

## 2. Hosting options compared
| Option | Strengths | Trade-offs |
|---|---|---|
| Managed Drupal platform (a provider that specialises in Drupal) | Built-in environments, backups, security patching of the platform, deployment tooling | Recurring cost; platform conventions to follow |
| Cloud virtual machine or container service | Full control, flexible cost | The team owns patching, backups, monitoring and TLS |
| Institution's existing infrastructure | May satisfy data-residency and procurement rules | Dependent on that institution's processes and timelines |

**Recommendation for a first public release:** a managed Drupal platform, because it removes most operating work for a small team. The choice would be confirmed against budget, data-residency requirements and procurement rules, which are not known for this project.

## 3. Environments and deployment
| Environment | Purpose |
|---|---|
| Local (DDEV) | Development |
| Staging | A copy for editors to review changes and for a final check |
| Production | The public site |

Deployment flow, building on the existing pipeline (`docs/ci.md`):
1. A change is pushed; CI rebuilds the site from the repository on a clean machine and runs the data checks, validation tests, user-journey tests and accessibility checks.
2. Only a green run is deployed. Code reaches staging first.
3. On the server: `composer install --no-dev`, then `drush updatedb`, `drush config:import` and `drush cache:rebuild`.
4. Content is not overwritten by configuration import. Records are managed in the site by editors and reach production through the review workflow, not through the repository.
5. Rollback: redeploy the previous tagged release and restore the pre-deployment backup if a database change must be reversed.

## 4. Configuration and secrets
- Database credentials, the hash salt and any API tokens are supplied as environment variables or a settings file kept outside the repository.
- The administrator account is created on the server with a strong generated password, stored in a password manager. The local development password is never reused.
- Set `trusted_host_patterns` for the production domain.
- Disable the development and debugging modules; keep error display off and log errors to a file or service.

## 5. Security
| Area | Measure |
|---|---|
| Transport | HTTPS only with an automatically renewed certificate; HSTS enabled |
| Access | Roles are Contributor and Editor, with an Administrator account limited to named people; two-factor authentication for editors and administrators (to be added) |
| Public API | Read-only; published content only |
| Updates | Subscribe to Drupal security announcements; apply core and module security updates promptly (within days for critical releases); run `composer audit` in CI |
| Hardening | Restrict admin paths by role, rate-limit the login form, keep file uploads to an allow-list of types |
| Privacy | The tracker stores no personal data about visitors beyond standard server logs and holds only the accounts of contributors and editors |

## 6. Backups and recovery
| Item | Frequency | Retention | Test |
|---|---|---|---|
| Database | Daily, plus before every deployment | 30 days daily, 12 months monthly | Restore to staging each quarter |
| Files | Daily | Same | Same |
| Repository | Every push (Git history) | Permanent | Fresh clone and CI rebuild proves it |

Recovery targets for a first release: restore service within one working day; lose no more than one day of editorial changes. The CI pipeline already proves the whole site can be rebuilt from the repository, configuration and CSV files; backups cover content created in the site since then.

## 7. Performance and availability
- Drupal's page cache for anonymous visitors; a content delivery network (CDN) in front for static assets and cached pages.
- Charts and the map load local scripts, so there is no dependence on third-party servers at page load.
- Uptime monitoring with an alert to the maintainers; a status check against the Overview page and `/tracker/data/commitments.csv`.

## 8. Ongoing maintenance
| Task | Cadence |
|---|---|
| Security updates | As released |
| Minor updates of core, modules and libraries | Monthly |
| Data refresh (indicator values; commitment source re-verification) | At least annually, with the verified-on and retrieved-on dates updated |
| Accessibility re-check (automated every push; manual pass) | After each significant change and at least annually |
| Backup restore test | Quarterly |
| Review of accounts and roles | Quarterly |
| Dependency and Node action versions in CI | When providers deprecate them |

The maintenance log is `docs/maintenance-log.md`; editor tasks are in `docs/admin-guide.md`.

## 9. Cost drivers
Hosting platform tier, the CDN, backup storage, a domain and certificate (often included), and maintainer time for monthly updates. No figures are given here because they depend on the provider chosen and are not known.

## 10. Before going live
1. Choose the host and confirm data-residency and procurement requirements.
2. Provision staging and production; configure secrets and backups.
3. Add two-factor authentication for editors and administrators.
4. Repeat the Spanish review for any new or changed Spanish text, and keep the "pending human review" label on it until it is checked.
5. Final accessibility and security checks on the production domain.
6. Confirm the publication state of every record and the licence of every source.
