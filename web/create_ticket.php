<?php
ob_start();
include 'header.php';

if (!isset($_SESSION['USERID'])) {
    // create a new session as ticket, to view in dashboard
    $_SESSION['ticket'] = 'new';
    header("Location:login.php");
    return;
}

checkAccess('customer'); // only customers should be allowed to create a ticket

$userid = $_SESSION['USERID'];
$db = dbConn();
$sql = "SELECT CustomerId FROM customers WHERE UserId='$userid'";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$customerid = $row['CustomerId'];

// when scan the qr or submit the serial number
extract($_GET);
if (!empty($serialno)) {
    $sql = "SELECT "
            . "isn.SerialNo,"
            . "o.order_date,"
            . "oii.order_id,"
            . "i.model_no,"
            . "i.item_category,"
            . "ic.category_name "
            . "FROM issued_serial_numbers isn "
            . "INNER JOIN order_items_issue oii ON oii.id=isn.Order_Items_Issue_Id "
            . "INNER JOIN items i ON i.id=oii.item_id "
            . "INNER JOIN orders o ON o.id=oii.order_id "
            . "INNER JOIN item_category ic ON ic.id=i.item_category "
            . "WHERE isn.SerialNo='$serialno'";
    $result = $db->query($sql);

    // if serial number record found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $serial_no = $row['SerialNo'];
        $machine_cat_id = $row['item_category']; // to use the category id in javascripts to load the issues according to category
        $machine_cat = $row['category_name'];
        $model_no = $row['model_no'];
        $purchase_date = $row['order_date'];
        $warranty = 'Active';
        $orderId = $row['order_id'];
    } else { // if there is no such serial number
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Serial',
                    html: '<h5>Please enter a valid serial number</h5>',
                    showCloseButton: false,
                    showConfirmButton: false,
                    timer: 4000
                    });
                </script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);

    $message = array();
    if (empty($machine_cat)) {
        $message['machine_cat'] = "Machine category can not be empty!";
    }
    if (empty($model_no)) {
        $message['model_no'] = "Model Number can not be empty!";
    }
    if (empty($issue)) {
        $message['issue'] = "Please select the issue with your machine";
    }
    if (empty($comments)) {
        $message['comments'] = "Please describe the issue with more details";
    }
    if (empty($message)) {
        // get the last record id from the table 
        $sql = "SELECT TicketId FROM tickets ORDER BY TicketId DESC LIMIT 1";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $tid = $row['TicketId'];
        $tid = $tid + 1;
        $ticket_no = 'T-' . date('y') . date('m') . date('d') . $tid;
        $_SESSION['TNO'] = $ticket_no;
        
        // get the order id from the serial

        $date = date("Y-m-d");
        $sql = "INSERT INTO tickets(TicketNo, OpenedDate, CustomerId, OrderId, MachineCatId, ModelNo, SerialNo, CommonIssueId, Description) "
                . "VALUES ('$ticket_no','$date','$customerid','$orderId','$machine_cat_id','$model_no','$serial_no','$issue','$comments')";
        $db->query($sql);

        header("Location:ticket_success.php");
    }
}
?>
<main id="main">
    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Need Help</h2>
                <p>Open a Ticket</p>
            </div>
            <div class="row">
                <div class="col-md-5" data-aos="fade-right" data-aos-delay="200">
                    <img src="assets/img/tech-support.png" width="100%" alt=""/>
                </div>
                <div class="col-md-7" data-aos="fade-left" data-aos-delay="500">
                    <form id="createTicket" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" role="form" class="php-email-form" novalidate>
                        <script src="../qr_scanner/instascan.min.js" type="text/javascript"></script>
                        <div class="row">
                            <h5>Scan the QR code on the machine or enter the Serial Number below</h5>
                            <div class="col-md-6 mt-4" >
                                <div>                                    
                                    <video id="scan_job" height="220" width="100%" class="border border-1 border-black"></video><br>
                                    <button type="button" class="btn btn-dark" onclick="scanjob()">Scan QR code</button>
                                    <button type="button" class="btn btn-danger" onclick="stopscan()">Stop camera</button>
                                </div>
                            </div>
                            <div class="col-md-6 mt-4" >
                                <div class="form-floating">                                    
                                    <input type="text" id="serial_no" name="serial_no" class="form-control" placeholder="" value="<?= @$serial_no ?>" >
                                    <label for="serial_no">Enter the Serial Number</label>
                                    <span class="error_span text-danger"><?= @$message['serial_no'] ?></span><br>
                                </div>
                                <!-- type=button is required-->
                                <button type="button" class="btn btn-dark" onclick="findSerialBtn()">Submit</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mt-4" >
                                <div class="form-floating mb-2">
                                    <!-- Use hidden field to store category id to use in loadCommonIssues() function-->
                                    <input type="hidden" name="machine_cat_id" id="machine_cat_id" value="<?= @$machine_cat_id ?>">
                                    <input type="text" class="form-control" name="machine_cat" id="machine_cat" placeholder="" value="<?= @$machine_cat ?>" readonly>
                                    <label for="machine_cat">Machine Category</label>
                                </div>
                                <div class="form-floating mb-2">                                    
                                    <input type="text" class="form-control" name="purchase_date" id="purchase_date" placeholder="" value="<?= @$purchase_date ?>" readonly>
                                    <label for="purchase_date">Purchased Date</label>
                                </div>                                
                            </div>
                            <div class="col-md-6 mt-4" >
                                <div class="form-floating mb-2">                                    
                                    <input type="text" class="form-control" name="model_no" id="model_no" placeholder="" value="<?= @$model_no ?>" readonly>
                                    <label for="model_no">Model Number</label>
                                </div>
                                <div class="form-floating mb-2">                                    
                                    <input type="text" class="form-control" name="warranty" id="warranty" placeholder="" value="<?= @$warranty ?>" readonly>
                                    <label for="purchase_date">Warranty Status</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <?php
                            if (!empty($machine_cat_id)) { // if a valid serial number is entered, this will be not empty
                                $sql = "SELECT * FROM common_machine_issues WHERE MachineCategory='$machine_cat_id'";
                            } else { // serial number is not set 
                                $sql = "SELECT * FROM common_machine_issues";
                            }
                            $result = $db->query($sql);
                            ?>
                            <select name="issue" id="issue" class="form-select mb-3">
                                <option value="" >Select the issue</option>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= $row['Id'] ?>" <?= @$issue == $row['Id'] ? 'selected' : '' ?>><?= $row['Issue'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="error_span text-danger"><?= @$message['issue'] ?></span>
                        </div>
                        <div class="form-floating">                            
                            <textarea class="form-control" placeholder="" id="comments" name="comments" style="height: 150px" value="<?= @$comments ?>" ></textarea>
                            <label for="comments">Describe the issue in detail</label>
                            <span class="error_span text-danger mt-4"><?= @$message['comments'] ?></span>
                        </div>
                        <input type="hidden" name="orderId" value="<?= $orderId ?>">
                        <div class="text-center mt-3"><button type="submit">Create Ticket</button></div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        </div>
    </section>
</main>

<?php
include 'footer.php';
ob_end_flush();
?>

<script>
    //start camera & scanning
    function scanjob() {
        //pass the video tag to the new scanner object created
        let scanner = new Instascan.Scanner({video: document.getElementById('scan_job')});
        scanner.addListener('scan', function (content) {
            findSerialByQR(content);
        });
        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]); //use the first camera to scan
            } else {
                console.error('No cameras found.');
            }
        }).catch(function (e) {
            console.error(e);
               });
    }

    //stop camera
    function stopscan() {
        const video = document.querySelector('video');
        const mediaStream = video.srcObject;
        const tracks = mediaStream.getTracks();
        tracks[0].stop();
        tracks.forEach(track => track.stop());
    }

    // fill details by QR
    function findSerialByQR(serialno) {
        window.location.href = "http://localhost/photon/web/create_ticket.php?serialno=" + serialno;
    }

    // fill details by submitting the serial no
    function findSerialBtn() {
        // check if serial number is empty or it equals to zero
        if ($('#serial_no').val().trim() === '' || $('#serial_no').val().trim() === '0') {
            Swal.fire({
                icon: 'warning',
                title: '',
                html: '<h5>Please enter a valid Serial Number</h5>',
                showConfirmButton: false,
                showCloseButton: false
            });
            return; // prevent executing below code
        }
        var serialno = document.getElementById('serial_no').value;
        window.location.href = "http://localhost/photon/web/create_ticket.php?serialno=" + serialno;
    }

    $(document).ready(function () {
        // when click on form submit button
        $('#createTicket').submit(function (event) {
            // check if serial number is empty
            if ($('#serial_no').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    html: '<h5>Please Scan the QR code or Enter a valid Serial Number</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return; // prevent executing below code
            }
            // check if issue type not selected
            if ($('#issue').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    html: '<h5>Please select the issue type</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return;
            }
            if ($('#machine_cat').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    html: '<h5>Please scan the QR code or enter the Serial Number</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return;
            }
            if ($('#comments').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    html: '<h5>Please describe the issue with more details</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                event.preventDefault();  // prevent the form submission 
                return;
            }
        });
    });

</script>