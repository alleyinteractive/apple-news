<?php

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Pre as Pre;

class Pre_Test extends Component_TestCase {

  public function testBuildingRemovesTags() {
    $component = new Pre( '<pre><code data-lang="javascript">var x = {
	"foo": 1;
}</code></pre>',
	null, $this->settings, $this->styles, $this->layouts
	);

    $result_wrapper = $component->to_array();
    $result = $result_wrapper['components'][0];
    $this->assertEquals( 'container', $result_wrapper['role'] );
    $this->assertEquals( 'body', $result['role'] );
    $this->assertEquals( '<code data-lang="javascript">var x = {
	"foo": 1;
}</code>', $result['text'] );
    $this->assertEquals( 'markdown', $result['format'] );
    $this->assertEquals( 'default-pre', $result['textStyle'] );
    $this->assertEquals( 'pre-layout', $result['layout'] );
  }

}

