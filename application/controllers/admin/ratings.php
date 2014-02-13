<?php
class Ratings extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('user');
	}

	public function index() {
			
		if (!$this->user->islogged()) {  
  			redirect('admin/login');
		}

    	if (!$this->user->hasPermissions('access', 'admin/ratings')) {
  			redirect('admin/permission');
		}
		
		if ($this->session->flashdata('alert')) {
			$data['alert'] = $this->session->flashdata('alert');
		} else {
			$data['alert'] = '';
		}

		$data['heading'] 			= 'Ratings';
		$data['sub_menu_save'] 	= 'Save';
		$data['text_empty'] 		= 'There are no ratings, please add!.';

		//load ratings data into array
		$data['ratings'] = array();
		
		if ($this->config->item('ratings')) {
			$results = $this->config->item('ratings');
		} else if ($this->input->post('ratings')) {
			$results = $this->input->post('ratings');
		} else {
			$results = '';
		}
		
		if (is_array($results)) {
			foreach ($results as $key => $value) {					
				$data['ratings'][] = array(
					'name'	=>	$value
				);
			}
		}

		if ($this->input->post() && $this->_updateRating() === TRUE) {
		
			redirect('admin/ratings');  			
		}

		$regions = array(
			'admin/header',
			'admin/footer'
		);
		
		$this->template->regions($regions);
		$this->template->load('admin/ratings', $data);
	}
	
	public function _updateRating() {
						
    	if (!$this->user->hasPermissions('modify', 'admin/ratings')) {
		
			$this->session->set_flashdata('alert', '<p class="warning">Warning: You do not have permission to modify!</p>');
			return TRUE;
    	
    	} else if ($this->input->post('ratings')) { 
			
			if ($this->input->post('ratings')) {
				foreach ($this->input->post('ratings') as $key => $value) {
					$this->form_validation->set_rules('ratings['.$key.']', 'Rating Name', 'trim|required|min_length[2]|max_length[32]');
				}
			}
						
			if ($this->form_validation->run() === TRUE) {

				$update = array();
			
				$update['ratings'] = $this->input->post('ratings');

				$this->load->model('Settings_model');
				if ($this->Settings_model->updateSettings('ratings', $update)) {						
			
					$this->session->set_flashdata('alert', '<p class="success">Rating Updated Sucessfully!</p>');
				} else {
			
					$this->session->set_flashdata('alert', '<p class="warning">Nothing Updated!</p>');				
				}
			
				return TRUE;
			}
		}
	}
}