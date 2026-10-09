# Client Logos

A WordPress plugin for "Proud members of…" logo carousel sections.

## Install
Upload `client-logos.zip` under **Plugins → Add New → Upload Plugin**, then activate.

## Use
1. Go to **Client Logos → Add New**.
2. Fill in the fields and add logos with **+ Add more** (or **Add several from Media Library**). Drag rows to reorder, **Delete** to remove.
3. Click **Save section** (saves over AJAX) — clicking Publish/Update saves the fields too.
4. Publish, then paste the shortcode from the sidebar: `[client_logos id="123"]`
   Optional extra class: `[client_logos id="123" class="my-class"]`

## Slider settings
Each section has a **Slider settings** panel in the editor:

| Setting | Default |
|---|---|
| Autoplay | On — moves one logo at a time, loops forever |
| Pause on logo hover | On |
| Wait between moves | 3 s (1–20) |
| Slide speed | 700 ms (200–3000; higher = softer) |
| Logos visible — desktop / tablet (<900px) / mobile (<560px) | 4 / 2 / 1 |
| Show arrows / Show dots | On / On — one dot per row of visible logos (5 logos, 4 visible = 2 dots) |

The slider also pauses while it's off screen or the browser tab is hidden, and doesn't autoplay for visitors who have reduced motion turned on. If all logos fit on screen, it stays still and the logos spread evenly.

## Where each field shows
| Field | Shown as |
|---|---|
| Sub title | Spaced-out line above the heading |
| Title | Heading. Use `|` to split: `Proud Members of Leading | Industry Associations` → blue line + green line |
| Small description | Paragraph under the heading |
| Logos | Slider (see Slider settings) |
| Background image | Faded on the right side of the section |
| Additional field 1–3 | The three icon features in the blue band (empty = hidden) |

## Data
Each field is stored in its own post meta key: `_cl_title`, `_cl_subtitle`, `_cl_description`, `_cl_bg_image`, `_cl_slider` (array of settings), `_cl_additional_field1..3`, `_cl_logos`.

`_cl_logos` is a PHP array:

```php
[
  [ 'id' => 45, 'alt' => 'Organisation name', 'link' => 'https://…' ],
  …
]
```

Read everything in one call: `CL_Fields::get( $post_id );`

## Developer hooks
- `client_logos_data` (filter) — modify the data array before rendering.
- `client_logos_saved` (action) — runs after save, receives `$post_id, $data`.
- `client_logos_load_font` (filter) — return `false` to skip loading Lexend from Google Fonts.
- Template override: copy `templates/section.php` to `yourtheme/client-logos/section.php`.
