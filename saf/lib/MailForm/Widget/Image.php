<?php
//
// +----------------------------------------------------------------------+
// | Sitellite Content Management System                                  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2010 Sitellite.org Community                           |
// +----------------------------------------------------------------------+
// | This software is released under the GNU GPL License.                 |
// | Please see the accompanying file docs/LICENSE for licensing details. |
// |                                                                      |
// | You should have received a copy of the GNU GPL License               |
// | along with this program; if not, visit www.sitellite.org.            |
// | The license text is also available at the following web site         |
// | address: <http://www.sitellite.org/index/license                     |
// +----------------------------------------------------------------------+
// | Authors: John Luxford <john.luxford@gmail.com>                       |
// +----------------------------------------------------------------------+
//
// Image widget.  Displays an HTML <input type="image" /> form field.
//

/**
	 * Image widget.  Displays an HTML <input type="image" /> form field.
	 *
	 * New in 1.2:
	 * - Added a getValue() method, which returns an array of the form
	 *   [x => #, y => #].
	 * 
	 * <code>
	 * <?php
	 * 
	 * $widget = new MF_Widget_image ('name');
	 * $widget->setValues ('/pix/button.gif');
	 * 
	 * ? >
	 * </code>
	 * 
	 * @package	MailForm
	 * @author	John Luxford <john.luxford@gmail.com>
	 * @license	http://www.sitellite.org/index/license	GNU GPL License
	 * @version	1.2, 2003-08-28, $Id: Image.php,v 1.3 2007/10/06 00:06:30 lux Exp $
	 * @access	public
	 * 
	 */

class MF_Widget_image extends MF_Widget {
	/**
	 * A way to pass extra parameters to the HTML form tag, for
	 * example 'enctype="multipart/formdata"'.
	 * 
	 * @access	public
	 * 
	 */
	var $extra = '';

	/**
	 * This is the short name for this widget.  The short name is
	 * the class name minus the 'MF_Widget_' prefix.
	 * 
	 * @access	public
	 * 
	 */
	var $type = 'image';

	/**
	 * Constructor Method.  Also sets the $passover_isset property
	 * to true.
	 * 
	 * @access	public
	 * @param	string	$name
	 * 
	 */
	function MF_Widget_image ($name) {
		parent::MF_Widget ($name);
		$this->passover_isset = true;
	}

	/**
	 * Fetches the actual value for this widget, which is an array with keys
	 * x and y, representing the coordinates of the mouse click of the visitor.
	 * 
	 * @access	public
	 * @param	object	$cgi
	 * @return	array
	 * 
	 */
	function getValue ($cgi = '') {
		if (is_object ($cgi) && isset ($cgi->{$this->name . '_x'})) {
			return array ('x' => $cgi->{$this->name . '_x'}, 'y' => $cgi->{$this->name . '_y'});
		}
		return false;
	}

	/**
	 * Returns the display HTML for this widget.  The optional
	 * parameter determines whether or not to automatically display the widget
	 * nicely, or whether to simply return the widget (for use in a template).
	 * 
	 * @access	public
	 * @param	boolean	$generate_html
	 * @return	string
	 * 
	 */
	function display ($generate_html = 0) {
		$attrstr = $this->getAttrs ();
		if ($generate_html) {
			return "\t" . '<tr>' . "\n\t\t" . '<td colspan="2" align="center" class="submit"><input type="image" ' . $attrstr . ' src="' . $this->value . '" ' . $this->extra . ' /></td>' . "\n\t" . '</tr>' . "\n";
		} else {
			return '<input type="image" ' . $attrstr . ' src="' . $this->value . '" ' . $this->extra . ' />';
		}
	}
}



?>