<?php
namespace Apple_Exporter\Components;

/**
 * An HTML's blockquote representation.
 *
 * @since 1.1.8
 */
class Pre extends Component {

  /**
   * Look for node matches for this component.
   *
   * @param DomNode $node
   * @return mixed
   * @static
   * @access public
   */
  public static function node_matches( $node ) {
    if ( 'pre' == $node->nodeName ) {
      return $node;
    }

    return null;
  }

  /**
   * Build the component.
   *
   * @param string $text
   * @access protected
   */
  protected function build( $text ) {
    preg_match( '#<pre.*?>(.*?)</pre>#si', $text, $matches );
    $text = $matches[1];

    $this->json = array(
      'role' => 'container',
      'components' => array( array(
        'role'   => 'body',
        'text'   => $text,
        'format' => 'markdown',
        'layout' => 'pre-layout',
        'textStyle' => 'default-pre',
      ) ),
    );

    $this->set_style();
    $this->set_layout();
  }

  /**
   * Set the layout for the component.
   *
   * @access private
   */
  private function set_layout() {
    $this->register_layout( 'pre-layout', array(
      'margin' => array(
        'top' => 12,
        'bottom' => 12,
      ),
    ) );
  }

  /**
   * Set the style for the component.
   *
   * @access private
   */
  private function set_style() {
    $this->json['textStyle'] = 'default-pre';
    $this->register_style( 'default-pre', array(
      'fontName'								=> $this->get_setting( 'pre_font' ),
      'fontSize'								=> intval( $this->get_setting( 'pre_size' ) ),
      'textColor'								=> $this->get_setting( 'pre_color' ),
      'lineHeight'    					=> intval( $this->get_setting( 'pre_line_height' ) ),
      'paragraphSpacingBefore'	=> 0,
      'paragraphSpacingAfter'		=> 0,
    ) );
  }

}

