<?php
/**
 * NOVIUS OS - Web OS for digital communication
 * 
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

namespace Cms\Blog;

use Fuel\Core\Arr;
use Fuel\Core\Config;

class Controller_Admin_Inspector_Date extends \Cms\Controller_Inspector_Date {

	public function before($response = null) {
		Config::load('cms_blog::admin/date', true);
		$this->config = Arr::merge($this->config, Config::get('cms_blog::admin/date'));

		parent::before($response);
	}
}