<?php get_header(); ?>
<?php 
$result = [];

$cached = wp_cache_get( 'coin', 'coin_group', true );

if($cached == false) {
    $result = get_posts(
        array(
            'post_type' => 'coin',
            'posts_per_page' => 1000,
        ));

    wp_cache_set( 'coin', $result, 'coin_group', '86400' );
} else {
    $result = $cached;
}




?>

<div class="container container--narrow page-section">
 <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
               
                <th>Base Symbol</th>
                <th>Rank</th>
                <th>Price (USD)</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $coin) : ?>   
                    <tr>
                        <td><?=get_field('basesymbol',$coin->ID); ?></td>
                        <td><?=get_field('rank',$coin->ID); ?></td>
                        <td><?=get_field('priceusd',$coin->ID); ?></td>
                        <td><?=get_field('percentexchangevolume',$coin->ID); ?></td>
                    </tr>
             <?php endforeach; ?>
          </tbody> 
        </tfoot>
    </table>
    
    </div>

    

<?php get_footer(); ?>
