<?php
/***********************************************************
 Copyright (C) 2008-2013 Hewlett-Packard Development Company, L.P.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
                    $this->tree($dir."/".$file."/");
                }
                else
                {
                    if($file!="." && $file!="..")
                    {
                        $this->FileList[] = $dir.$file;
                    }
                }
            }
            closedir($dh);
        }
    }
	}
  function AnalyzeFile($PackagePath) {
     
    global $SYSCONFDIR;

    $licenses = array();

    $licenseResult = "";
    // unpack package
    $subName = time().rand();
    //$subName = "";
    //echo "subName is $subName\n";
    $ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -C -m 2 -d $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName -R $PackagePath",$out,$rtn);
    //$ununpackResult = exec("$SYSCONFDIR/mods-enabled/ununpack/agent/ununpack -C -d $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName -R $PackagePath",$out,$rtn);
    //exec("/etc/fossology/mods-enabled/ununpack/agent/ununpack -d /usr/share/fossology/spdx/ui/output_file/output_spdx_license_once -R /tmp/fosslic-alo-rTutZc",$out,$rtn);
    //$ununpackResult = exec("sudo /usr/share/fossology/ununpack/agent/ununpack -d /home/liangcao/output_spdx_license_once -R $PackagePath",$out,$rtn);
    $chmodResult = exec("chmod 777 -R $SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName",$out,$rtn);
    $treeResult = $this->tree("$SYSCONFDIR/mods-enabled/spdx/ui/output_file/output_spdx_license_once_$subName");
    $licenseArr = array();
    //echo "FileList is: \n";
    //print_r($this->FileList);
    //$licenseOutArr = array();
    foreach($this->FileList as $FilePath)
    {
    	//$licenseOut = array();
    	$FilePath = str_replace("//","/",$FilePath);
    	//echo "\n $FilePath \n";
    	/* move the temp file */
    	/*
    	$this->WriteFile($FilePath,'/usr/share/fossology/spdx/ui/output_file/output.log');
    	
	    $licenseResult = exec("$SYSCONFDIR/mods-enabled/nomos/agent/nomos $FilePath",$licenseOut,$rtn);
	    $licenseOutArr[] = $licenseOut;
	  }
	  foreach($licenseOutArr as $licenseInFile)
	  {
	  	$this->WriteFile($licenseInFile,'/usr/share/fossology/spdx/ui/output_file/output_license.log');
	  }*/
	    
	    $licenseResult = exec("$SYSCONFDIR/mods-enabled/nomos/agent/nomos $FilePath",$licenseOut,$rtn);
	  }
	  //print_r($licenseOut);
	  foreach($licenseOut as $licenseInFile)
	  {
	  	if (!strpos($licenseInFile,"is not a plain file"))
	  	{
		  	$licensesInFile = trim(end(explode('contains license(s)',$licenseInFile))); //delete space
		  	$licensesInFileArr = explode(",", $licensesInFile);  
		  	$licenseArr = array_merge($licenseArr, $licensesInFileArr);
		  }
	  }
	  $uniqued_licenseArr = array_unique($licenseArr);
	  sort($uniqued_licenseArr);
	  echo "===PACKAGE LEVEL INFO===============================\n";
	  echo "SPDXVersion: SPDX-1.1\n";
		echo "DataLicense: CC0-1.0\n";
	  echo "Package License Info From Files: ".implode(",",$uniqued_licenseArr);
	  echo "\n";
	  echo "====================================================\n";
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
     wget -qO - --post-file=myfile.c http://myserv.com/?mod=agent_nomos_once
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
};
$NewPlugin = new spdx_license_once;
?>
