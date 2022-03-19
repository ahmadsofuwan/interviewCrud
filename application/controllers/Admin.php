<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Admin extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$role = $this->session->userdata('role');
		$login = $this->session->userdata('login');
		if (!$login && $role !== '1') {
			redirect(base_url('Auth'));
		}
	}

	public function index()
	{
		$data['html']['title'] = 'Dasboard';
		$this->template($data);
	}

	public function bonusList()
	{

		$dataBonus = $this->getDataRow('bonus', '* ,', '', '', '', 'bonus.name ASC');
		$data['html']['title'] = 'List Bonus';
		$data['html']['dataBonus'] = $dataBonus;
		$data['html']['form'] = get_class($this) . '/bonus';
		$data['url'] = 'admin/bonusList';
		$this->template($data);
	}


	public function bonus($id = '')
	{
		$tableName = 'bonus';
		$tableDetail = 'bonus_detail';
		$baseUrl = get_class($this) . '/' . __FUNCTION__;
		$detailRef = 'bonuskey';
		$formData = array(
			'pkey' => 'pkey',
			'name' => 'name',
			'bonus' => array('bonus', 'number'),
		);
		$formDetail = array(
			'pkey' => 'detailKey',
			'bonuskey' => 'refkey',
			'percentage' => array('detailBonus', 'number'),
			'employeekey' => 'detailEmployeKey',

		);

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (empty($_POST['action'])) redirect(base_url($baseUrl . 'List'));
			//validate form
			$arrMsgErr = array();
			if (empty($_POST['name'])) {
				array_push($arrMsgErr, "name wajib Di isi");
			}
			$detailPercentage = 0;
			foreach ($_POST['detailKey'] as $key => $value) {
				if (empty($_POST['detailKey'][$key]) && empty($_POST['detailBonus'][$key])) {
					unset($_POST['detailKey'][$key]);
				} else {
					$detailPercentage += (int)str_replace(",", "", $_POST['detailBonus'][$key]);
				}
			}
			if ($detailPercentage != 100)
				array_push($arrMsgErr, "total pesentasi harus 100%");

			$this->session->set_flashdata('arrMsgErr', $arrMsgErr);
			//validate form
			if (empty(count($arrMsgErr)))
				switch ($_POST['action']) {
					case 'add':
						//insert
						if (!empty(count($arrMsgErr)))
							break;
						$data = array();
						foreach ($formData as $key => $value) {
							if (is_array($value) && $value[1] == 'number') {
								$_POST[$value[0]] = str_replace(",", "", $_POST[$value[0]]);
								$value = $value[0];
							}
							$data[$key] = $_POST[$value];
						}
						$refkey = $this->insert($tableName, $data);
						// insert detail
						if (!empty(count($formDetail))) {
							$dataDetail = array();
							foreach ($_POST['detailKey'] as $item => $val) {
								foreach ($formDetail as $key => $value) {
									if ($value == 'refkey')
										$_POST[$value][$item] = $refkey;
									$dataDetail[$key] = $_POST[$value][$item];
								}
								$this->insert($tableDetail, $dataDetail);
							}
						}

						redirect(base_url($baseUrl . 'List')); //wajib terakhir
						//insert
						break;
					case 'update':

						$data = array();
						foreach ($formData as $key => $value) {
							if (is_array($value) && $value[1] == 'number') {
								$_POST[$value[0]] = str_replace(",", "", $_POST[$value[0]]);
								$value = $value[0];
							}
							$data[$key] = $_POST[$value];
						}
						$this->update($tableName, $data, 'pkey=' . $_POST['pkey']);
						//update detail
						$oldDataDetail = $this->getDataRow($tableDetail, 'pkey', $detailRef . '=' . $_POST['pkey']);
						$logs = array();
						foreach ($_POST['detailKey'] as $i => $value) {
							if (!empty($_POST['detailKey'][$i])) {
								$status = false;
								$arrNumber = 0;
								foreach ($oldDataDetail as $key => $item) {

									if ($item['pkey'] == $_POST['detailKey'][$i]) {
										$status = true;
										$arrNumber = $key;
									}
								}
								if ($status)
									unset($oldDataDetail[$arrNumber]);
							}

							$dataDetail = array();
							foreach ($formDetail as $key => $value) {
								if ($value == 'refkey')
									$_POST[$value][$i] = $id;

								if (is_array($value) && $value[1] == 'number') {
									$_POST[$value[0]] = str_replace(",", "", $_POST[$value[0]]);
									$value = $value[0];
								}
								$dataDetail[$key] = $_POST[$value][$i];
							}
							if (empty($_POST['detailKey'][$i])) {
								echo 'insert';
								$this->insert($tableDetail, $dataDetail);
							} else {
								echo 'update';
								$this->update($tableDetail, $dataDetail, 'pkey=' . $_POST['detailKey'][$i]);
							}
						}
						//delete detail
						$deleteId = '';
						foreach ($oldDataDetail as $item) {
							if (empty($deleteId)) {
								$deleteId = $item['pkey'];
							} else {
								$deleteId .= ', ' . $item['pkey'];
							}
						}
						if (!empty($deleteId))
							$this->delete($tableDetail, 'pkey in(' . $deleteId . ')');
						//update detail
						redirect(base_url($baseUrl . 'List'));
						break;
				}
		}

		if (!empty($id)) {
			$dataRow = $this->getDataRow($tableName, '*', array('pkey' => $id), 1)[0];
			foreach ($formData as $key => $value) {
				if (is_array($value))
					$value = $value[0];
				$_POST[$value] = $dataRow[$key];
			}
			$_POST['action'] = 'update';
		}
		$selVal = $this->getDataRow('employee', '*', '', '', '', 'employee.name ASC');
		$dataDetail = $this->getDataRow($tableDetail, '*', $detailRef . '=' . $id);
		$data['html']['title'] = 'Input Data ' . __FUNCTION__;
		$data['html']['baseUrl'] = $baseUrl;
		$data['html']['selVal'] = $selVal;
		$data['html']['dataDetail'] = $dataDetail;
		$data['html']['err'] = $this->genrateErr();
		$data['url'] = 'admin/' . __FUNCTION__ . 'Form';
		$this->template($data);

		function validateForm($arrMsgErr)
		{
			# code...
		}
	}

	public function userList()
	{

		$dataList = $this->getDataRow('account', '* ,', '', '', '', 'name ASC');
		$data['html']['title'] = 'List Account';
		$data['html']['dataList'] = $dataList;
		$data['html']['form'] = get_class($this) . '/user';
		$data['url'] = 'admin/userList';
		$this->template($data);
	}

	public function user($id = '')
	{
		$tableName = 'account';
		$tableDetail = '';
		$baseUrl = get_class($this) . '/' . __FUNCTION__;
		$detailRef = '';
		$formData = array(
			'pkey' => 'pkey',
			'name' => 'name',
			'username' => 'username',
			'password' => array('password', 'md5'),
			'role' => 'role',
		);
		$formDetail = array();

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (empty($_POST['action'])) redirect(base_url($baseUrl . 'List'));
			//validate form
			$arrMsgErr = array();
			if (empty($_POST['name']))
				array_push($arrMsgErr, "Password wajib Di isi");


			if (empty($_POST['password']))
				array_push($arrMsgErr, "Password wajib Di isi");


			$this->session->set_flashdata('arrMsgErr', $arrMsgErr);
			//validate form
			if (empty(count($arrMsgErr)))
				switch ($_POST['action']) {
					case 'add':
						//insert
						if (!empty(count($arrMsgErr)))
							break;

						$refkey = $this->insert($tableName, $this->dataForm($formData));
						// insert detail
						if (!empty(count($formDetail))) {
							$dataDetail = array();
							foreach ($_POST['detailKey'] as $item => $val) {
								foreach ($formDetail as $key => $value) {
									if (
										$value == 'refkey'
									)
										$_POST[$value][$item] = $refkey;
									$dataDetail[$key] = $_POST[$value][$item];
								}
								$this->insert($tableDetail, $dataDetail);
							}
						}
						redirect(base_url($baseUrl . 'List')); //wajib terakhir
						//insert
						break;
					case 'update':
						$this->update($tableName, $this->dataForm($formData), array('pkey' => $_POST['pkey']));
						//update detail
						if (!empty($tableDetail)) {
							$oldDataDetail = $this->getDataRow($tableDetail, 'pkey', $detailRef . '=' . $_POST['pkey']);
							foreach ($_POST['detailKey'] as $i => $value) {
								if (!empty($_POST['detailKey'][$i])) {
									$status = false;
									$arrNumber = 0;
									foreach ($oldDataDetail as $key => $item) {

										if ($item['pkey'] == $_POST['detailKey'][$i]) {
											$status = true;
											$arrNumber = $key;
										}
									}
									if ($status)
										unset($oldDataDetail[$arrNumber]);
								}

								$dataDetail = array();
								foreach ($formDetail as $key => $value) {
									if ($value == 'refkey')
										$_POST[$value][$i] = $id;

									if (is_array($value) && $value[1] == 'number') {
										$_POST[$value[0]] = str_replace(",", "", $_POST[$value[0]]);
										$value = $value[0];
									}
									$dataDetail[$key] = $_POST[$value][$i];
								}
								if (empty($_POST['detailKey'][$i])) {
									echo 'insert';
									$this->insert($tableDetail, $dataDetail);
								} else {
									echo 'update';
									$this->update($tableDetail, $dataDetail, 'pkey=' . $_POST['detailKey'][$i]);
								}
							}
							//delete detail
							$deleteId = '';
							foreach ($oldDataDetail as $item) {
								if (empty($deleteId)) {
									$deleteId = $item['pkey'];
								} else {
									$deleteId .= ', ' . $item['pkey'];
								}
							}
							if (!empty($deleteId))
								$this->delete($tableDetail, 'pkey in(' . $deleteId . ')');
						}
						//update detail
						redirect(base_url($baseUrl . 'List'));
						break;
				}
		}

		if (!empty($id)) {
			$dataRow = $this->getDataRow($tableName, '*', array('pkey' => $id), 1)[0];
			foreach ($formData as $key => $value) {
				if (is_array($value))
					$value = $value[0];
				$_POST[$value] = $dataRow[$key];
			}
			$_POST['action'] = 'update';
			$_POST['password'] = '';
		}
		$selVal = $this->getDataRow('role', '*', '', '', '', 'name ASC');
		if (!empty($tableDetail)) {
			$dataDetail = $this->getDataRow($tableDetail, '*', $detailRef . '=' . $id);
			$data['html']['dataDetail'] = $dataDetail;
		}
		$data['html']['title'] = 'Input Data ' . __FUNCTION__;
		$data['html']['baseUrl'] = $baseUrl;
		$data['html']['selVal'] = $selVal;
		$data['html']['err'] = $this->genrateErr();
		$data['url'] = 'admin/' . __FUNCTION__ . 'Form';
		$this->template($data);
	}

	public function ajax()
	{
		if (empty($_POST['action'])) {
			echo 'no action';
			die;
		}
		switch ($_POST['action']) {
			case 'deleteBonus':
				$this->delete('bonus', 'pkey=' . $_POST['pkey']);
				$this->delete('bonus_detail', 'bonus_detail.bonuskey=' . $_POST['pkey']);
				break;
			case 'deleteUser':
				$this->delete('account', 'pkey=' . $_POST['pkey']);
				break;
			default:
				echo 'action is not in the list';
				break;
		}
	}
}
