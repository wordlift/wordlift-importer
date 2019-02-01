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

<div style="width: 100%;">
    <div id="progress" style="background: blue; width: 0; height: 10px;"></div>
</div>

<br />
<pre id="console"></pre>


<script type="text/javascript">
  jQuery(function () {
    const uploader = new plupload.Uploader({
      browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
      multi_selection: false,
      url: '<?php echo admin_url( 'admin-ajax.php?action=wl_importer_import' ); ?>'
    })

    uploader.bind('Error', function(up, err) {
      document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
    });

    uploader.bind('FileUploaded', function(up, file, result) {
      document.getElementById('console').innerHTML += result.response;
    });

    uploader.bind('UploadProgress', function(up, file) {
      document.getElementById('progress').style.width = file.percent + '%';
    });

    uploader.init()

    document.getElementById('start-upload').onclick = function() {
      uploader.start();
    };

  })
</script>