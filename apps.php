<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Apps
Description: Default module for defining this apps
Version: 1.0.1
Requires at least: 2.3.*
*/

define('APPS_MODULE_NAME', 'apps');
define('APP_ATTACHMENTS_FOLDER', 'uploads/apps/');

hooks()->add_action('app_customers_footer', 'apps_client_footer_js__component');

hooks()->add_filter('before_app_updated', '_format_data_app_feature');
hooks()->add_filter('before_app_added', '_format_data_app_feature');

hooks()->add_action('after_cron_run', 'apps_notification');
hooks()->add_action('admin_init', 'apps_module_init_menu_items');
hooks()->add_action('admin_init', 'apps_permissions');
hooks()->add_action('admin_init', 'apps_settings_tab');
hooks()->add_action('clients_init', 'apps_clients_area_menu_items');

hooks()->add_filter('get_dashboard_widgets', 'apps_add_dashboard_widget');
hooks()->add_filter('module_apps_action_links', 'module_apps_action_links');


function apps_add_dashboard_widget($widgets)
{
    /*
    $widgets[] = [
        'path'      => 'apps/widgets/app_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'apps/widgets/project_not_appd',
        'container' => 'left-8',
    ];
    */
    return $widgets;
}


function apps_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('apps', $capabilities, _l('apps'));
}


/**
* Register activation module hook
*/
register_activation_hook(APPS_MODULE_NAME, 'apps_module_activation_hook');

function apps_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(APPS_MODULE_NAME, 'apps_module_deactivation_hook');

function apps_module_deactivation_hook()
{

     log_activity( 'Hello, world! . apps_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(APPS_MODULE_NAME, [APPS_MODULE_NAME]);

/**
 * Init apps module menu items in setup in admin_init hook
 * @return null
 */
function apps_module_init_menu_items()
{
    $CI = &get_instance();

    
    $CI->app->add_quick_actions_link([
            'name'       => _l('app'),
            'url'        => 'apps',
            'permission' => 'apps',
            'icon'     => 'fa-solid fa-gear',
            'position'   => 40,
            ]);

    if (has_permission('apps', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('apps', [
                'slug'     => 'projects',
                'name'     => _l('projects'),
                'icon'     => 'fa-solid fa-gear',
                'href'     => admin_url('projects'),
                'position' => 12,
        ]);
    }


    /*
    if (has_permission('apps', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('apps', [
                'slug'     => 'apps-tracking',
                'name'     => _l('apps'),
                'icon'     => 'fa-solid fa-gear',
                'href'     => admin_url('apps'),
                'position' => 12,
        ]);
    }
    */

}

function module_apps_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=apps') . '">' . _l('settings') . '</a>';

    return $actions;
}

function apps_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    /*
    if (is_client_logged_in()) {
        add_theme_menu_item('apps', [
                    'name'     => _l('apps'),
                    'href'     => site_url('apps/list'),
                    'position' => 15,
        ]);
    }
    */

}

/**
 * [apps_client_settings_tab net menu item in setup->settings]
 * @return void
 */
function apps_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('apps', [
        'name'     => _l('settings_group_apps'),
        //'view'     => module_views_path(APPS_MODULE_NAME, 'admin/settings/includes/apps'),
        'view'     => 'apps/apps_settings',
        'icon'     => 'fa-solid fa-gear',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(APPS_MODULE_NAME . '/apps');
if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='apps') || $CI->uri->segment(1)=='apps'){
    $CI->app_css->add(APPS_MODULE_NAME.'-css', base_url('modules/'.APPS_MODULE_NAME.'/assets/css/'.APPS_MODULE_NAME.'.css'));
    $CI->app_scripts->add(APPS_MODULE_NAME.'-js', base_url('modules/'.APPS_MODULE_NAME.'/assets/js/'.APPS_MODULE_NAME.'.js'));
}



/**
 * Injects theme js components in footer
 * @return null
 */

function apps_client_footer_js__component()
{
    echo '<script src="' . module_dir_url('apps', 'assets/js/apps.js') . '"></script>';
    echo '<script src="' . module_dir_url('apps', 'assets/js/clients.js') . '"></script>';
}
