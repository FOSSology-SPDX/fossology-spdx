<?php
/***********************************************************
 Copyright (C) 2013 University of Nebraska at Omaha.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the Apache License, Version 2.0
 as published by the Apache Software Foundation.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 Apache License for more details.
 
 You should have received a copy of the Apache License along
 with this program; if not, contact to the Apache Software Foundation.
***********************************************************/
  $fileSuffix = $_GET['fileSuffix'];
	include('spdx_output_db.php');
	Spdx_output_attribution($fileSuffix);
	$target_path = iconv('UTF-8', 'ASCII//TRANSLIT', dirname(__FILE__).'/../output_file/attribution'.$fileSuffix.'.csv');
	$filename1 = iconv('UTF-8', 'ASCII//TRANSLIT', 'attribution.csv');
	$filename = $target_path;
	//filetype
	header("Content-length: ".filesize($filename));
	header('Content-type:application/octet-stream');
	//download filename
	header('Content-Disposition: attachment; filename='.$filename1); 
	$file = fopen($filename,"r");
	echo fread($file,filesize($filename)); 
	fclose($file); 
	exec('rm -f '.$filename);
	exit();
?>
