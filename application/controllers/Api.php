<?php
require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

	public function __construct($config = "rest") {
		parent::__construct($config);
		$this->load->model('TaskModel');
	}

	public function index_get() {
		$response["status"] = REST_Controller::HTTP_NOT_FOUND;
		$response["error"] = true;
		$response["message"] = "Not found!";

		$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function index_post() {
		$response["status"] = REST_Controller::HTTP_NOT_FOUND;
		$response["error"] = true;
		$response["message"] = "Not found!";

		$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function tasks_post() {
		$response = $this->TaskModel->add_tasks(
			$this->post('task'),
			$this->post('due_date')
		);

		$this->response($response, REST_Controller::HTTP_CREATED);
	}

	public function tasks_get() {
		$response = $this->TaskModel->all_tasks();

		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function tasks_updateDone_patch() {
		$response = $this->TaskModel->update_task_done(
			$this->patch('id'),
			$this->patch('checked')
		);

		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function tasks_delete() {
		$response = $this->TaskModel->delete_task();

		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function tasks_allComplete_patch() {
		$response = $this->TaskModel->mark_all_complete();

		$this->response($response, REST_Controller::HTTP_OK);
	}
}
