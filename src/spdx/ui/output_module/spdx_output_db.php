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
function Spdx_output_attribution($SID) {
	session_id($SID);
	session_start();
	include('../../../lib/php/common-db.php');
	$PG_CONN =  DBconnect("/etc/fossology/"); // install from package
  //$PG_CONN =  DBconnect("/usr/local/etc/fossology/"); // install from source
  getGlobalEnv("/etc/fossology/");
  global $OUTPUT_FILE;
	$UNKNOWN = 'UNKNOWN';
	$NONE = 'NONE';
	$LICENSE_NOMOS="License by Nomos.";
	$spdxId = $_SESSION['spdxId'];
	$packageInfoPk = $_SESSION['packageInfoPk'];
	$buffer = "";
	$lastLicense =
	
	
	//File Information
	//select File Information
	$sql = "select * from spdx_file_info 
		where package_info_fk = '$packageInfoPk'
		and  spdx_fk = '$spdxId'
		ORDER By license_concluded DESC , filename";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  
  $buffer.= "License,File Name,File Type,License Concluded,License Info In File,License Comments,File Copyright Text,File Comment\r\n";
	while ($fileInfo = pg_fetch_assoc($result))
	{
		if($fileInfo['license_concluded'] != $lastLicense){
			$buffer.=",,,,,,,\r\n";
			$buffer.=$fileInfo['license_concluded'].",,,,,,,\r\n";					
		}
		$buffer.= checkCsvQuotes($fileInfo['license_concluded']).",";
		$buffer.= checkCsvQuotes($fileInfo['filename']).",";
		$buffer.= checkCsvQuotes($fileInfo['filetype']).",";
		$buffer.= checkCsvQuotes($fileInfo['license_concluded']).",";
		$buffer.= checkCsvQuotes($fileInfo['license_info_in_file']).",";
		$buffer.= checkCsvQuotes($fileInfo['license_comment']).",";
		$buffer.= checkCsvQuotes($fileInfo['file_copyright_text']).",";
		$buffer.= checkCsvQuotes($fileInfo['file_comment'])."\r\n";			    
		$lastLicense =$fileInfo['license_concluded'];
	}	
	$licenses = array();

  pg_free_result($result);
	
	//select license info and license name
	$sql = "select * from spdx_extracted_lic_info
		where spdx_fk = '$spdxId'";
		
	$result = pg_query($PG_CONN, $sql);
	DBCheckResult($result, $sql, __FILE__, __LINE__);
  
	while ($fileInfo = pg_fetch_assoc($result))
	{
		$licenses[$fileInfo['identifier']] = $fileInfo['license_display_name'];
	}	
	
	foreach ($licenses as $key => $value){
		$pattern = '/LicenseRef-'.$key.'/';
		$replacement = "$value";
		$buffer = preg_replace($pattern, $replacement, $buffer);
	}
	
  $fileSuffix = $_SESSION['fileSuffix'];
	if ( strlen($buffer) == 0){
		$buffer = $NOVALIDINFO;
	}
	//write tag file
	//WriteFile($buffer,'/../output_file/attribution'.$fileSuffix.'.csv');
	WriteFile($buffer,$OUTPUT_FILE.'/attribution'.$fileSuffix.'.csv');
}

function checkCsvQuotes($string) {
    if (strpos($string,'"') !== false) {
        return '"'.str_replace('"','""',$string).'"';
    } elseif (strpos($string,',') !== false || strpos($string,"\n") !== false) {
        return '"'.$string.'"';
    } else {
        return $string;
    }
}

function Spdx_output_notice($SID) {
	session_id($SID);
	session_start();
	include('../../../lib/php/common-db.php');
	$PG_CONN =  DBconnect("/etc/fossology/"); // install from package
  //$PG_CONN =  DBconnect("/usr/local/etc/fossology/"); // install from source
  getGlobalEnv("/etc/fossology/");
  global $OUTPUT_FILE;
	$UNKNOWN = 'UNKNOWN';
	$NONE = 'NONE';
	$LICENSE_NOMOS="License by Nomos.";
	$spdxId = $_SESSION['spdxId'];
	$packageInfoPk = $_SESSION['packageInfoPk'];
	$buffer = "";
	//select License Name
	$sql = "select distinct(lic_list.lic_name) as lic_name
				from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list
				where 'LicenseRef-'<> substring(lic_list.lic_name from 1 for 11)
				UNION
				select distinct(spdx_extracted_lic_info.license_display_name) as lic_name
				from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list, spdx_extracted_lic_info
				where concat('LicenseRef-',spdx_extracted_lic_info.identifier) = lic_list.lic_name
				and spdx_extracted_lic_info.spdx_fk = $spdxId";
  $resultLicName = pg_query($PG_CONN, $sql);
  DBCheckResult($resultLicName, $sql, __FILE__, __LINE__);
  while ($LicName = pg_fetch_assoc($resultLicName))
  {
  	$buffer.= "This package contains ".$LicName[lic_name]."\r\n\r\n";
  }
  pg_free_result($resultLicName);
  //select License Text
	$sql = "select distinct(rf_text) as lic_text
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list,license_ref,spdx_license_list
					where 'LicenseRef-'<> substring(lic_list.lic_name from 1 for 11)
					and lic_list.lic_name = spdx_license_list.license_identifier 
					and (spdx_license_list.license_matchname_1 = license_ref.rf_shortname or spdx_license_list.license_matchname_2 = license_ref.rf_shortname or spdx_license_list.license_matchname_3 = license_ref.rf_shortname )
					UNION
					select distinct(rf_text) as lic_text
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list, spdx_extracted_lic_info, license_ref
					where spdx_extracted_lic_info.licensename = license_ref.rf_shortname
					and spdx_extracted_lic_info.spdx_fk = $spdxId";
  $resultLicText = pg_query($PG_CONN, $sql);
  DBCheckResult($resultLicText, $sql, __FILE__, __LINE__);
  while ($LicText = pg_fetch_assoc($resultLicText))
  {
  	if ($LicText[lic_text] != "License by Nomos." )
  	{
  		$buffer.= $LicText[lic_text]."\r\n\r\n";
  	}
  }
  pg_free_result($resultLicText);
  $fileSuffix = $_SESSION['fileSuffix'];
	if ( strlen($buffer) == 0){
		$buffer = $NOVALIDINFO;
	}
	//write NOTICE1 file
	//WriteFile($buffer,'/../output_file/NOTICE'.$fileSuffix);
	WriteFile($buffer,$OUTPUT_FILE.'/NOTICE'.$fileSuffix);
}
function Spdx_output_notice2($SID) {
	session_id($SID);
	session_start();
	include('../../../lib/php/common-db.php');
	$PG_CONN =  DBconnect("/etc/fossology/"); // install from package
  //$PG_CONN =  DBconnect("/usr/local/etc/fossology/"); // install from source
  getGlobalEnv("/etc/fossology/");
  global $OUTPUT_FILE;
	$UNKNOWN = 'UNKNOWN';
	$NONE = 'NONE';
	$LICENSE_NOMOS="License by Nomos.";
	$spdxId = $_SESSION['spdxId'];
	$packageInfoPk = $_SESSION['packageInfoPk'];
	$buffer = "";
	//select PackageLicenseInfoFromFiles
	$sql = "select regexp_split_to_table((select * from (select license_info_from_files from spdx_package_info 
					where spdx_fk=$spdxId and package_info_pk =$packageInfoPk ) as T), E',') as packagelicenseinfofromfiles";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  while ($list = pg_fetch_assoc($result))
  {
  	$buffer.= "PackageLicenseInfoFromFiles: ".$list[packagelicenseinfofromfiles]."\r\n";
  }
  pg_free_result($result);
	//select License Name and License Id
	$sql = "select distinct(lic_list.lic_name) as lic_name, '' as lic_id
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list--license_ref
					where 'LicenseRef-'<> substring(lic_list.lic_name from 1 for 11)
					UNION
					select distinct(spdx_extracted_lic_info.license_display_name) as lic_name, concat('LicenseRef-',spdx_extracted_lic_info.identifier) as lic_id
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list, spdx_extracted_lic_info--license_ref
					where concat('LicenseRef-',spdx_extracted_lic_info.identifier) = lic_list.lic_name
					and spdx_extracted_lic_info.spdx_fk = $spdxId";
  $resultLicNameId = pg_query($PG_CONN, $sql);
  DBCheckResult($resultLicNameId, $sql, __FILE__, __LINE__);
  while ($LicNameId = pg_fetch_assoc($resultLicNameId))
  {
  	if (!empty($LicNameId[lic_id]))
  	{
  		$buffer.= "LicenseID: ".$LicNameId[lic_id]."\r\n";
  		$buffer.= "This package contains ".$LicNameId[lic_name]."\r\n\r\n";
  	}
  	else
  	{
  		$buffer.= "This package contains ".$LicNameId[lic_name]."\r\n\r\n";
  	}
  }
  pg_free_result($resultLicNameId);
  //select License Text
	$sql = "select distinct(rf_text) as lic_text
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list,license_ref,spdx_license_list
					where 'LicenseRef-'<> substring(lic_list.lic_name from 1 for 11)
					and lic_list.lic_name = spdx_license_list.license_identifier 
					and (spdx_license_list.license_matchname_1 = license_ref.rf_shortname or spdx_license_list.license_matchname_2 = license_ref.rf_shortname or spdx_license_list.license_matchname_3 = license_ref.rf_shortname )
					UNION
					select distinct(rf_text) as lic_text
					from (select regexp_split_to_table((select * from (SELECT license_info_from_files from spdx_package_info where spdx_fk=$spdxId) as T), E',') as lic_name) as lic_list, spdx_extracted_lic_info, license_ref
					where spdx_extracted_lic_info.licensename = license_ref.rf_shortname
					and spdx_extracted_lic_info.spdx_fk = $spdxId";
  $resultLicText = pg_query($PG_CONN, $sql);
  DBCheckResult($resultLicText, $sql, __FILE__, __LINE__);
  while ($LicText = pg_fetch_assoc($resultLicText))
  {
  	if ($LicText[lic_text] != "License by Nomos." )
  	{
  		$buffer.= $LicText[lic_text]."\r\n\r\n";
  	}
  }
  pg_free_result($resultLicText);
  $fileSuffix = $_SESSION['fileSuffix'];
	if ( strlen($buffer) == 0){
		$buffer = $NOVALIDINFO;
	}
	//write NOTICE2 file
	//WriteFile($buffer,'/../output_file/NOTICE2'.$fileSuffix);
	WriteFile($buffer,$OUTPUT_FILE.'/NOTICE2'.$fileSuffix);
}
function Spdx_output_tag($SID) {
	session_id($SID);
	session_start();
	include('../../../lib/php/common-db.php');
	$PG_CONN =  DBconnect("/etc/fossology/"); // install from package
  //$PG_CONN =  DBconnect("/usr/local/etc/fossology/"); // install from source
  //global $OUTPUT_FILE;
  //$OUTPUT_FILE = getenv('OUTPUT_FILE');
  getGlobalEnv("/etc/fossology/");
  global $OUTPUT_FILE;
	$UNKNOWN = 'UNKNOWN';
	$NONE = 'NONE';
	$LICENSE_NOMOS="License by Nomos.";
	$spdxId = $_SESSION['spdxId'];
	$packageInfoPk = $_SESSION['packageInfoPk'];
	$buffer = "";
	
	//select Creation Information
	$sql = "SELECT * from spdx_file where spdx_pk=$spdxId";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  while ($creationInfo = pg_fetch_assoc($result))
  {
    $buffer = $buffer.'SPDXVersion: '.$creationInfo["version"]."\r\n";
		$buffer = $buffer.'DataLicense: '.$creationInfo["data_license"]."\r\n";
		$buffer = $buffer.'DocumentComment: <text>'.$creationInfo["document_comment"]."</text>\r\n";
		$buffer = $buffer."\r\n## Creation Information\r\n";
		$creatorInfoArr = explode("\r\n",$creationInfo["creator"]);
		foreach ($creatorInfoArr as $creatorInfo)
		{
			$buffer = $buffer.'Creator: '.$creatorInfo."\r\n";
		}
		$createdDate = str_replace(" ","T",$creationInfo["created_date"])."Z";
		$buffer = $buffer.'Created: '.$createdDate."\r\n";
		$buffer = $buffer.'CreatorComment: <text>'.$creationInfo["creator_comment"]."</text>\r\n";
  }
  pg_free_result($result);
	//Package Information
	$buffer = $buffer."\r\n## Package Information\r\n";
  //select Package Information
	$sql = "SELECT * from spdx_package_info 
	        where package_info_pk=$packageInfoPk and spdx_fk=$spdxId
	        order by name";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  while ($packageInfo = pg_fetch_assoc($result))
  {
  	$buffer = $buffer.'PackageName: '.$packageInfo["name"]."\r\n";
		$buffer = $buffer.'PackageVersion: '.$packageInfo["version"]."\r\n";
		$buffer = $buffer.'PackageDownloadLocation: '.IsNONE($packageInfo["download_location"])."\r\n";
		$buffer = $buffer.'PackageSummary: <text>'.$packageInfo["summary"]."</text>\r\n";
		$buffer = $buffer.IsOptionalItem('PackageSourceInfo: ',$packageInfo["source_info"],"\r\n");
		$buffer = $buffer.'PackageFileName: '.$packageInfo["filename"]."\r\n";
		$buffer = $buffer.'PackageSupplier: '.$packageInfo["supplier_type"].IsNOASSERTION($packageInfo["supplier"])."\r\n";
		$buffer = $buffer.'PackageOriginator: '.$packageInfo["originator_type"].IsNOASSERTION($packageInfo["originator"])."\r\n";
		$buffer = $buffer.'PackageChecksum: SHA1: '.strtolower($packageInfo["checksum"])."\r\n"; //not perform the following(If the SPDX file is to be included in a package, this value should not be calculated).
		$buffer = $buffer.'PackageVerificationCode: '.$packageInfo["verificationcode"];
		if (!empty($packageInfo["verificationcode_excludedfiles"]))
		{
			$buffer = $buffer.'(excludes: '.$packageInfo["verificationcode_excludedfiles"].")\r\n";
		}
		else
		{
			$buffer = $buffer."\r\n";
		}
		$buffer = $buffer.'PackageDescription: <text>'.$packageInfo["description"]."</text>\r\n\r\n";
		$buffer = $buffer.'PackageCopyrightText: <text>'.IsNONE($packageInfo["package_copyright_text"])."</text>\r\n\r\n";
		$buffer = $buffer.'PackageLicenseDeclared: '.IsNONEParenthesis($packageInfo["license_declared"])."\r\n";
		$buffer = $buffer.'PackageLicenseConcluded: '.IsNONEParenthesis($packageInfo["license_concluded"])."\r\n";
		$packageLicenseInfoFromFilesArr = explode(",",$packageInfo["license_info_from_files"]);
		if (count($packageLicenseInfoFromFilesArr)>1)
		{
			foreach ($packageLicenseInfoFromFilesArr as $packageLicenseInfoFromFiles)
			{
				if (!empty($packageLicenseInfoFromFiles))
				{
					$buffer = $buffer.'PackageLicenseInfoFromFiles: '.$packageLicenseInfoFromFiles."\r\n";
				}
			}
		}
		else
		{
			$buffer = $buffer.'PackageLicenseInfoFromFiles: '.IsNONE($packageLicenseInfoFromFilesArr[0])."\r\n";
		}
		$buffer = $buffer.IsOptionalItem('PackageLicenseComments: <text>',$packageInfo["license_comment"],"</text>\r\n");
  }
  pg_free_result($result);
	//File Information
	$buffer = $buffer."\r\n## File Information\r\n";
	//select File Information
	$sql = "SELECT * from spdx_file_info 
	        where package_info_fk=$packageInfoPk and spdx_fk=$spdxId
	        order by filename";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  while ($fileInfo = pg_fetch_assoc($result))
  {
  	$buffer = $buffer."\r\nFileName: ".$fileInfo["filename"]."\r\n";
		$buffer = $buffer.'FileType: '.$fileInfo["filetype"]."\r\n";
		$buffer = $buffer.'FileChecksum: SHA1: '.strtolower($fileInfo["checksum"])."\r\n";
		$buffer = $buffer.'LicenseConcluded: '.IsNONEParenthesis($fileInfo["license_concluded"])."\r\n";

		$licenseInfoInFileArr = explode(",",$fileInfo["license_info_in_file"]);
		if (count($licenseInfoInFileArr)>1)
		{
			foreach ($licenseInfoInFileArr as $licenseInfoInFile)
			{
				if (!empty($licenseInfoInFile))
				{
					$buffer = $buffer.'LicenseInfoInFile: '.$licenseInfoInFile."\r\n";
				}
			}
		}
		else
		{
			$buffer = $buffer.'LicenseInfoInFile: '.IsNONE($licenseInfoInFileArr[0])."\r\n";
		}
		$buffer = $buffer.'FileCopyrightText: <text>'.IsNONE($fileInfo["file_copyright_text"])."</text>\r\n";
		$buffer = $buffer.IsOptionalItem('ArtifactOfProjectName: ',$fileInfo["artifact_of_project"],"\r\n");
		$buffer = $buffer.IsOptionalItem('ArtifactOfProjectHomePage: ',$fileInfo["artifact_of_homepage"],"\r\n");
		$buffer = $buffer.IsOptionalItem('ArtifactOfProjectURI: ',$fileInfo["artifact_of_url"],"\r\n");
		$buffer = $buffer.IsOptionalItem('FileComment: <text>',$fileInfo["file_comment"],"</text>\r\n");
		
  }
  pg_free_result($result);
	//License Information
	$buffer = $buffer."\r\n## License Information\r\n";
  //select Extracted License Information
	$sql = "SELECT identifier,
								 license_display_name as licensename,
	               cross_ref_url,
	               lic_comment,
	               rf_text
	        from spdx_extracted_lic_info, license_ref
	        where spdx_fk=$spdxId and rf_shortname = licensename
	        order by identifier";
  $result = pg_query($PG_CONN, $sql);
  DBCheckResult($result, $sql, __FILE__, __LINE__);
  while ($extractedLicenseInfo = pg_fetch_assoc($result))
  {
    $buffer = $buffer."\r\nLicenseID: LicenseRef-".$extractedLicenseInfo["identifier"]."\r\n";
    if ($extractedLicenseInfo['rf_text'] == $LICENSE_NOMOS)
    {
    	$formatedRftest = "Please see online publication for the full text of this license";
    }
    else
    {
    	$rule = '/[' . chr ( 1 ) . '-' . chr ( 8 ) . chr ( 11 ) . '-' . chr ( 12 ) . chr ( 14 ) . '-' . chr ( 31 ) . ']*/';
    	$formatedRftest = str_replace ( chr ( 0 ), '', preg_replace ( $rule, '', $extractedLicenseInfo['rf_text'] ) );
    }
		$buffer = $buffer.'ExtractedText: <text>'.$formatedRftest."</text>\r\n";
		$buffer = $buffer.'LicenseName: '.IsNOASSERTION($extractedLicenseInfo["licensename"])."\r\n";
		$buffer = $buffer.IsOptionalItem('LicenseCrossReference: ',$extractedLicenseInfo["cross_ref_url"],"\r\n");
		$buffer = $buffer.IsOptionalItem('LicenseComment: ',$extractedLicenseInfo["lic_comment"],"\r\n");
  }
  pg_free_result($result);
  $fileSuffix = $_SESSION['fileSuffix'];
	if ( strlen($buffer) == 0){
		$buffer = $NOVALIDINFO;
	}
	//write tag file
	//WriteFile($buffer,'/../output_file/spdx'.$fileSuffix.'.tag');
	WriteFile($buffer,$OUTPUT_FILE.'/spdx'.$fileSuffix.'.tag');
}
function WriteFile($buffer,$filename)
{
	//$file = dirname(__FILE__).$filename;
	$file = $filename;
	touch($file);
	$fh = fopen($file,'w');
	fwrite($fh,$buffer);
	fclose($fh);
}
function IsNONE($v)
{
  if (!empty($v))
  {
    return $v;
  }
  else
  {
  	return 'NONE';
  }
}
function IsNOASSERTION($v)
{
  if (!empty($v))
  {
    return $v;
  }
  else
  {
  	return 'NOASSERTION';
  }
}
function IsNONEParenthesis($v)
{
  if (!empty($v))
  {
  	$lower = strtolower($v);
		if ((count(explode(" or ",$lower))>1) || (count(explode(" and ",$lower))>1))
		{
			return "(".$v.")";
		}
		else
		{
			return $v;
		}
  }
  else
  {
  	return 'NONE';
  }
}
function IsOptionalItem($label1,$v,$label2)
{
	if (!empty($v))
  {
  	return $label1.$v.$label2;
  }
  else
  {
  	return '';
  }
}
//copy from bootstrap.php
function getGlobalEnv($sysconfdir="")
{
  $rcfile = "fossology.rc";

  if (empty($sysconfdir))
  {
    $sysconfdir = getenv('SYSCONFDIR');
    if ($sysconfdir === false)
    {
      if (file_exists($rcfile)) $sysconfdir = file_get_contents($rcfile);
      if ($sysconfdir === false)
      {
        /* NO SYSCONFDIR specified */
        $text = _("FATAL! System Configuration Error, no SYSCONFDIR.");
        echo "$text\n";
        exit(1);
      }
    }
  }

  $sysconfdir = trim($sysconfdir);
  $GLOBALS['SYSCONFDIR'] = $sysconfdir;

  /*************  Parse fossologyspdx.conf *******************/
  $ConfFile = "{$sysconfdir}/fossologyspdx.conf";
  if (!file_exists($ConfFile))
  {
    $text = _("FATAL! Missing configuration file: $ConfFile");
    echo "$text\n";
    exit(1);
  }
  $SysConf = parse_ini_file($ConfFile, true);
  if ($SysConf === false)
  {
    $text = _("FATAL! Invalid configuration file: $ConfFile");
    echo "$text\n";
    exit(1);
  }

  /* evaluate all the DIRECTORIES group for variable substitutions.
   * For example, if PREFIX=/usr/local and BINDIR=$PREFIX/bin, we
   * want BINDIR=/usr/local/bin
   */
  foreach($SysConf['DIRECTORIES'] as $var=>$assign)
  {
    /* Evaluate the individual variables because they may be referenced
     * in subsequent assignments.
     */
    $toeval = "\$$var = \"$assign\";";
    eval($toeval);

    /* now reassign the array value with the evaluated result */
    $SysConf['DIRECTORIES'][$var] = ${$var};
    $GLOBALS[$var] = ${$var};
  }
  require_once("$MODDIR/www/ui/template/template-plugin.php");
  require_once("$MODDIR/lib/php/common.php");
  return $SysConf;
}
?>
