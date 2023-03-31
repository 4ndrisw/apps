<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Apps Dashboard
Description: Apps Dashboard for Wasnaker.ID
Version: 1.0.1
Requires at least: 2.3.*
*/

define('APPS_MODULE_NAME', 'apps');
define('APPS_ASSETS_PATH', 'modules/apps/assets');
define('APP_ATTACHMENTS_FOLDER', 'uploads/apps/');

$CI = &get_instance();

hooks()->add_action('admin_init', 'apps_module_menu_admin_items');
hooks()->add_action('admin_init', 'apps_permissions');
hooks()->add_action('admin_init', 'apps_settings_tab');

hooks()->add_action('app_customers_footer', 'apps_client_footer_js__component');

function apps_module_menu_admin_items()
{
  $CI = &get_instance();

  if (has_permission('apps', '', 'my_dashboard_view') || has_permission('apps', '', 'all_dashboard_view') || has_permission('apps', '', 'widget_view') || has_permission('apps', '', 'dashboard_settings')) {
    $CI->app_menu->add_sidebar_menu_item('perfex-dashboard-module-menu-master', [
        'name'     => _l('apps'),
        'href'     => 'javascript:void(0);',
        'position' => 2,
        'icon'     => 'fa fa-home menu-icon',
    ]);
  }

  if (has_permission('apps', '', 'my_dashboard_view')) {
    $CI->app_menu->add_sidebar_children_item('perfex-dashboard-module-menu-master', [
      'name'     => _l('my_dashboard'),
      'href'     => admin_url('apps/dashboards/my_dashboard'),
      'position' => 1,
      'slug'     => 'dashboards',
    ]);
  }
  if (has_permission('apps', '', 'all_dashboard_view')) {
    $CI->app_menu->add_sidebar_children_item('perfex-dashboard-module-menu-master', [
      'name'     => _l('all_dashboards'),
      'href'     => admin_url('apps/dashboards'),
      'position' => 2,
      'slug'     => 'dashboards',
    ]);
  }
  if (has_permission('apps', '', 'widget_view')) {
    $CI->app_menu->add_sidebar_children_item('perfex-dashboard-module-menu-master', [
      'name'     => _l('all_widgets'),
      'href'     => admin_url('apps/widgets'),
      'position' => 3,
      'slug'     => 'widgets',
    ]);
  }
  if (has_permission('apps', '', 'widget_category_view')) {
    $CI->app_menu->add_sidebar_children_item('perfex-dashboard-module-menu-master', [
      'name'     => _l('widget_categories'),
      'href'     => admin_url('apps/categories'),
      'position' => 4,
      'slug'     => 'categories',
    ]);
  }
  if (has_permission('apps', '', 'dashboard_settings')) {
    $CI->app_menu->add_sidebar_children_item('perfex-dashboard-module-menu-master', [
      'name'     => _l('settings'),
      'href'     => admin_url('apps/settings'),
      'position' => 4,
      'slug'     => 'settings',
    ]);
  }
}

function apps_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'my_dashboard_view'   => _l('my_dashboard_view'),
            'all_dashboard_view'   => _l('all_dashboard_view'),
            'dashboard_create' => _l('dashboard_create'),
            'dashboard_edit'   => _l('dashboard_edit'),
            'dashboard_delete' => _l('dashboard_delete'),
            'dashboard_clone' => _l('dashboard_clone'),
            'widget_view'   => _l('widget_view'),
            'widget_create' => _l('widget_create'),
            'widget_edit'   => _l('widget_edit'),
            'widget_delete' => _l('widget_delete'),
            'widget_category_view'   => _l('widget_category_view'),
            'widget_category_create' => _l('widget_category_create'),
            'widget_category_edit'   => _l('widget_category_edit'),
            'widget_category_delete' => _l('widget_category_delete'),
            'dashboard_settings' => _l('dashboard_settings'),
    ];

    register_staff_capabilities('apps', $capabilities, _l('apps'));
}

$CI->load->helper(APPS_MODULE_NAME . '/apps');

/**
 * Register activation module hook
 */
register_activation_hook(APPS_MODULE_NAME, 'apps_module_activation_hook');

function apps_module_activation_hook()
{
  $CI = &get_instance();
  require_once(__DIR__ . '/install.php');
}


function module_apps_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=apps') . '">' . _l('settings') . '</a>';

    return $actions;
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(APPS_MODULE_NAME, [APPS_MODULE_NAME]);



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

$CI->app_css->add(APPS_MODULE_NAME.'-css', base_url('modules/'.APPS_MODULE_NAME.'/assets/css/'.APPS_MODULE_NAME.'.css'));
$CI->app_scripts->add(APPS_MODULE_NAME.'-js', base_url('modules/'.APPS_MODULE_NAME.'/assets/js/'.APPS_MODULE_NAME.'.js'));



/**
 * Injects theme js components in footer
 * @return null
 */

function apps_client_footer_js__component()
{
    echo '<script src="' . module_dir_url('apps', 'assets/js/apps.js') . '"></script>';
    //echo '<script src="' . module_dir_url('apps', 'assets/js/clients.js') . '"></script>';
}

//add css and js to clientside
hooks()->add_action('app_customers_head', 'include_app_customers_head');
//hooks()->add_action('app_customers_footer', 'include_app_customers_footer');


/**
 * Theme clients footer includes
 * @return stylesheet
 */
function include_app_customers_head()
{
    echo '<link href="' . module_dir_url('apps', 'assets/css/apps.css') . '"  rel="stylesheet" type="text/css" >';
}