<?php
/*
Code by Pro45s.com
ผู้พัฒนาโค้ด เจตน์สฤษฎิ์  พนิตอนงกริต
Developer by Mr.Chetsarhit  Phanitanongkrit
เมื่อ 2 กรกฏาคม 58

โค้ดใช้งาน การเชื่อมต่อฐานข้อมูลด้วย PDO Class (PHP Data Objects V2.0)
พร้อมชุดฟังชั่นสำหรับการรันคำสั่ง sql เพื่อเป็นการย่อคำสั่งให้สั้นลงไม่ยุ่งยากกับการใช้งาน
เราสามารถเรียกผ่าน getpdo($conn,"script sql"); ได้ทันที่

ตัวอย่างฐานข้อมูล

 CREATE TABLE IF NOT EXISTS `tb_pdo_test` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `FName` varchar(45) DEFAULT NULL,
  `bdate` datetime DEFAULT NULL,
  `bStatus` enum('Y','N') NOT NULL DEFAULT 'N',
  `bHack` varchar(45) DEFAULT NULL,
  `bDscp` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

ตัวอย่างการใช้งาน 45
$conn=conpdo();
$rs=getpdo($conn,"script sql");

*/

function getorder($s){/*order by field [asc desc] */if(strstr(strtoupper($s),'DESC'))return 'DESC';else return 'ASC';}
function conpdo($h=NULL,$d=NULL,$u=NULL,$p=NULL,$c="utf8",$t=3306){ /*เชื่อมต่อฐานข้อมูล*/
    if(!$h){$h="localhost";$d="db_test";$u="test";$p="test"; /* <- กำหนดค่าเชื่อมต่อฐานข้อมูลตรงนี้ 
     * $h คือ host เช่น localhost 127.0.0.1 etc. 
     * $d คือ database name
     * $u คือ username
     * $p คือ password 
     * */}
try {
    $m = new PDO("mysql:host=$h;port=$t;dbname=$d", $u, $p);
    $m->exec("set names $c");
    $m->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {echo 'ERROR: ' . $e->getMessage();}return $m;
}/* end function conpdo connect */

function getpdo($c,$s,$o=NULL,$b=NULL,$d=NULL){ /*รับคำสั่งประมวลผลสคริป sql พร้อมส่งค่ากลับ*/
    if(trim($s)=='')return '';if($o==NULL)$o=3;    
     $s=str_replace(array("']", "]"), "", $s);
     $s=str_replace('$_GET[', ":get_", $s);$s=str_replace('$_POST[', ":post_", $s);
     $s=str_replace('$_COOKIE[', ":cook_", $s);$s=str_replace('$_SESSION[', ":sess_", $s);
     if(isset($_GET))foreach($_GET as $k => $v) if(strstr($s,':get_'.$k))$p[':get_'.$k]=$v;
     if(isset($_POST))foreach($_POST as $k => $v) if(strstr($s,':post_'.$k))$p[':post_'.$k]=$v;       
     if(isset($_COOKIE))foreach($_COOKIE as $k => $v) if(strstr($s,':cook_'.$k))$p[':cook_'.$k]=$v;       
     if(isset($_SESSION))foreach($_SESSION as $k => $v) if(strstr($s,':sess_'.$k))$p[':sess_'.$k]=$v;         
     $c = $c->prepare($s, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    
    foreach($p as $k => $v){if(trim($v)==NULL){ echo  ' Error: '.$k; return; }if(is_numeric($v))$c->bindValue($k, (int)$v, PDO::PARAM_INT);else $c->bindValue($k, $v, PDO::PARAM_STR);}$rs=$c->execute();
    if(/*return field*/strlen($o)>1){$s=$c->fetchAll();foreach($s as $w)if(isset($w))$r=$w;if(!isset ($r))return ':(';$s=$r[$o]; if($b)$s.=' '.$r[$b];if($d)$s.=' '.$r[$d];return $s; }
    if(/*return array*/$o==1){return $a=$c->fetchAll();foreach($a as $w)if(!isset ($w))return array();$r=$w;return $r;}
    if(/*count row*/$o==2){return $c->rowCount();}
    if(/*check select*/strstr(strtolower(substr($s,0,6)),'select'))$o=4;
    if(/*exec script*/$o==3)return $rs;
    if(/*return fetch all*/$o==4)return $c->fetchAll();
}/* end function getpdo sql */

/*---------------------------------------- ใช้งานโค้ด getpdo $_GET $_POST ----------------------------------------------------*/
$conn=conpdo(); /* เชื่อมต่อฐานข้อมูล */

$bdat="สวัสดี PDO Test";

echo "\n\nInsert into: ";
$sq='insert into `tb_pdo_test` set `FName`=\'It Work! '.date("Y-m-d H:i:s").'\',`bdate`=\''.date("Y-m-d H:i:s").'\', `bStatus`=$_POST[bStatus],`bDscp`=\''.md5(microtime()).'\';';
getpdo($conn,$sq);


echo "\n\nDelete : ";
echo $sq='DELETE FROM `tb_pdo_test` WHERE `Id` = $_POST[del] Limit 1;';
if($_GET['del'])getpdo($conn,$sq);else echo ' # Error: $_POST[del] is NULL';

echo "\n\nField : ";
echo $sq='select `Id`,`FName` from `tb_pdo_test` where `Id` = $_GET[id] Limit 1;';
echo " # `Id`:".getpdo($conn,$sq,'Id','FName');

echo "\n\nUpdate: ";
echo $sq='UPDATE `tb_pdo_test` SET `FName`=\''.$bdat.'\', `bdate`=\''.date("Y-m-d H:i:s").'\', `bDscp`=$_POST[dat], `bStatus`=$_POST[bStatus] WHERE `Id`=$_GET[id] Limit 1;';
if((getpdo($conn,$sq)))echo "\nอัพเดทสำเร็จ".date("Y-m-d H:i:s");

echo "\n\nField : ";
echo $sq='select `FName` from `tb_pdo_test` where `Id` = $_GET[id] Limit 1;';
echo " # ".getpdo($conn,$sq,'FName');

echo "\n\nSelect return Array: ";
echo $sq='select * from `tb_pdo_test` where `Id` = $_GET[id] Limit 1;';
$result=getpdo($conn,$sq,1);
echo "\n";
if(isset($result))print_r($result); else echo "Limit : $c Error: No data!";

echo "\n\nRows Return count: ";
echo $sq='select * from `tb_pdo_test` where 1 OR `Id` = $_GET[id] Limit 100;';
echo " #".getpdo($conn,$sq,2);

echo "\n\nSelect return record: ";
echo $sq='select * from `tb_pdo_test` where 1 OR `Id` = $_GET[id] ORDER BY `Id` '.getorder($_POST['order']).' Limit $_POST[lm] ;';
echo "\n";
$result = getpdo($conn,$sq);

foreach ($result as $r) {
    print_r($r);
}

unset($result);

?>
