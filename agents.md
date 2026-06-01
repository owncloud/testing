# agents.md — testing

## Repository Overview

The Testing app provides helper utilities for automated testing of ownCloud Server. It exposes internal endpoints for acceptance test suites to manage test fixtures, users and server state. Not intended for production use.

- **Classification:** Infrastructure / Tooling
- **Activity Status:** Active
- **License:** AGPL-3.0
- **Language:** PHP

## Architecture & Key Paths

- `appinfo/` — ownCloud app metadata (info.xml, routes)
- `lib/` — PHP backend (test helper controllers, API endpoints)
- `data/` — Test data fixtures
- `tests/` — PHPUnit test suites
- `Makefile` — Build and test orchestration
- `composer.json` — PHP dependency management
- `phpcs.xml` — Code style rules
- `phpstan.neon` — Static analysis configuration
- `phpunit.xml` — PHPUnit configuration

## Development Conventions

- Standard ownCloud OC10 app structure
- Code style enforced by phpcs
- Static analysis via PHPStan
- PR template available
- SonarCloud integration

## Build & Test Commands

```bash
make dist                   # Build distribution package
make clean                  # Clean build artifacts
make test-php-unit          # Run PHP unit tests
make test-php-style         # Check code style (phpcs)
make test-php-style-fix     # Auto-fix style issues
make test-php-phpstan       # Run PHPStan static analysis
make test-php-phan          # Run Phan static analysis
```

## Important Constraints

- **AGPL-3.0 copyleft license:** This repository is AGPL-3.0. The OSPO Apache 2.0 migration requires auditing this copyleft license before any relicensing.
- **Not for production:** This app should only be enabled on test/CI instances.
- **ownCloud Server dependency:** Requires an ownCloud Server (classic, PHP-based) installation to function.
- **CI integration:** Used by GitHub Actions and Drone CI pipelines for acceptance testing.


## OSPO Policy Constraints

### GitHub Actions
- **Only** use actions owned by `owncloud`, created by GitHub (`actions/*`), verified on the GitHub Marketplace, or verified by the ownCloud Maintainers.
- Pin all actions to their full commit SHA (not tags): `uses: actions/checkout@<SHA> # vX.Y.Z`
- Never introduce actions from unverified third parties.

### Dependency Management
- Dependabot is configured for automated dependency updates.
- Review and merge Dependabot PRs as part of regular maintenance.
- Do not introduce new dependencies without discussion in an issue first.

### Git Workflow
- **Rebase policy**: Always rebase; never create merge commits. Use `git pull --rebase` and `git rebase` before pushing.
- **Signed commits**: All commits **must** be PGP/GPG signed (`git commit -S -s`).
- **DCO sign-off**: Every commit needs a `Signed-off-by` line (`git commit -s`).
- **Conventional Commits & Squash Merge**: Use the [Conventional Commits](https://www.conventionalcommits.org/) format where the repository enforces it. Many repos use squash merge, where the PR title becomes the commit message on the default branch — apply Conventional Commits format to PR titles as well. A reusable GitHub Actions workflow enforces this.

## Context for AI Agents

- This is an ownCloud Server (OC10) app used exclusively for testing infrastructure.
- The `lib/` directory contains test helper controllers that expose API endpoints.
- The `data/` directory contains test fixtures.
- This app is a dependency of many ownCloud acceptance test pipelines.
