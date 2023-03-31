<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('apps_model');
  }

  public function index()
  {
    if (!has_permission('apps', '', 'dashboard_settings')) {
        access_denied('apps');
    }
    $data['title'] = _l('settings_dashboard');
    $this->load->view('settings/index', $data);
  }
}
