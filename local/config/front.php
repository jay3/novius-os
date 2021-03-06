<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

return array(

	'cms_blog' => array(
		'title' => 'Blog',
		'id'    => 'cms_blog',
		'rewrite_prefix' => 'blog',
		'desc'  => '',
        'target' => 'cms_blog/front',
		'iconUrl' => 'static/modules/cms_blog/img/16/blog.png',
		'popupUrl' => 'admin/cms_blog/popup',
		'previewUrl' => 'admin/cms_blog/preview',
	),

	/*
    'blog' => array (
        'name' => 'blog',
        'title' => 'Blog',
        'target' => 'cms_blog/front',
        'icon' => 'static/cms/admin/icons/blog.gif',
    ),
    'news' => array (
        'name' => 'news',
        'title' => 'Actualités',
        'target' => 'cms_news/front/news',
        'icon' => 'static/cms/admin/icons/actualite.gif',
    ),
    'codesource' => array(
        'name' => 'codesource',
        'title' => 'Code source',
        'target' => 'labs_codesource/front',
        'icon' => 'static/cms/admin/icons/actualite.gif',
    ),
	 */
);