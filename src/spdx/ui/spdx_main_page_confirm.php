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

define("TITLE_spdx_main_page_confirm", _("SPDX Edit"));

/**
 * \class spdx_main_page extend from FO_Plugin
 * \brief 
 */
class spdx_main_page_confirm extends FO_Plugin
{
  public $Name       = "spdx_main_page_confirm";
  public $Title      = TITLE_spdx_main_page_confirm;
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
    session_start();
    $fileSuffix = session_id();
    $_SESSION['fileSuffix'] = $fileSuffix;
    $V = "";

    switch($this->OutputType) {
      case "XML":
        break;
      case "HTML":
        $Uri = Traceback_uri();
        
        /* Create JavaScript for outputing files */
        $V.= "\n<script language='javascript'>\n";
        $V.= "function outputspdx(){
              if (document.getElementById('spdxOutputType').value == 'rdf'){
                window.location.href='spdx-output-module/spdx_main_output_rdf.php';
                return true;
                }
              else if (document.getElementById('spdxOutputType').value == 'tag'){
                window.location.href='spdx-output-module/spdx_main_output_tag.php?fileSuffix=$_SESSION[fileSuffix]';
                return true;
              }
              else if (document.getElementById('spdxOutputType').value == 'notice'){
                window.location.href='spdx-output-module/spdx_main_output_notice.php?fileSuffix=$_SESSION[fileSuffix]';
                return true;
              }
              else if (document.getElementById('spdxOutputType').value == 'notice2'){
                window.location.href='spdx-output-module/spdx_main_output_notice2.php?fileSuffix=$_SESSION[fileSuffix]';
                return true;
              }
              else {
                return true;
              }
             }";
        $V.= "</script>\n";
        /* Build HTML form */
        $V.= "<form name='spdxEditAny' method='POST'>\n"; // no url = this url
        $V.= "<P />\n";
        $text = _("This option permits editing/creating a single SPDX file from analised packages/files by FOSSology.");
        $V.= "$text<P />\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
        $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow; table-layout: fixed;' width='100%'>";
        $Val = htmlentities(GetParm('spdxVersion', PARM_TEXT), ENT_QUOTES);
        $text = _("SPDX Version");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='spdxVersion'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creator', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='creator'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorOptional1', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator optional1");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='creatorOptional1' value='$Val'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorOptional2', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator optional2");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='creatorOptional2' value='$Val'>$Val</td>\n";
        $V.= "</tr>\n";
        $ValDate = htmlentities(GetParm('created_Date', PARM_TEXT), ENT_QUOTES);
        $ValTime = htmlentities(GetParm('created_Time', PARM_TEXT), ENT_QUOTES);
        $text = _("Created Date");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='created_Date' value='$ValDate'><input type='hidden' name='created_Time' value='$ValTime'>$ValDate $ValTime</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('dataLicense', PARM_TEXT), ENT_QUOTES);
        $text = _("Data License");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='dataLicense' value='$Val'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorComment', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator Comment");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='creatorComment' value='$Val'><span class='bodySmall'>$Val</span></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('documentComment', PARM_TEXT), ENT_QUOTES);
        $text = _("Document Comment");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='documentComment' value='$Val'><span class='bodySmall'>$Val</span></td>\n";
        $V.= "</tr>\n";
        $text = _("Output File Type");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><select id='spdxOutputType'><option value='tag'>SPDX-TAG</option><option value='notice'>NOTICE-Format1</option><option value='notice2'>NOTICE-Format2</option></select></td>\n";
        $V.= "</tr>\n";
        $V.= "</table><P />";
        /* Get selected packages */
        $packagePks = $_POST['packages'];
        
        $sql = "select max(pkg_name) as name,max(version) as version,max(source_info) as source_info,max(description) as description,max(pkg_filename) as filename,pfile_pk as pfile_fk,pfile_sha1,upload_fk,uploadtree_pk
							from(
							select pkg_name,version,source_rpm as source_info,description,source_rpm as pkg_filename,pfile_pk,pfile_sha1,upload_fk,uploadtree_pk from pkg_rpm,pfile,uploadtree where uploadtree_pk in($packagePks) and uploadtree.pfile_fk = pfile_pk and pfile_pk= pkg_rpm.pfile_fk UNION 
							select pkg_name,version,'' as source_info,description,source as pkg_filename,pfile_pk,pfile_sha1,upload_fk,uploadtree_pk from pkg_deb,pfile,uploadtree where uploadtree_pk in($packagePks) and uploadtree.pfile_fk = pfile_pk and pfile_pk = pkg_deb.pfile_fk UNION
							select upload_filename as pkg_name, '' as version, '' as source_info,upload_desc as description,upload_origin as pkg_filename,pfile_pk,pfile_sha1,upload_fk,uploadtree_pk from pfile,uploadtree,upload where uploadtree_pk in($packagePks) and uploadtree.pfile_fk = pfile_pk and uploadtree.upload_fk = upload_pk
							) T 
							group by pfile_fk,pfile_sha1,upload_fk,uploadtree_pk";
				$result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        $_SESSION['spdx_pkg']=$result;
        $_SESSION['packagePks']=$packagePks;
        Spdx_insert_update_spdx();
        $sqlPackage = "select * from spdx_package_info where verificationcode<> '' and spdx_fk = ".$_SESSION['spdxId'];
        $resultPackage = pg_query($PG_CONN, $sqlPackage);
        DBCheckResult($resultPackage, $sqlPackage, __FILE__, __LINE__);
        if (pg_num_rows($resultPackage) > 0){
        	$result = $resultPackage;
        }
        else
        {
	        $result = pg_query($PG_CONN, $sql);
	        DBCheckResult($result, $sql, __FILE__, __LINE__);
	      }
        if (pg_num_rows($result) > 0){
        	$text = _("Package(s)");
	        $V .= "$text<br>\n";
	        $V.= "<table border='1' width='100%'>";
	        $V.= "<tbody><tr><th width='15%'>Name</th><th width='20%'>Version</th><th width='20%'>Source Info</th><th width='40%'>Description</th><th>&nbsp;</th></tr>";
	        pg_result_seek($result, 0);
	        while ($package = pg_fetch_assoc($result))
	        {
	        	$V.= "<tr><td align='left'>" . $package['name'] . "</td><td align='left'>" . $package['version'] . "</td><td align='left'>" . $package['source_info'] . "</td><td align='left'style='overflow: hidden;'>" . $package['description'] . "</td><td><a href=".$Uri."?mod=spdx_packageInfoEdit_input&spdxId=" . $_SESSION['spdxId'] . "&pfile=" . $package['pfile_fk'] . " target='_blank')>detail/edit</a></td></tr>";
	        }
	        $V.= "</tbody></table><br>";
	      }
	      pg_free_result($result);
	      $V.= "\n<button type='button' onclick='history.back();'>Back</button>\n";
        $text = _("Create");
        $V.= "\n<button type='button' onclick='outputspdx()'>Create</button>\n";
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
$NewPlugin = new spdx_main_page_confirm;
?>
