<?php

defined('BASEPATH') or exit('No direct script access allowed');

function apps_get_token(){
    $_SESSION['token_key'] = bin2hex(random_bytes(32));
    $_SESSION['token_value'] = bin2hex(random_bytes(64));
    $_SESSION['csrf_token_key'] = hash_hmac('sha256', $_SESSION['token_key'], $_SESSION['token_value']);
    return $_SESSION['csrf_token_key'];
}

function apps_render_dashboard_widgets($container)
{
    $widgetsHtml = [];

    static $widgets     = null;
    static $widgetsData = null;

    include_once(APPPATH . 'third_party/simple_html_dom.php');

    $CI = &get_instance();

    if (!$widgets) {
        $widgetsData       = [];
        $widgets           = get_dashboard_widgets();

        foreach ($widgets as $key => $widget) {
            $html = str_get_html($CI->load->view($widget['path'], [], true));
            if ($html) {
                $widgetContainer = $html->firstChild();
                if ($widgetContainer) {
                    $htmlID = $widgetContainer->getAttribute('id');

                    $widgetsData[$htmlID] = [
                        'widgetIndex'     => $key,
                        'widgetPath'      => $widget['path'],
                        'widgetContainer' => $widget['container'],
                        'html'            => $widgetContainer,
                    ];

                    $widget['widgetID']         = $htmlID;
                    $widget['html']             = $widgetContainer;
                    $widgets[$key]['settingID'] = strafter($htmlID, 'widget-');
                    $widgets[$key]['html']      = $widgetContainer;
                } else {
                    // Not compatible widget
                    unset($widgets[$key]);
                }
            } else {
                // Not compatible widget
                unset($widgets[$key]);
            }
        }
    }
    foreach ($widgets as $widget) {
        if ($widget['container'] == $container) {
            $widgetsHtml[$widget['settingID']] = $widget['html'];
        }
    }
    foreach ($widgetsHtml as $widgetID => $widgetHTML) {
        echo $widgetHTML;
    }
}

function apps_render_widgets($widgets)
{
    $CI = &get_instance();

    foreach ($widgets as $widget) {
        echo '<div class="col-md-12">';
        echo $CI->load->view('apps/partials/widget_info', ['widget' => $widget], true);
        echo $CI->load->view('apps/widgets/' . $widget['widget_name'], [], true);
        echo '</div>';
    }
}

function apps_render_widgets_from_dashboard($dashboard, $container)
{
    $CI = &get_instance();
    $CI->load->model('apps_model');

    $ids = [];
    if(isset($dashboard['dashboard_widgets'][$container])) {
        $ids = $dashboard['dashboard_widgets'][$container];
    }

    $widgets = $CI->apps_model->select_widgets_by_ids($ids);

    foreach ($widgets as $widget) {
        echo $CI->load->view('apps/widgets/' . $widget['widget_name'], [
            'widget' => $widget,
        ], true);
    }
}

function apps_get_available_widgets($dashboard)
{
    $CI = &get_instance();
    $CI->load->model('apps_model');

    $containers = ['top-12', 'top-left-first-4', 'top-left-last-4', 'top-right-first-4', 'top-right-last-4', 'middle-left-6', 'middle-right-6', 'left-8', 'right-4', 'bottom-left-4', 'bottom-middle-4', 'bottom-right-4'];
    
    $present_widgets = [];
    foreach($containers as $container) {
        if(isset($dashboard['dashboard_widgets'][$container])) {
            $present_widgets = array_merge($present_widgets, $dashboard['dashboard_widgets'][$container]);
        }
    }
    $present_widgets= array_unique($present_widgets);

    $widgets = $CI->apps_model->select_widgets_except_ids($present_widgets);

    return $widgets;
}

function apps_get_categories() 
{
    $CI = &get_instance();
    $CI->load->model('apps_model');

    $categories = $CI->apps_model->get_categories();

    return $categories;
}

function apps_scan_widgets_2()
{
    $widget_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../views/widgets';
    $widgets = directory_map($widget_path, 1);

    // only files that start with prefix widget-
    $widgets = array_filter($widgets, function ($v) {
      if(strlen($v) > 7) {
        return substr($v, 0, 7) === 'widget-';
      }
      return false;
    });

    // generate data
    $widgets_data = [];
    if ($widgets) {

        foreach ($widgets as $widget_name) {
            $widget_name = strtolower(trim($widget_name));
            
            foreach (['\\', '/'] as $trim) {
                $widget_name = rtrim($widget_name, $trim);
            }

            $name = substr($widget_name, 7);
            $name = substr($name, 0, stripos($name, '.') - 0);

            $path = $widget_path . DIRECTORY_SEPARATOR . $widget_name;

            $header = apps_get_headers_widget($path);

            array_push($widgets_data, [
                'name' =>  $name,
                'file_name' => $widget_name,
                'path' => $path,
                'header' => $header,
            ]);
        }
    }

    return $widgets_data;
}

function apps_scan_widgets()
{
    $CI = &get_instance();
    $CI->load->model('apps_model');

    $widget_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../views/widgets';
    $widgets = directory_map($widget_path, 1);

    $widgets = array_filter($widgets, function ($v) {
      if(strlen($v) > 7) {
        return substr($v, 0, 7) === 'widget-';
      }
      return false;
    });

    $widgets_data = [];

    if ($widgets) {
        
        $db_widgets = $CI->apps_model->select_widgets_except_names();
        $db_widgets = array_map(function($widget) {
            return substr($widget['widget_name'], 7);
        }, $db_widgets);

        foreach ($widgets as $widget_name) {
            $widget_name = strtolower(trim($widget_name));
            
            foreach (['\\', '/'] as $trim) {
                $widget_name = rtrim($widget_name, $trim);
            }

            $name = substr($widget_name, 7);
            $name = substr($name, 0, stripos($name, '.') - 0);

            $path = $widget_path . DIRECTORY_SEPARATOR . $widget_name;

            $header = apps_get_headers_widget($path);

            array_push($widgets_data, [
            'name' =>  $name,
            'file_name' => $widget_name,
            'path' => $path,
            'header' => $header,
            'active' => in_array($name, $db_widgets),
            ]);
        }
    }

    return $widgets_data;
}

function apps_scan_widgets_with_activation($active)
{
    $data = apps_scan_widgets();
    return array_filter($data, function ($v) use ($active) {
        return $v['active'] == $active;
    });
}

function apps_get_headers_widget($widget_path)
{
    $widget_data = read_file($widget_path);

    preg_match('|Widget Name:(.*)$|mi', $widget_data, $name);
    preg_match('|Description:(.*)$|mi', $widget_data, $description);
    preg_match('|Category:(.*)$|mi', $widget_data, $category);

    $arr = [];

    if (isset($name[1])) {
        $arr['name'] = trim($name[1]);
    }

    if (isset($description[1])) {
        $arr['description'] = trim($description[1]);
    }

    if (isset($category[1])) {
        $arr['category'] = trim($category[1]);
    }

    return $arr;
}

/*
function get_widgest_folder_path(){
    
    return APP_MODULES_PATH.APPS_MODULE_NAME.'/views/widgets';
    
}
*/


function getYear($pdate) {
    $date = DateTime::createFromFormat("Y-m-d", $pdate);
    return $date->format("Y");
}

function getMonth($pdate) {
    $bulan = array(
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember',
        );
    $date = DateTime::createFromFormat("Y-m-d", $pdate);
    return $bulan[$date->format("m")];
}

function getDay($pdate) {
    $date = DateTime::createFromFormat("Y-m-d", $pdate);
    return $date->format("d");
}

function getDayName($pdate) {
$hari = array ( 1 =>    'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
            );
    $date = DateTime::createFromFormat("Y-m-d", $pdate);
    return $hari[$date->format("N")];
}

function html_date($date){
    $date_raw = isset($date) ? _d($date) : '1970-01-01';
    $tahun = getYear($date_raw);
    $bulan = getMonth($date_raw);
    $tanggal = getDay($date_raw);
    return $tanggal.' '.$bulan.' '.$tahun;
}



/**
 * Get items table for preview
 * @param  object  $transaction   e.q. invoice, estimate from database result row
 * @param  string  $type          type, e.q. invoice, estimate, proposal
 * @param  string  $for           where the items will be shown, html or pdf
 * @param  boolean $admin_preview is the preview for admin area
 * @return object
 */
function get_preview_table_data($transaction, $type, $for = 'html', $admin_preview = false)
{
    include_once(module_libs_path('apps') .'Apps_items_table.php');
    $class = new Apps_items_table($transaction, $type, $for, $admin_preview);

    $class = hooks()->apply_filters('items_table_class', $class, $transaction, $type, $for, $admin_preview);

    if (!$class instanceof Apps_items_table_template) {
        show_error(get_class($class) . ' must be instance of "Apps_items_template"');
    }

    return $class;
}


function get_client_type($staff_id){
    $CI = &get_instance();
        $CI->db->select(['client_id','client_type']);
        $CI->db->where('staffid', $staff_id);
    return $CI->db->get(db_prefix() . 'staff')->row();
}


/**
 * Function used to get related data based on rel_id and rel_type
 * Eq in the tasks section there is field where this task is related eq invoice with number INV-0005
 * @param  string $type
 * @param  string $rel_id
 * @param  array $extra
 * @return mixed
 */
function apps_get_relation_data($type, $rel_id = '', $extra = [])
{
    $CI = & get_instance();
    $q  = '';
    if ($CI->input->post('q')) {
        $q = $CI->input->post('q');
        $q = trim($q);
    }
    $input = $CI->input->post();
    log_activity(json_encode($input));
    $data = [];
    switch ($type) {
        case 'institution':
        case 'institutions':
            $where_clients = ''; 
            if ($q) {
                //$where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_institution = 1';
                $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_institution = 1';
            }
            include_once(APP_MODULES_PATH. 'institutions/models/Institutions_model.php');

            $CI->load->model('institutions_model');
            $data = $CI->institutions_model->get_select_option($rel_id, $where_clients);
            // code...
            break;
        case 'company':
        case 'companies':
            $where_clients = ''; 
            if ($q) {
                $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'clients.is_company = 1';
            }
            include_once(APP_MODULES_PATH. 'companies/models/Companies_model.php');
            $CI->load->model('companies_model');
            $data = $CI->companies_model->get($rel_id, $where_clients);
            // code...
            break;
        case 'inspector':
        case 'inspectors':
            $institution_id = $CI->input->post('institution_id');
            $where_clients = ''; 
            if ($q) {
                if(is_numeric($institution_id)){
                    $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_inspector = 1 AND ' . db_prefix() . 'clients.institution_id = ' .$institution_id;
                }
                else{
                    $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_inspector = 1';
                }
            }
            include_once(APP_MODULES_PATH. 'inspectors/models/Inspectors_model.php');
            $CI->load->model('inspectors_model');
            $data = $CI->inspectors_model->get_select_option($rel_id, $where_clients);
            // code...
            break;
        case 'inspector_staff':
        case 'inspector_staffs':
            $inspector_id = $CI->input->post('inspector_id');
            log_activity('inspector_id ' . $inspector_id);
            log_activity(json_encode($extra));
            log_activity(current_url());
            $where_clients = ''; 
            if ($q) {
                if(is_numeric($inspector_id)){
                    $where_clients .= '(firstname LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'staff.active = 1  AND ' . db_prefix() . 'staff.is_not_staff = 1 AND ' . db_prefix() . 'staff.client_id = ' .$inspector_id;
                }
                else{
                    $where_clients .= '(firstname LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'staff.active = 1  AND ' . db_prefix() . 'staff.is_not_staff = 1';
                }
            }
            include_once(APP_MODULES_PATH. 'inspectors/models/Inspectors_model.php');
            $CI->load->model('inspectors_model');
            $data = $CI->inspectors_model->get_inspector_staff_select_option($rel_id, $where_clients);
            // code...
            break;
        case 'surveyor':
        case 'surveyors':
            log_activity('11111111');
            $where_clients = ''; 
            if ($q) {
                $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'clients.is_surveyor = 1';
            }
            include_once(APP_MODULES_PATH. 'surveyors/models/Surveyors_model.php');
            $CI->load->model('surveyors_model');
            $data = $CI->surveyors_model->get($rel_id, $where_clients);
            // code...
            break;
        
        default:
            // code...
            break;
    }


    /*

    if ($type == 'company' || $type == 'companies') {
        $where_clients = ''; 
        if ($q) {
            $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'clients.is_company = 1';
        }
        include_once(APP_MODULES_PATH. 'companies/models/companies_model.php');
        $CI->load->model('companies_model');
        $data = $CI->companies_model->get($rel_id, $where_clients);
    } elseif ($type == 'institution' || $type == 'institutions') {
    log_activity('$type = 2 ' .$type);
        $where_clients = ''; 
        if ($q) {
            //$where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'clients.is_company = 1';
            $where_clients .= '(companys LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_institution = 1';
        }
        include_once(APP_MODULES_PATH. 'institutions/models/institutions_model.php');
        $CI->load->model('institutions_model');
        $data = $CI->institutions_model->get($rel_id, $where_clients);
    } elseif ($type == 'inspector' || $type == 'inspectors') {
    log_activity('$type =3 ' .$type);
        $where_clients = ''; 
        if ($q) {
            //$where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'clients.is_company = 1';
            $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1  AND ' . db_prefix() . 'clients.is_inspector = 1';
        }
        include_once(APP_MODULES_PATH. 'inspectors/models/inspectors_model.php');
        $CI->load->model('inspectors_model');
        $data = $CI->inspectors_model->get($rel_id, $where_clients);
    }


    if ($type == 'customer' || $type == 'customers') {
        $where_clients = ''; 
        if ($q) {
            $where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1';
        }

        $data = $CI->clients_model->get($rel_id, $where_clients);
    }

    elseif ($type == 'contact' || $type == 'contacts') {
        if ($rel_id != '') {
            $data = $CI->clients_model->get_contact($rel_id);
        } else {
            $where_contacts = db_prefix() . 'contacts.active=1';
            if (isset($extra['client_id']) && $extra['client_id'] != '') {
                $where_contacts .= ' AND '. db_prefix() . 'contacts.userid='. $extra['client_id'];
            }
            
            if ($CI->input->post('tickets_contacts')) {
                if (!has_permission('customers', '', 'view') && get_option('staff_members_open_tickets_to_all_contacts') == 0) {
                    $where_contacts .= ' AND ' . db_prefix() . 'contacts.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
                }
            }
            if ($CI->input->post('contact_userid')) {
                $where_contacts .= ' AND ' . db_prefix() . 'contacts.userid=' . $CI->db->escape_str($CI->input->post('contact_userid'));
            }
            $search = $CI->misc_model->_search_contacts($q, 0, $where_contacts);
            $data   = $search['result'];
        }
    } elseif ($type == 'invoice') {
        if ($rel_id != '') {
            $CI->load->model('invoices_model');
            $data = $CI->invoices_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_invoices($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'credit_note') {
        if ($rel_id != '') {
            $CI->load->model('credit_notes_model');
            $data = $CI->credit_notes_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_credit_notes($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'estimate') {
        if ($rel_id != '') {
            $CI->load->model('estimates_model');
            $data = $CI->estimates_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_estimates($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'contract' || $type == 'contracts') {
        $CI->load->model('contracts_model');

        if ($rel_id != '') {
            $CI->load->model('contracts_model');
            $data = $CI->contracts_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_contracts($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'ticket') {
        if ($rel_id != '') {
            $CI->load->model('tickets_model');
            $data = $CI->tickets_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_tickets($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'expense' || $type == 'expenses') {
        if ($rel_id != '') {
            $CI->load->model('expenses_model');
            $data = $CI->expenses_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_expenses($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'lead' || $type == 'leads') {
        if ($rel_id != '') {
            $CI->load->model('leads_model');
            $data = $CI->leads_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_leads($q, 0, [
                'junk' => 0,
                ]);
            $data = $search['result'];
        }
    } elseif ($type == 'proposal') {
        if ($rel_id != '') {
            $CI->load->model('proposals_model');
            $data = $CI->proposals_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_proposals($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'project') {
        if ($rel_id != '') {
            $CI->load->model('projects_model');
            $data = $CI->projects_model->get($rel_id);
        } else {
            $where_projects = '';
            if ($CI->input->post('customer_id')) {
                $where_projects .= 'clientid=' . $CI->db->escape_str($CI->input->post('customer_id'));
            }
            $search = $CI->misc_model->_search_projects($q, 0, $where_projects);
            $data   = $search['result'];
        }
    } elseif ($type == 'staff') {
        if ($rel_id != '') {
            $CI->load->model('staff_model');
            $data = $CI->staff_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_staff($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'tasks' || $type == 'task') {
        // Tasks only have relation with custom fields when searching on top
        if ($rel_id != '') {
            $data = $CI->tasks_model->get($rel_id);
        }
    }


    */
    return $data;
}

/**
 * Ger relation values eq invoice number or project name etc based on passed relation parsed results
 * from function get_relation_data
 * $relation can be object or array
 * @param  mixed $relation
 * @param  string $type
 * @return mixed
 */
function apps_get_relation_values($relation, $type)
{

    if ($relation == '') {
        return [
            'name'      => '',
            'id'        => '',
            'link'      => '',
            'addedfrom' => 0,
            'subtext'   => '',
            ];
    }

    $addedfrom = 0;
    $name      = '';
    $id        = '';
    $link      = '';
    $subtext   = '';

    if ($type == 'company' || $type == 'companies') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }
        $link = admin_url('companies/company/' . $id);
    }  
    elseif ($type == 'institution' || $type == 'institutions') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }

        $link = admin_url('institutions/institution/' . $id);
    }
    elseif ($type == 'inspector' || $type == 'inspectors') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }

        $link = admin_url('inspectors/inspector/' . $id);
    }
    elseif ($type == 'inspector_staff' || $type == 'inspector_staffs') {
        if (is_array($relation)) {
            $id   = $relation['staffid'];
            $name = $relation['firstname'];
        } else {
            $id   = $relation->staffid;
            $name = $relation->firstname;
        }

        $link = admin_url('pengguna/pengguna/' . $id);
    }
    elseif ($type == 'surveyor' || $type == 'surveyors') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }

        $link = admin_url('surveyors/surveyor/' . $id);
    }
    /*
    if ($type == 'customer' || $type == 'customers') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }
        $link = admin_url('clients/client/' . $id);
    }

     elseif ($type == 'contact' || $type == 'contacts') {
        if (is_array($relation)) {
            $userid = isset($relation['userid']) ? $relation['userid'] : $relation['relid'];
            $id     = $relation['id'];
            $name   = $relation['firstname'] . ' ' . $relation['lastname'];
        } else {
            $userid = $relation->userid;
            $id     = $relation->id;
            $name   = $relation->firstname . ' ' . $relation->lastname;
        }
        $subtext = get_company_name($userid);
        $link    = admin_url('clients/client/' . $userid . '?contactid=' . $id);
    } elseif ($type == 'invoice') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name = format_invoice_number($id);
        $link = admin_url('invoices/list_invoices/' . $id);
    } elseif ($type == 'credit_note') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name = format_credit_note_number($id);
        $link = admin_url('credit_notes/list_credit_notes/' . $id);
    } elseif ($type == 'estimate') {
        if (is_array($relation)) {
            $id        = $relation['estimateid'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name = format_estimate_number($id);
        $link = admin_url('estimates/list_estimates/' . $id);
    } elseif ($type == 'contract' || $type == 'contracts') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $name      = $relation['subject'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $name      = $relation->subject;
            $addedfrom = $relation->addedfrom;
        }
        $link = admin_url('contracts/contract/' . $id);
    } elseif ($type == 'ticket') {
        if (is_array($relation)) {
            $id   = $relation['ticketid'];
            $name = '#' . $relation['ticketid'];
            $name .= ' - ' . $relation['subject'];
        } else {
            $id   = $relation->ticketid;
            $name = '#' . $relation->ticketid;
            $name .= ' - ' . $relation->subject;
        }
        $link = admin_url('tickets/ticket/' . $id);
    } elseif ($type == 'expense' || $type == 'expenses') {
        if (is_array($relation)) {
            $id        = $relation['expenseid'];
            $name      = $relation['category_name'];
            $addedfrom = $relation['addedfrom'];

            if (!empty($relation['expense_name'])) {
                $name .= ' (' . $relation['expense_name'] . ')';
            }
        } else {
            $id        = $relation->expenseid;
            $name      = $relation->category_name;
            $addedfrom = $relation->addedfrom;
            if (!empty($relation->expense_name)) {
                $name .= ' (' . $relation->expense_name . ')';
            }
        }
        $link = admin_url('expenses/list_expenses/' . $id);
    } elseif ($type == 'lead' || $type == 'leads') {
        if (is_array($relation)) {
            $id   = $relation['id'];
            $name = $relation['name'];
            if ($relation['email'] != '') {
                $name .= ' - ' . $relation['email'];
            }
        } else {
            $id   = $relation->id;
            $name = $relation->name;
            if ($relation->email != '') {
                $name .= ' - ' . $relation->email;
            }
        }
        $link = admin_url('leads/index/' . $id);
    } elseif ($type == 'proposal') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
            if (!empty($relation['subject'])) {
                $name .= ' - ' . $relation['subject'];
            }
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
            if (!empty($relation->subject)) {
                $name .= ' - ' . $relation->subject;
            }
        }
        $name = format_proposal_number($id);
        $link = admin_url('proposals/list_proposals/' . $id);
    } elseif ($type == 'tasks' || $type == 'task') {
        if (is_array($relation)) {
            $id   = $relation['id'];
            $name = $relation['name'];
        } else {
            $id   = $relation->id;
            $name = $relation->name;
        }
        $link = admin_url('tasks/view/' . $id);
    } elseif ($type == 'staff') {
        if (is_array($relation)) {
            $id   = $relation['staffid'];
            $name = $relation['firstname'] . ' ' . $relation['lastname'];
        } else {
            $id   = $relation->staffid;
            $name = $relation->firstname . ' ' . $relation->lastname;
        }
        $link = admin_url('profile/' . $id);
    } elseif ($type == 'project') {
        if (is_array($relation)) {
            $id       = $relation['id'];
            $name     = $relation['name'];
            $clientId = $relation['clientid'];
        } else {
            $id       = $relation->id;
            $name     = $relation->name;
            $clientId = $relation->clientid;
        }


        $name = '#' . $id . ' - ' . $name . ' - ' . get_company_name($clientId);

        $link = admin_url('projects/view/' . $id);
    }
        */ 


    $relation_values = [
        'id'        => $id,
        'name'      => $name,
        'link'      => $link,
        'addedfrom' => $addedfrom,
        'subtext'   => $subtext,
        'type'      => $type,
        ];
    return $relation_values;
}


/**
 * Function used to render <option> for relation
 * This function will do all the necessary checking and return the options
 * @param  mixed $data
 * @param  string $type   rel_type
 * @param  string $rel_id rel_id
 * @return string
 */
function apps_init_relation_options($data, $type, $rel_id = '')
{
    $_data = [];

    $has_permission_companies_view = has_permission('companies', '', 'view');
    $has_permission_surveyors_view = has_permission('surveyors', '', 'view');
    $has_permission_inspectors_view = has_permission('inspectors', '', 'view');
    $has_permission_institutions_view = has_permission('institutions', '', 'view');
    $has_permission_goverments_view = has_permission('goverments', '', 'view');

    $is_admin                      = is_admin();
    $CI                            = & get_instance();

    foreach ($data as $relation) {
        $relation_values = apps_get_relation_values($relation, $type);
        
        if ($type == 'company' || $type == 'companies' ) {
            if (!$has_permission_companies_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } 
        elseif ($type == 'institution' || $type == 'institutions' ) {
            if (!$has_permission_institutions_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }
        elseif ($type == 'inspector' || $type == 'inspectors' ) {
            if (!$has_permission_inspectors_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }
        elseif ($type == 'inspector_staff' || $type == 'inspector_staffs' ) {
            if (!$has_permission_inspectors_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }
        elseif ($type == 'surveyor' || $type == 'surveyors' ) {
            if (!$has_permission_surveyors_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }
        /*
        if ($type == 'project') {
            if (!$has_permission_projects_view) {
                if (!$CI->projects_model->is_member($relation_values['id']) && $rel_id != $relation_values['id']) {
                    continue;
                }
            }
        } 

        elseif ($type == 'lead') {
            if (!has_permission('leads', '', 'view')) {
                if ($relation['assigned'] != get_staff_user_id() && $relation['addedfrom'] != get_staff_user_id() && $relation['is_public'] != 1 && $rel_id != $relation_values['id']) {
                    continue;
                }
            }
        } elseif ($type == 'customer') {
            if (!$has_permission_customers_view && !have_assigned_customers() && $rel_id != $relation_values['id']) {
                continue;
            } elseif (have_assigned_customers() && $rel_id != $relation_values['id'] && !$has_permission_customers_view) {
                if (!is_customer_admin($relation_values['id'])) {
                    continue;
                }
            }
        } elseif ($type == 'contract') {
            if (!$has_permission_contracts_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'invoice') {
            if (!$has_permission_invoices_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'estimate') {
            if (!$has_permission_estimates_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'expense') {
            if (!$has_permission_expenses_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'proposal') {
            if (!$has_permission_proposals_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }
        */

        $_data[] = $relation_values;
        //  echo '<option value="' . $relation_values['id'] . '"' . $selected . '>' . $relation_values['name'] . '</option>';
    }

    $_data = hooks()->apply_filters('init_relation_options', $_data, compact('data', 'type', 'rel_id'));

    return $_data;
}



/**
 * Helper function to replace info format merge fields
 * Info format = Address formats for customers, proposals, company information
 * @param  string $mergeCode merge field to check
 * @param  mixed $val       value to replace
 * @param  string $txt       from format
 * @return string
 */
function _apps_format_replace($mergeCode, $val, $txt)
{
    $tmpVal = '';

    if($val <> null || $val <> ''){
        $tmpVal = strip_tags($val);
    }

    if ($tmpVal != '') {
        $result = preg_replace('/({' . $mergeCode . '})/i', $val, $txt);
    } else {
        $re     = '/\s{0,}{' . $mergeCode . '}(<br ?\/?>(\n))?/i';
        $result = preg_replace($re, '', $txt);
    }

    return $result;
}


function get_client_company_by_clientid($id){
    $CI = &get_instance();

    $CI->load->model('clients_model');
    $client = $CI->clients_model->get($id);
    return $client->company;
}


function get_client_company_address($id){
    $CI = &get_instance();
    $CI->db->select('billing_street, billing_city, billing_state','billing_zip');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $id);
    $company = $CI->db->get()->row();

    $address  = '';
    $address .= isset($company->billing_street) ? $company->billing_street .' ' : '' ;
    $address .= isset($company->billing_city) ? $company->billing_city .' ' : '' ;
    $address .= isset($company->billing_state) ? $company->billing_state .' ' : '' ;
    $address .= isset($company->billing_zip) ? $company->billing_zip : '' ;

    return $address;
}
