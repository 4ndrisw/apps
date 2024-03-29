<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends AdminController
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('apps_model');
  }

  public function index()
  {
    if (!has_permission('apps', '', 'widget_category_view')) {
        access_denied('apps');
    }
    if ($this->input->server('REQUEST_METHOD') !== 'GET') {
      show_404();
      die();
    }

    $categories = $this->apps_model->get_categories();
    $data['categories'] = $categories;

    $data['title'] = _l('apps_widget_categories_index_title_page');
    $this->load->view('categories/index', $data);
  }

  public function store_category()
  {
    if (!has_permission('apps', '', 'widget_category_create')) {
        access_denied('apps');
    }
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
      show_404();
      die();
    }

    $name = $this->input->post('name');
    if(!isset($name)) {
      show_404();
      die();
    }

    $note = $this->input->post('note');
    if(!isset($note)) {
      show_404();
      die();
    }

    $this->apps_model->store_category($name, $note);

    set_alert('success', _l('apps_message_success_store_category'));
    redirect(admin_url('apps/categories'));
  }

  public function api_get_category_data() 
  {
    if ($this->input->server('REQUEST_METHOD') !== 'GET') {
      show_404();
      die();
    }

    $category_id = $this->input->get('category_id');
    if(!isset($category_id)) {
      show_404();
      die();
    }

    $category_rows = $this->apps_model->select_categories_by_ids([$category_id]);
    if(count($category_rows) > 0){
      echo json_encode($category_rows[0]);
    } else {
      echo json_encode(null);
    }

    die();
  }

  public function update_category()
  {
    if (!has_permission('apps', '', 'widget_category_edit')) {
        access_denied('apps');
    }
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
      show_404();
      die();
    }

    $id = $this->input->post('id');
    if(!isset($id)) {
      show_404();
      die();
    }

    $name = $this->input->post('name');
    if(!isset($name)) {
      show_404();
      die();
    }

    $note = $this->input->post('note');
    if(!isset($note)) {
      show_404();
      die();
    }

    $category_rows = $this->apps_model->select_categories_by_ids([$id]);
    if(count($category_rows) <= 0) {
      show_404();
      die();
    }

    $this->apps_model->update_category_by_id($id, $name, $note);

    set_alert('success', _l('apps_message_success_update_category'));
    redirect(admin_url('apps/categories'));
  }

  public function destroy_category()
  {
    if (!has_permission('apps', '', 'widget_category_delete')) {
        access_denied('apps');
    }
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
      show_404();
      die();
    }

    $id = $this->input->post('id');
    if(!isset($id)) {
      show_404();
      die();
    }

    $category_rows = $this->apps_model->select_categories_by_ids([$id]);
    if(count($category_rows) <= 0) {
      show_404();
      die();
    }

    $this->apps_model->destroy_category_by_id($id);

    set_alert('success', _l('apps_message_success_destroy_category'));
    redirect(admin_url('apps/categories'));
  }
}
