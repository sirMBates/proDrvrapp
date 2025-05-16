<?php
    $title = "Pro Driver - WWS Sheet";
    require_once '../includes/head.php';
?>

<body class="d-flex flex-column vh-100 overflow-x-hidden plain-bkgd">
<?php
    require_once '../includes/navbar.php';
?>

    <main class="container-fluid p-3 d-flex flex-column flex-nowrap">
        <div class="container-fluid">
            <div class="w-100 d-inline-flex align-items-center justify-content-center" style="height: 75px;">
                <img src="/public/images-videos/logoandicons/BestTrailsTravels_Logo.png" alt="N/A" width="60" height="60" class="mx-2">
                <h4 class="text-capitalize">best trails driver</h4>
            </div>
            <div class="w-100 my-2">
                <div class="d-flex justify-content-between">
                    <div class="text-capitalize my-2">last, first</div>
                    <div class="text-capitalize my-2">pay week start</div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="text-capitalize my-2">driver Id#</div>
                    <div class="text-capitalize my-2">pay week end</div>
                </div>
            </div>
        </div>

        <div id="paySheetCardCon" class="container-fluid">
            <div class="card border-0">
                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <thead class="text-capitalize text-center align-middle">
                            <tr>
                                <th scope="col">motorcoach<br>order#</th>
                                <th scope="col">destination<br>to/from</th>
                                <th scope="col">vehicle id</th>
                                <th scope="col">date</th>
                                <th scope="col">spot time</th>
                                <th scope="col">drop time</th>
                                <th scope="col">total hours</th>
                                <th scope="col">tolls</th>
                                <th scope="col">tip</th>
                                <th scope="col">amount paid</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
<?php
    require '../includes/getscripts.php';
?>
</body>
</html>