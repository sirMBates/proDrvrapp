<?php

if ($msg = $alert::getMsg('success')) { ?>
        <div id="flash-alert" class="my-2 alert alert-success alert-dismissible" role="alert"><i class="me-2 fa-solid fa-thumbs-up"><span class="flash-text"><?= $msg;?></span></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} elseif ($msg = $alert::getMsg('warning')) { ?>
        <div id="flash-alert" class="my-2 alert alert-warning alert-dismissible" role="alert"><i class="me-2 fa-solid fa-triangle-radiation"><span class="flash-text"><?= $msg;?></span></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} elseif ($msg = $alert::getMsg('danger')) { ?>
        <div id="flash-alert" class="my-2 alert alert-danger alert-dismissible" role="alert"><i class="me-2 fa-solid fa-circle-radiation"><span class="flash-text"><?= $msg;?></span></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} elseif ($msg = $alert::getMsg('info')) { ?>
        <div id="flash-alert" class="my-2 alert alert-info alert-dismissible" role="alert"><i class="me-2 fa-solid fa-circle-info"><span class="flash-text"><?= $msg;?></span></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} elseif ($msg = $alert::getMsg('error')) { ?>
        <div id="flash-alert" class="my-2 alert alert-dark alert-dismissible" role="alert"><i class="me-2 fa-solid fa-thumbs-down"><span class="flash-text"><?= $msg;?></span></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} ?>