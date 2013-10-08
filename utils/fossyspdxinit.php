#!/usr/bin/php
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
 * @file fossyspdxinit.php
 * @brief This program applies configuration variables to the program
 *
 * This should be used immediately after an install or update.
 *
 * @return 0 for success, 1 for failure.
 **/

/* Initialize the program configuration variables */
$SysConf = array();  // fossology+spdx system configuration variables
$Plugins = array();

/* Note: php 5 getopt() ignores options not specified in the function call, so add
 * dummy options in order to catch invalid options.
 */
$AllPossibleOpts = "abc:d:ef:ghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

/* defaults */
$Verbose = false;
$sysconfdir = '';

/* command-line options */
$Options = getopt($AllPossibleOpts);
foreach($Options as $Option => $OptVal)
{
  switch($Option)
  {
    case 'c': /* set SYSCONFIDR */
      $sysconfdir = $OptVal;
      break;
    case 'h': /* help */
      Usage();
    case 'v': /* verbose */
      $Verbose = true;
      break;
    default:
      echo "Invalid Option \"$Option\".\n";
      Usage();
  }
}

/* Set SYSCONFDIR and set global (for backward compatibility) */
$SysConf = bootstrap($sysconfdir);
// don't think we want all the other common libs
require_once("$MODDIR/lib/php/common.php");

/* Initialize global system configuration variables $SysConfig[] */
ConfigInit($SYSCONFDIR, $SysConf);

exit(0);


/** \brief Print Usage statement.
 *  \return No return, this calls exit.
 **/
function Usage()
{
  global $argv;

  $usage = "Usage: " . basename($argv[0]) . " [options]
  Update FOSSology+SPDX.  Options are:
  -c  path to fossology+spdx configuration files
  -h  this help usage";
  print "$usage\n";
  exit(0);
}

/************************************************/
/******  From src/lib/php/bootstrap.php  ********/
/** Included here so that fossinit can run from any directory **/
/**
 * \brief Bootstrap the fossology php library.
 *  - Determine SYSCONFDIR
 *  - parse fossology.conf
 *  - source template (require_once template-plugin.php)
 *  - source common files (require_once common.php)
 *
 * The following precedence is used to resolve SYSCONFDIR:
 *  - $SYSCONFDIR path passed in ($sysconfdir)
 *  - environment variable SYSCONFDIR
 *  - ./fossology.rc
 *
 * Any errors are fatal.  A text message will be printed followed by an exit(1)
 *
 * \param $sysconfdir Typically from the caller's -c command line parameter
 *
 * \return the $SysConf array of values.  The first array dimension
 * is the group, the second is the variable name.
 * For example:
 *  -  $SysConf[DIRECTORIES][MODDIR] => "/mymoduledir/
 *
 * The global $SYSCONFDIR is also set for backward compatibility.
 *
 * \Note Since so many files expect directory paths that used to be in pathinclude.php
 * to be global, this function will define the same globals (everything in the
 * DIRECTORIES section of fossology.conf).
 */
function bootstrap($sysconfdir="")
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
  //require("i18n.php"); DISABLED until i18n infrastructure is set-up.
  require_once("$MODDIR/www/ui/template/template-plugin.php");
  require_once("$MODDIR/lib/php/common.php");
  return $SysConf;
}
?>
