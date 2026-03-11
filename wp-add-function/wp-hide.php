<?php

/*
* Функції для приховування функціональності WordPress
*/

//===========================================
// Ховаємо оновлення ядра для всіх
//===========================================
add_filter('pre_site_transient_update_core', function($value) {
    return null; // Взагалі прибираємо інформацію про оновлення
});


//===========================================
// Прибрати пункт меню "Оновлення" в адмінці WordPress
//===========================================
add_action('admin_menu', function() {
    remove_submenu_page('index.php', 'update-core.php');
}, 999);

//===========================================
// Приховати меню "Оновлення" для не-адмінів
//===========================================
add_action('admin_menu', function() {
    if (!current_user_can('update_core')) {
        remove_submenu_page('index.php', 'update-core.php');
    }
}, 999);

//===========================================
// Ховаємо віджет "Новини та заходи WordPress" на головній адмінпанелі
//===========================================
add_action('wp_dashboard_setup', function() {
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
}, 999);

//===========================================
// Прибираємо футер "Дякуємо за творчість з WordPress"
//===========================================
add_filter('admin_footer_text', function($footer) {
    return ''; // повністю забрати
});

//===========================================
// Прибираємо номер версії у футері
//===========================================
add_filter('update_footer', function($text) {
    return ''; // або, наприклад, "© Card Cabinet"
}, 999);

//===========================================
// Ховаємо логотип WordPress у верхньому адмін-барі для всіх
//===========================================
add_action('admin_bar_menu', function($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}, 999);

//===========================================
// Прибираємо блок "Дизайн з WordPress"
//===========================================
add_action('wp_footer', function() {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const blocks = document.querySelectorAll(".wp-block-group.alignwide.is-layout-flow.wp-block-group-is-layout-flow");
            blocks.forEach(block => {
                if(block.textContent.includes("Дизайн з WordPress")) {
                    block.remove();
                }
            });
        });
    </script>';
});

//===========================================
// Приховує пункт меню "Майстерня" для всіх, крім адміністраторів.
//===========================================
add_action('admin_menu', function() {
    if (!current_user_can('manage_options')) {
        remove_menu_page('index.php'); // Прибирає "Майстерню"
    }
}, 999);

//===========================================
// Блокуємо доступ до "Майстерні" напряму
//===========================================
add_action('admin_init', function() {
    if (!current_user_can('manage_options') && is_admin()) {
        global $pagenow;
        if ($pagenow === 'index.php') {
            wp_redirect(admin_url('profile.php')); // перенаправлення на "Профіль"
            exit;
        }
    }
});

//===========================================
// Функція видаляє з admin_title "---Wordpress"
//===========================================
function wp_admin_title(string $admin_title, string $title): string {
    return get_bloginfo('name') . ' &bull; ' . $title;
}
add_filter('admin_title', 'wp_admin_title', 10, 2);

//===========================================
// Видалення метабоксів з консолі сайту
//===========================================
function clear_wp_dash(): void {
    $dash_side   = &$GLOBALS['wp_meta_boxes']['dashboard']['side']['core'];
    $dash_normal = &$GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'];

    foreach (['dashboard_quick_press', 'dashboard_recent_drafts', 'dashboard_primary', 'dashboard_secondary'] as $widget) {
        unset($dash_side[$widget]);
    }

    foreach (['dashboard_incoming_links', 'dashboard_right_now', 'dashboard_recent_comments', 'dashboard_plugins', 'dashboard_activity'] as $widget) {
        unset($dash_normal[$widget]);
    }

    remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action('wp_dashboard_setup', 'clear_wp_dash');

//=============================================
// Скрыть уведомление об обновлении WordPress с панели администрирования для обычных пользователей.
add_action( 'admin_init', function () {
    //if ( !current_user_can('update_core') ) {
    remove_action( 'admin_notices',         'update_nag', 3 );
    remove_action( 'network_admin_notices', 'update_nag', 3 );
    //}
});

//=============================================
// Отключаем сообщение «JQMIGRATE: Migrate is installed, version 1.4.1»
add_action('wp_default_scripts', function ($scripts) {
    if (!empty($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
    }
});

//===================================================
// Видалення пунктів меню з верхньої панелі
//===================================================
// comments    - меню "коментарі"
// my-account  - меню "мій профіль"
// site-editor - меню "редагувати запис"
// new-content - меню "додати"
// updates     - меню "оновлення"
// wp-logo     - меню "про wordpress"
// site-name   - меню "сайт"
function wp_new_toolbar(): void {
    global $wp_admin_bar;

    foreach (['comments', 'site-editor', 'new-content', 'wp-logo'] as $menu_id) {
        $wp_admin_bar->remove_menu($menu_id);
    }
}
add_action('admin_bar_menu', 'wp_new_toolbar', 999);

//=============================================
// Прибрати непотрібні поля у профілі користувача
//=============================================
function clean_user_profile_fields() {

    // Щоб не блимало при оновленні сторінки потрібно  приховати за допомогою CSS,
    // а потим вже видалити за допомогою JS
    echo '<style>
        tr.user-url-wrap,
        tr.user-display-name-wrap,
        tr.user-description-wrap,
        tr.user-sessions-wrap,
        tr.user-syntax-highlighting-wrap,
        tr.user-comment-shortcuts-wrap,
        tr.user-first-name-wrap,
        tr.user-last-name-wrap,
        #application-passwords-section,
        tr.user-profile-picture,
        .contextual-help-sidebar
    { display: none !important; }
    </style>';

   // all subheadings
   echo '<style>#your-profile h2{ display: none; }</style>';

    ?>
    <script>
    jQuery(document).ready(function($) {
        // Прибираємо "Сайт"
        $('tr.user-url-wrap').remove();
        // Прибираємо "Нік"
        //$('tr.user-nickname-wrap').remove();
        // Прибираємо "Відображати як"
        $('tr.user-display-name-wrap').remove();
        // Прибираємо "Опис"
        $('tr.user-description-wrap').remove();
        // Прибираємо "Сеанси"
        $('tr.user-sessions-wrap').remove();
        // Прибираємо "Підсвітка синтаксису"
        $('tr.user-syntax-highlighting-wrap').remove();
        // Прибираємо "Мова інтерфейсу"
        //$('tr.user-locale-wrap').remove();

        // Прибираємо чекбокс "Увімкнути клавіатурні скорочення для модерації коментарів"
        $('tr.user-comment-shortcuts-wrap').remove();

        // Прибрати Ім'я та Прізвище
         $('tr.user-first-name-wrap').remove();
         $('tr.user-last-name-wrap').remove();

        // Прибрати Email
        // $('tr.user-email-wrap').remove();

        // Прибрати поля пароля
        // $('tr.user-pass1-wrap').remove();
        // $('tr.user-pass2-wrap').remove();

        // Паролі додатків
        $('#application-passwords-section').remove();

        // Прибираємо рядок з аватаркою профілю (Gravatar)
        $('tr.user-profile-picture').remove();

        // Зміна блоку Help Tab у профілі користувача (tab-panel-overview)
        $('#tab-panel-overview').html('<p>Here you can set up your personal information, change your password, choose your display name, and manage other account settings.</p><p>After making your changes, be sure to click the "Update Profile" button to save them.</p>');
        // Прибираємо контекстний help sidebar
        $('.contextual-help-sidebar').remove();

        // Прибираємо всі підзаголовки h2
        $('#your-profile h2').remove();
    });
    </script>
    <?php
}
add_action('show_user_profile', 'clean_user_profile_fields');
add_action('edit_user_profile', 'clean_user_profile_fields');
add_action('personal_options', 'clean_user_profile_fields');

