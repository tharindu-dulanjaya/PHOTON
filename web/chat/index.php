<?php
include '../dashboard_header.php'; // header has the init.php 
?>
<style>
    #chat-box {
        width: 100%;
        height: 400px;
        border: 1px solid #ccc;
        overflow-y: scroll;
        margin-bottom: 10px;
        padding: 10px;
    }

    #chat-form {
        display: flex;
        justify-content: space-between;
    }

    #chat-form input[type="text"] {
        width: 98%;
    }

    #chat-form button {
        padding: 5px 10px;
    }
</style>
<main id="main">
    <!--Breadcrumb section-->
    <section class="breadcrumbs">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Customer Chat Forum</h2>
                <ol>
                    <li><b>Customer</b></li>
                    <li><a href="<?= WEB_URL ?>dashboard.php" style="color: #fff;">Dashboard</a></li>
                    <li>Chat Forum</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="inner-page">
        <div class="container" data-aos="fade-up">
            <div class="row">
                <div class="col-8">
                    <div id="chat-box"></div>

                    <div class="row">
                        <form id="chat-form">
                            <div class="col-10">
                                <input type="text" id="message" class="form-control border border-1 border-dark-subtle"  placeholder="Type a message">
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="col-2">
                </div>
            </div>

        </div>
    </section>
</main>
<?php
include '../dashboard_footer.php';
?>

<script>
    $(document).ready(function () {
        function fetchMessages() {
            $.ajax({
                url: 'fetch_messages.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    //let is a local variable. cannot access from outside of this scope
                    let chatBox = '';
                    data.forEach(function (message) {
                        chatBox += '<p><strong>' + message.username + ':</strong> ' + message.message + ' <em>(' + message.timestamp + ')</em></p>';
                    });
                    $('#chat-box').html(chatBox);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                }
            });
        }

        $('#chat-form').on('submit', function (event) {
            event.preventDefault(); // prevent reloading the page on submit
            const message = $('#message').val();
            $.ajax({
                url: 'send_message.php',
                method: 'POST',
                data: {
                    message: message
                },
                success: function () {
                    $('#message').val('');
                    fetchMessages();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                }
            });
        });

        // Fetch messages every 2 seconds
        setInterval(fetchMessages, 2000);
        fetchMessages();
    });
</script>

