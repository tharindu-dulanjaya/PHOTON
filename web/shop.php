<?php
ob_start(); //output buffer start. enables to pass multiple headers in the page

$page_title = "Machine Collection";
include 'shop_header.php';
$db = dbConn();
?>

<section class="product spad">
    <div class="container">
        <div class="row">

            <!--sidebar filter-->
            <div class="col-md-3">
                <div class="sidebar">
                    <div class="sidebar__item">
                        <h4>Categories</h4>                        
                        <ul>
                            <?php
                            $sql = "SELECT * FROM item_category WHERE status = '1'";
                            $result = $db->query($sql);

                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <!--pass the category name to the onclick function to change title of shop page-->
                                <li><a onclick="loadItemsByCategory(<?= $row['id'] ?>, '<?= $row['category_name'] ?>')" style="cursor: pointer"><?= $row['category_name'] ?></a></li>
                                <?php
                            }
                            ?>
                            <li><a onclick="loadItemsByCategory('0', 'All Products')" style="cursor: pointer">All</a></li>
                        </ul>
                    </div>
                    <div class="sidebar__item">
                        <h4>Popular Machines</h4>
                        <?php
                        $sql = "SELECT * FROM items WHERE status = '1'";
                        $result = $db->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="sidebar__item__size">
                                <label for="<?= $row['item_name'] ?>">
                                    <?= $row['item_name'] ?>
                                    <input type="radio" id="<?= $row['item_name'] ?>" onclick="loadItemsByName(<?= $row['id'] ?>, '<?= $row['item_name'] ?>')">
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="sidebar__item__size">
                            <label for="all_machines">
                                All Machines
                                <input type="radio" id="all_machines" onclick="loadItemsByName('0', 'All Products')">
                            </label>
                        </div>
                    </div>                    
                </div>
            </div>

            <!--products area-->
            <div class="col-md-9">
                <div class="row text-center">
                    <h2 id="categoryTitle">All Products</h2>
                </div>

                <!--products grid populate using ajax-->
                <div class="row" id="product_grid">

                </div>
            </div>
        </div>
    </div>
</section>

<!--compare items button-->
<button id="compareButton" class="floating-compare-button btn btn-dark">Compare</button>

<?php
include 'shop_footer.php';
ob_end_flush();
?>

<script>
    // handle product-compare using ajax to avoid nested forms with add to cart form
    $(document).ready(function () {

        // load all products when page refreshes
        loadItemsByCategory('0', 'All Products');

        $('#compareButton').click(function () {
            var selectedProducts = [];
            $('.compare-checkbox:checked').each(function () { // for each checked checkbox..
                selectedProducts.push($(this).val()); // store ids in selectedProducts array
            });

            if (selectedProducts.length < 2) { // must select minimum 2 products to compare
                Swal.fire({
                    icon: 'info',
                    title: '',
                    html: '<h5>Please select at least 2 products to compare!</h5>',
                    showConfirmButton: false,
                    showCloseButton: false
                });
                return; // prevent executing below code
            }

            // Redirect to the compare page with the selected product IDs
            var compareUrl = 'compare.php?products=' + selectedProducts.join(',');
            window.location.href = compareUrl;
        });
    });

    function loadItemsByCategory(categoryId, categoryName) {
        // update the page title with the selected category name
        $("#categoryTitle").text(categoryName);

        if (categoryId) {
            $.ajax({
                url: 'loadItemsByCategory.php?categoryId=' + categoryId,
                type: 'GET',
                success: function (data) {
                    $("#product_grid").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }

    function loadItemsByName(itemId, itemName) {
        // update the page title with the selected category name
        $("#categoryTitle").text(itemName);

        if (itemId) {
            $.ajax({
                url: 'loadItemsByName.php?itemId=' + itemId,
                type: 'GET',
                success: function (data) {
                    $("#product_grid").html(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }
</script>