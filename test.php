<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');


$result = $DB->get_records_sql('SELECT id, course, path_to_file FROM {directlink} ORDER BY course ASC');

foreach($result as $row) {
	$path = decrypt($row->path_to_file);
	if($path == '') {
		continue;
	}
	$color = '';
	if(strpos($path, '/mnt/directlink') !== 0) {
		$color = 'red';
		if($row->course == 18) {
			echo "needs update ... ";
			$crypt = encrypt($path);
			$DB->execute("UPDATE {directlink} SET path_to_file = '{$crypt}' WHERE id = '{$row->id}'");
		}
	}
	echo "{$row->course} - {$row->id} - <font color='{$color}'>{$path}</font><br />";
}