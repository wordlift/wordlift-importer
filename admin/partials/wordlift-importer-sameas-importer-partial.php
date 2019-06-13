<div class="wrap">
    <h1>WordLift Importer (using sameAs)</h1>
    <ul id="filelist"></ul>

    <div id="container">
        <input type="button" id="browse" class="button button-secondary" value="Browse" />
        <input type="button" id="start-upload" class="button button-primary" value="Start Upload" />
    </div>

    <br/>

    <div style="width: 100%;">
        <div id="progress" style="background: #0085ba; width: 0; height: 10px;"></div>
    </div>

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
          url: '<?php echo admin_url( 'admin-ajax.php?action=' . Wordlift_Importer_SameAs_Importer_Task::ID )
                           . '&_ajax_nonce=' . wp_create_nonce( Wordlift_Importer_SameAs_Importer_Task::ID ); ?>'
        })

        uploader.bind('FilesAdded', function (up, files) {
          plupload.each(files, function(file) {
              document.getElementById('filelist').innerHTML += '<li id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ')</li>';
          });
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
            limit: 0
          })
          uploader.start()
        }

      })
    </script>
</div>
