<form method="POST" action="<?php echo admin_url( 'admin-ajax.php?action=wl_importer_export' ); ?>">
	<?php // DO NOT use `post_type`. ?>
    <select name="post_types">
		<?php
		$post_types = get_post_types();
		sort( $post_types );
		foreach ( $post_types as $post_type ) { ?>
            <option><?php echo esc_html( $post_type ); ?></option>
		<?php } ?>
    </select>
    <input type="submit"/>
</form>