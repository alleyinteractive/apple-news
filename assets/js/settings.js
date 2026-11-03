(
  function ($, window, undefined) {
    'use strict';

    console.log('Apple News settings.js loaded');
    console.log('Secondary channel elements exist:', {
      'api_config_file_2': $('#api_config_file_2').length > 0,
      'api_config_file_input_2': $('#api_config_file_input_2').length > 0,
      'api_channel_2': $('#api_channel_2').length > 0,
      'api_key_2': $('#api_key_2').length > 0,
      'api_secret_2': $('#api_secret_2').length > 0
    });

    // storing RegExp strings for decoding the uploaded config file
    // Note: The .papi file format may have spaces around the colon (e.g., "channel_id : value" or "channel_id: value")
    var RegExpStrings = {
      channel_id: /channel_id\s*:\s*([^\s]+)/,
      key: /key\s*:\s*([^\s]+)/,
      secret: /secret\s*:\s*([^\s]+)/
    }

    /**
     * Updates credentials for a specific channel from parsed config file contents.
     * @param {string} input - The contents of the .papi file.
     * @param {string} suffix - The suffix for the field IDs ('' for primary, '_2' for secondary).
     */
    function updateCreds(input, suffix) {
      suffix = suffix || '';

      console.log('updateCreds called with suffix:', suffix);
      console.log('Input length:', input ? input.length : 0);

      // Check if elements exist
      var $channel = $('#api_channel' + suffix);
      var $key = $('#api_key' + suffix);
      var $secret = $('#api_secret' + suffix);

      console.log('Element #api_channel' + suffix + ' exists:', $channel.length > 0);
      console.log('Element #api_key' + suffix + ' exists:', $key.length > 0);
      console.log('Element #api_secret' + suffix + ' exists:', $secret.length > 0);

      try {
        var channelIdMatch = input.match(RegExpStrings.channel_id);
        var channelValue = channelIdMatch ? channelIdMatch[1] : '';
        $channel.val(channelValue);
        console.log('Setting #api_channel' + suffix + ' to:', channelValue, 'Result:', $channel.val());

        var keyMatch = input.match(RegExpStrings.key);
        var keyValue = keyMatch ? keyMatch[1] : '';
        $key.val(keyValue);
        console.log('Setting #api_key' + suffix + ' to:', keyValue, 'Result:', $key.val());

        var secretMatch = input.match(RegExpStrings.secret);
        var secretValue = secretMatch ? secretMatch[1] : '';
        $secret.val(secretValue);
        console.log('Setting #api_secret' + suffix + ' to:', secretValue, 'Result:', $secret.val());
      } catch (e) {
        console.error('Error in updateCreds:', e);
      }
    }

    // Hide manual-input textarea for creds on load (primary channel).
    $('#api_config_file_input').css({
      'display': 'none',
      'width': '300px',
      'height': '250px'
    });

    $('a[href$="#api_config_file"]').click(function () {
      $('#api_config_file_input').css({
        'display': 'block'
      });
    });

    // Hide manual-input textarea for creds on load (secondary channel).
    $('#api_config_file_input_2').css({
      'display': 'none',
      'width': '300px',
      'height': '250px'
    });

    $('a[href$="#api_config_file_2"]').click(function () {
      $('#api_config_file_input_2').css({
        'display': 'block'
      });
    });

    // Hide skip auto-publish term IDs box on load.
    var skipBox = document.getElementById('api_autosync_skip');
    if (skipBox) {
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
    } // End if (skipBox)

    // Listen for changes to the debugging settings.
    $('#apple_news_enable_debugging').on('change', function () {
      var $email = $('#apple_news_admin_email');
      if ('yes' === $(this).val()) {
        $email.attr('required', 'required');
      } else {
        $email.removeAttr('required');
      }
    }).change();

    // Code for reading uploaded papi file (primary channel).
    $('#api_config_file').on('change', function (e) {
      if (!e.target.files || !e.target.files[0]) {
        return;
      } else {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = function (f) {
          // When a file is uploaded, the read contents populate the hidden textarea.
          var contents = f.target.result;
          updateCreds(contents, '');
          $('#api_config_file_input').val(contents);
        };
        reader.readAsText(file);
      }
    });

    // Reading in the needed values from the hidden textarea field (primary channel).
    $('#api_config_file_input').on('change', function () {
      var input = $('#api_config_file_input').val();
      updateCreds(input, '');
    });

    // Code for reading uploaded papi file (secondary channel).
    console.log('Attaching event handler to #api_config_file_2, element exists:', $('#api_config_file_2').length > 0);
    $('#api_config_file_2').on('change', function (e) {
      alert('Secondary channel file selected!'); // TEMP DEBUG
      console.log('Secondary channel file input changed');
      if (!e.target.files || !e.target.files[0]) {
        console.log('No file selected');
        return;
      } else {
        const file = e.target.files[0];
        console.log('Reading file:', file.name);
        const reader = new FileReader();
        reader.onload = function (f) {
          // When a file is uploaded, the read contents populate the hidden textarea.
          var contents = f.target.result;
          console.log('File contents loaded, length:', contents.length);
          console.log('File contents preview:', contents.substring(0, 200));
          alert('File loaded! Contents: ' + contents.substring(0, 100)); // TEMP DEBUG
          updateCreds(contents, '_2');
          $('#api_config_file_input_2').val(contents);
        };
        reader.readAsText(file);
      }
    });

    // Reading in the needed values from the hidden textarea field (secondary channel).
    $('#api_config_file_input_2').on('change', function () {
      var input = $('#api_config_file_input_2').val();
      updateCreds(input, '_2');
    });
  }
)(jQuery, window);
