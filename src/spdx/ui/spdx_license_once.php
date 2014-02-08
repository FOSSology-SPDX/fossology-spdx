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

/**
 * \file spdx_license_once.php
 * \brief Run an analysis for a single package, do not store results in the DB.
 */

define("TITLE_spdx_license_once", _("One-Shot SPDX License Analysis"));

class spdx_license_once extends FO_Plugin {

  public $Name = "spdx_license_once";
  public $Title = TITLE_spdx_license_once;
  public $Version = "1.0";
  /* note: no menulist needed, it's insterted in the code below */
  public $Dependency = array();
  public $NoHTML = 0;  // always print text output for now
  /** For anyone to access, without login, use: **/
  public $DBaccess   = PLUGIN_DB_NONE;
  public $LoginFlag  = 0;
  public $FileList = array();
  public $copyrightOutputFlag = "";
  public $logOutputFlag = "ON";
	
  /**
   * \biref travers all files in unpacked component.
   *
   * \param string $dir the directory to the file to travers.
   *
   * \return null, add files to FileList.
   */ 
	function tree($dir)
	{
		if(is_dir($dir))
    {
        if ($dh = opendir($dir))
        {
        	  while (($file = readdir($dh)) !== false)
            {
            	  if((is_dir($dir."/".$file)) && $file!="." && $file!="..")
                {
                		if(substr_count($dir."/".$file,"/".$file)<20)
                		{
                    	$this->tree($dir."/".$file."/");
                    }
                }
                else
                {
                    if($file!="." && $file!="..")
                    {
                        $this->FileList[] = $dir."/".$file;
                        //echo "Path :".$dir.$file." is put in the tree \r\n";
                    }
                }
            }
            closedir($dh);
        }
    }
	}
	function get_file_type($file)
	{
	  if(function_exists('shell_exec') === TRUE) {
	    $dump = shell_exec(sprintf('file -bi %s', $file));
	    $info = explode(';', $dump);
	    return $info[0];
	  }
	  return FALSE;
	}
  function AnalyzeFile($PackagePath) {
     
    global $SYSCONFDIR;
    global $PG_CONN;
    
    $LICENSE_NOMOS = "License by Nomos.";
    $NOASSERTION = "NOASSERTION";
    $licenses = array();
		$this->getGlobalEnv($SYSCONFDIR);
	  global $OUTPUT_FILE;
	  global $LOGDIR;
    $licenseResult = "";
    // unpack package
    $subName = time().rand();
    // get option for recursive unpack the scanning object: true-> recursive unpack; otherwise no recursive unpack;
    $recursiveUnpackFlag = GetParm("recursiveUnpack", PARM_STRING);
    // get option for JSON output format: true-> JSON format; otherwise plain text format;
    $jsonFlag = GetParm("jsonOutput", PARM_STRING);
    // get option for full SPDX TAG output : true-> full SPDX TAG format; otherwise file and extracted license sectors information in SPDX specification;
    $fullSPDXFlag = GetParm("fullSPDXFlag", PARM_STRING);
    $outputfile_path = $OUTPUT_FILE;
    $logOutputFlag = "ON";
    $packageName = GetParm("packageNameInLog", PARM_STRING);
    if(empty($packageName))
    {
    	//$packageName = "Anonym".date("Y-m-dH:i:s", time()); 
    	$logOutputFlag = "OFF";
    }
    $logFile = $LOGDIR."/".$packageName.".log";
    $logText = "Package is being processed from now\t[Package Name]:\t".$packageName;
    $this->WriteLogFile($logText, $logFile);
    if ($recursiveUnpackFlag == "true")
		{
			$ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -d $outputfile_path/output_spdx_license_once_$subName -CRX $PackagePath",$out,$rtn);
	    $logText = "unpack package finished\t[Package Name]:\t".$packageName;
	    $this->WriteLogFile($logText, $logFile);
		}
		else
		{
			exec("mkdir $outputfile_path",$out,$rtn);
	    exec("mkdir $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
	    unset($rtn);
	    // unpack gz file
	    exec("tar -zxvf $PackagePath -C $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
	    if ($rtn != 0)
	    {
	    	// unpack bz2 file
	    	exec("tar -jxvf $PackagePath -C $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
	    	if ($rtn != 0)
	    	{
	    		// unpack tar file
	    		exec("tar -xvf $PackagePath -C $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
	    		if ($rtn != 0)
	    		{
	    			// unpack zip file
	    			exec("unzip $PackagePath -d $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
	    			if ($rtn != 0)
	    			{
	    				echo "FATAL: your file does not belong to specific type.  Make sure this your file belongs to gz,bz2,zip or tar format.";
 					    $logText = "FATAL: your file does not belong to specific type.  Make sure this your file belongs to gz,bz2,zip or tar format.";
              $this->WriteLogFile($logText, $logFile);
				    	exec("rm -R $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
				    	return;
	    			}
	    		}
	    	}
	    }
		}
    //$ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -d $outputfile_path/output_spdx_license_once_$subName -CRX $PackagePath",$out,$rtn);
    $chmodResult = exec("chmod 777 -R $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
    $treeResult = $this->tree("$outputfile_path/output_spdx_license_once_$subName");
    $SPDXLicenseList = array();
    $nonSPDXLicenseList = array();
    //list all possible license shortname for licenses in SPDX license list
    $sql = "select license_identifier as license_name from spdx_license_list
						union
						select license_matchname_1 as license_name from spdx_license_list where license_matchname_1 <> ''
						union
						select license_matchname_2 as license_name from spdx_license_list where license_matchname_2 <> ''
						union
						select license_matchname_3 as license_name from spdx_license_list where license_matchname_3 <> ''";
		$result = pg_query($PG_CONN, $sql);
    DBCheckResult($result, $sql, __FILE__, __LINE__);
    if (pg_num_rows($result) > 0){
			pg_result_seek($result, 0);
			while ($SPDXLicense = pg_fetch_assoc($result)) {
				$SPDXLicenseList[$SPDXLicense['license_name']] = 1;
			}
    }
    pg_free_result($result);
    
    $licenseArr = array();
    $PIPEArr = array("inode/fifo");
    $SOURCEArr = array("application/x-debian-source",
												"text/plain",
												"text/x-c++",
												"text/x-shellscript",
												"text/x-php",
												"text/x-c",
												"application/x-wais-source",
												"text/x-csrc",
												"text/x-c++src",
												"text/x-chdr",
												"text/x-diff",
												"application/xml",
												"application/x-sh",
												"text/html",
												"text/x-pascal",
												"text/x-makefile",
												"text/x-perl",
												"text/x-fortran",
												"text/x-awk",
												"text/x-m4",
												"text/x-python",
												"application/x-info",
												"text/x-msdos-batch",
												"text/x-java",
												"text/css",
												"text/cache-manifest",
												"application/javascript",
												"application/x-python-code");
    $BINARYArr = array("application/octet-stream",
												"text/x-tex");
    $ARCHIVEArr = array("application/x-gzip",
												"application/x-compress",
												"application/x-bzip",
												"application/x-bzip2",
												"application/x-zip",
												"application/zip",
												"application/x-tar",
												"application/x-gtar",
												"application/x-cpio",
												"application/x-rar",
												"application/x-cab",
												"application/x-7z-compressed",
												"application/x-7z-w-compressed",
												"application/x-rpm",
												"application/x-archive",
												"application/x-debian-package");
    $copyrightOutputFlag = GetParm("noCopyright", PARM_STRING);
    $FIndex = 0;
    $FileCount = count($this->FileList);
    $currentFileNumber = 0;
		foreach($this->FileList as $FilePath)
    {
    	$currentFileNumber = $currentFileNumber + 1;
    	$FilePath = str_replace("//","/",$FilePath);
    	// Get FileName
			$fileName = basename($FilePath);
    	// Get FileType
			$fileType = $this->get_file_type($FilePath);
	    if (in_array($fileType,$SOURCEArr))
      {
			    $fileType = 'SOURCE';
			}
			else if (in_array($fileType,$BINARYArr))
			{
					$fileType = 'BINARY';
			}
			else if (in_array($fileType,$ARCHIVEArr))
			{
					$fileType = 'ARCHIVE';
			}
			else if (in_array($fileType,$PIPEArr))
			{
					$fileType = 'PIPE';
			}
			else
			{
					$fileType = 'OTHER';
			}
    	//echo "File working on: ".$FilePath."\r\n";
    	//echo "Start to get license\r\n";
    	if ($fileType == "PIPE")
    	{
    		$licensesInFile = $NOASSERTION;
    		$copyrightText = $NOASSERTION;
    	}
    	else
    	{
		    $logText = "license scanning started.\t[".$currentFileNumber."/".$FileCount."][File Name]:\t".$fileName;
        $this->WriteLogFile($logText, $logFile);
	    	$licenseResult = exec("$SYSCONFDIR/mods-enabled/nomos/agent/nomos $FilePath",$licenseOut,$rtn);
		    $logText = "license scanning finished.\t[".$currentFileNumber."/".$FileCount."][File Name]:\t".$fileName;
        $this->WriteLogFile($logText, $logFile);
	    	foreach($licenseOut as $licenseInFile)
			  {
			  	if (strpos($licenseInFile,"is not a plain file")=== false)
			  	{
				  	$licensesInFile = trim(end(explode('contains license(s)',$licenseInFile))); //delete space
				  	$licenseInFileArr = explode(',',$licensesInFile);
				  	foreach($licenseInFileArr as $license) {
				  		$license_name = trim($license);
						   if (isset($SPDXLicenseList[$license_name]) === false) {
							   $nonSPDXLicenseList[$license_name] = $license_name;
						   }
						}
				  }
				  else
				  {
				  	$licensesInFile = $NOASSERTION; //"is not a plain file"
				  }
			  }
			  //echo "Gotten license: ".$licensesInFile."\r\n";
			  unset($licenseOut);
			  
				// Get FileCopyrightText
				if ($copyrightOutputFlag == "true")
				{
					$copyrightText = $NOASSERTION;
				}
				else
				{
		
					$copyrightOut = array();
   		    $logText = "copyright scanning started.\t[".$currentFileNumber."/".$FileCount."][File Name]:\t".$fileName;
          $this->WriteLogFile($logText, $logFile);
					$copyrightResult = exec("$SYSCONFDIR/mods-enabled/copyright/agent/copyright -C $FilePath",$copyrightOut,$rtn);
			    $logText = "copyright scanning finished.\t[".$currentFileNumber."/".$FileCount."][File Name]:\t".$fileName;
          $this->WriteLogFile($logText, $logFile);
					$copyrightText = "";
					foreach($copyrightOut as $copyrightInFile)
					  {
						$copyrightBeginLineArr = preg_split("/\[\d{1,}\:\d{1,}\:\w*]\s'/",$copyrightInFile);
						if (count($copyrightBeginLineArr) > 1)
						{
							$copyright = end($copyrightBeginLineArr); //delete comment
							  $copyright = reset(preg_split("/'$/",$copyright)); //delete end ' mark
							  $copyrightText = $copyrightText.$copyright."\r\n";
						}
						else
						{
							$copyrightEndLineArr = preg_split("/\:\:$/",$copyrightInFile);
							if (count($copyrightEndLineArr) < 2)
							{
								$copyright = reset(preg_split("/'$/",reset($copyrightEndLineArr))); //delete end ' mark
								  $copyrightText = $copyrightText.$copyright."\r\n";
							}
						}
					  }
					  $copyrightText = substr($copyrightText,0,strlen($copyrightText)-2);
					if (empty($copyrightText))
					{
						$copyrightText = "NONE";
					}
				}
			}
			// Get SHA1
			if ($fileType == "PIPE")
			{
				$fileSHA1 = sha1("");
			}
			else
			{
			$fileSHA1 = sha1_file($FilePath);
				/*
				if ($fileSHA1 ===false)
				{
					$fileSHA1 = sha1("");
				}
				*/
			}
			$FileInfo[$FIndex]['FileName'] = $fileName;
			$FileInfo[$FIndex]['FileType'] = $fileType;
			$FileInfo[$FIndex]['FileChecksum'] = strtolower($fileSHA1);
			$FileInfo[$FIndex]['FileChecksumAlgorithm'] = "SHA1";
			$FileInfo[$FIndex]['LicenseConcluded'] = $NOASSERTION;
			$FileInfo[$FIndex]['LicenseInfoInFile'] = $licensesInFile;
			$FileInfo[$FIndex]['FileCopyrightText'] = "<text>".$copyrightText."</text>";
			$FIndex = $FIndex + 1;
			if (($fullSPDXFlag != "true") && ($jsonFlag != "true"))
			{
				echo "FileName: ".$fileName."\r\n";
				echo "FileType: ".$fileType."\r\n";
				echo "FileChecksum: SHA1: ".strtolower($fileSHA1)."\r\n";
				echo "LicenseConcluded: ".$NOASSERTION."\r\n";
				echo "LicenseInfoInFile: ".$licensesInFile."\r\n";
				echo "FileCopyrightText: <text>".$copyrightText."</text>"."\r\n";
				echo "\r\n";
			}
	  }
	  // Get Extracted License information
    $ELIndex = 0;
		//$ExtractedLicenseInfo = array();
		//$extractedLicenseNameList = "'".implode('\',\'',$nonSPDXLicenseList)."'";
		if (count($nonSPDXLicenseList)>0)
		{
			$extractedLicenseNameList = join(',',$nonSPDXLicenseList);
			$extractedLicenseNameList = str_replace("'","''",$extractedLicenseNameList);
		}
		else
		{
			$extractedLicenseNameList = '';
		}
		//$sql = "select rf_shortname,rf_text,rf_url from license_ref where rf_shortname in($extractedLicenseNameList)";
		$sql = "select rf_shortname,rf_text,rf_url from license_ref where rf_shortname in(select regexp_split_to_table('$extractedLicenseNameList', E','))";
		$resultLicRf = pg_query($PG_CONN, $sql);
    DBCheckResult($resultLicRf, $sql, __FILE__, __LINE__);
    if (pg_num_rows($resultLicRf) > 0){
			pg_result_seek($resultLicRf, 0);
      while ($ExtractedLicInfo = pg_fetch_assoc($resultLicRf))
      {
      	$ExtractedLicenseInfo[$ELIndex]['LicenseName'] = $ExtractedLicInfo['rf_shortname'];
      	if ($ExtractedLicInfo['rf_text'] == $LICENSE_NOMOS)
		    {
		    	$formatedRftest = "Please see online publication for the full text of this license";
		    }
		    else
		    {
		    	$rule = '/[' . chr ( 1 ) . '-' . chr ( 8 ) . chr ( 11 ) . '-' . chr ( 12 ) . chr ( 14 ) . '-' . chr ( 31 ) . ']*/';
		    	$formatedRftest = str_replace ( chr ( 0 ), '', preg_replace ( $rule, '', $ExtractedLicInfo['rf_text'] ) );
		    }
      	$ExtractedLicenseInfo[$ELIndex]['ExtractedText'] = "<text>".$formatedRftest."</text>";
      	$ExtractedLicenseInfo[$ELIndex]['LicenseCrossReference'] = $this->IsOptionalItem("",$extractedLicenseInfo["rf_url"],"");
      	if (($fullSPDXFlag != "true") && ($jsonFlag != "true"))
				{
	      	echo "LicenseName: ".$ExtractedLicenseInfo[$ELIndex]['LicenseName']."\r\n";
	      	echo "ExtractedText: ".$ExtractedLicenseInfo[$ELIndex]['ExtractedText']."\r\n";
	      	echo "LicenseCrossReference: ".$ExtractedLicenseInfo[$ELIndex]['LicenseCrossReference']."\r\n";
	      	echo "\r\n";
				}
				$ELIndex = $ELIndex + 1;
      }
    }
    pg_free_result($resultLicRf);
    if (($fullSPDXFlag == "true")&&($jsonFlag != "true"))
    {
    	if(empty($FileInfo))
    	{
    		echo "No file level information found\r\n";
    	}
    	echo $this->GegerateSPDXTAG($PackagePath ,$FileInfo, $ExtractedLicenseInfo);
    }
    else if ($jsonFlag == "true")
		{
			$jsonOutput = array("file_level_info"=>$FileInfo,"extracted_license_info"=>$ExtractedLicenseInfo);
			$jsonOutputString = json_encode($jsonOutput);
			echo $jsonOutputString;
		}

    $logText = "Package has been processed\t[Package Name]:\t".$packageName;
    $this->WriteLogFile($logText, $logFile);
	  exec("rm -R $outputfile_path/output_spdx_license_once_$subName",$out,$rtn);
    return;

  } // AnalyzeFile()

  /**
   * \brief Change the type of output
   * based on user-supplied parameters.
   *
   * \return 1 on success.
   */
  function RegisterMenus() {
  	/*
  	if ($this->State != PLUGIN_STATE_READY) {
      return (0);
    } // don't run
    */
    if (GetParm("mod", PARM_STRING) == $this->Name) {
      $ThisMod = 1;
    }
    else {
      $ThisMod = 0;
    }
	  /*
     * This if stmt is true only for wget.
     * For wget, populate the $_FILES array, just like the UI post would do.
     * Sets the unlink_flag if there is a temp file.
     */
    if ($ThisMod && ($_SERVER['REQUEST_METHOD'] == "POST"))
    {
    	$Fin = fopen("php://input", "r");
      $Ftmp = tempnam(NULL, "fosslic-spdx-package-level-");
      $Fout = fopen($Ftmp, "w");
      while (!feof($Fin)) {
        $Line = fgets($Fin);
        fwrite($Fout, $Line);
      }
      fclose($Fin);
      fclose($Fout);

      /* Populate _FILES from wget so the processing logic only has to look in one
       * place wether the data came from wget or the UI
       */
      if (filesize($Ftmp) > 0)
      {
      	$_FILES['licfile']['tmp_name'] = $Ftmp;
        $_FILES['licfile']['size'] = filesize($Ftmp);
        $_FILES['licfile']['unlink_flag'] = 1;
        $this->NoHTML = 1;
      }
      else
      {
        unlink($Ftmp);
        /* If there is no input data, then something is wrong.
         * For example the php POST limit is too low and prevented
         * the data from coming through.  Or there was an apache redirect,
         * which removes the POST data.
         */
        $text = _("FATAL: your file did not get passed throught.  Make sure this page wasn't a result of a web server redirect, or that it didn't exceed your php POST limit.");
        echo $text;
      }
    }
  } // RegisterMenus()

  /**
   * \brief Generate the text for this plugin.
   */
  function Output() {
    global $Plugins;
    if ($this->State != PLUGIN_STATE_READY) {
      return;
    }

    /* Ignore php Notice is array keys don't exist */
    $errlev = error_reporting(E_ERROR | E_WARNING | E_PARSE);
    $tmp_name = $_FILES['licfile']['tmp_name'];
    error_reporting($errlev);

    /* For REST API:
     wget -qO - --post-file=myfile.c http://myserv.com/?mod=spdx_license_once
    */
    if ($this->NoHTML && file_exists($tmp_name))
    {
      echo $this->AnalyzeFile($tmp_name);
      //echo "Ends: ".date("Y-m-d H:i:s")."\n";
      echo "\n";
      unlink($tmp_name);
      return;
    }
    return;
  }
  function GegerateSPDXTAG($packageFile, $files, $extractedLicenses)
	{
		$NOASSERTION = "NOASSERTION";
		$buffer = "";
		$buffer = $buffer."SPDXVersion: SPDX-1.1\r\n";
		$buffer = $buffer."DataLicense: CC0-1.0\r\n";
		$buffer = $buffer."DocumentComment: <text></text>\r\n";
		$buffer = $buffer."\r\n## Creation Information\r\n";
		$buffer = $buffer."Creator: Tool: FOSSology+SPDX command line\r\n";
		$createdDate = Date("Y-m-d")."T".Date("H:i:s")."Z";
		$buffer = $buffer."Created: ".$createdDate."\r\n";
		$buffer = $buffer."CreatorComment: <text></text>\r\n";
		$buffer = $buffer."\r\n## Package Information\r\n";
		
		$packageName = GetParm("packageNameInLog", PARM_STRING);
		$buffer = $buffer."PackageName: ".$packageName."\r\n";
		$buffer = $buffer."PackageVersion: \r\n";
		$buffer = $buffer."PackageDownloadLocation: ".$NOASSERTION."\r\n";
		$buffer = $buffer."PackageSummary: <text></text>\r\n";
		$buffer = $buffer.$this->IsOptionalItem("PackageSourceInfo: ","","\r\n");
		$buffer = $buffer."PackageFileName: \r\n";
		$buffer = $buffer."PackageSupplier: ".$NOASSERTION."\r\n";
		$buffer = $buffer."PackageOriginator: ".$NOASSERTION."\r\n";
		$buffer = $buffer."PackageChecksum: SHA1: ".strtolower(sha1_file($packageFile))."\r\n";
		$verificationcode_excludedfiles = "*.spdx";
		$buffer = $buffer."PackageVerificationCode: ".$this->getVerificationCode($files,$verificationcode_excludedfiles);
		$buffer = $buffer."(excludes: ".$verificationcode_excludedfiles.")\r\n";
		$buffer = $buffer."PackageDescription: <text></text>\r\n\r\n";
		$buffer = $buffer."PackageCopyrightText: <text>".$NOASSERTION."</text>\r\n\r\n";
		$packageLicenseInfoFromFilesArr = array();
		if(!empty($files))
		{
			foreach($files as $file)
			{
				$licensesInFile = $file['LicenseInfoInFile'];
		  	$licenseInFileArr = explode(',',$licensesInFile);
		  	foreach($licenseInFileArr as $license) {
		  		$license_name = trim($license);
				   if (isset($packageLicenseInfoFromFilesArr[$license_name]) === false) {
					   $packageLicenseInfoFromFilesArr[$license_name] = $license_name;
				   }
				}
			}
		}
		$LicenseRefIndex = 1;
		$extractedLicensesWithRefIndex = array();
		if(!empty($extractedLicenses))
		{
			foreach($extractedLicenses as $extractedLicense)
			{
				$extractedLicense['LicenseID'] = "";
				if ((isset($packageLicenseInfoFromFilesArr[$extractedLicense['LicenseName']])) ===true) {
					$packageLicenseInfoFromFilesArr[$extractedLicense['LicenseName']] = "LicenseRef-".$LicenseRefIndex;
					$extractedLicense['LicenseID'] = "LicenseRef-".$LicenseRefIndex;
					$LicenseRefIndex = $LicenseRefIndex + 1;
				}
				$extractedLicensesWithRefIndex[] = $extractedLicense;
			}
		}
		$packageLicenseDeclared = trim(implode(' and ', $packageLicenseInfoFromFilesArr));
		if (empty($packageLicenseDeclared))
		{
			
			$buffer = $buffer."PackageLicenseDeclared: NONE\r\n";
		}
		else
		{
			$buffer = $buffer."PackageLicenseDeclared: ".$this->IsNONEParenthesis($packageLicenseDeclared)."\r\n";
		}
		
		$buffer = $buffer."PackageLicenseConcluded: ".$NOASSERTION."\r\n";
		if (count($packageLicenseInfoFromFilesArr)>0)
		{
			foreach ($packageLicenseInfoFromFilesArr as $packageLicenseInfoFromFiles)
			{
				if (!empty($packageLicenseInfoFromFiles))
				{
					$buffer = $buffer."PackageLicenseInfoFromFiles: ".$packageLicenseInfoFromFiles."\r\n";
				}
			}
		}
		else
		{
			$buffer = $buffer."PackageLicenseInfoFromFiles: NONE\r\n";
		}
		$buffer = $buffer."PackageLicenseComments: <text></text>\r\n";
		
		//File Information
		$buffer = $buffer."\r\n## File Information\r\n";
		if(!empty($files))
		{
			foreach($files as $fileInfo)
		  {
		  	$buffer = $buffer."\r\nFileName: ".$fileInfo["FileName"]."\r\n";
				$buffer = $buffer."FileType: ".$fileInfo["FileType"]."\r\n";
				$buffer = $buffer."FileChecksum: SHA1: ".strtolower($fileInfo["FileChecksum"])."\r\n";
				$buffer = $buffer."LicenseConcluded: ".$this->IsNONEParenthesis($fileInfo["LicenseConcluded"])."\r\n";
		
				$licenseInfoInFileArr = explode(",",$fileInfo["LicenseInfoInFile"]);
				if (count($licenseInfoInFileArr)>0 )
				{
					foreach ($licenseInfoInFileArr as $licenseInfoInFile)
					{
						
						foreach($extractedLicensesWithRefIndex as $extractedLicense)
						{
							if ($licenseInfoInFile == $extractedLicense['LicenseName']) {
								$licenseInfoInFile = $extractedLicense['LicenseID'];
							}
						}
						$buffer = $buffer.'LicenseInfoInFile: '.$licenseInfoInFile."\r\n";
					}
				}
				else
				{
					$buffer = $buffer."LicenseInfoInFile: NONE\r\n";
				}
				$buffer = $buffer."FileCopyrightText: ".$this->IsNONE($fileInfo["FileCopyrightText"])."\r\n";
				$buffer = $buffer.$this->IsOptionalItem('ArtifactOfProjectName: ',$fileInfo["artifact_of_project"],"\r\n");
				$buffer = $buffer.$this->IsOptionalItem('ArtifactOfProjectHomePage: ',$fileInfo["artifact_of_homepage"],"\r\n");
				$buffer = $buffer.$this->IsOptionalItem('ArtifactOfProjectURI: ',$fileInfo["artifact_of_url"],"\r\n");
				$buffer = $buffer.$this->IsOptionalItem('FileComment: <text>',$fileInfo["file_comment"],"</text>\r\n");
		  }
		}
	  //License Information
		$buffer = $buffer."\r\n## License Information\r\n";
		foreach ($extractedLicensesWithRefIndex as $extractedLicenseInfo)
	  {
	    $buffer = $buffer."\r\nLicenseID: ".$extractedLicenseInfo["LicenseID"]."\r\n";
			$buffer = $buffer.'ExtractedText: '.$extractedLicenseInfo["ExtractedText"]."\r\n";
			$buffer = $buffer.'LicenseName: '.$extractedLicenseInfo["LicenseName"]."\r\n";
			$buffer = $buffer.$this->IsOptionalItem('LicenseCrossReference: ',$extractedLicenseInfo["LicenseCrossReference"],"\r\n");
			$buffer = $buffer.$this->IsOptionalItem('LicenseComment: ',$extractedLicenseInfo["LicenseComment"],"\r\n");
	  }
		return $buffer;
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
	function getVerificationCode($files,$excludedfiles) {
		if(!empty($files))
		{
			foreach ($files as $row)
			{
				if (empty($excludedfiles))
				{
					$templist[] = $row['FileChecksum'];
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
							if ( substr($excludedfile,2) == substr(strrchr($row['FileName'],"."),1))
							{
								$match = true;
							}
						}
						else
						{
							//check full path
							if ($excludedfile == $row['FileName'])
							{
								$match = true;
							}
						}
					}
					if ($match == false)
					{
						$templist[] = $row['FileChecksum'];
					}
				}
			}
		}
		if(empty($templist))
		{
			$filelist = "";
		}
		else
		{
			sort($templist);
			$num = count($templist); 
			for($i=0;$i < $num;++$i)
			{ 
				$filelist = $filelist.$templist[$i]; 
			}
		} 
		$verificationCode = SHA1(strtolower($filelist));
		return $verificationCode;
	}
  function WriteLogFile($buffer,$filename)
	{
		//only when user identify package name, log will be written for it
		if (!($this->logOutputFlag == "ON"))
		{
			return;
		}
		$file = $filename;
		touch($file);
		$fh = fopen($file,'a+');
		//$logTime = @date('[d/M/Y:H:i:s]');
		$logTime = date("Y-m-d H:i:s");
		fwrite($fh,$logTime." ".$buffer.PHP_EOL);
		fclose($fh);
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
};
$NewPlugin = new spdx_license_once;
?>
