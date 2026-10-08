<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends MY_Controller
{
    public function error_404()
    {
        $this->page_not_found();
    }

    public function page_not_found()
    {
        $this->output->set_status_header(404);
        $data = ['title' => '404 Not Found'];
        $this->load->view('errors/404', $data);
    }
}
