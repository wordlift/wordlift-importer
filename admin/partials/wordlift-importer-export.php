<h1><?php echo esc_html__( 'Export', 'wordlift-importer' ); ?></h1>

<form method="POST" action="<?php echo admin_url( 'admin-ajax.php?action=wl_importer_export' ); ?>">
    <label><?php echo __( 'Post Type:', 'wordlift-importer' ); ?>
		<?php // DO NOT use `post_type`. ?>
        <select name="post_types">
			<?php
			$post_types = get_post_types();
			sort( $post_types );
			foreach ( $post_types as $post_type ) { ?>
                <option><?php echo esc_html( $post_type ); ?></option>
			<?php } ?>
        </select>
    </label>
    <label><?php echo esc_html__( 'Offset:', 'wordlift-importer' ); ?>
        <input type="number" name="offset"/>
    </label>
    <label><?php echo esc_html__( 'Limit:', 'wordlift-importer' ); ?>
        <input type="number" name="limit"/>
    </label>
    <input type="submit" value="<?php echo esc_attr__( 'Export', 'wordlift-importer' ); ?>"/>
</form>