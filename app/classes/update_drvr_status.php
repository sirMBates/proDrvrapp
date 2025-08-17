<?php

use core\Flash;

class UpdateDrvrStatusContr extends UpdateDrvrStatus {
    private $drvrid;
    private $drvrStatus;
    private $drvrToken;

    public function __construct($drvrid, $drvrStatus, $drvrToken) {
        $this->drvrid = $drvrid;
        $this->drvrStatus = $drvrStatus;
        $this->drvrToken = $drvrToken;
    }

    public function checkAndUpdateDrvrStatus() {}
}
?>