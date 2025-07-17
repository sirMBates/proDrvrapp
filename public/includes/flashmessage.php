<?php

if ($msg = $alert::getMsg('success')) { ?>
        <div id="flash-alert" class="my-2 alert alert-success alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-thumbs-up"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('warning')) { ?>
        <div id="flash-alert" class="my-2 alert alert-warning alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-triangle-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('danger')) { ?>
        <div id="flash-alert" class="my-2 alert alert-danger alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('info')) { ?>
        <div id="flash-alert" class="my-2 alert alert-info alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-info"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('error')) { ?>
        <div id="flash-alert" class="my-2 alert alert-dark alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-thumbs-down"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
}
elseif ($msg = $alert::getMsg('validate')) { ?>
        <div id="flash-alert" class="my-2 alert alert-primary alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-exclamation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
}?>