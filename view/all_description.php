<?php 
global $wpdb;
$table_name = $wpdb->prefix . 'crypto_coins_description';

$page_num = ! empty( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$items_per_page =10;

$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
$total_pages = ceil( $total_items / $items_per_page );


$range = 4; 
$start = max(1, $page_num - $range);
$end = min($total_pages, $page_num + $range);

if($page_num <= $range) $end = min($total_pages, 1 + ($range*2));

if($page_num > ($total_pages - $range)) $start = max(1, $total_pages - ($range*2));


?>

<div class="crypto-description-cointainer">

    <div class="head-btn-wrapper">
        <div class="sync-btn-warpper">
            <button id='sync_all_coins'>Sync All Coins</button>
        </div>
        <div class="search-coin-field">
            <input type="search" placeholder="search coin" id="search-crypto-coin" />
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Update at</th>
            </tr>
        </thead>
        <tbody id="crypto_coins_description_rows">

        </tbody>
    </table>
    <p class= "total_item"> Total Item : <?php echo  $total_items; ?><p>
    <div class="pagination">
        <!-- Previous Page Link -->
        <?php if ($page_num > 1): ?>
        <a href="<?php echo add_query_arg('paged', $page_num - 1) ?>">Prev</a>
        <?php endif; ?>

        <!-- Page number Links -->
        <?php for ($page = $start; $page <= $end; $page++): ?>
        <a href="<?php echo add_query_arg('paged', $page) ?>" <?php echo $page === $page_num ? 'class="active"' : '' ?>>
            <?php echo $page ?>
        </a>
        <?php endfor; ?>

        <!-- Next Page Link -->
        <?php if ($page_num < $total_pages): ?>
        <a href="<?php echo add_query_arg('paged', $page_num + 1) ?>">Next</a>
        <?php endif; ?>
    </div>

</div>
