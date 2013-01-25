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

function Spdx_main_output_spdx() {
		global $PG_CONN;
	session_start();
		$result = $_SESSION['spdx_pkg'];
		unset($_SESSION['spdx_pkg']);
		if(!isset($result)){
			return;
		}
		$packagePks = $_SESSION['packagePks'];
		unset($_SESSION['packagePks']);
		if(!isset($packagePks)){
			return;
		}
	  $EXEC = 1;
		if(isset($EXEC)){
			$NOASSERTION = 'NOASSERTION';
			$NOVALIDINFO = 'No valid information';
			$UNKNOWN = 'UNKNOWN';
			$NONE = 'NONE';
			$NoLicenseFound = 'No_license_found';
			$bufferNotice = '';
			$bufferNotice2 = '';
      $buffer = '';
      
      //spdx
			$spdxVersion = 'SPDXVersion: '.htmlentities(GetParm('spdxVersion', PARM_TEXT), ENT_QUOTES);
			$dataLicense = 'DataLicense: CC0-1.0';
			$documentComment = 'DocumentComment: <text>'.htmlentities(GetParm('documentComment', PARM_TEXT), ENT_QUOTES)."</text>\r\n";
			
			$buffer = $buffer.$spdxVersion."\r\n";
			$buffer = $buffer.$dataLicense."\r\n";
			$buffer = $buffer.$documentComment."\r\n";
			
			//Creation Information
			$creator = 'Creator: '.htmlentities(GetParm('creator', PARM_TEXT), ENT_QUOTES);
			$creatorOptional1 = 'Creator: '.htmlentities(GetParm('creatorOptional1', PARM_TEXT), ENT_QUOTES);
			$creatorOptional2 = 'Creator: '.htmlentities(GetParm('creatorOptional2', PARM_TEXT), ENT_QUOTES);
			$createdDate = 'Created: '.str_replace(" ","T",htmlentities(GetParm('createdDate', PARM_TEXT), ENT_QUOTES))."Z";
			$creatorComment = 'CreatorComment: <text>'.htmlentities(GetParm('creatorComments', PARM_TEXT), ENT_QUOTES)."</text>\r\n";
			
			$buffer = $buffer."## Creation Information\r\n";
			$buffer = $buffer.$creator."\r\n";
			$buffer = $buffer.$creatorOptional1."\r\n";
			$buffer = $buffer.$creatorOptional2."\r\n";
			$buffer = $buffer.$createdDate."\r\n";
			$buffer = $buffer.$creatorComment."\r\n";
			
			//Review Information
			/* available in spec 2.* */
			
			//Package Information
			$buffer = $buffer."## Package Information\r\n";
			if(!empty($result)){
				DBCheckResult($result, $sql, __FILE__, __LINE__);
				
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
		            group by rf_shortname, rf_text, rf_url";
		    $licenseRefResult = pg_query($PG_CONN, $sql);
		    DBCheckResult($licenseRefResult, $sql, __FILE__, __LINE__);
		    $LRI = 0;
		    while ($lrrow = pg_fetch_assoc($licenseRefResult))
		    {
		    	$lrArr[$LRI]['LicenseID']="LicenseRef-".$LRI;
		    	$lrArr[$LRI]['LRName']=$lrrow['rf_shortname'];
		    	$lrArr[$LRI]['rf_text']=$lrrow['rf_text'];
		    	$lrArr[$LRI]['rf_url']=$lrrow['rf_url'];
		    	//generate Notice file contents
		    	if ($lrrow['rf_text'] != "License by Nomos." )
		    	{
		    		$bufferNotice = $bufferNotice.$lrrow['rf_text']."\r\n\r\n";
		    	}
		    	else
		    	{
		    		$bufferNotice = $bufferNotice."This package contains ".$lrrow['rf_shortname']."\r\n\r\n";
		    	}
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
			  		$packageLicenseConcludedArr[$currentPackage] = "";
			  	}
			  	$pLicenseID = $packageLrrow['rf_shortname'];
			  	foreach ($lrArr as $lr)
			  	{
			  		if ($lr['LRName'] == $pLicenseID)
			  		{
			  			$pLicenseID = $lr['LicenseID'];
						}
			  	}
			  	if (empty($packageLicenseConcludedArr[$currentPackage]))
					{
						$packageLicenseConcludedArr[$currentPackage] = "(".$pLicenseID;
					}
					else
					{
						$packageLicenseConcludedArr[$currentPackage] = $packageLicenseConcludedArr[$currentPackage]." AND ".$pLicenseID;
					}
					$packageLicenseInfoFromFilesArr[$currentPackage][] = $pLicenseID;
				}
				pg_free_result($packageLicenseRefResult);
				while ($packages = pg_fetch_assoc($result))
				{
					$packageName = 'PackageName: '.$packages['pkg_name'];
					$packageVersion = $packages['version'];
					//available in next version 
					$packageVersion = IsNOASSERTION($packageVersion);
					
					$packageVersion = 'PackageVersion: '.$packageVersion;
					$packageDownloadLocation = 'PackageDownloadLocation: '.$NOASSERTION;
					$packageSummary = 'PackageSummary: '.$NOASSERTION;
					$packageSourceinfo = $packages['source_info'];
					if (empty($packageSourceinfo))
					{
						$packageSourceinfo = $NOASSERTION;
					}
					$packageSourceinfo = 'PackageSourceInfo: '.$packageSourceinfo;
					$packageFileName = 'PackageFileName: '.$packages['pkg_filename'];
					$packageSupplier = 'PackageSupplier: '.$NOASSERTION;
					$packageOriginator = 'PackageOriginator: '.$NOASSERTION;
					$packageChecksum = 'PackageChecksum: SHA1: '.$packages['pfile_sha1'];
					$excludesFile = '';//There is no excludes Files' information since no existing table to judge if a file is a SPDX file
					$sql = "select pfile_sha1,pfile_pk from uploadtree left outer join pfile on uploadtree.pfile_fk = pfile.pfile_pk,
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
					$templist[] = $row['pfile_sha1'];
				}
				sort($templist);
				$num = count($templist); 
				for($i=0;$i < $num;++$i)
				{ 
					$filelist = $filelist.$templist[$i]; 
				} 
					$verificationCode = SHA1($filelist);
					$packageVerifcationCode = 'PackageVerifcationCode: '.$verificationCode;  //SPDX Tool's bug? should be "PackageVerificationCode"
					$packageDescription = 'PackageDescription: <text>'.$packages['description']."</text>\r\n";
					$packageCopyrightText = 'PackageCopyrightText: <text>'.$NOASSERTION."</text>\r\n";
					$packageLicenseDeclared = 'PackageLicenseDeclared: '.$NOASSERTION;	//dummy
					$packageLC = "";
					$packageLicenseInfoFromFiles = "";
					if (isset($packageLicenseInfoFromFilesArr[$packages['uploadtree_pk']]))
  				{
  					foreach($packageLicenseInfoFromFilesArr[$packages['uploadtree_pk']] as $packageLIFF)
  					{
  						$packageLicenseInfoFromFiles = $packageLicenseInfoFromFiles.'PackageLicenseInfoFromFiles: '.$packageLIFF."\r\n";
  					}
  					//NOTICE file type2
  					$bufferNotice2 = $packageLicenseInfoFromFiles."\r\n";
  				}
  				else
  				{
  					$packageLicenseInfoFromFiles = 'PackageLicenseInfoFromFiles: '.$NOASSERTION."\r\n";
  				}
  				if (isset($packageLicenseConcludedArr[$packages['uploadtree_pk']]))
  				{
  					$packageLC = $packageLicenseConcludedArr[$packages['uploadtree_pk']].")";
  				}
					else
					{
						$packageLC = $NOASSERTION;
					}

					$packageLicenseConcluded = 'PackageLicenseConcluded: '.$packageLC; //??
					
					$buffer = $buffer.$packageName."\r\n";
					$buffer = $buffer.$packageVersion."\r\n";
					$buffer = $buffer.$packageDownloadLocation."\r\n";
					$buffer = $buffer.$packageSummary."\r\n";
					$buffer = $buffer.$packageSourceinfo."\r\n";
					$buffer = $buffer.$packageFileName."\r\n";
					$buffer = $buffer.$packageSupplier."\r\n";
					$buffer = $buffer.$packageOriginator."\r\n";
					$buffer = $buffer.$packageChecksum."\r\n";
					$buffer = $buffer.$packageVerifcationCode."\r\n";
					$buffer = $buffer.$packageDescription."\r\n";
					$buffer = $buffer.$packageCopyrightText."\r\n";
					$buffer = $buffer.$packageLicenseDeclared."\r\n";
					$buffer = $buffer.$packageLicenseConcluded."\r\n";
					$buffer = $buffer."\r\n".$packageLicenseInfoFromFiles."\r\n";
					
				}
			}
			else
			{
			}
			pg_free_result($result);
		  //File Information
      $buffer = $buffer."## File Information\r\n";
      
      /* get the top of tree */
	    $sql = "SELECT upload_fk, lft, rgt, pfile_fk from uploadtree where uploadtree_pk in ($packagePks)";
	    $result = pg_query($PG_CONN, $sql);
	    DBCheckResult($result, $sql, __FILE__, __LINE__);
	    while ($toprow = pg_fetch_assoc($result))
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
		    	$filepath = "";
		      foreach($filepatharray as $uploadtreeRow)
		      {
		        if (!empty($filepath)) $filepath .= "/";
		        $filepath .= $uploadtreeRow['ufile_name'];
		      }
		      $fileName = 'FileName: '.$filepath;
		      $fileType = $row['pfile_mimetypefk'];
		      //??
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

		      $fileType = 'FileType: '.$fileType; 
		      $fileChecksum = 'FileChecksum: SHA1: '.$row['pfile_sha1'];
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
						if (!empty($lrArr))
						{
							foreach ( $lrArr as $lr)
	  					{
	  						foreach( $LicArray as $key => $value )
								{
									if($value == $lr['LRName'])
									{
										$LicArray[$key] = $lr['LicenseID'];
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
						  $first = false;
					    else
					    $LicStr .= " and ";
					    if ($Lic == $NoLicenseFound)
					    {
					    	$Lic = $NOASSERTION;
					    	$fileLicenseInfoInFile = $fileLicenseInfoInFile.'LicenseInfoInFile: '.$NOASSERTION."\r\n";
					    }
					    else
					    {
					    	$fileLicenseInfoInFile = $fileLicenseInfoInFile.'LicenseInfoInFile: '.$Lic."\r\n";
					    }
					    $LicStr .= $Lic;
					  }
  				}
  				else
  				{
  					$LicStr = $NOASSERTION;
  				}
  				if (strstr($LicStr, " and ")) 
					{ 
					  $LicStr = '('.$LicStr.')';
					} 
  				$fileLicenseConcluded = 'LicenseConcluded: '.$LicStr;
  				if (empty($fileLicenseInfoInFile))
  				{
						$fileLicenseInfoInFile = 'LicenseInfoInFile: '.$NOASSERTION;
					}
		      
		      $sql = "SELECT ct_pk, content 
            from copyright
            where pfile_fk='$row[pfile_fk]'";
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
				    if ($first)
				    $first = false;
				    else
				    $CopyrightStr .= " ,";
				    $CopyrightStr .= $ct;
				  }
				  $fileCopyrightText = $CopyrightStr;
				  
		      if (empty($fileCopyrightText))
		      {
		      	$fileCopyrightText = $NONE;
		      }
		      $fileCopyrightText = 'FileCopyrightText: <text>'.$fileCopyrightText."</text>\r\n";
		      
		      $buffer = $buffer.$fileName."\r\n";
		      $buffer = $buffer.$fileType."\r\n";
		      $buffer = $buffer.$fileChecksum."\r\n";
		      $buffer = $buffer.$fileLicenseConcluded."\r\n";
		      $buffer = $buffer.$fileLicenseInfoInFile."\r\n";
		      $buffer = $buffer.$fileCopyrightText."\r\n";
		    }
		    pg_free_result($outerresult);
	  	}
	  	pg_free_result($result);
      if (!empty($lrArr) )
			{
		      //License Information
		      $buffer = $buffer."## License Information\r\n";
		      foreach ( $lrArr as $lr)
		      {
			      $licenseID = 'LicenseID: '.$lr['LicenseID'];
			      $buffer = $buffer.$licenseID."\r\n";
			      $rule = '/[' . chr ( 1 ) . '-' . chr ( 8 ) . chr ( 11 ) . '-' . chr ( 12 ) . chr ( 14 ) . '-' . chr ( 31 ) . ']*/';
			      $formatedRftest = str_replace ( chr ( 0 ), '', preg_replace ( $rule, '', $lr['rf_text'] ) );
  					$extractedText = 'ExtractedText: <text>'.$formatedRftest."</text>";
			      $buffer = $buffer.$extractedText."\r\n";
			      $licenseName = 'LicenseName: '.$lr['LRName'];
			      $buffer = $buffer.$licenseName."\r\n";
			      if (!empty($lr['rf_url']))
			      {
				      $licenseCrossReference = 'LicenseCrossReference: '.$lr['rf_url'];
				      $buffer = $buffer.$licenseCrossReference."\r\n";
				    }
				    $buffer = $buffer."\r\n";
				    $bufferNotice2 = $bufferNotice2.$licenseID."\r\n";
				    if ($formatedRftest != "License by Nomos." )
			    	{
			    		$bufferNotice2 = $bufferNotice2.$formatedRftest."\r\n\r\n";
			    	}
			    	else
			    	{
			    		$bufferNotice2 = $bufferNotice2."This package contains ".$lr['LRName']."\r\n\r\n";
			    	}

			    }
		  }

			$fileSuffix = $_SESSION['fileSuffix'];
			if ( strlen($buffer) == 0){
				$buffer = $NOVALIDINFO;
  		}
  		//write tag file
  		WriteFile($buffer,'/output_file/spdx'.$fileSuffix.'.tag');
  		
  		if ( strlen($bufferNotice) == 0){
  			$bufferNotice = $NOVALIDINFO;
  		}
  		//write NOTICE1 file
  		WriteFile($bufferNotice,'/output_file/NOTICE'.$fileSuffix);
  		
  		if ( strlen($bufferNotice2) == 0){
  			$bufferNotice2 = $NOVALIDINFO;
  		}
  		//write NOTICE2 file
  		WriteFile($bufferNotice2,'/output_file/NOTICE2'.$fileSuffix);
		}
}
function WriteFile($buffer,$filename)
{
	$file = dirname(__FILE__).$filename;
	touch($file);
	$fh = fopen($file,'w');
	fwrite($fh,$buffer);
	fclose($fh);
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
?>
