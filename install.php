<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add options for apps

add_option('app_prefix', 'OPR-');
add_option('next_app_number', 1);
add_option('default_app_assigned', 9);
add_option('app_number_decrement_on_delete', 0);
add_option('app_number_format', 4);
add_option('app_year', date('Y'));
add_option('exclude_app_from_client_area_with_draft_status', 1);

add_option('app_due_after', 1);
add_option('allow_staff_view_apps_assigned', 1);
add_option('show_assigned_on_apps', 1);
add_option('require_client_logged_in_to_view_app', 0);
add_option('app_send_telegram_message', 0);
