<?php
/**
 * Publish to Apple News tests: Apple_News_Recipe_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Recipe class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Recipe_Test extends Apple_News_TestCase {
	/**
	 * Set the setting for the recipe class before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		update_option(
			Apple_News::$option_name,
			array_merge(
				get_option( Apple_News::$option_name ) ?: [],
				[
					'recipe_component_class' => 'test-recipe-class',
				]
			)
		);

		// To be able to save SCRIPT tags.
		$user = $this->acting_as( 'administrator' );
		grant_super_admin( $user->ID );
		kses_init();
	}

	/**
	 * Test that recipe tokens with no replacement value from the JSON schema are removed.
	 */
	public function test_remove_objects_with_empty_tokens(): void {
		$post_content = <<<HTML
<div class="test-recipe-class">
	<h2>Chocolate Cake</h2>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake",
	  "datePublished": "2024-10-13",
	  "dateModified": "2024-10-13",
	  "prepTime": "PT15M",
	  "cookTime": "PT0M",
	  "totalTime": "PT15M",
	  "image": {
	    "@type": "ImageObject",
	    "url": "https:\/\/www.example.com\/wp-content\/uploads\/2024\/09\/photo.jpg",
	    "height": 1200,
	    "width": 1200
	  }
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		// There should be a photo component, but there shouldn't be a caption.
		$this->assertTrue( isset( $json['components'][3]['components'][0]['role'] ) );
		$this->assertSame( 'photo', $json['components'][3]['components'][0]['role'] );
		$this->assertFalse( isset( $json['components'][3]['components'][0]['caption'] ) );
	}

	/**
	 * Test that recipe instructions are expanded from a list of strings.
	 */
	public function test_recipe_instructions_list_of_strings(): void {
		// These instructions via https://www.delicious.com.au/recipes/huevos-rancheros-beans/7WDKzQe3.
		$post_content = <<<HTML
<div class="test-recipe-class">
	<h2>Chocolate Cake</h2>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake",
	  "recipeInstructions": [
      "Preheat oven to 180\u00b0C\/160\u00b0C fan-forced. Grease a baking tray and line with baking paper. Spread tortilla pieces over prepared tray and bake for 8-10 minute, until golden.",
      "Meanwhile, heat oil in a large frypan with a lid over high heat. Add chorizo and cook, stirring occasionally, for 2-3 minute, until starting to crisp. Add garlic, capsicum, shallot and chilli, and cook, stirring regularly, for 10 minutes, or until vegetables have softened and caramelised.",
      "Add tomato and beans, together with the canning liquid. Bring to the boil, then reduce heat to medium and cook, stirring occasionally, for 10 minutes, or until sauce thickens slightly. Stir through the baby spinach for 1 minute, or until just wilted.",
      "Using a spoon, make 4 small wells in the mixture, then carefully crack an egg into each. Cover and cook for 3-4 minutes, or until eggs are cooked to your liking.",
      "Drizzle lime juice over beans and eggs, and serve immediately with crisp tortillas, lime wedges, extra shallot and sliced chilli, and the avocado slices."
		]
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		$this->assertStringStartsWith(
			'Preheat oven to 180',
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][0]['text']
		);
	}

	/**
	 * Test that recipe instructions are expanded from a list of HowToStep objects.
	 */
	public function test_recipe_instructions_steps(): void {
		// These instructions via https://chatelaine.com/recipe/desserts/easy-chocolate-cake/.
		$post_content = <<<HTML
<div class="test-recipe-class">
	<h2>Chocolate Cake</h2>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake",
	  "recipeInstructions": [
	    {
	      "@type": "HowToStep",
	      "name": "Step 1",
	      "text": "PREHEAT oven to 350F. Spray a 9 x 13-in. baking pan and line with parchment up the length of 2 sides.",
	      "url": "https://chatelaine.com/recipe/desserts/easy-chocolate-cake/#step-1"
	    },
	    {
	      "@type": "HowToStep",
	      "name": "Step 2",
	      "text": "WHISK flour, sugar, cocoa, baking soda, baking powder and salt in a medium bowl.",
	      "url": "https://chatelaine.com/recipe/desserts/easy-chocolate-cake/#step-2"
	    },
	    {
	      "@type": "HowToStep",
	      "name": "Step 3",
	      "text": "WHISK eggs, buttermilk, oil and vanilla in a large bowl. Stir in flour mixture until just combined. Whisk in hot water. Pour into prepared pan.",
	      "url": "https://chatelaine.com/recipe/desserts/easy-chocolate-cake/#step-3"
	    },
	    {
	      "@type": "HowToStep",
	      "name": "Step 4",
	      "text": "BAKE until a cake tester inserted in centre of cake comes out clean, about 30 min. Let cake cool in pan on a rack for 30 min. Place rack on top of cake, then invert and remove baking pan and parchment. Cool completely, about 30 min. Invert onto serving platter.",
	      "url": "https://chatelaine.com/recipe/desserts/easy-chocolate-cake/#step-4"
	    },
	    {
	      "@type": "HowToStep",
	      "name": "Step 5",
	      "text": "STIR icing sugar and cocoa in a medium bowl. Beat butter in a large bowl with a hand mixer on medium-high, until fluffy. Reduce speed to medium-low and add icing sugar mixture, alternating with milk and ending with icing sugar mixture, until combined. Spread on chocolate cake and sprinkle with candy.",
	      "url": "https://chatelaine.com/recipe/desserts/easy-chocolate-cake/#step-5"
	    }
	  ]
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		$this->assertStringStartsWith(
			'Step 1',
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][0]['text']
		);
		$this->assertStringStartsWith(
			'PREHEAT oven to 350F',
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][1]['text']
		);
	}

	/**
	 * Test that recipe instructions are expanded from a tree of HowToSection and HowToStep objects.
	 */
	public function test_recipe_instructions_sections_and_steps(): void {
		// These instructions via https://www.ricardocuisine.com/recettes/3808-chop-suey-au-poulet.
		$post_content = <<<HTML
<div class="test-recipe-class">
	<h2>Chocolate Cake</h2>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake",
	  "recipeInstructions": [
      {
        "@type": "HowToSection",
        "name": "Chop suey au poulet",
        "itemListElement": [
          {
            "@type": "HowToStep",
            "text": "Dans un bol, m\u00e9langer le bouillon, la sauce soya et la f\u00e9cule. "
          },
          {
            "@type": "HowToStep",
            "text": "Dans un wok ou une grande po\u00eale \u00e0 feu \u00e9lev\u00e9, dorer les champignons, le poivron, le c\u00e9leri et le blanc des oignons verts dans l'huile. Ajouter les f\u00e8ves germ\u00e9es, l'ail et poursuivre la cuisson 2 minutes. Ajouter le poulet et le m\u00e9lange de bouillon. Porter \u00e0 \u00e9bullition en remuant et laisser mijoter 2 minutes ou jusqu'\u00e0 ce que les f\u00e8ves soient tendres. Rectifier l'assaisonnement. Parsemer le vert des oignons verts et les noix de cajou."
          }
        ]
      }
    ]
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		$this->assertSame(
			'Chop suey au poulet',
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][0]['text']
		);
		$this->assertStringStartsWith(
			'Dans un bol',
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][1]['components'][0]['components'][0]['text']
		);
	}

	/**
	 * Test that recipe instructions don't include a heading and body text with the same content.
	 */
	public function test_recipe_step_with_duplicate_text(): void {
		$post_content = <<<HTML
<div class="test-recipe-class">
	<h2>Chocolate Cake</h2>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake",
	  "recipeInstructions": [
	    {
	      "@type": "HowToStep",
	      "name": "Preheat the oven",
	      "text": "Preheat the oven"
	    }
	  ]
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		$this->assertCount(
			1,
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components']
		);
		$this->assertSame(
			'body', // Not heading.
			$json['components'][3]['components'][0]['components'][4]['components'][0]['components'][1]['components'][1]['components'][0]['components'][0]['role']
		);
	}

	/**
	 * Test that recipe HTML is processed normally when the 'use schema' setting is 'no'.
	 */
	public function test_recipe_component_with_component_use_schema_no(): void {
		$this->settings->__set( 'recipe_component_use_schema', 'no' );

		// These instructions via https://www.ricardocuisine.com/recettes/3808-chop-suey-au-poulet.
		$post_content = <<<HTML
<div class="test-recipe-class">
	<table>Chocolate Cake</table>

	<script type="application/ld+json">
	{
	  "@context": "https:\/\/schema.org",
	  "@type": "Recipe",
	  "@id": "https:\/\/www.example.com\/recipes\/chocolate-cake\/",
	  "name": "Chocolate Cake"
	}
	</script>
</div>
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		// This should be a table because the raw HTML found in the matching recipe element should be used.
		$this->assertSame(
			'htmltable',
			$json['components'][3]['components'][0]['role']
		);
	}
}
