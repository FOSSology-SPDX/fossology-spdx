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

define("TITLE_spdx_packageInfoEdit_input", _("Package Info Edit"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_packageInfoEdit_input extends FO_Plugin
{
  public $Name       = "spdx_packageInfoEdit_input";
  public $Title      = TITLE_spdx_packageInfoEdit_input;
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
        $Uri = Traceback_uri();
        /* Create JavaScript for outputing files */
        $V.= "\n<script language='javascript'>\n";
        $V.= "function createVerificationCode(SpdxId,fileId){";
        $V.= "			var verificationcodeexcludedfiles = document.getElementById('vcExcludedfiles').value;";
        $V.= "			var form = document.getElementById('packageEdit');";
        $V.= "			form.setAttribute('action','".$Uri."?mod=".$this->Name."&spdxId='+SpdxId+'&pfile='+fileId+'&init=1');";
        $V.= "			form.submit();";
        $V.= "     }\n";
        $V.= "function goSubmit(){";
        $V.= "			var spdxId = document.getElementById('spdxId').value;";
        $V.= "			var pfile = document.getElementById('pfile').value;";
        $V.= "			var packageInfoPk = document.getElementById('packageInfoPk').value;";
        $V.= "			var packagename = encodeURIComponent(document.getElementById('packagename').value);";
        $V.= "			var packageversion = document.getElementById('packageversion').value;";
        $V.= "			var packagefileName = encodeURIComponent(document.getElementById('packagefileName').value);";
        $V.= "			var supplier = document.getElementById('supplier').value;";
        $V.= "			var pkgsupplier = encodeURIComponent(document.getElementById('packagesupplier').value);";
        $V.= "			var originator = document.getElementById('originator').value;";
        $V.= "			var pkgoriginator = encodeURIComponent(document.getElementById('packageoriginator').value);";
        $V.= "			var dllocation = document.getElementById('packagedownloadlocation').value;";
        $V.= "			var packagechecksum = document.getElementById('packagechecksum').value;";
        $V.= "			var verificationcode = document.getElementById('packageverificationcode').value;";
        $V.= "			var vcExcludedfiles = encodeURIComponent(document.getElementById('vcExcludedfiles').value);";
        $V.= "			var sourceinfo = encodeURIComponent(document.getElementById('sourceinfo').value);";
        $V.= "			var licensedeclared = encodeURIComponent(document.getElementById('licensedeclared').value);";
        $V.= "			var licenseconcluded = encodeURIComponent(document.getElementById('licenseconcluded').value);";
        $V.= "			var licenseinfofromfiles = encodeURIComponent(document.getElementById('licenseinfofromfiles').value);";
        $V.= "			var licensecomment = encodeURIComponent(document.getElementById('licensecomment').value);";
        $V.= "			var packagecopyrighttext = encodeURIComponent(document.getElementById('packagecopyrighttext').value);";
        $V.= "			var summary = encodeURIComponent(document.getElementById('summary').value);";
        $V.= "			var description = encodeURIComponent(document.getElementById('description').value);";
        $V.= '  var url = "'.$Uri.'?mod='.$this->Name.'&spdxId="+spdxId+"&pfile="+pfile+"&packageInfoPk="+packageInfoPk+"&packagename="+packagename+"&packageversion="+packageversion+"&packagefileName="+packagefileName+"&supplier="+supplier+"&packagesupplier="+pkgsupplier+"&originator="+originator+"&packageoriginator="+pkgoriginator+"&packagedownloadlocation="+dllocation+"&packagechecksum="+packagechecksum+"&packageverificationcode="+verificationcode+"&vcExcludedfiles="+vcExcludedfiles+"&sourceinfo="+sourceinfo+"&licensedeclared="+licensedeclared+"&licenseconcluded="+licenseconcluded+"&licenseinfofromfiles="+licenseinfofromfiles+"&licensecomment="+licensecomment+"&packagecopyrighttext="+packagecopyrighttext+"&summary="+summary+"&description="+description+"&init=1";';
        $V.= '  window.location.assign(url);';
        $V.= "     }";
        $V.= "</script>\n";
        /* Build HTML form */
        $V.= "<form name='packageEditAny' id='packageEdit' method='POST' action='" . $Uri . "?mod=spdx_packageInfoEdit_confirm'>\n";
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
				$Val_pfile = htmlentities(GetParm('pfile', PARM_TEXT), ENT_QUOTES);
				$init = htmlentities(GetParm('init', PARM_TEXT), ENT_QUOTES);
				$Val_vcexcludedfiles = htmlentities(GetParm('vcExcludedfiles', PARM_TEXT), ENT_QUOTES);
				$sql = "select * from spdx_package_info 
		        where pfile_fk = '$Val_pfile'
				and  spdx_fk = '$Val_SpdxId'";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$V.= "<P />\n";
	        $text = _("This option permits editing a single package.");
	        $text1 = _("Fields denoted by an * are required.");
	        $V.= "$text <br>$text1<P />\n";
					$Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
		      $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
		        
	        pg_result_seek($result, 0);
	        while ($packageInfo = pg_fetch_assoc($result))
	        {
	        	$V.= "<input type='hidden' value='$Val_pfile' name='pfile' id='pfile'>\n";
						$V.= "<input type='hidden' value='$packageInfo[package_info_pk]' name='packageInfoPk' id='packageInfoPk'>\n";
						$packageId = $packageInfo[package_info_pk];
	        	$text = _("Package Name");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[name],htmlentities(GetParm('packagename', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagename' id='packagename' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Version");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[version],htmlentities(GetParm('packageversion', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packageversion' id='packageversion' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package FileName");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[filename],htmlentities(GetParm('packagefileName', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagefileName' id='packagefileName' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Supplier");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $ValType = GetHistoryBackFormValue($packageInfo[supplier_type],htmlentities(GetParm('supplier', PARM_TEXT), ENT_QUOTES));
		        $Val = GetHistoryBackFormValue($packageInfo[supplier],htmlentities(GetParm('packagesupplier', PARM_TEXT), ENT_QUOTES));
				    $V.= "<td><select name='supplier' id='supplier'>".GetPersonOrganization($ValType,$Val,'packagesupplier')."<input type='text' value='$Val' name='packagesupplier' id='packagesupplier' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Originator");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $ValType = GetHistoryBackFormValue($packageInfo[originator_type],htmlentities(GetParm('originator', PARM_TEXT), ENT_QUOTES));
		        $Val = GetHistoryBackFormValue($packageInfo[originator],htmlentities(GetParm('packageoriginator', PARM_TEXT), ENT_QUOTES));
						$V.= "<td><select name='originator' id='originator'>".GetPersonOrganization($ValType,$Val,'packageoriginator')."<input type='text' value='$Val' name='packageoriginator' id='packageoriginator' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Download Location");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[download_location],htmlentities(GetParm('packagedownloadlocation', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagedownloadlocation' id='packagedownloadlocation' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Checksum");
		        $V.= "$Style<th width='25%'>$text</th>";
				$lowercaseChecksum = strtolower($packageInfo[checksum]);
		        $V.= "<td><input type='hidden' value='$packageInfo[checksum]' name='packagechecksum' id='packagechecksum'>$lowercaseChecksum</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Verification Code");
		        $V.= "$Style<th width='25%'>$text</th>";
		        if (empty($init))
		        {
		        	$vcexcludedfiles = $packageInfo['verificationcode_excludedfiles'];
		        	$verificationcode = $packageInfo[verificationcode];
		        }
		        else
		        {
		        	$vcexcludedfiles = htmlentities(GetParm('vcExcludedfiles', PARM_TEXT), ENT_QUOTES);
		        	$verificationcode = getVerificationCode($Val_SpdxId,$packageId,$Val_vcexcludedfiles);
		        }
		        $V.= "<input type='hidden' value='$verificationcode' name='packageverificationcode' id='packageverificationcode'><td id='verificationCode'>$verificationcode &nbsp;&nbsp;&nbsp;&nbsp;<button type='button' name = 'Code' onclick='goSubmit()'>Create Verification Code</button></td>\n";
		        
		        $V.= "</tr>\n";
		        $text = _("Verification Code Excluded Files");
		        $V.= "$Style<th width='25%'>$text</th>";
		        
		        $V.= "<td><input type='text' value='$vcexcludedfiles' id='vcExcludedfiles' name='vcExcludedfiles' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Source Info");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[source_info],htmlentities(GetParm('sourceinfo', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='sourceinfo' id='sourceinfo' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Declared");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_declared],htmlentities(GetParm('licensedeclared', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><textarea cols='120' rows='3' id='licensedeclared' name='licensedeclared'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Concluded");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_concluded],htmlentities(GetParm('licenseconcluded', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><textarea cols='120' rows='3' id='licenseconcluded' name='licenseconcluded'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Info From Files");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_info_from_files],htmlentities(GetParm('licenseinfofromfiles', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><textarea cols='120' rows='3' id='licenseinfofromfiles' name='licenseinfofromfiles'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Comment");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_comment],htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licensecomment' id='licensecomment' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Copyright Text");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[package_copyright_text],htmlentities(GetParm('packagecopyrighttext', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagecopyrighttext' id='packagecopyrighttext' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Summary");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[summary],htmlentities(GetParm('summary', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='summary' id='summary' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Description");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[description],htmlentities(GetParm('description', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='description' id='description' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $V.= "<tr><td colspan='3' style='background:black;'></td></tr>\n";
		        $V.= "</table><P/>";
	        }
					$V.= "\n<button type='button' onclick='window.close();'>Close</button>\n";
					$text = _("Next");
	        $V.= "\n<input type='hidden' value='$Val_SpdxId' name='spdxId' id='spdxId'><input type='submit' value='$text'>\n";
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
$NewPlugin = new spdx_packageInfoEdit_input;
?>
