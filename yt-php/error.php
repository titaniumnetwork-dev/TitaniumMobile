<?php

require_once "lang.conf.php";

header("HTTP/1.0 404 Not Found");
$headtitle=$lang['ERROR_HTITLE'];
include("./header.php");?>

<div class="container-fluid" style="height: 480px;
    background-color: #dbdbdb;">
    <div class="container" style="height: 100%">
        <div class="row" style="height: 100%">
 <div class="col-12 justify-content-center align-self-center text-center">
     <img src="//wx3.sinaimg.cn/large/b0738b0agy1fm04l0cw4ej203w02s0sl.jpg" class="p-2" >
      <h2><?php echo $lang['ERROR_T']?></h2>
      <p><?php echo $lang['ERROR_M1']?></p>
      <p><?php echo $lang['ERROR_M2']?></p>
      <p><?php echo $lang['ERROR_M3']?></p>
      <p><?php echo $lang['ERROR_M4']?></p>
      <p><?php echo $lang['ERROR_M5']?></p>
      <p><?php echo $lang['ERROR_M6']?></p>
  </div>

  </div>
    </div>

</div>


<?php
include("./footer.php");
?>
