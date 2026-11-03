(
	function ( $, window, undefined ) {
		'use strict';

    // storing RegExp strings for decoding the uploaded config file
    var RegExpStrings = {
      channel_id: /channel_id: ([^\s]+)/g,
      key: /key: ([^\s]+)/g,
      secret: /secret: ([^\s]+)/g
    }

    function updateCreds(input) {
      var channelIdMatch = input.match(RegExpStrings.channel_id).toString().split(" ");
      $( '#api_channel' ).val(channelIdMatch ? channelIdMatch[1] : '');

      var keyMatch = input.match(RegExpStrings.key).toString().split(" ");
      $( '#api_key' ).val(keyMatch ? keyMatch[1] : '');

      var secretMatch = input.match(RegExpStrings.secret).toString().split(" ");
      $( '#api_secret' ).val(secretMatch ? secretMatch[1] : '');
    }

    // Hide manual-input textarea for creds on load.
    $( '#api_config_file_input' ).css({
        'display': 'none',
        'width': '300px',
        'height': '250px'
    });

    $( 'a[href$="#api_config_file"]' ).click(function () {
      $( '#api_config_file_input' ).css({
              'display': 'block'
      });
    });

    // Hide skip auto-publish term IDs box on load.
    var skipBox = document.getElementById('api_autosync_skip');
    skipBox.style.display = 'none';

    /**
     * A helper function to add a term ID box.
     * @param {number} id - Optional. The term ID to add. Defaults to 0 (empty).
     */
    function addTermIdBox(id = 0) {
      var termIdContainer = document.createElement('div');
      termIdContainer.classList.add('apple-news-skip-term');
      var termIdSelector = document.createElement('input');
      termIdSelector.type = 'number';
      termIdSelector.onchange = reloadTermIds;
      termIdSelector.onkeyup = reloadTermIds;
      if (id !== 0) {
        termIdSelector.setAttribute('value', id.toString());
      }
      var termRemover = document.createElement('button');
      termRemover.innerText = 'Remove';
      termRemover.role = 'button';
      termRemover.onclick = removeTerm;
      termIdContainer.appendChild(termIdSelector);
      termIdContainer.appendChild(termRemover);
      skipBox.parentElement.appendChild(termIdContainer);
    }

    /**
     * An event handler for adding a new term ID input.
     * @param {Event} event - The click event on the button.
     */
    function addNewTermIdBox(event) {
      event.preventDefault();
      addTermIdBox();
    }

    /**
     * Gets an array of selected term IDs from the input.
     * @returns {int[]} An array of selected term IDs.
     */
    function getTermIds() {
      var ids = [];
      try {
        ids = JSON.parse(skipBox.value);
      } catch (error) {
        ids = [];
      } finally {
        if (!Array.isArray(ids)) {
          ids = [];
        }
      }

      return ids;
    }

    /**
     * Queries inputs to reload the term IDs into the hidden input.
     */
    function reloadTermIds() {
      var newTermIds = [];
      var inputs = skipBox.parentElement.getElementsByTagName('input');
      for (var i = 1; i < inputs.length; i++) {
        var termId = parseInt(inputs[i].value, 10);
        if (0 !== termId && !Number.isNaN(termId)) {
          newTermIds.push(termId);
        }
      }
      skipBox.value = JSON.stringify(newTermIds);
    }

    /**
     * A function to handle clicks on the remove button.
     * @param {Event} event - The click event on the remove button.
     */
    function removeTerm(event) {
      event.preventDefault();
      event.target.parentElement.remove();
      reloadTermIds();
    }

    // Add basic controls for working with term IDs.
    var termIds = getTermIds();
    for (var i = 0; i < termIds.length; i++) {
      addTermIdBox(termIds[i]);
    }

    // Add the button to add a new item.
    var inserter = document.createElement('button');
    inserter.onclick = addNewTermIdBox;
    inserter.innerHTML = 'Add Term ID';
    skipBox.parentElement.parentElement.appendChild(inserter);

		// Listen for changes to the debugging settings.
		$( '#apple_news_enable_debugging' ).on( 'change', function () {
			var $email = $( '#apple_news_admin_email' );
			if ( 'yes' === $( this ).val() ) {
				$email.attr( 'required', 'required' );
			} else {
				$email.removeAttr( 'required' );
			}
		} ).change();

    // Code for reading uploaded papi file.
		$( '#api_config_file' ).on( 'change', function (e) {
			if (!e.target.files || !e.target.files[0]) {
        return;
      } else {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = function(f) {
          // When a file is uploaded, the read contents populate the hidden textarea.
          var contents = f.target.result;
          updateCreds(contents);
          $( '#api_config_file_input' ).val(contents);
        };
        reader.readAsText(file);
      }
		} );

    // Reading in the needed values from the hidden textarea field.
    $( '#api_config_file_input' ).on( 'change', function () {
      var input = $( '#api_config_file_input' ).val();
      updateCreds(input);
    });
	}
)( jQuery, window );
