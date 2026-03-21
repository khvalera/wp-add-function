# WP Add Function: універсальні admin helper-механізми

Цей набір helper-ів призначений для повторного використання в різних WordPress-плагінах.
Він не повинен містити бізнес-логіку конкретного модуля.

## Що вже є

### 1) Signed token core
- `wpaf_build_signed_token( array $payload, $namespace )`
- `wpaf_parse_signed_token( $token, $namespace )`

Використання:
- route/state у URL
- signed notice token
- компактні внутрішні query-параметри

Не переносити сюди:
- allowlist screen names
- allowlist notice codes
- перевірки доступу
- бізнес-логіку save/delete/history

### 2) Admin page URL helpers
- `wpaf_admin_page_url( $page, array $args = [] )`
- `wpaf_get_signed_query_args( $param_name, array $payload, $namespace )`
- `wpaf_get_request_signed_payload( $param_name, $namespace )`
- `wpaf_admin_page_signed_url( $page, $param_name, array $payload, $namespace, array $extra = [] )`
- `wpaf_normalize_allowed_key( $value, array $allowed, $default = '' )`

Використання:
- чисті admin URL
- signed route / signed notice
- allowlist-normalization для screen/list/notice key

### 3) Form helpers
- `wpaf_prepare_hidden_fields( array $fields )`
- `wpaf_render_hidden_fields( array $fields )`
- `wpaf_render_post_form_start( array $args = [] )`
- `wpaf_render_form_end()`
- `wpaf_render_action_buttons( array $args = [] )`
  - підтримує `wrapper_style` для точкового керування layout-відступами без зміни бізнес-логіки
  - підтримує `trailing_actions` для безпечного reusable split-layout action row, коли частину кнопок треба тримати праворуч без втручання в submit-flow чи бізнес-логіку модуля
- `wpaf_get_form_action_buttons_wrapper_style( array $args = [] )`
  - повертає компактний shared inline-style для ряду кнопок у form/confirm screens
- `wpaf_get_confirm_action_buttons_args( $submit_name, $submit_label, $cancel_url = '', array $args = [] )`
  - збирає reusable `actions_args` для `wpaf_render_action_buttons()` у confirm screens з cancel-link без дублювання wrapper/cancel конфігурації в модулі
- `wpaf_get_confirm_form_args( $nonce_action, $nonce_name, array $actions_args = [], array $args = [] )`
  - збирає reusable args-масив для `wpaf_render_confirm_form()`, щоб модулі не дублювали `wrapper_class / wrapper_style / form_args / actions_args` на confirm screens
- `wpaf_get_form_action_buttons_args( $submit_name, $submit_label, $cancel_url = '', array $args = [] )`
  - збирає reusable `actions_args` для `wpaf_render_action_buttons()` у create/edit form screens без дублювання submit/cancel/split-layout конфігурації в модулі
- `wpaf_get_readonly_action_buttons_args( array $args = [] )`
  - збирає reusable `actions_args` для readonly/view action rows через `wpaf_render_action_buttons()` без дублювання split-layout wrapper-конфігурації в модулі
- `wpaf_get_action_link_groups_html( array $leading_items = [], array $trailing_items = [], array $args = [] )`
  - збирає reusable HTML для left/right action-link groups, щоб модулі менше руками складали `extra_actions` і `trailing_actions`
- `wpaf_get_button_action_groups_html( array $leading_definitions = [], array $trailing_definitions = [], array $args = [] )`
  - поєднує `wpaf_build_button_action_items()` і `wpaf_get_action_link_groups_html()`, щоб модулі могли декларативно описувати leading/trailing button groups без ручного складання проміжних масивів item-ів

Використання:
- create/edit forms
- confirm forms
- загальні кнопки submit/cancel
- shared save/delete button labels for reusable form actions

### 4) Action link helpers
- `wpaf_prepare_action_link_items( array $items )`
- `wpaf_get_action_links_html( array $items, array $args = [] )`
- `wpaf_render_action_links( array $items, array $args = [] )`
- `wpaf_get_notice_action_links_html( array $items, array $args = [] )`
  - збирає невеликий reusable action-link block для notice/error/fallback screen states з дефолтною `p`-обгорткою, щоб модулі не дублювали `wrapper_tag => 'p'`
- `wpaf_get_button_action_item( $url, $label, array $args = [] )`
  - збирає стандартний button-like action item для admin screens, щоб модулі менше руками складали масиви `url/label/class`
- `wpaf_get_primary_button_action_item( $url, $label, array $args = [] )`
  - збирає стандартний primary button-like action item для readonly/view/history action rows без дублювання `button button-primary`
- `wpaf_build_button_action_items( array $definitions )`
  - збирає набір стандартних button-like action items із компактних definitions (`condition / primary / url / label / class`), щоб модулі ще менше руками складали однотипні масиви дій
- `wpaf_get_title_action_item( $url, $label, array $args = [] )`
  - збирає стандартний title action item для page headers без дублювання масивів `url / label / class` з `page-title-action`
- `wpaf_get_primary_title_action_item( $url, $label, array $args = [] )`
  - збирає стандартний primary title action item для page headers без дублювання `page-title-action wpaf-button wpaf-button-primary`
- `wpaf_build_title_action_items( array $definitions )`
  - збирає набір title action items із компактних definitions (`condition / primary / url / label / class`), щоб модулі менше руками складали page-header actions

Використання:
- page-title actions
- header action item sets from compact definitions
- toolbar links
- button-like links у screen layout
- fallback action blocks під notice/error повідомленнями
- readonly/view/history action item sets
- split leading/trailing button groups from compact definitions

### 5) Confirm layout helper
- `wpaf_render_confirm_form( array $args = [] )`
  - підтримує `wrapper_style` для точкового відступу/обгортки confirm-блоку на рівні render-layer

Використання:
- delete confirm screen
- restore confirm screen
- інші прості POST confirm-екрани
- використовується як shared shell у `form_delete()` і `form_cancel_deletion()`

Не переносити сюди:
- тексти конкретного модуля
- submit names конкретного модуля
- permission/business checks

### 6) Admin notice helper
- `wpaf_render_admin_notice( $message, array $args = [] )`
- `wpaf_get_notice_entry_by_code( $notice_code, array $notice_map = [], array $args = [] )`
  - нормалізує reusable notice-map entry (`message / type / dismissible / class / message_tag`) за notice code, щоб модулі могли тримати декларативну карту повідомлень замість ручних `if ( $notice_code === ... )`
- `wpaf_render_admin_notice_by_code( $notice_code, array $notice_map = [], array $args = [] )`
  - рендерить shared admin notice напряму з reusable notice map, залишаючи модулю notice codes, тексти, переклади та бізнес-семантику
- `wpaf_render_admin_notice_with_actions( $message, array $items = [], array $args = [] )`
  - комбінує shared admin notice + compact action-links block під повідомленням, щоб модулі не дублювали render-пару `notice + fallback actions`

Використання:
- success / error / warning / info notices
- error/empty states з кнопками під повідомленням


### 7) Screen layout helpers
- `wpaf_render_admin_wrap_start( array $args = [] )`
- `wpaf_render_admin_wrap_end()`
- `wpaf_render_admin_intro_box( array $args = [] )`
- `wpaf_render_admin_screen_header( array $args = [] )`
- `wpaf_render_directory_confirm_header( array $args = [] )`
- `wpaf_get_title_with_actions_html( $title, array $items = [], array $args = [] )`
- `wpaf_get_return_action_item( $url, array $args = [] )`
- `wpaf_render_return_list_header( array $args = [] )`

Використання:
- стандартний outer wrapper admin screen
- іконка + description box для журналів/списків/звітів
- спільна шапка екрана: icon32 + title + intro box
- title + page actions для h2/h1 без дублювання HTML
- стандартний Return action item для title/actions block без дублювання масивів лінків
- shared list-screen header shell з Return action + intro box для history/deletion-подібних сторінок
- helper сам уміє modern render через `wpaf_render_admin_screen_header()` і legacy fallback без дублювання цього коду в `pages-forms.php`
- shared confirm-screen header shell для простих delete/restore-подібних діалогів
- helper `wpaf_render_directory_confirm_header()` тепер сам уміє modern render через `wpaf_render_admin_screen_header()` і legacy fallback через `html_title()`, без дублювання проміжного header-коду в `pages-forms.php`
- без дублювання HTML у page/form render-функціях
- використовується як базовий shell для `form_report()`, `form_journal()`, `form_directory()`, `form_directory_history()` і `form_deletion()`

Не переносити сюди:
- бізнес-логіку конкретного журналу
- query/filter/save logic
- permissions конкретного модуля

### 8) Safe page callback pattern для submenu screens
`add_admin_submenu_class_table` тепер може приймати в `$config` ключ:
- `page_callback` — ім'я функції, яку треба викликати після `require_once page.php`

Використання:
- `page.php` лише оголошує render-функції
- HTML не виводиться автоматично під час `require_once`
- фактичний рендер іде через явний callback

Це зручно для модулів, де важливо чітко розділити:
- ранню POST-обробку
- підключення файлу
- явний render-етап

Backward compatibility:
- якщо `page_callback` не передано, стара поведінка не ламається

## Базовий шаблон підключення в новому плагіні

1. У плагіні залишаються свої wrapper-и:
- `my_plugin_get_route_url()`
- `my_plugin_get_request_route()`
- `my_plugin_normalize_notice_code()`
- `my_plugin_render_notice()`

2. У wrapper-ах використовуються універсальні helper-и з `wp-add-function`.

3. Універсальний helper шар не повинен знати:
- таблиці модуля
- статуси модуля
- ревізії/історію модуля
- правила нумерації
- права доступу конкретного екрана

## Рекомендація по архітектурі

Правильний поділ відповідальності:
- `wp-add-function` → загальні helper-и, render/layout, signed state, URL builder
- модуль плагіна → screen names, routes, permission checks, queries, save logic, business rules

## Поточний safe scope для винесення

На поточному етапі безпечно універсалізовані лише:
- signed token core
- admin state/url helpers
- hidden/form/action helpers
- confirm layout helper
- admin notice helper
- action links helper
- screen layout helpers (`wrap`, `intro box`)

Усе, що знає про конкретний модуль, повинно лишатися в самому модулі.

- `form_directory()` now uses the same shared admin wrap + screen header shell, while keeping its directory-specific buttons/search/table logic in place.
- `form_directory_history()` and `form_deletion()` now also share `wpaf_render_return_list_header()` for the common Return action + intro-header shell, while preserving their existing URLs, descriptions, and table/search rendering.
- `wpaf_render_return_list_header()` now owns both the modern header render path and the legacy fallback path, so `pages-forms.php` no longer duplicates that intermediate header logic.

- `wpaf_render_list_subtitles()` — shared subtitle block for list screens: marked-for-deletion, search results, filter text.
- `wpaf_render_list_search_row()` — shared search-row shell for list screens: optional period controls + `search_box()` wrapper.

- `wpaf_get_confirm_message_html()` — build a small reusable confirm-message block for delete/apply/cancel screens without touching POST-flow.
- `wpaf_get_submit_button_html()` — build reusable submit button HTML for shared confirm/layout shells without duplicating `ob_start()` blocks in page forms.


- `wpaf_get_confirm_actions_args()` — build reusable `actions_args` arrays for shared confirm-form screens without duplicating cancel-button config in `pages-forms.php`.
- `wpaf_get_confirm_action_buttons_args()` — build reusable `actions_args` arrays for `wpaf_render_action_buttons()` on confirm screens with a shared compact button row and cancel-link config.
- `wpaf_get_confirm_form_args()` — build reusable args arrays for `wpaf_render_confirm_form()` so modules can keep only nonce values + ready action rows without duplicating confirm wrapper config.
- `wpaf_get_form_action_buttons_args()` — build reusable `actions_args` arrays for `wpaf_render_action_buttons()` on create/edit screens with shared submit/cancel/trailing-action config.
- `wpaf_get_action_link_groups_html()` — build reusable left/right action-link group HTML so modules can compose readonly/view/history action rows without duplicating `wpaf_get_action_links_html()` calls.
- `wpaf_get_notice_action_links_html()` — build a reusable notice/fallback action-link block with the default `p` wrapper for error/empty states without moving messages, URLs, or business rules into `wp-add-function`.
- `wpaf_render_admin_notice_with_actions()` — render a shared admin notice together with its compact fallback action links, so modules can keep only message text + ready action items and avoid duplicating the render pair.
- `wpaf_render_admin_notice_with_actions()` and `wpaf_render_admin_notice_with_button_actions()` now also accept short notice options directly (`type`, `dismissible`, `class`, `message_tag`) so modules do not have to pre-build `notice_args` for common cases.
- `wpaf_get_button_action_item()` / `wpaf_get_primary_button_action_item()` — build shared button-like action item definitions for admin screen links so modules can reuse standard `button` and `button button-primary` configs without duplicating tiny arrays.
- `wpaf_build_button_action_items()` — build a reusable array of button-like action items from compact definitions, so modules can describe small readonly/fallback action sets declaratively instead of appending the same arrays by hand.
- `wpaf_get_title_action_item()` / `wpaf_get_primary_title_action_item()` — build shared page-title action item definitions so modules can reuse standard `page-title-action` and primary header-action configs without duplicating tiny arrays.
- `wpaf_build_title_action_items()` — build a reusable array of page-title action items from compact definitions, so modules can describe small header action sets declaratively instead of manually appending each title action array.
- `wpaf_get_title_with_button_actions_html()` — one-step helper that turns compact title-action definitions into final page-title HTML, so simple journal/list screens do not have to call `wpaf_build_title_action_items()` and `wpaf_get_title_with_actions_html()` separately.
- `wpaf_get_admin_screen_header_args()` — build a reusable args array for `wpaf_render_admin_screen_header()` so modules can keep only title, picture URL, descriptions and small intro-box overrides without duplicating the shared header-array shape.
- `wpaf_render_admin_screen_header_with_fallback()` — render a standard admin screen header through the shared modern helper first and automatically fall back to `html_title()` or raw heading/intro markup, so modules do not duplicate the same header fallback chain.
- `wpaf_get_admin_screen_start_args()` — build a reusable args array for the standard screen shell so modules can keep only title, header options, wrap options, and whether `display_message()` should be rendered.
- `wpaf_get_admin_screen_header_args_by_key()` — resolve a module-owned `screen_key => title/description/header overrides` map into the standard shared header-args shape, so modules can keep declarative screen metadata without hand-building `title + description1 + intro_args` arrays for every screen.
- `wpaf_get_admin_screen_start_args_by_key()` — resolve the same `screen_key => metadata` map into a full shared screen-shell args array, so modules can keep only the screen key and a small base header config.
- `wpaf_render_admin_screen_start_by_key()` / `wpaf_render_admin_screen_end()` — render a shared `wrap + header + optional display_message()` shell directly from a module-owned screen map, so create/edit/view/history-like screens can drop repetitive `screen_key -> title/description -> header args -> start shell` glue code.
- `wpaf_render_admin_screen_start()` — render a shared `wrap + header + optional display_message()` shell for standard admin screens when the module already has a ready title and header args.
- `wpaf_get_admin_notice_args()` — build a reusable args array for `wpaf_render_admin_notice()` so modules can share standard `type + dismissible + class + message_tag` notice config without duplicating the same tiny arrays.
- `wpaf_get_notice_entry_by_code()` / `wpaf_render_admin_notice_by_code()` — let modules keep a small declarative `notice_code => notice config` map and render standard success/error/warning/info notices without repeating manual `if notice_code === ...` chains for journal/list screens.
- `wpaf_get_form_action_buttons_wrapper_style()` — build a shared compact inline button-row style for create/edit/confirm action blocks without touching module logic.
- `wpaf_get_delete_button_label()` — build a shared `🗑️ Delete` button label for destructive form buttons and delete action links without changing delete semantics.
- `wpaf_get_save_button_label()` — build a shared `💾 Save` button label for save/update form buttons without changing submit handling or module save logic.
- `wpaf_get_cancel_button_label()` — build a shared `⬅️ Cancel` button label for cancel buttons/links on form and confirm screens without changing navigation or POST flow.
- `wpaf_get_add_button_label()` — build a shared `➕ Add` button label for add-row/add-item buttons without changing row templates or create semantics.
- `wpaf_get_search_button_label()` — build a shared search-button label with the `🔍` icon for list screens without touching search logic.
- `wpaf_get_filter_button_label()` — build a shared filter-button label with the `⏳` icon for list screens without touching filter logic.
- `wpaf_get_reset_button_label()` — build a shared reset-button label with the `🔄` icon for list screens without touching reset logic.
- `wpaf_render_filter_reset_actions()` — render shared `Filter + Reset` toolbar actions for list/table screens via `button_action()` without moving module filter fields, state handling, or redirect logic into `wp-add-function`.
- `wpaf_render_list_export_actions()` — render shared CSV / HTML / PDF toolbar controls for list tables without moving any module export logic into `wp-add-function`.

- `wpaf_get_apply_button_label()` — build a shared generic Apply-button label with the `✅` icon for non-period submit actions without touching module logic.
- `wpaf_get_period_apply_button_label()` — build a shared period/date Apply-button label with the `🗓️` icon for list screens without touching period logic.
- `wpaf_render_period_apply_action()` — render a shared period Apply action via `button_action()` for list table toolbars without moving period validation or module rules into `wp-add-function`.
- `wpaf_render_list_toolbar_actions()` — compose shared `Filter / Reset / CSV / HTML / PDF` toolbar actions for list screens, while modules still own filter fields, search, dates, state and business rules.


## Recent helper additions

- `wpaf_get_edit_button_label()` — shared emoji label helper for edit buttons such as `📝 Edit`.
- `wpaf_get_history_button_label()` — shared emoji label helper for history buttons such as `📜 History`.
- `wpaf_get_return_button_label()` — shared emoji label helper for return/back buttons such as `📖 Return`.
- `wpaf_get_open_button_label()` — shared emoji label helper for open buttons such as `📂 Open`.
- `wpaf_get_confirm_layout_wrapper_style()` — shared compact wrapper spacing helper for confirm-layout blocks.
- `wpaf_render_action_buttons()` now can render an optional right-side trailing action group via `trailing_actions`, which is useful for layouts like `Save / Cancel` on the left and a destructive action on the right.
- `wpaf_get_readonly_action_buttons_args()` helps reuse the same split-layout action row on readonly/view screens, for example keeping document actions on the left and a return/back action on the right.
- `wpaf_get_form_action_buttons_args()` helps reuse the same action-row shell on create/edit forms, including shared submit/cancel config and optional right-side destructive actions.
- `wpaf_get_action_link_groups_html()` helps modules build leading/trailing action-link HTML pairs for readonly/view/history screens without moving any URLs, permissions, or business rules into `wp-add-function`.
- `wpaf_build_button_action_items()` helps modules describe compact readonly/view/history/fallback button sets with `condition + primary + url + label`, while module code still owns URLs, branching, permissions, and business rules.
- `wpaf_get_button_action_groups_html()` helps modules declaratively compose left/right readonly action groups from the same compact definitions, without manually building intermediate item arrays before calling the split action-link renderer.
- `wpaf_get_readonly_button_actions_args()` helps modules go one step further and build the final `wpaf_render_action_buttons()` args for readonly/view/history screens directly from left/right button definitions.
- `wpaf_get_form_button_actions_args()` helps modules build the final `wpaf_render_action_buttons()` args for create/edit forms directly from left/right button definitions, while submit names, cancel URLs and business rules still stay in the module.
- `wpaf_render_admin_notice_with_button_actions()` helps modules render `notice + fallback buttons` directly from compact button definitions, so they no longer need an intermediate `build items -> render notice` step for simple empty/error states.
- `wpaf_render_confirm_form_with_fallback()` helps modules render a confirm-form shell through the modern shared helper first and automatically fall back to simpler shared/raw POST form rendering, so delete/cancel screens can avoid duplicating the same fallback tree.
- `wpaf_build_title_action_items()` helps modules declaratively compose page-header action sets such as `Create / Active / Marked for deletion`, while module code still owns URLs, labels, conditions, and navigation semantics.
- `wpaf_render_admin_screen_header_with_fallback()` helps modules keep only screen title, descriptions, and intro-box overrides while the shared layer owns the `modern header -> legacy html_title -> raw markup` fallback path.
- `wpaf_get_admin_screen_start_args()` helps modules keep the standard screen-shell array shape in one place: title, header args, wrap args, and the optional `display_message()` toggle.
- `wpaf_render_admin_screen_start()` / `wpaf_render_admin_screen_end()` help modules reuse the whole standard screen shell — wrapper, header, and optional `display_message()` call — instead of repeating that bootstrap on every admin page.


## Button label helpers

Shared presentation helpers now cover compact reusable labels for common admin/form actions, including:
- `wpaf_get_add_button_label()` → `➕`
- `wpaf_get_save_button_label()` → `💾`
- `wpaf_get_edit_button_label()` → `📝`
- `wpaf_get_history_button_label()` → `📜`
- `wpaf_get_return_button_label()` → `📖`
- `wpaf_get_open_button_label()` → `📂`
- `wpaf_get_delete_button_label()` → `🗑️`
- `wpaf_get_remove_button_label()` → `❌` for row/item remove buttons inside forms
- `wpaf_get_cancel_button_label()` → `⬅️`

These helpers stay render-only and must not contain module business logic.


### Shorter one-step action-row helpers

For simpler admin modules, `wp-add-function` now also provides direct render helpers so screens do not need to manually chain `definitions -> args -> wpaf_render_action_buttons()`:

- `wpaf_render_form_button_actions( $submit_name, $submit_label, $cancel_url, $leading_definitions, $trailing_definitions, $args )`
- `wpaf_render_readonly_button_actions( $leading_definitions, $trailing_definitions, $args )`
- `wpaf_render_screen_button_actions( $args )`
- `wpaf_render_title_with_button_actions( $title, $definitions, $args )`
- `wpaf_render_confirm_button_form( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $args )`

`wpaf_render_screen_button_actions()` is the shortest shared path for a standard screen action row: the module passes `mode => form|readonly` plus submit/cancel data and optional left/right button definitions, and the framework chooses the proper shared renderer or fallback automatically.

`wpaf_render_confirm_button_form()` is the short one-step path for simple delete/restore/apply/cancel confirm screens: the module provides nonce values, submit/cancel data, and optional wrapper/style overrides, while the shared layer composes confirm action buttons, confirm form args, and fallback rendering automatically.

- `wpaf_get_standard_screen_notice( $message, $type, $args )`
- `wpaf_get_standard_screen_notice_with_buttons( $message, $buttons, $type, $args )`
- `wpaf_get_standard_screen_error_notice( $message, $buttons, $args )`
- `wpaf_get_standard_screen_warning_notice( $message, $buttons, $args )`
- `wpaf_get_standard_screen_code_notice( $notice_code, $notice_map, $args )`
- `wpaf_get_standard_screen_content( $callback, $content_args, $args )`
- `wpaf_get_standard_screen_confirm( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $args )`
- `wpaf_get_standard_screen_callback_content( $callback, $content_args = [], $args = [] )`
  - короткий shortcut для найчастішого `content`-кейсу через callback + args без зайвого ручного DSL
- `wpaf_get_standard_screen_html_content( $html, $args = [] )`
  - короткий shortcut для `content`-кейсу з готовим HTML
- `wpaf_get_standard_screen_confirm_buttons( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $confirm_args = [], $args = [] )`
- `wpaf_render_standard_notice_screen_by_key( $screen_key, $screen_definitions, $notice, $args = [] )`
  - one-step renderer для `standard screen by key + notice`, щоб прості модулі не збирали руками `notice => [...]`
- `wpaf_render_standard_content_screen_by_key( $screen_key, $screen_definitions, $callback, $content_args = [], $args = [] )`
  - one-step renderer для `standard screen by key + callback content`, щоб коротко рендерити form/view/history screens
- `wpaf_render_standard_confirm_screen_by_key( $screen_key, $screen_definitions, $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $confirm_args = [], $args = [] )`
  - one-step renderer для `standard screen by key + confirm block`, щоб confirm screens не збирали вручну runtime DSL
  - короткий shortcut для confirm-block без зайвого `array( 'args' => ... )` у модульному коді
- `wpaf_normalize_standard_screen_runtime_args( $args )`
- `wpaf_render_standard_screen( $args )`
- `wpaf_get_standard_screen_args_by_key( $screen_key, $screen_definitions, $args )`
- `wpaf_render_standard_screen_by_key( $screen_key, $screen_definitions, $args )`
- `wpaf_render_report_screen_with_button_actions( $args )`
- `wpaf_render_document_screen( $screen_key, $screen_map, $args )`
- `wpaf_render_form_document_screen( $screen_key, $screen_map, $args )`
- `wpaf_render_readonly_document_screen( $screen_key, $screen_map, $args )`
- `wpaf_render_confirm_document_screen( $screen_key, $screen_map, $args )`

`wpaf_get_standard_screen_notice()`, `wpaf_get_standard_screen_notice_with_buttons()`, `wpaf_get_standard_screen_error_notice()`, `wpaf_get_standard_screen_warning_notice()`, `wpaf_get_standard_screen_code_notice()`, `wpaf_get_standard_screen_content()`, and `wpaf_get_standard_screen_confirm()` make that runtime DSL shorter to write in module code: instead of hand-building nested `notice/content/confirm` arrays every time, a simple plugin can compose those blocks with small shared builders and pass them directly into `wpaf_render_standard_screen()` or `...by_key()`.

`wpaf_normalize_standard_screen_runtime_args()` keeps the upper API compact: a module may pass small nested blocks like `title_actions`, `notice`, `content`, or `confirm`, and the shared layer expands them into the flat args shape expected by lower-level helpers.

Supported compact runtime aliases:
- `title_actions` → `title_definitions`
- `notice => [ code, map, args ]` for notice-by-code flow on report/list screens
- `notice => [ message, type, dismissible, class, message_tag, buttons, action_links_args ]` for document/report notice rendering
- helper shortcuts like `wpaf_get_standard_screen_code_notice()`, `wpaf_get_standard_screen_error_notice()`, and `wpaf_get_standard_screen_warning_notice()` for the most common notice cases
- `content => [ callback, args, html, before_html, after_html ]`
- `confirm => [ nonce_action, nonce_name, submit_name, submit_label, cancel_url, args ]`

`wpaf_render_standard_screen()` is the shortest generic top-level entry point for simple admin modules: the caller passes `type => report|form|readonly|confirm|document` plus compact runtime blocks, and the framework dispatches to the proper shared screen renderer automatically.

`wpaf_get_standard_screen_args_by_key()` and `wpaf_render_standard_screen_by_key()` make the top-level API even simpler for reusable modules: the module keeps a small declarative `screen_key => standard-screen args` map and then passes only the runtime overrides for the current request. This removes repeated `type + screen_key + screen_map + header_args` glue from module page files without moving any business logic into the framework.

For even shorter module code, `wpaf_get_standard_screen_definition()` and `wpaf_build_standard_screen_definitions()` let a plugin keep the map itself in a compact form. A screen can be described as a short scalar like `'edit'`, `'view'`, or `'confirm'`, while the shared layer expands common defaults such as `screen_map`, `header_args`, and optional nested `args`. This makes a typical `page.php` read more like a declarative screen config and less like glue code.

`wpaf_render_report_screen_with_button_actions()` is the short one-step path for simple journal/list/report screens: the module passes `plural_name`, raw title text, compact title-action definitions, descriptions, and either `notice_code => notice map` or direct `notice_message`/button args, while the shared layer renders the notice and calls the standard `form_report()` shell.

`wpaf_render_document_screen()` is now the preferred base API for simple document-like admin screens: the module passes a `screen_key => header map`, optional notice text plus optional button-like notice actions, and either `content_html` or `content_callback`, while the shared layer renders `screen start -> optional notice -> content -> screen end` in one call.

`wpaf_render_form_document_screen()` and `wpaf_render_readonly_document_screen()` are thin semantic aliases over the same shared helper for create/edit-like and view/history-like screens.

`wpaf_render_confirm_document_screen()` is the short one-step path for simple confirm screens that still need the normal document shell: the module passes `screen_key`, the screen map, optional notice text or notice buttons, plus nonce/submit/cancel data, while the shared layer renders `screen start -> optional notice -> confirm form -> screen end` in one call.

These helpers stay presentation-only. Modules still own URLs, labels, permissions, routing, submit names, nonces, notice codes, descriptions, and all business semantics.


### 9) Email transport helper
- `wpaf_send_html_email( array $args = [] )`
  - універсальний transport-only helper для HTML email через `wp_mail()`
  - підтримує `to / subject / message / headers / attachments`
  - модуль сам вирішує, кому, коли і з яким шаблоном слати листи

Використання:
- технічна відправка email
- custom headers на кшталт `Message-ID`, `In-Reply-To`, `References`
- без перенесення подій, шаблонів або логіки отримувачів у `wp-add-function`

Не переносити сюди:
- правила, коли слати лист
- шаблони конкретного модуля
- бізнес-події create/update/delete конкретного документа


## Transport / email helpers

- `wpaf_send_html_email( array $args = [] )` is a transport-only helper.
- It supports generic threading args: `message_id`, `in_reply_to`, `references`.
- These are applied through `phpmailer_init`, not as raw `Message-ID` headers, to avoid duplicate `Message-ID` values.
- Module-specific rules about when to send and how to group emails stay in the consumer plugin.


### 9) Notification transport helpers
- `wpaf_send_html_email( array $args = [] )`
  - універсальний transport-level helper для HTML email через `wp_mail()`
  - підтримує `to / subject / message / headers / attachments` і threading metadata (`message_id / in_reply_to / references`)
- `wpaf_send_matrix_message( array $args = [] )`
  - універсальний transport-level helper для надсилання `m.room.message` у Matrix через Client-Server API
  - підтримує `homeserver / room_id / access_token / body / formatted_body / msgtype / event_type / txn_id / timeout`

Важливо:
- обидва helper-и не повинні знати нічого про замовлення, ревізії, номери документів чи правила подій
- модуль сам вирішує, коли слати повідомлення, кому слати і як будувати текст/HTML


## Matrix transport helper

`wpaf_send_matrix_message()` accepts an optional `thread_root_event_id` argument. When it is set, the helper sends the message with an `m.relates_to` payload using `rel_type = m.thread` so the message becomes part of the Matrix thread rooted at that event.


