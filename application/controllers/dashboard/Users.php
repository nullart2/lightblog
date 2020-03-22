<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index() {

		if (!$this->session->userdata('is_logged_in')) {
			redirect('login');
		} else {
			// Admin ONLY area!
			if (!$this->session->userdata('user_is_admin')) {
				redirect($this->agent->referrer());
			}
		}

		$data = $this->Static_model->get_static_data();
		$data['pages'] = $this->Pages_model->get_pages();
		$data['categories'] = $this->Categories_model->get_categories();
		$data['authors'] = $this->Usermodel->getAuthors();

		$this->load->view('partials/header', $data);
		$this->load->view('dashboard/authors');
		$this->load->view('partials/footer');
		
	}

	public function edit($id) {
		// Only logged in users can edit user profiles
		if (!$this->session->userdata('is_logged_in')) {
			redirect('login');
		}

		$data = $this->Static_model->get_static_data();
		$data['pages'] = $this->Pages_model->get_pages();
		$data['categories'] = $this->Categories_model->get_categories();
		$data['author'] = $this->Usermodel->editAuthor($id);

		$this->load->view('partials/header', $data);
		$this->load->view('dashboard/edit-author');
		$this->load->view('partials/footer');
		
	}

	public function update() {
		// Only logged in users can update user profiles
		if (!$this->session->userdata('is_logged_in')) {
			redirect('login');
		}

		$id = $this->input->post('id');

		$data = $this->Static_model->get_static_data();
		$data['pages'] = $this->Pages_model->get_pages();
		$data['categories'] = $this->Categories_model->get_categories();
		$data['author'] = $this->Usermodel->editAuthor($id);

		$this->form_validation->set_rules('first_name', 'First name', 'required');
		$this->form_validation->set_rules('last_name', 'Last name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

		$this->form_validation->set_error_delimiters('<p class="error-message">', '</p>');

		// Upload avatar
		$config['upload_path'] = './assets/img/authors';
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size'] = '1024';

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('userfile')) {
			$uerrors = array('uerrors' => $this->upload->display_errors());

			if (empty($_FILES['userfile']['name'])) {
				$uerrors = [];
			}

			$data['uerrors'] = $uerrors;

		} else {
			$data = array('upload_data' => $this->upload->data());
			$avatar = $_FILES['userfile']['name'];
		}

		if(!$this->form_validation->run() || !empty($uerrors))
		{

			$this->load->view('partials/header', $data);
			$this->load->view('dashboard/edit-author');
			$this->load->view('partials/footer');
		} else
		{
			$this->Usermodel->update_user($avatar, $id);
			$this->session->set_flashdata('user_updated', 'Your account details have been updated');
			redirect(base_url('/dashboard/manage-authors'));
		}
	}

	public function delete($id) {
		$this->load->model('Usermodel');
		if ($this->Usermodel->deleteAuthor($id)) {
			$this->session->set_flashdata('author_delete', "The author was deleted");
		} else {
			$this->session->set_flashdata('author_delete', "Failed to delete author");
		}
		redirect('dashboard/users');
	}

	public function activate($id) {
		$this->load->model('Usermodel');
		$author = $this->Usermodel->activateAuthor($id);
		redirect($this->agent->referrer());
	}

	public function deactivate($id) {
		$this->load->model('Usermodel');
		$author = $this->Usermodel->deactivateAuthor($id);
		redirect($this->agent->referrer());
	}

}