<?php
$alert::setMsg($alert::displayType($_GET), $alert::displayMsg());
if ($msg = $alert::getMsg($alert::getMsgType())) { ?>
        <div id="flash-alert" class="alert alert-<?php $alert::displayType($_GET);?> alert-dismissible" role="alert"><i class="ms-2 fa-solid<?php $alert::iconType($_GET);?>"><?= $msg;?></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} /*elseif ($msg = $alert->getMsg('error')) {
        # code...
};*/
?>