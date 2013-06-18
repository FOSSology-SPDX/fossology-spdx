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
function getVerificationCode($SpdxId,$packageId,$excludedfiles) {
		global $PG_CONN;
		
		// select spdx_file_info table
		$sql = "select filename,checksum from spdx_file_info, pfile,uploadtree
		            where spdx_fk = $SpdxId
		            	and package_info_fk = $packageId
						and checksum = pfile_sha1
		            	and pfile_pk = pfile_fk
		            	and (ufile_mode & (1<<29) = 0)
		            	group by filename,checksum";

		$filesSha1Result = pg_query($PG_CONN, $sql);
    DBCheckResult($filesSha1Result, $sql, __FILE__, __LINE__);
		while ($row = pg_fetch_assoc($filesSha1Result))
		{
			if (empty($excludedfiles))
			{
				$templist[] = $row['checksum'];
			}
			else
			{
				//get excluded files
				$excludedfileArr = explode(",",$excludedfiles);
				$match = false;
				foreach ($excludedfileArr as $excludedfile)
				{
					//check if *.suffix, e.g. *.spdx
					if(substr($excludedfile,0,2) =="*.")
					{
						if ( substr($excludedfile,2) == substr(strrchr($row['filename'],"."),1))
						{
							$match = true;
						}
					}
					else
					{
						//check full path
						if ($excludedfile == $row['filename'])
						{
							$match = true;
						}
					}
				}
				if ($match == false)
				{
					$templist[] = $row['checksum'];
				}
			}
		}
		sort($templist);
		$num = count($templist); 
		for($i=0;$i < $num;++$i)
		{ 
			$filelist = $filelist.$templist[$i]; 
		} 
		$verificationCode = SHA1(strtolower($filelist));
		pg_result_seek($filesSha1Result, 0);
		return $verificationCode;
}
function Spdx_update_extractedLic($SpdxId,$Identifier) {
		global $PG_CONN;
		$license_display_name = htmlentities(GetParm('licensename',PARM_TEXT),ENT_QUOTES);
		$cross_ref_url = htmlentities(GetParm('crossReferenceURLs',PARM_TEXT),ENT_QUOTES);
		$lic_comment = htmlentities(GetParm('licensecomment',PARM_TEXT),ENT_QUOTES);
		
		// update spdx_extracted_lic_info table
		$sql = "update spdx_extracted_lic_info
		            set (license_display_name,cross_ref_url,lic_comment)
		            = ('$license_display_name','$cross_ref_url','$lic_comment')
		            where spdx_fk = $SpdxId
		            	and Identifier = $Identifier";
		$resultUpdateExtractedLic = pg_query($PG_CONN, $sql);
    DBCheckResult($resultUpdateExtractedLic, $sql, __FILE__, __LINE__);
    pg_free_result($resultUpdateExtractedLic);

}
function Spdx_update_package($SpdxId,$PackageInfoPk) {
		global $PG_CONN;
		$name = htmlentities(GetParm('packagename',PARM_TEXT),ENT_QUOTES);
		$version = htmlentities(GetParm('packageversion',PARM_TEXT),ENT_QUOTES);
		$filename = htmlentities(GetParm('packagefileName',PARM_TEXT),ENT_QUOTES);
		$supplier_type = htmlentities(GetParm('supplier',PARM_TEXT),ENT_QUOTES);
		$supplier = htmlentities(GetParm('packagesupplier',PARM_TEXT),ENT_QUOTES);
		$originator_type = htmlentities(GetParm('originator',PARM_TEXT),ENT_QUOTES);
		$originator = htmlentities(GetParm('packageoriginator',PARM_TEXT),ENT_QUOTES);
		$download_location = htmlentities(GetParm('packagedownloadlocation',PARM_TEXT),ENT_QUOTES);
		$checksum = htmlentities(GetParm('packagechecksum',PARM_TEXT),ENT_QUOTES);
		$verificationcode = htmlentities(GetParm('packageverificationcode',PARM_TEXT),ENT_QUOTES);
		$verificationcode_excludedfiles = htmlentities(GetParm('vcExcludedfiles',PARM_TEXT),ENT_QUOTES);
		$source_info = htmlentities(GetParm('sourceinfo',PARM_TEXT),ENT_QUOTES);
		$license_declared = htmlentities(GetParm('licensedeclared',PARM_TEXT),ENT_QUOTES);
		$license_concluded = htmlentities(GetParm('licenseconcluded',PARM_TEXT),ENT_QUOTES);
		$license_info_from_files = htmlentities(GetParm('licenseinfofromfiles',PARM_TEXT),ENT_QUOTES);
		$license_comment = htmlentities(GetParm('licensecomment',PARM_TEXT),ENT_QUOTES);
		$package_copyright_text = htmlentities(GetParm('packagecopyrighttext',PARM_TEXT),ENT_QUOTES);
		$summary = htmlentities(GetParm('summary',PARM_TEXT),ENT_QUOTES);
		$description = htmlentities(GetParm('description',PARM_TEXT),ENT_QUOTES);
		
		// update spdx_package_info table
		$sql = "update spdx_package_info
		            set (name,version,filename,supplier_type,supplier,originator_type,originator,download_location,checksum,verificationcode,verificationcode_excludedfiles,source_info,license_declared,license_concluded,license_info_from_files,license_comment,package_copyright_text,summary,description)
		            = ('$name','$version','$filename','$supplier_type','$supplier','$originator_type','$originator','$download_location','$checksum','$verificationcode','$verificationcode_excludedfiles','$source_info','$license_declared','$license_concluded','$license_info_from_files','$license_comment','$package_copyright_text','$summary','$description')
		            where spdx_fk = $SpdxId and package_info_pk = $PackageInfoPk";
		$resultUpdatePackage = pg_query($PG_CONN, $sql);
    DBCheckResult($resultUpdatePackage, $sql, __FILE__, __LINE__);
    pg_free_result($resultUpdatePackage);
    // update spdx_file table
		$sql = "update spdx_file
			          set (verificationcode)
			          = ('$verificationcode')
			          where spdx_pk = $SpdxId";
		$resultUpdateSPDX = pg_query($PG_CONN, $sql);
    DBCheckResult($resultUpdateSPDX, $sql, __FILE__, __LINE__);
    pg_free_result($resultUpdateSPDX);

}

function Spdx_update_file($SpdxId,$FileInfoPk){
		global $PG_CONN;
		$filename = htmlentities(GetParm('filename',PARM_TEXT),ENT_QUOTES);
		$filetype = htmlentities(GetParm('filetype',PARM_TEXT),ENT_QUOTES);
		$checksum = htmlentities(GetParm('checksum',PARM_TEXT),ENT_QUOTES);
		$license_concluded = htmlentities(GetParm('licenseConcluded',PARM_TEXT),ENT_QUOTES);
		$license_info_in_file = htmlentities(GetParm('licenseInfoInFile',PARM_TEXT),ENT_QUOTES);
		$license_comment = htmlentities(GetParm('licenseComment',PARM_TEXT),ENT_QUOTES);
		$file_copyright_text = htmlentities(GetParm('fileCopyrightText',PARM_TEXT),ENT_QUOTES);
		$artifact_of_project = htmlentities(GetParm('artifactOfProject',PARM_TEXT),ENT_QUOTES);
		$artifact_of_homepage = htmlentities(GetParm('artifactOfHomepage',PARM_TEXT),ENT_QUOTES);
		$artifact_of_url = htmlentities(GetParm('artifactOfUrl',PARM_TEXT),ENT_QUOTES);
		$file_comment = htmlentities(GetParm('fileComment',PARM_TEXT),ENT_QUOTES);
		
		// update spdx_file_info table
		$sql = "update spdx_file_info
		            set (filename,filetype,checksum,license_concluded,license_info_in_file,license_comment,file_copyright_text,artifact_of_project,artifact_of_homepage,artifact_of_url,file_comment)
		            = ('$filename','$filetype','$checksum','$license_concluded','$license_info_in_file','$license_comment','$file_copyright_text','$artifact_of_project','$artifact_of_homepage','$artifact_of_url','$file_comment')
		            where spdx_fk = $SpdxId and file_info_pk = $FileInfoPk";
		            //echo "sql_update: ".$sql."<br>";
		$resultUpdatePackage = pg_query($PG_CONN, $sql);
    DBCheckResult($resultUpdatePackage, $sql, __FILE__, __LINE__);
    pg_free_result($resultUpdatePackage);
}
function Spdx_insert_update_spdx() {
		global $PG_CONN;
		$NOASSERTION = 'NOASSERTION';
		$NOVALIDINFO = 'No valid information';
		$UNKNOWN = 'UNKNOWN';
		$NONE = 'NONE';
		$NoLicenseFound = 'No_license_found';
		$dataLicense = 'CC0-1.0';
		session_start();
		$resultPackage = $_SESSION['spdx_pkg'];
		unset($_SESSION['spdx_pkg']);
		if(!isset($resultPackage)){
			return;
		}
		$packagePks = $_SESSION['packagePks'];
		unset($_SESSION['packagePks']);
		if(!isset($packagePks)){
			return;
		}
		$pfile=0;
		if(!empty($resultPackage)){
			DBCheckResult($resultPackage, $sql, __FILE__, __LINE__);
			while ($packages = pg_fetch_assoc($resultPackage))
			{
				$pfile = $packages[pfile_fk];
			}
		}
		pg_result_seek($resultPackage, 0);
		$spdxVersion = htmlentities(GetParm('spdxVersion', PARM_TEXT), ENT_QUOTES);
		$documentComment = htmlentities(GetParm('documentComment', PARM_TEXT), ENT_QUOTES);
		//Creation Information
		$creator = htmlentities(GetParm('creator', PARM_TEXT), ENT_QUOTES);
		$creatorOptional1 = htmlentities(GetParm('creatorOptional1', PARM_TEXT), ENT_QUOTES);
		$creatorOptional2 = htmlentities(GetParm('creatorOptional2', PARM_TEXT), ENT_QUOTES);
		$createdDate = gmdate('Y-m-d G:i:s', strtotime(htmlentities(GetParm('created_Date', PARM_TEXT), ENT_QUOTES)." ".htmlentities(GetParm('created_Time', PARM_TEXT), ENT_QUOTES)));
		$creatorComment = htmlentities(GetParm('creatorComment', PARM_TEXT), ENT_QUOTES);
		$sql = "select spdx_fk as spdx_id, package_info_pk
			            from spdx_package_info
			            where pfile_fk = $pfile";
		$resultSpdxPackage = pg_query($PG_CONN, $sql);
    DBCheckResult($resultSpdxPackage, $sql, __FILE__, __LINE__);
    while ($spdx_package_info = pg_fetch_assoc($resultSpdxPackage))
    {
    	$spdxId = $spdx_package_info[spdx_id];
    	$packageInfoPk = $spdx_package_info[package_info_pk];
    }
    pg_free_result($resultSpdxPackage);
		if (!empty($spdxId)){
			// update spdx_file table
			$sql = "update spdx_file
			            set (creator,creator_optional1,creator_optional2,created_date,document_comment,creator_comment)
			            = ('$creator','$creatorOptional1','$creatorOptional2','$createdDate','$documentComment','$creatorComment')
			            where spdx_file.spdx_pk = $spdxId";
			$resultUpdate = pg_query($PG_CONN, $sql);
      DBCheckResult($resultUpdate, $sql, __FILE__, __LINE__);
      pg_free_result($resultUpdate);
		}
		else{
			//get new spdxId
			$sql = "select max(spdx_pk) as spdx_pk from spdx_file";
			$resultMaxSpdxId = pg_query($PG_CONN, $sql);
		  DBCheckResult($resultMaxSpdxId, $sql, __FILE__, __LINE__);
		  while ($spdx_id = pg_fetch_assoc($resultMaxSpdxId))
		  {
		  	$spdxId = $spdx_id[spdx_pk];
		  }
		  if (empty($spdxId)){
		  	$spdxId = 0;
		  }
		  $spdxId = $spdxId + 1;
		  pg_free_result($resultMaxSpdxId);
			
			//Review Information
			/* available in spec 2.* */
			
			//Package Information
			if(!empty($resultPackage)){
			//Find licenses which are in SPDX License list(http://spdx.org/licenses/)
				$sql = "select rf_shortname, license_identifier
          			from (SELECT license_ref.rf_shortname, license_file.pfile_fk, spdx_license_list.license_identifier
											FROM license_file
											JOIN license_ref ON license_file.rf_fk = license_ref.rf_pk
											JOIN spdx_license_list ON (
											(spdx_license_list.license_matchname_1 = license_ref.rf_shortname) OR
											(spdx_license_list.license_matchname_2 = license_ref.rf_shortname) OR
											(spdx_license_list.license_matchname_3 = license_ref.rf_shortname)
										 )) as license_file_ref
		            right join uploadtree on license_file_ref.pfile_fk=uploadtree.pfile_fk,
		            (select upload_fk, lft, rgt from uploadtree where uploadtree_pk in ($packagePks)) T
		            where uploadtree.lft > T.lft
		            and uploadtree.rgt < T.rgt
		            and uploadtree.upload_fk = T.upload_fk
		            and rf_shortname is not NULL
		            and rf_shortname <> '$NoLicenseFound'
		            group by rf_shortname, license_identifier";
				$licenseSPDXResult = pg_query($PG_CONN, $sql);
		    DBCheckResult($licenseSPDXResult, $sql, __FILE__, __LINE__);
		    $LSPDXI = 0;
		    while ($lSPDXrow = pg_fetch_assoc($licenseSPDXResult))
		    {
		    	$licArrSpdxName[$lSPDXrow['rf_shortname']]=$lSPDXrow['license_identifier'];
		    	$licArrMatchingName[$LSPDXI]=$lSPDXrow['rf_shortname'];
		    	$LSPDXI = $LSPDXI+1;
		    }
		    if (count($licArrMatchingName)>0)
		    {
		    	$licSpdxStr = join(',',$licArrMatchingName);
		    }
		    else
		    {
		    	$licSpdxStr = '';
		    }

		    pg_free_result($licenseSPDXResult);
				//Find licenses which are NOT in SPDX License list
				$sql = "select rf_shortname , rf_text, rf_url
          			from (SELECT license_ref.rf_shortname, license_ref.rf_text,license_ref.rf_url, license_file.pfile_fk
											FROM license_file
											JOIN license_ref ON license_file.rf_fk = license_ref.rf_pk) as license_file_ref
		            right join uploadtree on license_file_ref.pfile_fk=uploadtree.pfile_fk,
		            (select upload_fk, lft, rgt from uploadtree where uploadtree_pk in ($packagePks)) T
		            where uploadtree.lft > T.lft
		            and uploadtree.rgt < T.rgt
		            and uploadtree.upload_fk = T.upload_fk
		            and rf_shortname is not NULL
		            and rf_shortname <> '$NoLicenseFound'
		            and rf_shortname not in(select regexp_split_to_table('$licSpdxStr', E','))
		            group by rf_shortname, rf_text, rf_url";

		    $licenseRefResult = pg_query($PG_CONN, $sql);
		    DBCheckResult($licenseRefResult, $sql, __FILE__, __LINE__);
		    $LRI = 0;
		    while ($lrrow = pg_fetch_assoc($licenseRefResult))
		    {
		    	$lrArr[$LRI]['Identifier']=$LRI;
		    	$lrArr[$LRI]['LicenseID']="LicenseRef-".$LRI;
		    	$lrArr[$LRI]['LRName']=$lrrow['rf_shortname'];
		    	$lrArr[$LRI]['rf_url']=$lrrow['rf_url'];
		    	$LRI = $LRI+1;
		    }
		    pg_free_result($licenseRefResult);
		    $sql = "select rf_shortname , T.uploadtree_pk
            			from (SELECT license_ref.rf_shortname, license_ref.rf_text, license_file.pfile_fk
												FROM license_file
												JOIN license_ref ON license_file.rf_fk = license_ref.rf_pk) as license_file_ref
			            right join uploadtree on license_file_ref.pfile_fk=uploadtree.pfile_fk, 
			            (select upload_fk, lft, rgt,uploadtree_pk from uploadtree where uploadtree_pk in ($packagePks))T
			            where uploadtree.lft > T.lft
			            and uploadtree.rgt < T.rgt
			            and uploadtree.upload_fk = T.upload_fk
			            and rf_shortname is not NULL
			            and rf_shortname <> '$NoLicenseFound'
			            group by rf_shortname,T.uploadtree_pk";

			  $packageLicenseRefResult = pg_query($PG_CONN, $sql);
			  DBCheckResult($packageLicenseRefResult, $sql, __FILE__, __LINE__);
			  $currentPackage = "";
			  while ($packageLrrow = pg_fetch_assoc($packageLicenseRefResult))
			  {
			  	if ($currentPackage != $packageLrrow['uploadtree_pk'])
			  	{
			  		$currentPackage = $packageLrrow['uploadtree_pk'];
			  	}
			  	$pLicenseID = $packageLrrow['rf_shortname'];
			  	foreach ($lrArr as $lr)
			  	{
			  		if ($lr['LRName'] == $pLicenseID)
			  		{
			  			$pLicenseID = $lr['LicenseID'];
						}
						else
			  		{
			  			if(!empty($licArrSpdxName[$pLicenseID]))
			  			{
			  				$pLicenseID = $licArrSpdxName[$pLicenseID];
			  			}
			  		}
			  	}
			  	if (empty($packageLicenseConcludedArr[$currentPackage]))
					{
						$packageLicenseConcludedArr[$currentPackage] = $pLicenseID;
					}
					else
					{
						$packageLicenseConcludedArr[$currentPackage] = $packageLicenseConcludedArr[$currentPackage]." AND ".$pLicenseID;
					}
					$packageLicenseInfoFromFilesArr[$currentPackage][] = $pLicenseID;
				}
				pg_free_result($packageLicenseRefResult);
				while ($packages = pg_fetch_assoc($resultPackage))
				{
					$packageName = $packages['name'];
					$packageVersion = $packages['version'];
					$packageDownloadLocation = "";
					$packageSummary = "";
					$packageSourceinfo = $packages['source_info'];
					$packageFileName = $packages['filename'];
					$packageSupplier = "";
					$packageOriginator = "";
					$packageChecksum = strtolower($packages['pfile_sha1']);
					//default excluded files
					$excludesFile = "*.spdx";
					$packageLicComment = "";
					$sql = "select pfile_sha1,pfile_pk,ufile_name from uploadtree left outer join pfile on uploadtree.pfile_fk = pfile.pfile_pk,
							  (select upload_fk, lft, rgt from uploadtree where uploadtree_pk in ($packages[uploadtree_pk])) T
			            where uploadtree.lft > T.lft
			            and uploadtree.rgt < T.rgt
			            and uploadtree.upload_fk = T.upload_fk
									and pfile_fk <> 0
									and ((ufile_mode & (1<<28)) = 0)
									and parent is not null";
					$filesSha1Result = pg_query($PG_CONN, $sql);
					DBCheckResult($filesSha1Result, $sql, __FILE__, __LINE__);
				
					pg_result_seek($filesSha1Result, 0);
					while ($row = pg_fetch_assoc($filesSha1Result))
					{
						if ( substr($excludesFile,2) != substr(strrchr($row['ufile_name'],"."),1))
						{
						$templist[] = strtolower($row['pfile_sha1']);
					}
					}
					sort($templist);
					$num = count($templist); 
					for($i=0;$i < $num;++$i)
					{ 
						$filelist = $filelist.$templist[$i]; 
					} 
					$verificationCode = SHA1($filelist);
					$packageVerificationCode = $verificationCode;
					$packageDescription = $packages['description'];
					$packageCopyrightText = "";
					$packageLicenseDeclared = "";
					$packageLicenseInfoFromFiles = "";
					if (isset($packageLicenseInfoFromFilesArr[$packages['uploadtree_pk']]))
  				{
  					foreach($packageLicenseInfoFromFilesArr[$packages['uploadtree_pk']] as $packageLIFF)
  					{
  						if ($packageLicenseInfoFromFiles == "")
  						{
  							$packageLicenseInfoFromFiles = $packageLIFF;
  						}
  						else{
  							$packageLicenseInfoFromFiles = $packageLicenseInfoFromFiles.','.$packageLIFF;
  						}
  					}
  				}
  				else
  				{
  					$packageLicenseInfoFromFiles = "";
  				}
  				if (isset($packageLicenseConcludedArr[$packages['uploadtree_pk']]))
  				{
  					$packageLicenseConcluded = $packageLicenseConcludedArr[$packages['uploadtree_pk']];
  				}
					else
					{
						$packageLicenseConcluded = "";
					}
					//regarding to feedback from 2013bakeoff, concluded license should be empty,meanwhile scanned license information will be shown in Declared license. So here we exchange these
					$packageLicenseDeclared = $packageLicenseConcluded;
					$packageLicenseConcluded = "";

					//get new package_info_pk
					$sql = "select max(package_info_pk) as package_info_pk from spdx_package_info";
					$resultMaxPk = pg_query($PG_CONN, $sql);
				  DBCheckResult($resultMaxPk, $sql, __FILE__, __LINE__);
				  while ($package_info_pk = pg_fetch_assoc($resultMaxPk))
				  {
				  	$packageInfoPk = $package_info_pk[package_info_pk];
				  }
				  $packageInfoPk = $packageInfoPk + 1;
				  pg_free_result($resultMaxPk);
					//insert spdx_package_info table
					$sql = "insert into spdx_package_info (package_info_pk,pfile_fk,name,version,filename,supplier_type,supplier,originator_type,originator,download_location,checksum,verificationcode,verificationcode_excludedfiles,source_info,license_declared,license_concluded,license_info_from_files,license_comment,package_copyright_text,summary,description,spdx_fk)
						            values ($packageInfoPk,$pfile,'$packageName','$packageVersion','$packageFileName','','$packageSupplier','','$packageOriginator','$packageDownloadLocation','$packageChecksum','$packageVerificationCode','$excludesFile','$packageSourceinfo','$packageLicenseDeclared','$packageLicenseConcluded','$packageLicenseInfoFromFiles','$packageLicComment','$packageCopyrightText','$packageSummary','$packageDescription',$spdxId)";
		      $resultInsert = pg_query($PG_CONN, $sql);
		      DBCheckResult($resultInsert, $sql, __FILE__, __LINE__);
		      pg_free_result($resultInsert);
				}
			}
			else
			{
			}
			pg_free_result($resultPackage);
			//insert spdx_file table
			$sql = "insert into spdx_file (spdx_pk,version,data_license,document_comment,creator,creator_optional1,creator_optional2,created_date,creator_comment,verificationcode)
				            values ($spdxId,'$spdxVersion','$dataLicense','$documentComment','$creator','$creatorOptional1','$creatorOptional2','$createdDate','$creatorComment','$packageVerificationCode')";
      $resultInsert = pg_query($PG_CONN, $sql);
      DBCheckResult($resultInsert, $sql, __FILE__, __LINE__);
      pg_free_result($resultInsert);
      
			//File Information
			/* get the top of tree */
	    $sql = "SELECT upload_fk, lft, rgt, pfile_fk from uploadtree where uploadtree_pk in ($packagePks)";
	    $resultFile = pg_query($PG_CONN, $sql);
	    DBCheckResult($resultFile, $sql, __FILE__, __LINE__);
	    while ($toprow = pg_fetch_assoc($resultFile))
	    {
		    /* loop through all the records in this tree */
		    $sql = "select uploadtree_pk, ufile_name, lft, rgt, pfile_sha1,pfile_mimetypefk, pfile_fk from uploadtree left outer join pfile on uploadtree.pfile_fk = pfile.pfile_pk
		              where upload_fk='$toprow[upload_fk]' 
		                    and uploadtree.pfile_fk <> 0
		                    and lft>'$toprow[lft]'  and rgt<'$toprow[rgt]'
		                    and ((ufile_mode & (1<<28)) = 0)
		                    and parent is not null";
		    $outerresult = pg_query($PG_CONN, $sql);
		    DBCheckResult($outerresult, $sql, __FILE__, __LINE__);
		    pg_result_seek($outerresult, 0);
		    while ($row = pg_fetch_assoc($outerresult))
		    {
		    	$filepatharray = Dir2Path($row['uploadtree_pk']);
		    	$filepath = "./";
		      foreach($filepatharray as $uploadtreeRow)
		      {
		        if ($filepath != "./") $filepath .= "/";
		        $filepath .= $uploadtreeRow['ufile_name'];
		      }
		      $fileName = $filepath;
		      $fileType = $row['pfile_mimetypefk'];
		      //??need confirm with SPDX team about file type mapping
		      $SOURCEArr = array(27,28,29,30,31,32,33,34,35,39,40,41,42,43,44,45,46,50,51,53,54,55,56,58,59,61,64,70,73,75,77,78,91,97,99,100,101,104);
		      $BINARYArr = array(38,47,48);
		      $ARCHIVEArr = array(1,2,3,4,8,9,10,11,12,13,14,15,16,17,18,19);
		      if (in_array( $fileType,$SOURCEArr))
		      {
					    $fileType = 'SOURCE';
					}
					else if (in_array( $fileType,$BINARYArr))
					{
							$fileType = 'BINARY';
					}
					else if (in_array( $fileType,$ARCHIVEArr))
					{
							$fileType = 'ARCHIVE';
					}
					else
					{
							$fileType = 'OTHER';
					}

		      $fileChecksum = $row['pfile_sha1'];
		      $LicArray = array();
		      $sql = "SELECT distinct(rf_shortname) as rf_shortname, rf_fk
			              from license_ref,license_file
			              where pfile_fk='$row[pfile_fk]' and rf_fk=rf_pk
			              order by rf_shortname asc";
			    $resultLicArr = pg_query($PG_CONN, $sql);
			    DBCheckResult($resultLicArr, $sql, __FILE__, __LINE__);
			    while ($rowLic = pg_fetch_assoc($resultLicArr))
				  {
				    $LicArray[$rowLic['rf_fk']] = $rowLic["rf_shortname"];
				  }
				  pg_free_result($resultLicArr);
				  if (!empty($LicArray))
					{
  					foreach( $LicArray as $key => $value )
						{
							if(!empty($licArrSpdxName[$value]))
			  			{
			  				$LicArray[$key] = $licArrSpdxName[$value];
			  			}
			  			else
			  			{
			  				if (!empty($lrArr))
								{
									foreach ( $lrArr as $lr)
			  					{
					  				if($value == $lr['LRName'])
										{
					  					$LicArray[$key] = $lr['LicenseID'];
					  				}
					  			}
				  			}
			  			}
						}
	  				$LicStr = "";
	  				$first = true;
	  				$fileLicenseInfoInFile = "";
					  foreach($LicArray as $Lic)
					  {
					    if ($first)
					    {
						  	$first = false;
						  	$delimiter = "";
						  }
					    else
					    {
					    	$LicStr .= " and ";  //default conjunctive license
					    	$delimiter = ",";
					    }
					    if ($Lic == $NoLicenseFound)
					    {
					    	$Lic = $NOASSERTION;
					    	$fileLicenseInfoInFile = "";
					    }
					    else
					    {
					    	$fileLicenseInfoInFile = $fileLicenseInfoInFile.$delimiter.$Lic;
					    }
					    $LicStr .= $Lic;
					  }
  				}
  				else
  				{
  					$LicStr = $NOASSERTION;
  					$fileLicenseInfoInFile = $LicStr;
  				}
  				$fileLicenseConcluded = $LicStr;
  				
		      $sql = "SELECT ct_pk, content 
            from copyright
            where pfile_fk='$row[pfile_fk]'
            order by content";
			    $resultCopyrightArr = pg_query($PG_CONN, $sql);
			    DBCheckResult($resultCopyrightArr, $sql, __FILE__, __LINE__);
  				$CopyrightArray = array();
				  while ($rowCopyright = pg_fetch_assoc($resultCopyrightArr))
				  {
				    $CopyrightArray[$rowCopyright['ct_pk']] = $rowCopyright["content"];
				  }
				  pg_free_result($resultCopyrightArr);
				  $CopyrightStr = "";
				  $first = true;
				  foreach($CopyrightArray as $ct)
				  {
				    $CopyrightStr .= $ct;
				  }
				  $fileCopyrightText = $CopyrightStr;
				  //insert spdx_file_info table
					$sql = "insert into spdx_file_info (filename,filetype,checksum,license_concluded,license_info_in_file,license_comment,file_copyright_text,artifact_of_project,artifact_of_homepage,artifact_of_url,file_comment,package_info_fk,spdx_fk)
						            values ('$fileName','$fileType','$fileChecksum','$fileLicenseConcluded','$fileLicenseInfoInFile','','$fileCopyrightText','','','','',$packageInfoPk,$spdxId)";
		      $resultInsert = pg_query($PG_CONN, $sql);
		      DBCheckResult($resultInsert, $sql, __FILE__, __LINE__);
		      pg_free_result($resultInsert);
		    }
		    pg_free_result($outerresult);
	  	}
	  	pg_free_result($resultFile);
	  	
	  	//Extracted License Information
			if (!empty($lrArr) )
			{
		      foreach ( $lrArr as $lr)
		      {
			      $identifier = $lr['Identifier'];
			      $licenseName = $lr['LRName'];
			      $license_display_name = $licenseName;
			      if (empty($license_display_name))
		        {
		        	$license_display_name = $NOASSERTION;
		        }
			      $licenseCrossReference = $lr['rf_url'];
				    //insert extracted lic table
				    $sql = "insert into spdx_extracted_lic_info (identifier,licensename,license_display_name,cross_ref_url,lic_comment,spdx_fk)
				            values ($identifier,'$licenseName','$license_display_name','$licenseCrossReference','',$spdxId)";
			      $resultInsert = pg_query($PG_CONN, $sql);
			      DBCheckResult($resultInsert, $sql, __FILE__, __LINE__);
			      pg_free_result($resultInsert);
			    }
		  }
		}
		$_SESSION['spdxId']=$spdxId;
		$_SESSION['packageInfoPk']=$packageInfoPk;
}
function GetPersonOrganization($type,$name,$sectionName)
{
	if ( $type == "Organization:" )
	{
		$html = "<option value='Person:'>Person:</option><option value='Organization:' selected='true'>Organization:</option></select>";
	}
	else
	{
		$html = "<option value='Person:' selected='true'>Person:</option><option value='Organization:'>Organization:</option></select>";
	}
	return $html;
}
function GetHistoryBackFormValue($DBValue,$FormValue)
{
	if (!empty($FormValue))
	{
		return $FormValue;
	}
	else
	{
		return $DBValue;
	}
}
?>
