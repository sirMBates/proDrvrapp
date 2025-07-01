<?php
if ($msg = $alert->getMsg($alert::displayType($_GET))) { ?>
        <div id="flash-alert" class="alert alert-<?= $alert::displayType($_GET);?> alert-dismissible" role="alert"><i class="<?= $alert::iconType($_GET)?>"><?= $msg;?></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} elseif ($msg = $alert->getMsg('error', 'acct-created')) {
        # code...
};
?>