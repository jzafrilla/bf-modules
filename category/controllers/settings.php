<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

	//--------------------------------------------------------------------

	public function __construct() 
	{
		parent::__construct();

		$this->auth->restrict('Category.Settings.View');
		$this->load->model('category_model', null, true);
		$this->lang->load('category');
		
		
	}
	
	//--------------------------------------------------------------------

	/*
		Method: index()
		
		Displays a list of form data.
	*/
	public function index() 
	{
		Assets::add_js($this->load->view('settings/js', null, true), 'inline');
		
		Template::set('records', $this->category_model->find_all());
		Template::set('toolbar_title', "Manage Category");
		Template::render();
	}
	
	//--------------------------------------------------------------------

	/*
		Method: create()
		
		Creates a Category object.
	*/
	public function create() 
	{
		$this->auth->restrict('Category.Settings.Create');

		if ($this->input->post('submit'))
		{
			if ($insert_id = $this->save_category())
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('category_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'category');
					
				Template::set_message(lang("category_create_success"), 'success');
				Template::redirect(SITE_AREA .'/settings/category');
			}
			else 
			{
				Template::set_message(lang('category_create_failure') . $this->category_model->error, 'error');
			}
		}
	
		Template::set('toolbar_title', lang('category_create_new_button'));
		Template::set('toolbar_title', lang('category_create') . ' Category');
		Template::render();
	}
	
	//--------------------------------------------------------------------

	/*
		Method: edit()
		
		Allows editing of Category data.
	*/
	public function edit() 
	{
		$this->auth->restrict('Category.Settings.Edit');

		$id = (int)$this->uri->segment(5);
		
		if (empty($id))
		{
			Template::set_message(lang('category_invalid_id'), 'error');
			redirect(SITE_AREA .'/settings/category');
		}
	
		if ($this->input->post('submit'))
		{
			if ($this->save_category('update', $id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('category_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'category');
					
				Template::set_message(lang('category_edit_success'), 'success');
			}
			else 
			{
				Template::set_message(lang('category_edit_failure') . $this->category_model->error, 'error');
			}
		}
		
		Template::set('category', $this->category_model->find($id));
	
		Template::set('toolbar_title', lang('category_edit_heading'));
		Template::set('toolbar_title', lang('category_edit') . ' Category');
		Template::render();		
	}
	
	//--------------------------------------------------------------------

	/*
		Method: delete()
		
		Allows deleting of Category data.
	*/
	public function delete() 
	{	
		$this->auth->restrict('Category.Settings.Delete');

		$id = $this->uri->segment(5);
	
		if (!empty($id))
		{	
			if ($this->category_model->delete($id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('category_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'category');
					
				Template::set_message(lang('category_delete_success'), 'success');
			} else
			{
				Template::set_message(lang('category_delete_failure') . $this->category_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA .'/settings/category');
	}
	
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------
	
	/*
		Method: save_category()
		
		Does the actual validation and saving of form data.
		
		Parameters:
			$type	- Either "insert" or "update"
			$id		- The ID of the record to update. Not needed for inserts.
		
		Returns:
			An INT id for successful inserts. If updating, returns TRUE on success.
			Otherwise, returns FALSE.
	*/
	private function save_category($type='insert', $id=0) 
	{	
					
		$this->form_validation->set_rules('category_name','Name','required|trim|xss_clean|max_length[128]');			
		$this->form_validation->set_rules('category_is_active','Is active','required|trim|xss_clean|is_numeric|max_length[1]');			
		$this->form_validation->set_rules('category_parent_id','Parent','trim|xss_clean|max_length[11]');			
		$this->form_validation->set_rules('category_products_count','Products count','trim|xss_clean|max_length[11]');			
		$this->form_validation->set_rules('category_meta_title','Meta title','trim|xss_clean|max_length[250]');			
		$this->form_validation->set_rules('category_meta_description','Meta description','trim|xss_clean');			
		$this->form_validation->set_rules('category_url','Url','unique[bf_category.category_url,bf_category.id]|trim|xss_clean|max_length[255]');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}
		
		// make sure we only pass in the fields we want
		
		$data = array();
		$data['category_name']        = $this->input->post('category_name');
		$data['category_is_active']        = $this->input->post('category_is_active');
		$data['category_parent_id']        = $this->input->post('category_parent_id');
		$data['category_products_count']        = $this->input->post('category_products_count');
		$data['category_meta_title']        = $this->input->post('category_meta_title');
		$data['category_meta_description']        = $this->input->post('category_meta_description');
		$data['category_url']        = $this->input->post('category_url');
		
		if ($type == 'insert')
		{
			$id = $this->category_model->insert($data);
			
			if (is_numeric($id))
			{
				$return = $id;
			} else
			{
				$return = FALSE;
			}
		}
		else if ($type == 'update')
		{
			$return = $this->category_model->update($id, $data);
		}
		
		return $return;
	}

	//--------------------------------------------------------------------



}