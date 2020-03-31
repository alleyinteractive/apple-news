<?php
/**
 * Publish to Apple News Tests: Embed_Generic class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Embed_Generic.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Embed_Generic;

/**
 * A class which is used to test the Apple_Exporter\Components\Embed_Generic class.
 */
class Embed_Generic_Test extends Component_TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * We will test all embed signatures that the plugin supports, which includes all embed signatures for Gutenberg
	 * embeds other than the ones that Apple News supports explicitly (Facebook, Instagram, Twitter, Vimeo, YouTube)
	 * as well as any iframe embeds that are either handled through the standard WordPress oEmbed system, in which
	 * the iframe is embedded directly inside of a paragraph tag, or where an iframe is the root-level element. Any
	 * other embed signatures are not supported out of the box due to the difficulty of matching against the HTML,
	 * and handling will fall back to standard element processing (images, links, etc).
	 *
	 * @see self::test_transform()
	 *
	 * @access public
	 * @return array An array of test data.
	 */
	public function data_transform() {
		return [
			// Gutenberg: Amazon Kindle embed.
			[
				<<<HTML
<figure class="wp-block-embed-amazon-kindle wp-block-embed is-type-rich is-provider-amazon"><div class="wp-block-embed__wrapper">
<iframe title="The Design of Everyday Things: Revised and Expanded Edition" type="text/html" width="640" height="550" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_7cXLROJYP0bDqM&#038;asin=B00E257T6C&#038;tag=kpembed-20"></iframe>
</div></figure>
HTML
				,
				'https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_7cXLROJYP0bDqM&#038;asin=B00E257T6C&#038;tag=kpembed-20',
				'Amazon',
				'The Design of Everyday Things: Revised and Expanded Edition',
			],

			// Gutenberg: Animoto embed.
			[
				<<<HTML
<figure class="wp-block-embed-animoto wp-block-embed is-type-video is-provider-animoto wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Video Player" id="vp1WmGs0" width="640" height="360" frameborder="0" src="https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565635838&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=" allowfullscreen></iframe>
</div></figure>
HTML
				,
				'https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565635838&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=',
				'Animoto',
				'Video Player',
			],

			// Gutenberg: Cloudup embed.
			[
				<<<HTML
<figure class="wp-block-embed-cloudup wp-block-embed is-type-rich is-provider-cloudup wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Video Stream - share clips and home movies" src="https://cloudup.com/cjZ6QGIsErH?chromeless" data-uid="cjZ6QGIsErH" data-aspect-ratio='1.3704496788008564' width="640" height="467" scrolling="no" frameborder="0" mozallowfullscreen="true" webkitallowfullscreen="true" allowfullscreen="true" class="cloudup_iframe_embed"></iframe>
</div></figure>
HTML
				,
				'https://cloudup.com/cjZ6QGIsErH?chromeless',
				'Cloudup',
				'Video Stream - share clips and home movies',
			],

			// Gutenberg: CollegeHumor embed.
			[
				<<<HTML
<figure class="wp-block-embed-collegehumor wp-block-embed is-type-video is-provider-collegehumor wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Prank War 7: The Half Million Dollar Shot" src="https://www.collegehumor.com/e/3922232" width="640" height="360" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe>
</div></figure>
HTML
				,
				'https://www.collegehumor.com/e/3922232',
				'CollegeHumor',
				'Prank War 7: The Half Million Dollar Shot',
			],

			// Gutenberg: Crowdsignal embed.
			[
				<<<HTML
<figure class="wp-block-embed-crowdsignal wp-block-embed is-type-rich is-provider-crowdsignal"><div class="wp-block-embed__wrapper">
<script type="text/javascript" charset="utf-8" src="https://secure.polldaddy.com/p/10029863.js"></script><noscript><a href="https://poll.fm/10029863">What&#039;s Your Favourite Ice Cream?</a></noscript>
</div></figure>
HTML
				,
				'https://poll.fm/10029863',
				'Crowdsignal',
			],

			// Gutenberg: Dailymotion embed.
			[
				<<<HTML
<figure class="wp-block-embed-dailymotion wp-block-embed is-type-video is-provider-dailymotion wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Babysitter!" frameborder="0" width="640" height="480" src="https://www.dailymotion.com/embed/video/xoxulz" allowfullscreen allow="autoplay"></iframe>
</div></figure>
HTML
				,
				'https://www.dailymotion.com/embed/video/xoxulz',
				'Dailymotion',
				'Babysitter!',
			],

			// Gutenberg: Flickr embed.
			[
				<<<HTML
<figure class="wp-block-embed-flickr wp-block-embed is-type-photo is-provider-flickr"><div class="wp-block-embed__wrapper">
<a href="https://flickr.com/photos/bees/2362225867/"><img src="https://live.staticflickr.com/3040/2362225867_4a87ab8baf_z.jpg" alt="Bacon Lollys" width="640" height="480" /></a>
</div></figure>
HTML
				,
				'https://flickr.com/photos/bees/2362225867/',
				'Flickr',
			],

			// Gutenberg: Hulu embed.
			[
				<<<HTML
<figure class="wp-block-embed-hulu wp-block-embed is-type-video is-provider-hulu wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Wed, May 21, 2008 (Late Night With Conan O&#039;Brien)" width="640" height="370" src="//www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowfullscreen> </iframe>
</div></figure>
HTML
				,
				'https://www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w',
				'Hulu',
				'Wed, May 21, 2008 (Late Night With Conan O&#039;Brien)',
			],

			// Gutenberg: Imgur embed.
			[
				<<<HTML
<figure class="wp-block-embed-imgur wp-block-embed is-type-rich is-provider-imgur"><div class="wp-block-embed__wrapper">
<blockquote class="imgur-embed-pub" lang="en" data-id="a/CxNMoZy"><a href="https://imgur.com/a/CxNMoZy">Additional Pylons have been constructed.</a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>
</div></figure>
HTML
				,
				'https://imgur.com/a/CxNMoZy',
				'Imgur',
			],

			// Gutenberg: Issuu embed.
			[
				<<<HTML
<figure class="wp-block-embed-issuu wp-block-embed is-type-rich is-provider-issuu"><div class="wp-block-embed__wrapper">
<div data-url="https://issuu.com/iscience/docs/issue23" style="width: 640px; height: 395px;" class="issuuembed"></div><script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>
</div></figure>
HTML
				,
				'https://issuu.com/iscience/docs/issue23',
				'Issuu',
			],

			// Gutenberg: Kickstarter embed.
			[
				<<<HTML
<figure class="wp-block-embed-kickstarter wp-block-embed is-type-rich is-provider-kickstarter wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Help Support The Kiggins Theatre to go Digital!" src="https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html" height="360.0" width="640" frameborder="0" scrolling="no"></iframe>
</div></figure>
HTML
				,
				'https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html',
				'Kickstarter',
				'Help Support The Kiggins Theatre to go Digital!',
			],

			// Gutenberg: Meetup.com embed.
			[
				<<<HTML
<figure class="wp-block-embed-meetup-com wp-block-embed is-type-rich is-provider-meetup"><div class="wp-block-embed__wrapper">
<style type="text/css">#meetup_oembed .mu_clearfix:after { visibility: hidden; display: block; font-size: 0; content: " "; clear: both; height: 0; }* html #meetup_oembed .mu_clearfix, *:first-child+html #meetup_oembed .mu_clearfix { zoom: 1; }#meetup_oembed { background:#eee;border:1px solid #ccc;padding:10px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;margin:0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; }#meetup_oembed h3 { font-weight:normal; margin:0 0 10px; padding:0; line-height:26px; font-family:Georgia,Palatino,serif; font-size:24px }#meetup_oembed p { margin: 0 0 10px; padding:0; line-height:16px; }#meetup_oembed img { border:none; margin:0; padding:0; }#meetup_oembed a, #meetup_oembed a:visited, #meetup_oembed a:link { color: #1B76B3; text-decoration: none; cursor: hand; cursor: pointer; }#meetup_oembed a:hover { color: #1B76B3; text-decoration: underline; }#meetup_oembed a.mu_button { font-size:14px; -moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:2px solid #A7241D;color:white!important;text-decoration:none;background-color: #CA3E47; background-image: -moz-linear-gradient(top, #ca3e47, #a8252e); background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0, #a8252e), color-stop(1, #ca3e47));disvplay:inline-block;padding:5px 10px; }#meetup_oembed a.mu_button:hover { color: #fff!important; text-decoration: none; }#meetup_oembed .photo { width:50px; height:50px; overflow:hidden;background:#ccc;float:left;margin:0 5px 0 0;text-align:center;padding:1px; }#meetup_oembed .photo img { height:50px }#meetup_oembed .number { font-size:18px; }#meetup_oembed .thing { text-transform: uppercase; color: #555; }</style><div id="meetup_oembed" style="height:309px">     <div style="overflow:hidden;max-height:269px">          <h3>PHP Colombia</h3>          <p style="margin:0 0 10px;font-size:12px;line-height:16px;">Bogotá, CO <br />          <span style="font-size:14px;font-weight:bold;">1,539</span> <em>phperos</em></p>          <a href="https://www.meetup.com/PHPColMeetup/" target="_blank"><img src="https://secure.meetupstatic.com/photos/event/d/6/d/0/600_392754992.jpeg" style="float:right;max-width:150px;margin-right:0;" /></a>                          <div style="margin-right:170px;line-height:16px;">Este grupo es de desarrolladores para desarrolladores queremos crear reuniones físicas con todos los que amamos este incomprendido lenguaje de programación y apasionar a los d&#8230;</div>                                 </div>     <p style="margin:10px 0 5px;"><a href="https://www.meetup.com/PHPColMeetup/" target="_blank" class="mu_button">Check out this Meetup Group &rarr;</a></p></div>
</div></figure>
HTML
				,
				'https://www.meetup.com/PHPColMeetup/',
				'Meetup.com',
			],

			// Gutenberg: Mixcloud embed.
			[
				<<<HTML
<figure class="wp-block-embed-mixcloud wp-block-embed is-type-rich is-provider-mixcloud wp-embed-aspect-21-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Moving Sounds With James Heather (11/08/2019)" width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&amp;hide_cover=1" frameborder="0"></iframe>
</div></figure>
HTML
				,
				'https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&#038;hide_cover=1',
				'Mixcloud',
				'Moving Sounds With James Heather (11/08/2019)',
			],

			// Gutenberg: Reddit embed.
			[
				<<<HTML
<figure class="wp-block-embed-reddit wp-block-embed is-type-rich is-provider-reddit"><div class="wp-block-embed__wrapper">
<div class="reddit-embed" data-embed-media="www.redditmedia.com" data-embed-parent="false" data-embed-live="false" data-embed-uuid="16b7b7a4-bd32-11e9-90b9-0e5bd4bf4a44" data-embed-created="2019-08-12T18:50:43.847033+00:00"><a href="https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/d3qol9a/">Comment</a> from discussion <a href="https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/">hobnobbinbobthegob&#8217;s comment from discussion &quot;Someone came to visit. (Woodchips for scale.)&quot;</a>.</div><script async src="https://www.redditstatic.com/comment-embed.js"></script>
</div></figure>
HTML
				,
				'https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/d3qol9a/',
				'Reddit',
			],

			// Gutenberg: ReverbNation embed.
			[
				<<<HTML
<figure class="wp-block-embed-reverbnation wp-block-embed is-type-rich wp-embed-aspect-9-16 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Easy by Dyaphonic" width="640" height="960" scrolling="no" frameborder="no" src="https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc[song_ids]=30572216"></iframe>
</div></figure>
HTML
				,
				'https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc%5Bsong_ids%5D=30572216',
				'ReverbNation',
				'Easy by Dyaphonic',
			],

			// Gutenberg: Screencast embed.
			[
				<<<HTML
<figure class="wp-block-embed-screencast wp-block-embed is-type-video is-provider-screencast-com wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<!-- copy and paste. Modify height and width if desired. --><iframe title="B-roll Blog Post - BB-8 - David Patton" class="embeddedObject shadow resizable" name="embedded_content" scrolling="no" frameborder="0" type="text/html"         style="overflow:hidden;" src="https://www.screencast.com/users/TechSmith_Media/folders/Camtasia/media/d89af74a-3a32-4c9f-8a85-ef83fdb5c39c/embed" height="720" width="1280" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
</div></figure>
HTML
				,
				'https://www.screencast.com/users/TechSmith_Media/folders/Camtasia/media/d89af74a-3a32-4c9f-8a85-ef83fdb5c39c/embed',
				'Screencast',
				'B-roll Blog Post - BB-8 - David Patton',
			],

			// Gutenberg: Scribd embed.
			[
				<<<HTML
<figure class="wp-block-embed-scribd wp-block-embed is-type-rich is-provider-scribd wp-embed-aspect-9-16 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests" class="scribd_iframe_embed" src="https://www.scribd.com/embeds/110799637/content" scrolling="no" id="110799637" width="640" height="960" frameborder="0"></iframe><script type="text/javascript">          (function() { var scribd = document.createElement("script"); scribd.type = "text/javascript"; scribd.async = true; scribd.src = "https://www.scribd.com/javascripts/embed_code/inject.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(scribd, s); })()        </script>
</div></figure>
HTML
				,
				'https://www.scribd.com/embeds/110799637/content',
				'Scribd',
				'Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests',
			],

			// Gutenberg: Slideshare embed.
			[
				<<<HTML
<figure class="wp-block-embed-slideshare wp-block-embed is-type-rich is-provider-slideshare wp-embed-aspect-1-1 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Business Quotes for 2011" src="https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY" width="427" height="356" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC; border-width:1px; margin-bottom:5px; max-width: 100%;" allowfullscreen> </iframe> <div style="margin-bottom:5px"> <strong> <a href="https://www.slideshare.net/haraldf/business-quotes-for-2011" title="Business Quotes for 2011" target="_blank">Business Quotes for 2011</a> </strong> from <strong><a href="https://www.slideshare.net/haraldf" target="_blank">Harald Felgner, PhD</a></strong> </div>
</div></figure>
HTML
				,
				'https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY',
				'Slideshare',
				'Business Quotes for 2011',
			],

			// Gutenberg: SmugMug embed.
			[
				<<<HTML
<figure class="wp-block-embed-smugmug wp-block-embed is-type-photo is-provider-smugmug"><div class="wp-block-embed__wrapper">
<a href="https://stuckincustoms.smugmug.com/Portfolio/i-R8SMwnh"><img src="https://stuckincustoms.smugmug.com/Portfolio/i-R8SMwnh/2/29508a44/640x442/3985718888_d4435fb72d_o-640x442.jpg" alt="The Treetop Temple Protects Kyoto" width="640" height="442" /></a>
</div></figure>
HTML
				,
				'https://stuckincustoms.smugmug.com/Portfolio/i-R8SMwnh',
				'SmugMug',
			],

			// Gutenberg: SoundCloud embed.
			[
				<<<HTML
<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Flickermood by Forss" width="640" height="400" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1"></iframe>
</div></figure>
HTML
				,
				'https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1',
				'SoundCloud',
				'Flickermood by Forss',
			],

			// Gutenberg: Speaker Deck embed.
			[
				<<<HTML
<figure class="wp-block-embed-speaker-deck wp-block-embed is-type-rich is-provider-speaker-deck wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Why Backbone" id="talk_frame_48643" src="//speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>
</div></figure>
HTML
				,
				'https://speakerdeck.com/player/4648d440a3230130452522b217532879',
				'Speaker Deck',
				'Why Backbone',
			],

			// Gutenberg: Spotify embed.
			[
				<<<HTML
<figure class="wp-block-embed-spotify wp-block-embed is-type-rich is-provider-spotify wp-embed-aspect-9-16 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Spotify Embed: M1 A1" width="300" height="380" allowtransparency="true" frameborder="0" allow="encrypted-media" src="https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW"></iframe>
</div></figure>
HTML
				,
				'https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW',
				'Spotify',
				'Spotify Embed: M1 A1',
			],

			// Gutenberg: TED embed.
			[
				<<<HTML
<figure class="wp-block-embed-ted wp-block-embed is-type-video is-provider-ted wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Jill Bolte Taylor: My stroke of insight" src="https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight" width="640" height="361" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
</div></figure>
HTML
				,
				'https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight',
				'TED',
				'Jill Bolte Taylor: My stroke of insight',
			],

			// Gutenberg: TikTok embed.
			[
				<<<HTML
<figure class="wp-block-embed-tiktok wp-block-embed is-type-video is-provider-tiktok"><div class="wp-block-embed__wrapper">
<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@dynamic_wallpaper/video/6778286193776938241" data-video-id="6778286193776938241" style="max-width: 605px;min-width: 325px;"> <section><a target="_blank" title="@dynamic_wallpaper" href="https://www.tiktok.com/@dynamic_wallpaper">@dynamic_wallpaper</a> <p>そうゆうアプリがあるから無断転載じゃないよ</p> <a target="_blank" title="♬ Monster - LUM!X" href="https://www.tiktok.com/music/Monster-6764388127734761473">♬ Monster – LUM!X</a> </section>
</blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
</div></figure>
HTML
				,
				'https://www.tiktok.com/@dynamic_wallpaper/video/6778286193776938241',
				'TikTok',
			],

			// Gutenberg: Tumblr embed.
			[
				<<<HTML
<figure class="wp-block-embed-tumblr wp-block-embed is-type-rich is-provider-tumblr"><div class="wp-block-embed__wrapper">
<div class="tumblr-post" data-href="https://embed.tumblr.com/embed/post/D5irYqe4SehhxRSSl9nZ2Q/186928288420" data-did="da39a3ee5e6b4b0d3255bfef95601890afd80709"  ><a href="https://blog.cutecataccessories.com/post/186928288420">https://blog.cutecataccessories.com/post/186928288420</a></div><script async src="https://assets.tumblr.com/post.js"></script>
</div></figure>
HTML
				,
				'https://embed.tumblr.com/embed/post/D5irYqe4SehhxRSSl9nZ2Q/186928288420',
				'Tumblr',
			],

			// Gutenberg: VideoPress embed.
			[
				<<<HTML
<figure class="wp-block-embed-videopress wp-block-embed is-type-video is-provider-videopress wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe title="Matt Mullenweg: Matt on WordPress" width='640' height='360' src='https://videopress.com/embed/bd2G0c0g?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script>
</div></figure>
HTML
				,
				'https://videopress.com/embed/bd2G0c0g?hd=0',
				'VideoPress',
				'Matt Mullenweg: Matt on WordPress',
			],

			// Gutenberg: WordPress embed.
			[
				<<<HTML
<figure class="wp-block-embed-wordpress wp-block-embed is-type-link is-provider-the-wordpress-com-blog"><div class="wp-block-embed__wrapper">
<a href="https://en.blog.wordpress.com/2019/08/10/the-second-edition-of-our-learn-user-support-workshop-is-open-for-signups/">The Second Edition of Our &#8220;Learn User Support&#8221; Workshop Is Open for&nbsp;Signups</a>
</div></figure>
HTML
				,
				'https://en.blog.wordpress.com/2019/08/10/the-second-edition-of-our-learn-user-support-workshop-is-open-for-signups/',
				'the original site',
			],

			// Gutenberg: WordPress.tv embed.
			[
				<<<HTML
<figure class="wp-block-embed-wordpress-tv wp-block-embed is-type-video wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
<iframe width='640' height='360' src='https://videopress.com/embed/DK5mLrbr?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script>
</div></figure>
HTML
				,
				'https://videopress.com/embed/DK5mLrbr?hd=0',
				'WordPress.tv',
			],

			// Classic: Amazon Kindle embed.
			[
				<<<HTML
<p><iframe title="The Design of Everyday Things: Revised and Expanded Edition" type="text/html" width="640" height="550" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_fQJVU4T7hZhNqQ&#038;asin=B00E257T6C&#038;tag=kpembed-20"></iframe></p>
HTML
				,
				'https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_fQJVU4T7hZhNqQ&#038;asin=B00E257T6C&#038;tag=kpembed-20',
				'Amazon',
				'The Design of Everyday Things: Revised and Expanded Edition',
			],

			// Classic: Animoto embed.
			[
				<<<HTML
<p><iframe title="Video Player" id="vp1WmGs0" width="640" height="360" frameborder="0" src="https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565903296&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=" allowfullscreen></iframe></p>
HTML
				,
				'https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565903296&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=',
				'Animoto',
				'Video Player',
			],

			// Classic: Cloudup embed.
			[
				<<<HTML
<p><iframe title="Video Stream - share clips and home movies" src="https://cloudup.com/cjZ6QGIsErH?chromeless" data-uid="cjZ6QGIsErH" data-aspect-ratio='1.3704496788008564' width="640" height="467" scrolling="no" frameborder="0" mozallowfullscreen="true" webkitallowfullscreen="true" allowfullscreen="true" class="cloudup_iframe_embed"></iframe></p>
HTML
				,
				'https://cloudup.com/cjZ6QGIsErH?chromeless',
				'Cloudup',
				'Video Stream - share clips and home movies',
			],

			// Classic: CollegeHumor embed.
			[
				<<<HTML
<p><iframe title="Prank War 7: The Half Million Dollar Shot" src="https://www.collegehumor.com/e/3922232" width="640" height="360" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe></p>
HTML
				,
				'https://www.collegehumor.com/e/3922232',
				'CollegeHumor',
				'Prank War 7: The Half Million Dollar Shot',
			],

			// Classic: Dailymotion embed.
			[
				<<<HTML
<p><iframe title="Babysitter!" frameborder="0" width="640" height="480" src="https://www.dailymotion.com/embed/video/xoxulz" allowfullscreen allow="autoplay"></iframe></p>
HTML
				,
				'https://www.dailymotion.com/embed/video/xoxulz',
				'Dailymotion',
				'Babysitter!',
			],

			// Classic: Hulu embed.
			[
				<<<HTML
<p><iframe title="Wed, May 21, 2008 (Late Night With Conan O&#039;Brien)" width="640" height="370" src="//www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowfullscreen> </iframe></p>
HTML
				,
				'https://www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w',
				'Hulu',
				'Wed, May 21, 2008 (Late Night With Conan O&#039;Brien)',
			],

			// Classic: Kickstarter embed.
			[
				<<<HTML
<p><iframe title="Help Support The Kiggins Theatre to go Digital!" src="https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html" height="360.0" width="640" frameborder="0" scrolling="no"></iframe></p>
HTML
				,
				'https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html',
				'Kickstarter',
				'Help Support The Kiggins Theatre to go Digital!',
			],

			// Classic: Mixcloud embed.
			[
				<<<HTML
<p><iframe title="Moving Sounds With James Heather (11/08/2019)" width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&amp;hide_cover=1" frameborder="0"></iframe></p>
HTML
				,
				'https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&#038;hide_cover=1',
				'Mixcloud',
				'Moving Sounds With James Heather (11/08/2019)',
			],

			// Classic: ReverbNation embed.
			[
				<<<HTML
<p><iframe title="Easy by Dyaphonic" width="640" height="960" scrolling="no" frameborder="no" src="https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc[song_ids]=30572216"></iframe></p>
HTML
				,
				'https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc%5Bsong_ids%5D=30572216',
				'ReverbNation',
				'Easy by Dyaphonic',
			],

			// Classic: Scribd embed.
			[
				<<<HTML
<p><iframe title="Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests" class="scribd_iframe_embed" src="https://www.scribd.com/embeds/110799637/content" scrolling="no" id="110799637" width="640" height="960" frameborder="0"></iframe><script type="text/javascript">          (function() { var scribd = document.createElement("script"); scribd.type = "text/javascript"; scribd.async = true; scribd.src = "https://www.scribd.com/javascripts/embed_code/inject.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(scribd, s); })()        </script></p>
HTML
				,
				'https://www.scribd.com/embeds/110799637/content',
				'Scribd',
				'Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests',
			],

			// Classic: Slideshare embed.
			[
				<<<HTML
<p><iframe title="Business Quotes for 2011" src="https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY" width="427" height="356" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC; border-width:1px; margin-bottom:5px; max-width: 100%;" allowfullscreen> </iframe> </p>
<div style="margin-bottom:5px"> <strong> <a href="https://www.slideshare.net/haraldf/business-quotes-for-2011" title="Business Quotes for 2011" target="_blank">Business Quotes for 2011</a> </strong> from <strong><a href="https://www.slideshare.net/haraldf" target="_blank">Harald Felgner, PhD</a></strong> </div>
HTML
				,
				'https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY',
				'Slideshare',
				'Business Quotes for 2011',
			],

			// Classic: SoundCloud embed.
			[
				<<<HTML
<p><iframe title="Flickermood by Forss" width="640" height="400" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1"></iframe></p>
HTML
				,
				'https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1',
				'SoundCloud',
				'Flickermood by Forss',
			],

			// Classic: Speaker Deck embed.
			[
				<<<HTML
<p><iframe title="Why Backbone" id="talk_frame_48643" src="//speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe></p>
HTML
				,
				'https://speakerdeck.com/player/4648d440a3230130452522b217532879',
				'Speaker Deck',
				'Why Backbone',
			],

			// Classic: Spotify embed.
			[
				<<<HTML
<p><iframe title="Spotify Embed: M1 A1" width="300" height="380" allowtransparency="true" frameborder="0" allow="encrypted-media" src="https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW"></iframe></p>
HTML
				,
				'https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW',
				'Spotify',
				'Spotify Embed: M1 A1',
			],

			// Classic: TED embed.
			[
				<<<HTML
<p><iframe title="Jill Bolte Taylor: My stroke of insight" src="https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight" width="640" height="361" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></p>
HTML
				,
				'https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight',
				'TED',
				'Jill Bolte Taylor: My stroke of insight',
			],

			// Classic: VideoPress embed.
			[
				<<<HTML
<p><iframe title="Matt Mullenweg: Matt on WordPress" width='640' height='360' src='https://videopress.com/embed/bd2G0c0g?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script></p>
HTML
				,
				'https://videopress.com/embed/bd2G0c0g?hd=0',
				'VideoPress',
				'Matt Mullenweg: Matt on WordPress',
			],

			// Classic: WordPress.tv embed. WordPress.tv will present as VideoPress when embedded this way.
			[
				<<<HTML
<p><iframe width='640' height='360' src='https://videopress.com/embed/DK5mLrbr?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script></p>
HTML
				,
				'https://videopress.com/embed/DK5mLrbr?hd=0',
				'VideoPress',
			],
		];
	}

	/**
	 * A filter function to modify the URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 *
	public function filter_apple_news_embed_generic_json( $json ) {
		$json['URL'] = 'https://www.embed_generic.com/test/posts/54321';

		return $json;
	}

	/**
	 * Test the `apple_news_embed_generic_json` filter.
	 *
	 * @access public
	 *
	public function testFilter() {

		// Setup.
		$component = new Embed_Generic(
			'https://www.embed_generic.com/test/posts/12345',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_embed_generic_json',
			array( $this, 'filter_apple_news_embed_generic_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://www.embed_generic.com/test/posts/54321',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_embed_generic_json',
			array( $this, 'filter_apple_news_embed_generic_json' )
		);
	}

	/**
	 * Tests the transformation process from oEmbed HTML to an Embed Generic component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $html     The HTML to test.
	 * @param string $url      The expected URL associated with the embed.
	 * @param string $provider The expected name of the provider associated with the embed.
	 * @param string $title    Optional. The title for the embed, if there is one.
	 *
	 * @access public
	 */
	public function testTransform( $html, $url, $provider, $title = '' ) {

		// Setup.
		$component = new Embed_Generic(
			$html,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Ensure that the node match returns true for valid signatures.
		$node = self::build_node( $html );
		$this->assertEquals(
			$component->node_matches( $node ),
			$node
		);

		// Set up expected components result.
		$components = [
			[
				'role'      => 'body',
				'text'      => '<a href="' . $url . '">' . 'View on ' . $provider . '.' . '</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
		];

		// If there is a provided title, push it on to the front of the components array.
		if ( ! empty( $title ) ) {
			array_unshift(
				$components,
				[
					'role'   => 'heading2',
					'text'   => $title,
					'format' => 'html',
				]
			);
		}

		// Test.
		$this->assertEquals(
			[
				'layout'     => 'embed-generic-layout',
				'role'       => 'container',
				'components' => $components,
			],
			$component->to_array()
		);
	}
}
