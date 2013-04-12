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

define("TITLE_spdx_fileInfoEdit_input", _("File Info Edit"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_fileInfoEdit_input extends FO_Plugin
{
  public $Name       = "spdx_fileInfoEdit_input";
  public $Title      = "SPDX File Edit Input";
  public $Version    = "1.1";
  public $DBaccess   = PLUGIN_DB_NONE;
  /**
   * \brief Generate the text for this plugin.
   */

	function Output() {
    if ($this->State != PLUGIN_STATE_READY) {
      return;
    }

    global $PG_CONN;
    $V = "";

    switch($this->OutputType) {
      case "XML":
        break;
      case "HTML":
        /* If this is a POST, then process the request. */
        $UserId = GetParm('userid', PARM_INTEGER);
        
        /* Build HTML form */
        $Uri = Traceback_uri();
        $V.= "<form name='fileEditAny' method='POST' action='" . $Uri . "?mod=spdx_fileInfoEdit_confirm'>\n";
		
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
				$Val_packageInfoPk = htmlentities(GetParm('packageInfoPk',PARM_TEXT),ENT_QUOTES);
				$Val_fileInfoPk = htmlentities(GetParm('fileInfoPk',PARM_TEXT),ENT_QUOTES);
				$_SESSION['spdxId'] = $Val_SpdxId;
				$_SESSION['packageInfoPk'] = $Val_packageInfoPk;
				
				//getting package name
				$sql = "select name from spdx_package_info 
				where package_info_pk = '$Val_packageInfoPk'";
				
				$result = pg_query($PG_CONN, $sql);
		    DBCheckResult($result, $sql, __FILE__, __LINE__);
		    if (pg_num_rows($result) > 0){
					while ($packageInfo = pg_fetch_assoc($result)){
					$VAL_packageName = $packageInfo['name'];
			        }
				}
				
				$sql = "select * from spdx_file_info 
				where package_info_fk = '$Val_packageInfoPk'
				and  spdx_fk = '$Val_SpdxId'
				and  file_info_pk = $Val_fileInfoPk;";
				
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$V.= "<P />\n";
	        $text = _("This option permits editing a single file.");
	        $text1 = _("Fields denoted by an * are required.");
	        $V.= "$text <br>$text1<P />\n";
					$Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
		      $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
		        
	        pg_result_seek($result, 0);
	        while ($fileInfo = pg_fetch_assoc($result))
	        {
	        	$V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'>\n";
						$V.= "<input type='hidden' value='$Val_fileInfoPk' name='fileInfoPk'>\n";
						$V.= "<input type='hidden' value='$fileInfo[package_info_pk]' name='packageInfoPk'>\n";
	        	//package name
						$text = _("Package Name");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = $VAL_packageName;
		        $V.= "<td><input type='hidden' value='$Val' name='packagename'/><label>$Val</label></td>\n";
		        $V.= "</tr>\n";
				
						//file name
						$text = _("File Name");
		        $V.= "$Style<th width='25%'>$text </th>";
		        $Val = GetHistoryBackFormValue($fileInfo[filename],htmlentities(GetParm('filename', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='hidden' value='$Val' name='filename'/><label><label >$Val</label></td>\n";
		        $V.= "</tr>\n";
		        
						//file type
						$text = _("File Type");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[filetype],htmlentities(GetParm('filetype', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='hidden' value='$Val' name='filetype'/><label >$Val</label></td>\n";
		        $V.= "</tr>\n";
		        
						//checksum
						$text = _("Checksum");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[checksum],htmlentities(GetParm('checksum', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='hidden' value='$Val' name='checksum'/><label >$Val</label></td>\n";
		        $V.= "</tr>\n";
				
						//license concluded
						$text = _("License Concluded");
		        $V.= "$Style<th width='25%'>$text<font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($fileInfo[license_concluded],htmlentities(GetParm('licenseConcluded', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licenseConcluded' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//license Info In File
						$text = _("License Info In File");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[license_info_in_file],htmlentities(GetParm('licenseInfoInFile', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licenseInfoInFile' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//license Comments
						$text = _("License Comments");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[license_comment],htmlentities(GetParm('licenseComment', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licenseComment' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//File Copyright Text
						$text = _("File Copyright Text");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[file_copyright_text],htmlentities(GetParm('fileCopyrightText', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><textarea cols = '50' rows = '5'  name='fileCopyrightText'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
				
						//Artifact of Project
						$text = _("Artifact of Project");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[artifact_of_project],htmlentities(GetParm('artifactOfProject', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='artifactOfProject' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//Artifact of Homepage
						$text = _("Artifact of Homepage");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[artifact_of_homepage],htmlentities(GetParm('artifactOfHomepage', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='artifactOfHomepage' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//Artifact of URL
						$text = _("Artifact of URL");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[artifact_of_url],htmlentities(GetParm('artifactOfUrl', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='artifactOfUrl' size=40></td>\n";
		        $V.= "</tr>\n";
				
						//File Comment
						$text = _("File Comment");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($fileInfo[file_comment],htmlentities(GetParm('fileComment', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='fileComment' size=40></td>\n";
		        $V.= "</tr>\n";
		        $V.= "<tr><td colspan='3' style='background:black;'></td></tr>\n";
		        $V.= "</table><P/>";
	        }
					$V.= "\n<button type='button' onclick='window.close();'>Cancel</button>\n";
					$text = _("Next");
					$V.= "\n<input type='hidden' value='$Val_SpdxId' name='spdxId'>";
					$V.="<input type='submit' value='$text'>\n";
		}
		else{
			$V.= "\nThere is no package info, please back to package list\n";
			// close window
			$V.= "\n<button type='button' onclick='window.close()'>Close</button>\n";
		}
	      
        $V.= "</form>\n";
        break;
    case "Text":
      break;
    default:
      break;
  }
  if (!$this->OutputToStdout) {
    return ($V);
  }
  print ("$V");
  return;
 }
};
$NewPlugin = new spdx_fileInfoEdit_input;
?>
