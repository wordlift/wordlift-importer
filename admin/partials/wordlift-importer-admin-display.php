<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wordlift.io
 * @since      1.0.0
 *
 * @package    Wordlift_Importer
 * @subpackage Wordlift_Importer/admin/partials
 */

wp_enqueue_script( 'plupload' );
?>

<ul id="filelist"></ul>
<br/>

<div id="container">
    <a id="browse" href="javascript:;">[Browse...]</a>
    <a id="start-upload" href="javascript:;">[Start Upload]</a>
</div>

<label><?php echo esc_html( __( 'sameAs', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_same_as" id="do_same_as"></label>
<label><?php echo esc_html( __( 'Labels', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_alt_labels" id="do_alt_labels"></label>
<label><?php echo esc_html( __( 'Thumbnails', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_thumbnails" id="do_thumbnails"></label>
<label><?php echo esc_html( __( 'Content', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_post_content" id="do_post_content"></label>
<label><?php echo esc_html( __( 'Overwrite Thumbnails', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" name="force_thumbnails" id="force_thumbnails"></label>
<label><?php echo esc_html( __( 'Title', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_title" id="do_title"></label>
<label><?php echo esc_html( __( 'URL', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_url" id="do_url"></label>
<label><?php echo esc_html( __( 'Type', 'wordlift-importer' ) ); ?>
    <input type="checkbox" value="yes" checked="checked" name="do_type" id="do_type"></label>

<div style="width: 100%;">
    <div id="progress" style="background: blue; width: 0; height: 10px;"></div>
</div>

<br/>
<pre id="console"></pre>


<script type="text/javascript">
  function checkedValue (id) {
    const element = document.getElementById(id)
    if (null === element || false === element.checked) return false
    return element.value
  }

  jQuery(function () {

    const uploader = new plupload.Uploader({
      browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
      multi_selection: false,
      url: '<?php echo admin_url( 'admin-ajax.php?action=wl_importer_import' ); ?>'
    })

    uploader.bind('Error', function (up, err) {
      document.getElementById('console').innerHTML += '\nError #' + err.code + ': ' + err.message
    })

    uploader.bind('FileUploaded', function (up, file, result) {
      document.getElementById('console').innerHTML += result.response
    })

    uploader.bind('UploadProgress', function (up, file) {
      document.getElementById('progress').style.width = file.percent + '%'
    })

    uploader.init()

    document.getElementById('start-upload').onclick = function () {
      uploader.setOption('multipart_params', {
        do_same_as: checkedValue('do_same_as'),
        do_alt_labels: checkedValue('do_alt_labels'),
        do_thumbnails: checkedValue('do_thumbnails'),
        do_post_content: checkedValue('do_post_content'),
        force_thumbnails: checkedValue('force_thumbnails'),
        do_title: checkedValue('do_title'),
        do_url: checkedValue('do_url'),
        do_type: checkedValue('do_type')
      })
      uploader.start()
    }

  })
</script>
