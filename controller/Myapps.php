<?php

use app\services\programs\ProgramsPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Myapps extends ClientController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('apps_model');
    }

    public function client_get_relation_data()
    {
        if ($this->input->post()) {
            $type = $this->input->post('type');
            //$data = get_relation_data($type, '', $this->input->post('extra'));
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