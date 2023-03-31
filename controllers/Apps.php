<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Apps extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('apps_model');
    }

    public function index()
    {

    }

    public function get_relation_data()
    {
        if ($this->input->post()) {
            $type = $this->input->post('type');

            $data = apps_get_relation_data($type, '', $this->input->post('extra'));
            
            if ($this->input->post('rel_id')) {
                $rel_id = $this->input->post('rel_id');
            } else {
                $rel_id = '';
            }
            
            $relOptions = apps_init_relation_options($data, $type, $rel_id);
            echo json_encode($relOptions);
            die;
        }
    }

}