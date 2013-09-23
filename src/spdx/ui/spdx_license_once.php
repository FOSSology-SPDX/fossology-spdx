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

    $licenseResult = "";
    // unpack package
    $subName = time().rand();
    // get option for recursive unpack the scanning object: true-> recursive unpack; otherwise no recursive unpack;
    $recursiveUnpackFlag = GetParm("recursiveUnpack", PARM_STRING);
    // get option for JSON output format: true-> JSON format; otherwise plain text format;
    $jsonFlag = GetParm("jsonOutput", PARM_STRING);
    if ($recursiveUnpackFlag == "true")
		{
			$ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -d $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName -CRX $PackagePath",$out,$rtn);
		}
		else
		{
	    exec("mkdir $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
	    unset($rtn);
	    // unpack gz file
	    exec("tar -zxvf $PackagePath -C $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
	    if ($rtn != 0)
	    {
	    	// unpack bz2 file
	    	exec("tar -jxvf $PackagePath -C $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
	    	if ($rtn != 0)
	    	{
	    		// unpack tar file
	    		exec("tar -xvf $PackagePath -C $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
	    		if ($rtn != 0)
	    		{
	    			// unpack zip file
	    			exec("unzip $PackagePath -d $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
	    			if ($rtn != 0)
	    			{
	    				echo "FATAL: your file does not belong to specific type.  Make sure this your file belongs to gz,bz2,zip or tar format.";
				    	exec("rm -R $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
				    	return;
	    			}
	    		}
	    	}
	    }
		}
    //$ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -d $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName -CRX $PackagePath",$out,$rtn);
    $chmodResult = exec("chmod 777 -R $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
    $treeResult = $this->tree("$SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName");
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
		foreach($this->FileList as $FilePath)
    {
    	$FilePath = str_replace("//","/",$FilePath);
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
	    	$licenseResult = exec("$SYSCONFDIR/mods-enabled/nomos/agent/nomos $FilePath",$licenseOut,$rtn);
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
					$copyrightResult = exec("$SYSCONFDIR/mods-enabled/copyright/agent/copyright -C $FilePath",$copyrightOut,$rtn);
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
			// Get FileName
			$fileName = basename($FilePath);
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
			if ($jsonFlag == "true")
			{
				$FileInfo[$FIndex]['FileName'] = $fileName;
				$FileInfo[$FIndex]['FileType'] = $fileType;
				$FileInfo[$FIndex]['FileChecksum'] = strtolower($fileSHA1);
				$FileInfo[$FIndex]['FileChecksumAlgorithm'] = "SHA1";
				$FileInfo[$FIndex]['LicenseConcluded'] = $NOASSERTION;
				$FileInfo[$FIndex]['LicenseInfoInFile'] = $licensesInFile;
				$FileInfo[$FIndex]['FileCopyrightText'] = "<text>".$copyrightText."</text>";
				$FIndex = $FIndex + 1;
			}
			else
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
      	if ($jsonFlag != "true")
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
		if ($jsonFlag == "true")
		{
			$jsonOutput = array("file_level_info"=>$FileInfo,"extracted_license_info"=>$ExtractedLicenseInfo);
			$jsonOutputString = json_encode($jsonOutput);
			echo $jsonOutputString;
		}
	  exec("rm -R $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
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
  function WriteFile($buffer,$filename)
	{
		$file = $filename;
		touch($file);
		$fh = fopen($file,'a+');
		fwrite($fh,$buffer."\n");
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
};
$NewPlugin = new spdx_license_once;
?>
