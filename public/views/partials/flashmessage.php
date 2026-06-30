<?php

if ($flash = $alert::getMsg('success')) { 
        $msg = $flash['message'];
        $greet = $flash['options']['greet'] ?? false;
?>
        <div id="flash-alert" data-greet="<?= $greet ? 'true' : 'false'; ?>" class="my-2 alert alert-success alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-thumbs-up"></i><span class="fs-5"><?= htmlspecialchars($msg); ?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($flash = $alert::getMsg('warning')) {
        $msg = $flash['message']; 
?>
        <div id="flash-alert" class="my-2 alert alert-warning alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($flash = $alert::getMsg('danger')) {
        $msg = $flash['message']; 
?>
        <div id="flash-alert" class="my-2 alert alert-danger alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-radiation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($flash = $alert::getMsg('info')) { 
        $msg = $flash['message'];
?>
        <div id="flash-alert" class="my-2 alert alert-info alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-info"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
} 
elseif ($flash = $alert::getMsg('error')) {
        $msg = $flash['message']; 
?>
        <div id="flash-alert" class="my-2 alert alert-dark alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-thumbs-down"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
}
elseif ($flash = $alert::getMsg('validate')) {
        $msg = $flash['message'];
?>
        <div id="flash-alert" class="my-2 alert alert-primary alert-dismissible" role="alert"><i class="fs-5 me-2 fa-solid fa-circle-exclamation"></i><span class="fs-5"><?= $msg;?></span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
}
?>
<script>
        document.addEventListener("DOMContentLoaded", function() {
                const flash = document.querySelector('#flash-alert');
                if (!flash) return;

                const alertSpan = flash.querySelector("span");
                if (!alertSpan) return;

                const shouldGreet = flash.dataset.greet === "true";

                if (shouldGreet) {
                        const name = alertSpan.textContent.trim();
                        if (name) {
                                const hour = new Date().getHours();
                                let greeting = 'Hello';

                                if ( hour >= 5 && hour < 12 ) {
                                        greeting = "Good morning";
                                } else if ( hour >= 12 && hour < 18 ) {
                                        greeting = "Good afternoon";
                                } else if ( hour >= 18 && hour <= 23 ) {
                                        greeting = "Good evening";
                                }

                                alertSpan.textContent = `${greeting}, ${name}`;
                        }
                }

                // ===== ANIMATION STYLE =====
                flash.style.opacity = 0;
                flash.style.transform = "translateY(-15px)";
                flash.style.transition = "opacity 0.5s ease-in, transform 0.5s ease";

                // Animate in
                requestAnimationFrame(() => {
                        flash.style.opacity = 1;
                        flash.style.transform = "translateY(0)";
                });

                // Auto remove after 3.5 seconds (matches your helper default)
                const timeout = 4000;

                setTimeout(() => {
                        flash.style.opacity = 0;
                        flash.style.transform = "translateY(-15px)";
                        setTimeout(() => flash.remove(), 500);
                }, timeout);
        });
</script>