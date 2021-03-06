<?php
/**
 * NOVIUS OS - Web OS for digital communication
 * 
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

?>
<h1><?= __('Templates list'); ?></h1>

<style type="text/css">
  table.borderized {
    border-collapse: collapse;
  }
  table.borderized td, table.borderized th {
    border: 1px solid #000;
    padding: 0 0.5em;
  }
  table.borderized ul {
      padding-left: 20px;
  }
</style>
<table class="borderized">
  <tr>
    <th><?= __('Title') ?></th>
    <th><abbr title="What You See Is What You Get">WYSIWYG</abbr></th>
  </tr>
  <?php foreach ($templates as $template) { ?>
    <tr>
      <td><?= $template['title'] ?></td>
      <td>
        <ul>
          <li><?= implode('</li><li>', $template['wysiwyg']) ?></li>
        </ul>
      </td>
    </tr>
  <?php } ?>
</table>