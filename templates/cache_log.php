<style>
	.cache_log > div {
		display: table-cell;
	}
	
	.cache_log .title {
		width: 200px;
	}

	.cache_log .entity-title {
		width: 340px;
	}

	.cache_log .time {
		width: 190px;
	}

	.cache_log .timestamp {
		width: 120px;
	}
	
	.cache_log .op {
		width: 180px;
	}
	
	.cache_log .size-300 {
		width: 300px;
	}

	.cache_log .nested-field {
		display: table-row;
	}
	
	.cache_log .nested-field .param-name,
	.cache_log .nested-field .param-value {
		display: table-cell;
	}

	.cache_log .title.collapsed:before {
		border: 1px solid black;
		content: '+';
	}
	
	.cache_log .title.collapsed:after {
		content: '...';
	}
	
	.cache_log .title.collapsed>.data {
		display: none;
	}
	
	.cache_log .title.expanded:before {
		border: 1px solid black;
		content: '-';
	}

	.cache_log .title.expanded>.data {
		display: block;
		padding-left: 15px;
	}
</style>
<?php

function render_nested( $name, $data ) {
	if ( is_array( $data ) || is_object( $data ) ) {

		echo "<div class='size-300'><label class='title collapsed' onClick='jQuery(this).toggleClass(\"expanded\").toggleClass(\"collapsed\"); event.stopPropagation(); return false;'>" . esc_html( $name );

		foreach ( $data as $key => $item ) {
			echo '<div class="data">';
			render_nested( $key, $item );
			echo '</div>';
		}

		echo '</label></div>';
	} else {
		echo "<div class='nested-field'><div class='param-name'>" . esc_html( $name ) . ":</div><div class='param-value'>" . esc_html( $data ) . '</div></div>';
	}
}

$cache = get_option( 'ecwid_cache_log' );

$kill = 0;
if ( isset( $_GET['kill'] ) ) {
	$kill = sanitize_text_field( wp_unslash( $_GET['kill'] ) );
}
while ( $kill-- > 0 && count( $cache ) > 0 ) {
	array_pop( $cache );
}

update_option( 'ecwid_cache_log', $cache );

$cache = get_option( 'ecwid_cache_log' );

if ( ! $cache ) {
	$cache = array();
}

foreach ( $cache as $item ) {
	echo '<div class="cache_log">';
	$ts = date_i18n( 'H:i:s m/d/y', $item['timestamp'] );
	echo '<div class="timestamp">' . esc_html( $ts ) . '</div>';
	echo '<div class="op">' . esc_html( $item['operation'] ) . '</div>';
	if ( $item['operation'] == 'invalidate_products_cache' || $item['operation'] == 'invalidate_categories_cache' ) {
		$time = date_i18n( 'D F j H:i:s Y', $item['time'] );
		echo '<div class="time">' . esc_html( $time ) . '</div>';
	}
	if ( $item['operation'] === 'get' ) {
		echo '<div class="entity-title">' . esc_html( $item['name'] ) . '</div>';
		render_nested( 'result', $item['result'] );
	}
	if ( $item['operation'] === 'set' ) {
		echo '<div class="entity-title">' . esc_html( $item['name'] ) . '</div>';
		render_nested( 'value', $item['value'] );
	}
	if ( in_array( $item['operation'], array( 'get_from_categories_cache', 'get_from_products_cache', 'get_from_static_pages_cache' ) ) ) {
		$key = @$item['name'];
		echo '<div class="entity-title">' . esc_html( $key ) . '</div>';
		render_nested( 'result', $item['result'] );
	}
	if ( $item['operation'] === 'get_from_static_pages_cache' ) {
		$valid_from = @$item['valid_from'];
		echo '<div class="entity-title">' . esc_html( $valid_from ) . '</div>';
	}

	if ( $item['operation'] === 'reg cache check' ) {
		render_nested( 'stats', $item['stats'] );
	}

	if ( $item['operation'] === 'is_trusted' ) {
		render_nested( 'self', $item );
	}

	echo '</div>';
}//end foreach

echo '<br />';
echo 'cats:' . esc_html( EcwidPlatform::get( EcwidPlatform::CATEGORIES_CACHE_VALID_FROM ) ) . '<br />';
echo 'prods:' . esc_html( EcwidPlatform::get( EcwidPlatform::PRODUCTS_CACHE_VALID_FROM ) ) . '<br />';
echo 'profile:' . esc_html( EcwidPlatform::get( EcwidPlatform::PROFILE_CACHE_VALID_FROM ) ) . '<br />';
