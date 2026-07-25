# Coding Standards

Rules for this theme. Human and AI must follow.

---

## PHP

- All classes: `final class RH_*`
- One class per file
- All methods: `public static` or `private static`
- Always type hint parameters and return
- `<?php` opening, no closing tag
- `defined( 'ABSPATH' ) || exit;` at top of every file

```php
final class RH_Example {
    public static function get( string $key ): ?string { }
}
```

---

## Config Files

Return arrays only. No logic.

```php
<?php
defined( 'ABSPATH' ) || exit;
return [ 'key' => 'value' ];
```

---

## Handler Files

One class per file. Self-register at bottom.

```php
final class RH_Ajax_MyAction {
    public static function process(): void {
        wp_send_json_success();
    }
}
RH_Ajax::register( 'my_action', [
    'callback' => [ 'RH_Ajax_MyAction', 'process' ],
    'public'   => true,
] );
```

---

## Security

```php
// Nonce
wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'action' );

// Capability
current_user_can( 'manage_options' );

// Escaping
esc_html( $value );  esc_attr( $value );  esc_url( $value );

// Sanitization
sanitize_text_field( wp_unslash( $_POST['name'] ) );
sanitize_email( wp_unslash( $_POST['email'] ) );

// SQL
$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id );
```

---

## CSS

- Prefix: `rh-` → `.rh-card`
- BEM: `.rh-card__title`, `.rh-card--featured`
- Variables: `--rh-primary`

---

## React

- Mount: `#root`
- Context: `window.rtSite`
- Components: `PascalCase` → `Button.tsx`
- Hooks: `use` prefix → `useApi.ts`

---

## Quick Reference

| What | Rule |
|------|------|
| Class prefix | `RH_` |
| Method style | `snake_case` |
| CSS class | `rh-` prefix |
| Indent | 4 spaces |
| Array style | `[ 'key' => 'value' ]` |
| File tag | `<?php` no closing |
