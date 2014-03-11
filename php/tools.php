<?php

  session_start();

  /*
  $PATH = getenv("PATH");
  putenv("PATH=".
              "/home/ivan/Dropbox/Code/python:".
              "/home/ivan/Programs/anaconda/bin:".
              "$PATH");
  putenv("XDG_CONFIG_HOME=/var/www");
  */

  $PATH = getenv("PATH");
  putenv("PATH=".
              "/home/astrochasqui/code/python:".
              "/home/astrochasqui/bin:".
              "$PATH");
  putenv("XDG_CONFIG_HOME=/home/astrochasqui/webapps/astro");

  $errMsg = "";

  // Color-Teff

  $color = "B-V";
  $color_value = 0.641;
  $feh_ct = "0.00";
  $color_teff_value = 5777;

  if(isset($_POST["color_teff"])) {
    $x = array("color_value", "feh_ct");
    foreach($x as $xi)
      if ($_POST[$xi] == "")
         $errMsg = "[Color-Teff] One or more fields are empty.";
    $_SESSION["color"] = $color = $_POST["color"];
    $_SESSION["color_value"] = $color_value = $_POST["color_value"];
    $_SESSION["feh_ct"] = $feh_ct = $_POST["feh_ct"];
    if ($errMsg == "") {
      if ($color == "B-V") $xcol = "bv";
      if ($color == "b-y") $xcol = "by_";
      if ($color == "V-Ks") $xcol = "vk";
      if ($color == "BT-VT") $xcol = "btvt";
      if ($color == "VT-Ks") $xcol = "vtk";
      $cmd = "c10teff_simple.py $xcol $color_value $feh_ct";
      $res = exec($cmd, $outs, $out);
      if($res > 0) {
        $color_teff_value = $res;
      } else {
        $errMsg = "[Color-Teff] Could not calculate T<sub>eff</sub>.";
        $color_teff_value = "";
      }
      $_SESSION["color_teff_value"] = $color_teff_value;
    }
    $_SESSION["errMsg"] = $errMsg;
    header("Location:".$_SERVER["PHP_SELF"]);
    exit();
  }
  if(isset($_SESSION["color"])) {
    $errMsg = $_SESSION["errMsg"];
    $color = $_SESSION["color"];
    $color_value = sprintf("%.3f", $_SESSION["color_value"]);
    $feh_ct = sprintf("%.2f", $_SESSION["feh_ct"]);
    $color_teff_value = $_SESSION["color_teff_value"];
    $_SESSION = array();
    session_destroy();
  }

  // Model Atmosphere Generator

  $teff_mag = 5777;
  $logg_mag = 4.44;
  $feh_mag = "0.00";
  $vt_mag = "1.00";
  $grid_mag = "odfnew";

  if(isset($_POST["model_atmosphere_generator"])) {
    if (!file_exists("tools_files")) mkdir("tools_files");
    $x = array("teff_mag", "logg_mag", "feh_mag", "vt_mag");
    foreach($x as $xi)
      if ($_POST[$xi] == "")
        $errMsg = "[Model Atmosphere Generator] One or more fields are empty.";
    if ($errMsg == "") {
      $teff = $_SESSION["teff_mag"] = $_POST["teff_mag"];
      $logg = $_SESSION["logg_mag"] = $_POST["logg_mag"];
      $feh = $_SESSION["feh_mag"] = $_POST["feh_mag"];
      $vt = $_SESSION["vt_mag"] = $_POST["vt_mag"];
      $grid = $_SESSION["grid_mag"] = $_POST["grid_mag"];
      if ($feh>=0) $fehx="p".abs($feh); else $fehx="m".abs($feh);
      $fname = "tools_files/t$teff"."g$logg$fehx.$grid";
      if (!file_exists($fname)) {
        $cmd = "modatmgen.py $teff $logg $feh $vt $grid $fname";
        $res = exec($cmd);
      }
      if (file_exists($fname)) {
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename='.basename($fname));
        ob_clean();
        flush();
        readfile($fname);
      } else {
        $errMsg = "[Model Atmosphere Generator] Could not interpolate model.";
      }
    }
    $_SESSION["errMsg"] = $errMsg;
    header("Location:".$_SERVER["PHP_SELF"]);
    exit();
  }
  if(isset($_SESSION["teff_mag"])) {
    $errMsg = $_SESSION["errMsg"];
    $teff_mag = $_SESSION["teff_mag"];
    $logg_mag = sprintf("%.2f", $_SESSION["logg_mag"]);
    $feh_mag = sprintf("%.2f", $_SESSION["feh_mag"]);
    $vt_mag = sprintf("%.2f", $_SESSION["vt_mag"]);
    $grid_mag = $_SESSION["grid_mag"];
    $_SESSION = array();
    session_destroy();
  }

  // NLTE oxygen triplet

  $teff_nlte = 5777;
  $logg_nlte = 4.44;
  $feh_nlte = "0.00";
  $ao1 = "8.80";
  $ao2 = "8.78";
  $ao3 = "8.77";
  $d0 = 0.136;
  $d1 = 0.127;
  $d2 = 0.116;

  if(isset($_POST["nlte"])) {
    $x = array("teff_nlte", "logg_nlte", "feh_nlte", "ao1", "ao2", "ao3");
    foreach($x as $xi)
      if ($_POST[$xi] == "")
         $errMsg = "[NLTE oxygen triplet] One or more fields are empty.";
    if ($errMsg == "") {
      $teff_nlte = $_SESSION["teff_nlte"] = $_POST["teff_nlte"];
      $logg_nlte = $_SESSION["logg_nlte"] = $_POST["logg_nlte"];
      $feh_nlte = $_SESSION["feh_nlte"] = $_POST["feh_nlte"];
      $ao1 = $_SESSION["ao1"] = $_POST["ao1"];
      $ao2 = $_SESSION["ao2"] = $_POST["ao2"];
      $ao3 = $_SESSION["ao3"] = $_POST["ao3"];
      $cmd = "nlte_triplet.py $teff_nlte $logg_nlte $feh_nlte $ao1 $ao2 $ao3";
      exec($cmd, $res);
      if (substr($res[0], 0, 3) != "Wav") {
        $errMsg = "[Non-LTE corrections] $res[0]";
      } else {
        $x = explode("|", $res[1]); $d0 = 1.*$x[2];
        $x = explode("|", $res[2]); $d1 = 1.*$x[2];
        $x = explode("|", $res[3]); $d2 = 1.*$x[2];
        if ($d0 == 0) $d0 = "";
        if ($d1 == 0) $d1 = "";
        if ($d2 == 0) $d2 = "";
        $_SESSION["d0"] = $d0;
        $_SESSION["d1"] = $d1;
        $_SESSION["d2"] = $d2;
      }
    }
    $_SESSION["errMsg"] = $errMsg;
    header("Location:".$_SERVER["PHP_SELF"]);
    exit();
  }
  if(isset($_SESSION["teff_nlte"])) {
    $errMsg = $_SESSION["errMsg"];
    $teff_nlte = $_SESSION["teff_nlte"];
    $logg_nlte = sprintf("%.2f", $_SESSION["logg_nlte"]);
    $feh_nlte = sprintf("%.2f", $_SESSION["feh_nlte"]);
    $ao1 = sprintf("%.2f", $_SESSION["ao1"]);
    $ao2 = sprintf("%.2f", $_SESSION["ao2"]);
    $ao3 = sprintf("%.2f", $_SESSION["ao3"]);
    $d0 = sprintf("%.3f", $_SESSION["d0"]);
    $d1 = sprintf("%.3f", $_SESSION["d1"]);
    $d2 = sprintf("%.3f", $_SESSION["d2"]);
    $_SESSION = array();
    session_destroy();
  }

?>
