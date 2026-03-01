# TransitiveUtils

**Legacy** *junk* utility helpers used by older Transitive projects.

This package is a grab bag of standalone helpers for strings, arrays, sessions, validation, pagination, JSON handling, image processing, mail building, and lightweight profiling. It is useful when maintaining an existing codebase that already depends on these helpers.

## What is included

- `Transitive\Utils\Arrays`: array uniqueness, flattening, and recursive diff helpers.
- `Transitive\Utils\HttpRequest`: HTTP status code and status message helpers.
- `Transitive\Utils\Images`: GD-based image scaling and save helpers.
- `Transitive\Utils\JsonHandler`: JSON encode/decode wrapper with error handling.
- `Transitive\Utils\Mail`: mail message builder with plain text and HTML support.
- `Transitive\Utils\ModelException` and `Transitive\Utils\DAOException`: lightweight domain exceptions.
- `Transitive\Utils\Optimization`: cache-busting, include listing, and timing helpers.
- `Transitive\Utils\Pagination`: pagination math and link generation.
- `Transitive\Utils\Sessions`: static session wrapper.
- `Transitive\Utils\Signals`: process-state helper.
- `Transitive\Utils\Strings`: string cleanup, slug generation, date formatting, and misc text utilities.
- `Transitive\Utils\Validation`: form validation and common input validators.
