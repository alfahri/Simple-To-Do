<?php
class TaskModel extends CI_Model
{
	public function empty_response($message = "Field cannot be empty!")
	{
		$response['status'] = 502;
		$response['error'] = true;
		$response['message'] = $message;
		return $response;
	}

	public function count_pending_done() {
		$this->db->where(['done' => 1]);
		$intCountDone = $this->db->count_all_results("tasks");
		$this->db->where(['done' => 0]);
		$intCountPending = $this->db->count_all_results("tasks");

		return [
			"pending" => $intCountPending,
			"done" => $intCountDone
		];
	}

	public function validateDate($date, $format = 'Y-m-d')
	{
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) === $date;
	}


	public function get_latest_data() {
		$intCountRecords = $this->db->count_all_results('tasks');

		return $intCountRecords;
	}

	public function add_tasks($strTask, $strDueDate)
	{
		$strDateNow = date('Y-m-d');
		$strDateInput = date('Y-m-d', strtotime($strDueDate));

		if (!$this->validateDate($strDueDate)) {
			return $this->empty_response("Wrong date format!");
		}

		if ($strDateInput > $strDateNow) {
			return $this->empty_response("Date cannot larger than now!");
		}

		if (empty($strTask) || empty($strDueDate)) {
			return $this->empty_response();
		} else {
			$intOrderValue = $this->get_latest_data() + 1;

			$objData = [
				"text" => $strTask,
				"date" => $strDueDate,
				"order" => $intOrderValue
			];

			$insert = $this->db->insert("tasks", $objData);
			if ($insert) {
				$arrDataTask = $this->db->get("tasks")->result();
				$objCountPd = $this->count_pending_done();

				$response['status'] = 200;
				$response['error'] = false;
				$response['message'] = 'Task added successfully.';
				$response['data'] = $arrDataTask;
				$response['pending'] = $objCountPd["pending"];
				$response['done'] = $objCountPd["done"];
				return $response;
			} else {
				$response['status'] = 502;
				$response['error'] = true;
				$response['message'] = 'Task failed to add.';
				return $response;
			}
		}
	}

	public function all_tasks()
	{
		$arrDataTask = $this->db->get("tasks")->result();
		$objCountPd = $this->count_pending_done();

		$response['status'] = 200;
		$response['error'] = false;
		$response['data'] = $arrDataTask;
		$response["done"] = $objCountPd["done"];
		$response["pending"] = $objCountPd["pending"];

		return $response;
	}

	public function delete_task()
	{
		$objWhere = [
			"done" => 1
		];
		$this->db->where($objWhere);
		$delete = $this->db->delete("tasks");
		if ($delete) {
			$arrDataTask = $this->db->get("tasks")->result();
			$objCountPd = $this->count_pending_done();

			$response['status'] = 200;
			$response['error'] = false;
			$response['message'] = 'Task successfully deleted.';
			$response["data"] = $arrDataTask;
			$response["pending"] = $objCountPd["pending"];
			$response["done"] = $objCountPd["done"];
			return $response;
		} else {
			$response['status'] = 502;
			$response['error'] = true;
			$response['message'] = 'Task failed to delete.';
			return $response;
		}
	}

	public function update_task($strId, $strTask, $strDueDate)
	{
		if ($strId == '' || empty($strTask) || empty($strDueDate)) {
			return $this->empty_response();
		} else {
			$objWhere = [
				"id" => $strId
			];
			$objSet = [
				"text" => $strTask,
				"date" => $strDueDate
			];
			$this->db->where($objWhere);
			$update = $this->db->update("tasks", $objSet);
			if ($update) {
				$response['status'] = 200;
				$response['error'] = false;
				$response['message'] = 'Task successfully changed.';
				return $response;
			} else {
				$response['status'] = 502;
				$response['error'] = true;
				$response['message'] = 'Task failed to change.';
				return $response;
			}
		}
	}

	public function mark_all_complete() {
		$objSet = [
			"done" => 1
		];

		$update = $this->db->update("tasks", $objSet);

		if ($update) {
			$arrDataTask = $this->db->get("tasks")->result();
			$objCountPd = $this->count_pending_done();

			$response["status"] = 200;
			$response["error"] = false;
			$response["message"] = "All tasks successfully completed.";
			$response["pending"] = $objCountPd["pending"];
			$response["done"] = $objCountPd["done"];
			$response["data"] = $arrDataTask;
			return $response;
		} else {
			$response["status"] = 502;
			$response["error"] = true;
			$response["message"] = "All tasks failed to complete.";

			return $response;
		}
	}

	public function update_task_done($id, $checked) {
		$objSet = [
			"done" => $checked ? 1 : 0
		];

		$this->db->where(['id' => $id]);
		$update = $this->db->update("tasks", $objSet);
		if ($update) {
			$objCountPd = $this->count_pending_done();
			$arrDataTask = $this->db->get("tasks")->result();

			$response["status"] = 200;
			$response["error"] = false;
			$response["message"] = "Task successfully completed.";
			$response["data"] = $arrDataTask;
			$response["done"] = $objCountPd["done"];
			$response["pending"] = $objCountPd["pending"];
			return $response;
		} else {
			$response["status"] = 502;
			$response["error"] = true;
			$response["message"] = "Task failed to complete.";
			return $response;
		}
	}
}
