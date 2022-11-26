<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Task</title>
	<link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendor/css/bootstrap.min.css') ?>">
</head>
<body>
	<div class="container pt-5">
		<div class="card p-3">
			<form id="formTask">
				<div class="row">
					<div class="col-12" style="display: none;" id="sctAlertResponse">
					</div>
					<div class="col-6">
						<input type="text" name="task" id="fieldTask" class="form-control" placeholder="Task to do" required>
					</div>
					<div class="col-6">
						<input type="date" name="date" id="fieldDate" class="form-control" min="2011-07-16" required>
					</div>
				</div>
			</form>
			<div class="row my-3">
				<div class="col-6">
					<input type="checkbox" name="markAllComplete" id="ckbxAllComplete">
					<label for="ckbxAllComplete">Mark all as complete</label>
				</div>
				<div class="col-6 text-right">
					<button class="btn btn-outline-dark" id="btnAdd">Add</button>
				</div>
			</div>
			<div class="row px-3" id="sctListData">
			</div>
			<div class="row pt-3">
				<div class="col-6 d-flex align-items-center">
					<span><span id="countPending">-</span> Items left</span>
				</div>
				<div class="col-6 text-right">
					<button class="btn btn-outline-dark" id="btnClearCompleted">Clear <span id="countDone">-</span> completed item</button>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="<?= base_url('assets/vendor/js/jquery.slim.min.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/vendor/js/popper.min.js') ?>"></script>
	<script type="text/javascript" src="<?= base_url('assets/vendor/js/bootstrap.min.js') ?>"></script>
	<script type="text/javascript">
		let baseUrl = `<?= base_url('api') ?>`
		let sctAlertResponse = $("#sctAlertResponse");
		let intTaskDone = 0;
		let intTaskPending = 0;
		let countPending = $("#countPending");
		let countDone = $("#countDone");

		function todayDate() {
		    var today = new Date();
		    var dd = today.getDate();
		    var mm = today.getMonth()+1;
		    var yyyy = today.getFullYear();

		    if(dd<10) {
		        dd='0'+dd
		    }

		    if(mm<10) {
		        mm='0'+mm
		    }

		    return yyyy+'-'+mm+'-'+dd;
		}

		function renderGrid(data) {
			let strHtml = "";
			$("#sctListData").html('')

			data.map((val, item) => {
				data[item].done = parseInt(val.done)

				if (val.done) {
					strHtml += `
						<div class="col-4 border border-dark py-3 d-flex align-items-center">
							<input type="checkbox" name="singleComplete" id="cbkxSingleComplete" class="form-control" checked="true" data-id="${val.id}">
						</div>
						<div class="col-4 border border-dark py-3">
							<del><span>${val.text}</span></del>
						</div>
						<div class="col-4 border border-dark py-3">
							<span>${val.date}</span>
						</div>
					`;
				} else {
					strHtml += `
						<div class="col-4 border border-dark py-3 d-flex align-items-center">
							<input type="checkbox" name="singleComplete" id="cbkxSingleComplete" class="form-control" data-id="${val.id}">
						</div>
						<div class="col-4 border border-dark py-3">
							<span>${val.text}</span>
						</div>
						<div class="col-4 border border-dark py-3">
							<span>${val.date}</span>
						</div>
					`;
				}
			})

			$("#sctListData").html(strHtml)
		}

		$(document).ready(function() {
			$("#fieldDate").attr("max", todayDate());

			loadData();
		})

		function loadData() {
			$.ajax({
				url: `${baseUrl}/tasks`,
				method: 'get',
				headers: {
					'Content-Type': 'application/json'
				},
				success: function(e) {
					let strHtml = ``;

					renderGrid(e.data)

					intTaskDone = e.done
					intTaskPending = e.pending

					countDone.html(intTaskDone)
					countPending.html(intTaskPending)
				},error: function(e) {}
			})
		}

		$(document).on("submit", "#formTask", function(e) {
			e.preventDefault();

			let task = $("#fieldTask").val()
			let due_date = $("#fieldDate").val()

			$.ajax({
				url: `${baseUrl}/tasks`,
				method: 'post',
				headers: {
					'Content-Type': 'application/json'
				},
				data: JSON.stringify({
					task,
					due_date
				}),
				success: function(e) {
					if (e.error == true) {
						sctAlertResponse.css("display", "block");
						sctAlertResponse.html(`
							<div class="alert alert-danger" role="alert">
							${e.message}
							</div>
						`)
					} else {
						countDone.html(e.done)
						countPending.html(e.pending)

						$(':input', "#formTask").val('')
					}
				},error: function(e) {
					alert("Gagal")
				}
			})
		})

		$(document).on("change", "#cbkxSingleComplete", function(e) {
			let id = $(this).data("id");
			let checked = $(this).is(":checked");

			$.ajax({
				url: `${baseUrl}/tasks_updateDone`,
				data: JSON.stringify({
					id,
					checked
				}),
				headers: {
					"Content-Type": "application/json"
				},
				method: 'patch',
				success: function(e) {
					if (e.error) {
						alert(e.message)
					} else {
						renderGrid(e.data);

						countDone.html(e.done)
						countPending.html(e.pending)
					}
				},error: function(e) {
					alert("Gagal")
				}
			})
		})

		$(document).on("click", "#btnAdd", function(e) {
			e.preventDefault();

			let task = $("#fieldTask").val()
			let due_date = $("#fieldDate").val()

			$.ajax({
				url: `${baseUrl}/tasks`,
				method: 'post',
				headers: {
					'Content-Type': 'application/json'
				},
				data: JSON.stringify({
					task,
					due_date
				}),
				success: function(e) {
					let strHtml = '';

					if (e.error == true) {
						sctAlertResponse.css("display", "block");
						sctAlertResponse.html(`
							<div class="alert alert-danger" role="alert">
							${e.message}
							</div>
						`)
					} else {
						renderGrid(e.data);

						countDone.html(e.done)
						countPending.html(e.pending)

						$(':input', "#formTask").val('')
					}

					$("#sctListData").append(strHtml)
				},error: function(e) {
					alert("Gagal")
				}
			})
		})

		$(document).on("change", "#ckbxAllComplete", function(e) {
			e.preventDefault()

			$.ajax({
				url: `${baseUrl}/tasks_allComplete`,
				method: 'patch',
				headers: {
					"Content-Type": "application/json"
				},
				success: function(e) {
					if (e.error) {} else {
						renderGrid(e.data)
						countDone.html(e.done)
						countPending.html(e.pending)
					}
				},error: function(e) {
					alert("Gagal")
				}
			})
		})

		$(document).on("click", "#btnClearCompleted", function(e) {
			e.preventDefault();

			$.ajax({
				url: `${baseUrl}/tasks`,
				method: 'delete',
				headers: {
					"Content-Type": "application/json"
				},
				success: function(e) {
					if (e.error) {} else {
						renderGrid(e.data)

						countDone.html(e.done)
						countPending.html(e.pending)
					}
				},error: function(e) {
					alert("Gagal")
				}
			})
		})
	</script>
</body>
</html>
