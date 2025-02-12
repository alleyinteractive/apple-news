(function ( $, window, undefined ) {
  'use strict';

  // Set up show/hide functionality for cover media provider containers.
  (function () {
    var $provider = $( '[name="apple_news_cover_media_provider"]' ),
      $containers = $( '.apple-news-cover-media-provider-container' ),
      showSelectedContainer = function () {
        $containers.hide();
        $containers.filter( '[data-provider="' + $provider.filter( ':checked' ).val() + '"]' ).show();
      };

    showSelectedContainer();
    $provider.on( 'change', showSelectedContainer );
  })();

  // Set up add and remove image functionality.
  $( '.apple-news-coverimage-image-container' ).each( function () {
    var $this = $( this ),
      $addImgButton = $this.find( '.apple-news-coverimage-add' ),
      $delImgButton = $this.find( '.apple-news-coverimage-remove' ),
      $imgContainer = $this.find( '.apple-news-coverimage-image' ),
      $imgIdInput = $this.find( '.apple-news-coverimage-id' ),
      frame;

    // Set up handler for remove image functionality.
    $delImgButton.on( 'click', function() {
      $imgContainer.empty();
      $addImgButton.removeClass( 'hidden' );
      $delImgButton.addClass( 'hidden' );
      $imgIdInput.val( '' );
    } );

    // Set up handler for add image functionality.
    $addImgButton.on( 'click', function () {

      // Open frame, if it already exists.
      if ( frame ) {
        frame.open();
        return;
      }

      // Set configuration for media frame.
      frame = wp.media( { multiple: false } );

      // Set up handler for image selection.
      frame.on( 'select', function () {

        // Get information about the attachment.
        var attachment = frame.state().get( 'selection' ).first().toJSON(),
          imgUrl = attachment.url;

        // Set image URL to medium size, if available.
        if ( attachment.sizes.medium && attachment.sizes.medium.url ) {
          imgUrl = attachment.sizes.medium.url;
        }

        // Clear current values.
        $imgContainer.empty();
        $imgIdInput.val( '' );

        // Add the image and ID, swap visibility of add and remove buttons.
        $imgContainer.append( '<img src="' + imgUrl + '" alt="" />' ); // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found, WordPressVIPMinimum.JS.HTMLExecutingFunctions.append
        $imgIdInput.val( attachment.id );
        $addImgButton.addClass( 'hidden' );
        $delImgButton.removeClass( 'hidden' );
      } );

      // Open the media frame.
      frame.open();
    } );
  } );

  // Set up add and remove video functionality.
  $( '[data-provider="video_id"]' ).each( function () {
    var $this = $( this ),
      $addVideoButton = $this.find( '.apple-news-cover-video-id-add' ),
      $delVideoButton = $this.find( '.apple-news-cover-video-id-remove' ),
      $previewContainer = $this.find( '.apple-news-cover-media-video' ),
      $videoIdInput = $this.find( '[name="apple_news_cover_video_id"]' ),
      frame;

    // Set up handler for remove functionality.
    $delVideoButton.on( 'click', function() {
      $previewContainer.empty();
      $addVideoButton.removeClass( 'hidden' );
      $delVideoButton.addClass( 'hidden' );
      $videoIdInput.val( '' );
    } );

    // Set up handler for add functionality.
    $addVideoButton.on( 'click', function () {
      // Open frame, if it already exists.
      if ( frame ) {
        frame.open();
        return;
      }

      // Set configuration for media frame.
      frame = wp.media( {
        multiple: false,
        library: {
          type: 'video/mp4'
        },
      } );

      // Set up handler for image selection.
      frame.on( 'select', function () {
        // Get information about the attachment.
        var attachment = frame.state().get( 'selection' ).first().toJSON(),
          videoUrl = attachment.url;

        // Clear current values.
        $previewContainer.empty();
        $videoIdInput.val( '' );

        // Add the preview and ID, swap visibility of add and remove buttons.
        $previewContainer.append( $( '<video controls>' ).attr( 'src', videoUrl ) );
        $videoIdInput.val( attachment.id );
        $addVideoButton.addClass( 'hidden' );
        $delVideoButton.removeClass( 'hidden' );
      } );

      // Open the media frame.
      frame.open();
    } );
  } );

  // Set up URL validation for video URLs.
  (function () {
    var $videoInputs = $( '[name="apple_news_cover_video_url"], [name="apple_news_cover_embedwebvideo_url"]' ),
      validateVideoUrl = function () {
        var input = $(this),
          $container = input.closest('.apple-news-cover-media-provider-container'),
          $notice = $container.find('.notice'),
          options;

        options = {
          path: '/apple-news/v1/is-valid-cover-media',
          data: {
            url: input.val(),
            type: $container.data('provider'),
          },
        };

        wp.apiRequest(options).done(function (response) {
          $notice.toggle(!response.isValidCoverMedia);
        });
      };

    $videoInputs.each( validateVideoUrl );
    $videoInputs.on( 'input', validateVideoUrl );
  })();
})( jQuery, window );
