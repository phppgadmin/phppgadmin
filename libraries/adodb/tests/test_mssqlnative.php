<?php

error_reporting(E_ALL|E_STRICT);

include('../adodb.inc.php');
// -------- Internal Trace functions
function Trace($Msg){
  echo "<br>\n".$Msg;
}
function DieTrace($Msg){
  die("<br>\n".$Msg);
}


define('ADODB_ASSOC_CASE',0);
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$db = ADONewConnection("mssqlnative");  // create a connection
$db->Connect('127.0.0.1','adodb','natsoft','northwind') or die('Fail');

//==========================
// This code tests GenId
//==========================
function TestGenID() {
  global $db;
  $PrevDebug=$db->debug;  $db->debug=false; // Hide debug if present as Drop can cause errors
  $db->Execute("drop sequence MySequence1;
drop sequence MySequence2;
drop sequence MySequence3;
drop table MySequence1Emul;
drop table MySequence2Emul;
drop table MySequence3Emul;
");
  $db->debug=$PrevDebug; // Restore debug Initial State

  $ID1a=$db->GenID("MySequence1");
  $ID2a=$db->GenID("MySequence2");
  $ID1b=$db->GenID("MySequence1");
  $ID2b=$db->GenID("MySequence2");
  Trace("ID1a=$ID1a,ID1b=$ID1b, ID2a=$ID2a,ID2b=$ID2b");
  if(intval($ID1a)+1!==intval($ID1b)) DieTrace(sprintf("ERROR : Second value obtains by MySequence1 should be %d but is %d",$ID1a+1,$ID1b));

  $db->CreateSequence("MySequence3",100);
  $ID2b=$db->GenID("MySequence3");
  if(intval($ID2b)!==100) DieTrace(sprintf("ERROR : Value from MySequence3 should be 100 but is %d",$ID2b));

  $db->mssql_version=10; // Force to simulate Pre 2012 (without sequence) behavior
  $ID1a=$db->GenID("MySequence1Emul");
  $ID2a=$db->GenID("MySequence2Emul");
  $ID1b=$db->GenID("MySequence1Emul");
  $ID2b=$db->GenID("MySequence2Emul");
  echo "ID1a=$ID1a,ID1b=$ID1b, ID2a=$ID2a,ID2b=$ID2b <br>\n";
  if(intval($ID1a+1)!==intval($ID1b)) DieTrace(sprintf("ERROR : Second value obtains by MySequence1Emul should be %d but is %d",$ID1a+1,$ID1b));

  $db->CreateSequence("MySequence3Emul",100);
  $ID2b=$db->GenID("MySequence3Emul");
  if(intval($ID2b)!==100) DieTrace(sprintf("ERROR : Value from MySequence3Emul should be 100 but is %d",$ID2b));
  } //TestGenID()

//==========================
// This code tests SQLDate
//==========================
function TestSQLDate()
{
  global $db;
  $res = $db->GetRow("select testdate,"
    . $db->SQLDate("d/m/Y", "testdate") . " FR4,"
    . $db->SQLDate("d/m/y", "testdate") . " FR4b,"
    . $db->SQLDate("d/m/Y", "NULL") . " nullFR4,"
    . $db->SQLDate("m/d/Y", "testdate") . " US4,"
    . $db->SQLDate("m/d/y", "testdate") . " US4b,"
    . $db->SQLDate("m-d-Y", "testdate") . " USD4,"
    . $db->SQLDate("m-d-y", "testdate") . " USD4b,"
    . $db->SQLDate("Y.m.d", "testdate") . " ANSI4,"
    . $db->SQLDate("d.m.Y", "testdate") . " GE4,"
    . $db->SQLDate("d.m.y", "testdate") . " GE4b,"
    . $db->SQLDate("d-m-Y", "testdate") . " IT4,"
    . $db->SQLDate("d-m-y", "testdate") . " IT4b,"
    . $db->SQLDate("Y/m/d", "testdate") . " Japan4,"
    . $db->SQLDate("y/m/d", "testdate") . " Japan4b,"
    . $db->SQLDate("H:i:s", "testdate") . " timeonly,"
    . $db->SQLDate("d m Y", "testdate") . " Space4,"  // Is done by former method
    . $db->SQLDate("d m Y", "NULL") . " nullSpace4,"
    . $db->SQLDate("m-d-Y", "testdatesmall") . " nowUSdash4,"
    . "null from (select convert(datetime,'2016-12-17 18:55:30.590' ,121) testdate,
        convert(datetime,'2016-01-01 18:55:30.590' ,121) testdatesmall,null nulldate) q "
  );
  $TestRes=array(
    "fr4"=>"17/12/2016",
    "fr4b"=>"17/12/2016",
    "nullfr4"=>null,
    "us4"=>"12/17/2016",
    "us4b"=>"12/17/2016",
    "ansi4"=>"2016.12.17",
    "ge4"=>"17.12.2016",
    "ge4b"=>"17.12.2016",
    "it4"=>"17-12-2016",
    "it4b"=>"17-12-2016",
    "japan4"=>"2016/12/17",
    "japan4b"=>"2016/12/17",
    "space4"=>"17 12 2016",
    "nullspace4"=>null,
    "timeonly"=>"18:55:30",
  );
  var_dump($res);
  foreach($TestRes as $k=>$v)
    if($v!==$res[$k])
      DieTrace(sprintf("ERROR : Expected for '%s' is '%s', but got '%s'",$k,$v,$res[$k]));
} //TestSQLDate()

//==========================
// This code is the tests RUNNER
//==========================
$db->debug=true;
// $ToTest Contains * or the name of the test function to RUN
$ToTest="*";
//$ToTest="TestSQLDate";

// Here the generic test runner, will launch all functions of the current file beginning by "test", should not be changed use $ToTest
$functions = get_defined_functions();
$functions = $functions['user'];
foreach( $functions as $f) {
  $refFunc = new ReflectionFunction($f);
  if(($refFunc->getFileName()==__FILE__)&&(substr($f,0,4)=='test'))
    if(($ToTest=='*')||(strtolower($ToTest)==$f))
    {
      Trace("<b>-------- Launch Test : $f ------------------</b>");
      $f();
    }
}

Trace("<b>=========== End of tests Without Error. ===================</b>");