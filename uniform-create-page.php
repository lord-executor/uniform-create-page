<?php

uniform::$actions['create-page'] = function ($form, $actionOptions)
{

	$now = new DateTime();
	$today = $now->format('Ymd');
	$submissionsRoot = page(a::get($actionOptions, 'submissionsRoot'));

	if (a::get($actionOptions, 'dailyFolder', false)) {
		$folder = $submissionsRoot->children()->find($today);
		if (empty($folder)) {
			$folder = $submissionsRoot->children()->create($today, a::get($actionOptions, 'dailyFolderTemplate', 'default'), []);
		}
	} else {
		$folder = $submissionsRoot;
	}

	$data = [];

	foreach ($form as $key => $value) {
		if (is_array($value)) {
			$data[$key] = filter_var(implode(', ', array_filter($value, function ($i) {
				return $i !== '';
			})), FILTER_SANITIZE_STRING);
		} else {
			$data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
		}
	}

	$data['uid'] = $now->getTimestamp();

	$data['created'] = $now->getTimestamp();
	$data['email'] = filter_var($form['_from'], FILTER_SANITIZE_EMAIL);
	unset($data['_from']);

	if (a::get($actionOptions, 'dataCallback')) {
		call_user_func_array(a::get($actionOptions, 'dataCallback'), [&$data, $folder, $form, $actionOptions]);
	}

	$id = $data['uid'];
	unset($data['uid']);

	$folder->children()->create($id, a::get($actionOptions, 'submissionsTemplate', 'dynamic'), $data);

	return [
		'success' => true,
		'message' => l::get('uniform-create-page-submission-stored'),
	];

};
