<?php
$alert::setMsg($alert::setType($_GET), $alert::displayMsg());
if ($msg = $alert::getMsg($alert::setType($_GET))) { ?>
        <div id="flash-alert" class="alert alert-success alert-dismissible" role="alert"><i class="ms-2 fa-solid<?php $alert::iconType($_GET);?>"><?= $msg;?></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} /*elseif ($msg = $alert->getMsg('error')) {
        # code...
};*/
?>