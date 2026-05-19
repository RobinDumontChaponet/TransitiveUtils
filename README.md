# TransitiveUtils

**Legacy** *junk* utility helpers used by older Transitive projects.

This package is a grab bag of standalone helpers for strings, arrays, sessions, validation, pagination, image processing, mail building, and lightweight profiling.

## What is included
- `Transitive\Utils\Arrays`: array uniqueness, flattening, and recursive diff helpers.
- `Transitive\Utils\HttpRequest`: HTTP status code and status message helpers.
- `Transitive\Utils\Images`: GD-based image scaling and save helpers.
- `Transitive\Utils\Mail`: mail message builder with plain text, HTML, and attachment support.
- `Transitive\Utils\Optimization`: cache-busting, include listing, and timing helpers.
- `Transitive\Utils\Pagination`: pagination math and link generation.
- `Transitive\Utils\Sessions`: static session wrapper.
- `Transitive\Utils\Signals`: process-state helper.
- `Transitive\Utils\Strings`: string cleanup, slug generation, date formatting, and misc text utilities.
- `Transitive\Utils\Validation`: form validation and common input validators.

```php
use Transitive\Utils\Mail;

$mail = new Mail(
    'robot@example.com',
    'Transitive',
    'Export ready',
    'The export is attached.',
    '<p>The export is attached.</p>'
);

$mail
    ->setReplyToAddress('support@example.com')
    ->addFile('/path/to/export.csv')
    ->send('you@example.com');
```

Use `addAttachment($content, $filename, $contentType)` when the file content is already in memory.
