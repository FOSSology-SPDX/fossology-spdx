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

define("TITLE_spdx_fileInfoEdit_accept", _("Package Info Edit"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_fileInfoEdit_accept extends FO_Plugin
{
  public $Name       = "spdx_fileInfoEdit_accept";
  public $Title      = "SPDX File Edit Accept";
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
        $Uri = Traceback_uri();
		$Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
		$Val_PackageInfoPk = htmlentities(GetParm('packageInfoPk', PARM_TEXT), ENT_QUOTES);
		$Val_FileInfoPk = htmlentities(GetParm('fileInfoPk',PARM_TEXT),ENT_QUOTES);
		Spdx_update_file($Val_SpdxId,$Val_FileInfoPk);
				
				
        /* Build HTML form */
        $V.= "<form name='packageEditAny' method='POST'>\n"; // no url = this url
        $V.= "<P />\n";
        $text = _("Package Info has been updated.");
        $V.= "$text<P />\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
			$V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'>\n";
			$V.= "<input type='hidden' value='$Val_fileInfoPk' name='fileInfoPk'>\n";
			$V.= "<input type='hidden' value='$fileInfo[package_info_pk]' name='packageInfoPk'>\n";
			
		    $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
			
			//package name
			$text = _("Package Name");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('packagename', PARM_TEXT), ENT_QUOTES);
		    $V.= "<td><input type='hidden' value='$Val' name='packagename'/><label>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//file name
			$text = _("File Name");
			$V.= "$Style<th width='25%'>$text </th>";
			$Val = htmlentities(GetParm('filename', PARM_TEXT), ENT_QUOTES);
		    $V.= "<td><input type='hidden' value='$Val' name='filename'/><label><label >$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//file type
			$text = _("File Type");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('filetype', PARM_TEXT), ENT_QUOTES);
		    $V.= "<td><input type='hidden' value='$Val' name='filetype'/><label >$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//checksum
			$text = _("Checksum");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('checksum', PARM_TEXT), ENT_QUOTES);
		    $V.= "<td><input type='hidden' value='$Val' name='checksum'/><label >$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//license concluded
			$text = _("License Concluded");
			$V.= "$Style<th width='25%'>$text<font color='red'>*</font></th>";
			$Val = htmlentities(GetParm('licenseConcluded', PARM_TEXT), ENT_QUOTES);
		    $V.= "<td><input type='hidden' value='$Val' name='licenseConcluded'><label >$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//license Info In File
			$text = _("License Info In File");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('licenseInfoInFile', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='licenseInfoInFile'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//license Comments
			$text = _("License Comments");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('licenseComment', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='licenseComment'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//File Copyright Text
			$text = _("File Copyright Text");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('fileCopyrightText', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='fileCopyrightText'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//Artifact of Project
			$text = _("Artifact of Project");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('artifactOfProject', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='artifactOfProject'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//Artifact of Homepage
			$text = _("Artifact of Homepage");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('artifactOfHomepage', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='artifactOfHomepage'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//Artifact of URL
			$text = _("Artifact of URL");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('artifactOfUrl', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='artifactOfUrl'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			//File Comment
			$text = _("File Comment");
			$V.= "$Style<th width='25%'>$text</th>";
			$Val = htmlentities(GetParm('fileComment', PARM_TEXT), ENT_QUOTES);
			$V.= "<td><label name='fileComment'>$Val</label></td>\n";
			$V.= "</tr>\n";
			
			$V.= "</tbody></table>\n";
	      $V.= "\n<button type='button' onclick='window.close()'>Close</button>\n";
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
$NewPlugin = new spdx_fileInfoEdit_accept;
?>
