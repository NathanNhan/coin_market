<?php 

function load_assets(){
    wp_enqueue_style("font","//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i", array(), "1.0", "all");
    wp_enqueue_style( "bootstrapcss", '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), "1.1", 'all');
    wp_enqueue_style( "maincss", get_theme_file_uri() . '/build/index.css', array(), '1.0.2', 'all' );
    wp_enqueue_style( "mainstylecss", get_theme_file_uri() . '/build/style-index.css', array(), '1.0.3', 'all' );

    wp_enqueue_script( "university_scripts", get_theme_file_uri() . '/build/index.js', array('jquery'), '1.02', true );
}
add_action("wp_enqueue_scripts","load_assets");



//Step 1: Create custom post type
add_action('init', 'register_coin_market_cpt');
function register_coin_market_cpt() {
    register_post_type( 'coin', [
        'label' => 'Coin',
        'public' => true,
        'capability_type' => 'post'
    ] );
}


//Step 2: Xử lý call api
add_action('wp_ajax_nopriv_get_coin_market_from_api','get_coin_market_from_api');
add_action('wp_ajax_get_coin_market_from_api', 'get_coin_market_from_api');

function get_coin_market_from_api() {
    //test ghi log
    $coinList = [];
    $file = get_stylesheet_directory() . '/report.txt';
    $current_page =!empty($_POST['current_page']) ? $_POST['current_page'] : 1;
    $results = wp_remote_retrieve_body(wp_remote_get( 'https://api.coincap.io/v2/markets?offset=' . $current_page . '&limit=10'));
    
    file_put_contents($file,"Current Page: " . $current_page . "\n\n", FILE_APPEND);
    //Chuyển dữ liệu từ kiểu string json sang object trong php 
    $results = json_decode($results);
    
    if(!is_array( $results->data) || empty($results->data)) {
        return false;
    }

    $coinList[] = $results->data;
    foreach($coinList[0] as $coin) {
        $coin_slug = sanitize_title($coin->baseSymbol . '-' . $coin->baseId);

        $insered_coin = wp_insert_post(
            [
                'post_name' => $coin_slug,
                'post_title' => $coin_slug,
                'post_type' => 'coin',
                'post_status' => 'publish'
            ]
        );

        if(is_wp_error($insered_coin)) {
            continue;
        }

        $fillable = [
            'field_67062d0163f3c' => 'rank',
            'field_67062d1163f3d' => 'baseSymbol',
            'field_67062d1e63f3e' => 'priceUsd',
            'field_67062d2c63f3f' => 'percentExchangeVolume'
        ];

        foreach($fillable as $key => $value) {
            update_field($key, $coin->$value,$insered_coin);
        }
    }
    $current_page = $current_page + 1;
    //Sử dụng đệ quy bằng việc gọi AJAX
    wp_remote_post(admin_url('admin-ajax.php?action=get_coin_market_from_api'), [
        'blocking' => false,
        'sslverify' => false,
        'body' => [
            'current_page' => $current_page
        ]
    ]);



}