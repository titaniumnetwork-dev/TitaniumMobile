<?php

$Lang="en";

/////////////////////////////////////////////////////
if(isset($Lang) and $Lang!==''){
  $lang_file = 'lang.'.$Lang.'.php';
}else{
  $lang_file = 'lang.ch.php';
}

  include_once 'lang/'.$lang_file;
/////////////////////////////////////////////////////
?>
