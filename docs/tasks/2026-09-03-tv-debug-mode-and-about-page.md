# TV debug mode and About page

## Goal

Keep TV diagnostics visible only when the page URL includes `?debug`, remove the decorative demo heading, and move the model attribution to an About page.

## Completion criteria

- A normal TV page has no diagnostic text overlays.
- `?debug` displays the TV connection and current-action diagnostics.
- The home page links to an About page containing the Stripe the Cat CC BY 4.0 attribution.

## Stages

1. **Complete:** made debug overlays conditional and propagated the parameter to the embedded 3D scene.
2. **Complete:** removed the decorative demo heading and moved the attribution to `/about`.
3. **Pending:** run focused feature tests, formatting, build, and diff checks.
