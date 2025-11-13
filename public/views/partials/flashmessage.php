<?php

if ($msg = $alert::getMsg('success')) { ?>
        <div id="flash-alert" class="my-2 alert alert-success alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-thumbs-up"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('warning')) { ?>
        <div id="flash-alert" class="my-2 alert alert-warning alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($msg = $alert::getMsg('danger')) { ?>
        <div id="flash-alert" class="my-2 alert alert-danger alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
}
?>
<script>
        document.addEventListener("DOMContentLoaded", function() {
                const alertSpan = document.querySelector("#flash-alert span");
                if (!alertSpan) return;

                const name = alertSpan.textContent.trim();
                if (!name) return;

                const hour = new Date().getHours();
                let greeting = 'Hello';

                if ( hour >= 5 && hour < 12 ) {
                        greeting = "Good morning";
                } else if ( hour >= 12 && hour < 18 ) {
                        greeting = "Good afternoon";
                } else if ( hour >= 18 && hour <= 23 ) {
                        greeting = "Good evening";
                } else {
                        greeting = "Hello";
                }

                alertSpan.textContent = `${greeting}, ${name}`;

                // ===== ANIMATION STYLE =====
                flash.style.opacity = 0;
                flash.style.transform = "translateY(-15px)";
                flash.style.transition = "opacity 0.5s ease, transform 0.5s ease";

                // Animate in
                requestAnimationFrame(() => {
                        flash.style.opacity = 1;
                        flash.style.transform = "translateY(0)";
                });

                // Auto remove after 3.5 seconds (matches your helper default)
                const timeout = 3500;

                setTimeout(() => {
                        flash.style.opacity = 0;
                        flash.style.transform = "translateY(-15px)";
                        setTimeout(() => flash.remove(), 500);
                }, timeout);
        });
</script>