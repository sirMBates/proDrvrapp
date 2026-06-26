<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$alert = new core\Flash();

$headers = getallheaders();
$headerToken = $headers['X-CSRF-Token'] ?? $_POST['X-CSRF-Token'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if ($sessionToken === null) {
    $alert::setMsg('error', 'Your session has expired. Please sign in again.');
    header("Location: /signin");
    exit();
}

if ($headerToken !== $sessionToken) {
    $alert::setMsg('error', 'Access denied due to invalid token.');
    header("Location: /assignments?error=csrf");
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}

if ($method === 'PATCH') {
    if (isset($_POST['modify'])) {
        include_once base_path("app/models/assignmenthandlermodel.php");
        include_once base_path("app/errors/check_assignment_details.php");
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $modification = new UpdateAssignmentDetailsContr($data);
        $result = $modification->modify();
        /*file_put_contents('D:/webapps/logs/updateassignment_debug.log', "[" . date('Y-m-d H:i:s') . "] RESULT:\n" . print_r($result, true) . "\nPOST:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);*/

        if ( ($result['status'] ?? '') === 'success' ) {
            $alert::setMsg('success', $result['message'] ?? 'Assignment updated successfully.');
            $orderId = urlencode( (string)($result['order_id'] ?? ($data['order_id'] ?? '')) );
            header("Location: /assignments?updated=1&order_id={$orderId}");
            exit();
        }

        $alert::setMsg('error', $result['message'] ?? 'Assignment update failed.');
        header("Location: /assignments?error=update+failed");
        exit();
    }
}

?>