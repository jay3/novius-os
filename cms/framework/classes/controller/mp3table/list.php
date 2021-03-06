<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

namespace Cms;

use Fuel\Core\Request;

use Asset, Format, Input, Session, View, Uri;

/**
 * The cloud Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Mp3table_List extends Controller_Generic_Admin {

	protected $config = array(
		'tab' => array(),
		'headers' => array(),
		'searchmenu' => array(),
	);

	public function after($response) {
		\Asset::add_path('static/cms/');
		\Asset::add_path('static/cms/js/jquery/wijmo/');
		\Asset::add_path('static/cms/js/jquery/jquery-ui-noviusos/');
		\Asset::css('aristo/jquery-wijmo.css', array(), 'css');
		\Asset::css('jquery.wijmo-open.1.5.0.css', array(), 'css');
		\Asset::css('jquery.wijmo-complete.1.5.0.css', array(), 'css');
		\Asset::css('base.css', array(), 'css');
		\Asset::css('jquery.nos.mp3grid.css', array(), 'css');

		return parent::after($response);
	}

	public function action_index()
	{
		if (!\Cms\Auth::check()) {
			\Response::redirect('/admin/login?redirect='.urlencode($_SERVER['REDIRECT_URL']));
			exit();
		}

		$view = View::forge('mp3table/list');

        $view->set('urljson', $this->config['urljson'], false);
		$view->set('i18n', \Format::forge($this->config['i18n'])->to_json(), false);

		$this->template->body = $view;
	}

    public function action_json()
    {

		if (!\Cms\Auth::check()) {
			$json = \Format::forge()->to_json(array(
				'login_page' => \Uri::base(false).'admin/login',
			));
			\Response::forge($json, 403, array(
				'Content-Type' => 'application/json',
			))->send(true);
			exit();
		}

        $offset = intval(Input::get('offset', 0));
        $limit = intval(Input::get('limit', \Arr::get($this->config['query'], 'limit')));

        $items = array();

        $model = $this->config['query']['model'];

        $query = \Cms\Orm\Query::forge($model, $model::connection());
        foreach ($this->config['query']['related'] as $related) {
            $query->related($related);
        }

        foreach ($this->config['inputs'] as $input => $condition) {
            $value = Input::get('inspectors.'.$input);
            if (is_callable($condition)) {
                $query = $condition($value, $query);
            }
        }



        $inspectors_lang = Input::get('inspectors.lang', null);
        $translatable  = $model::observers('Cms\Orm_Translatable');
        if ($translatable) {

            if (empty($inspectors_lang)) {
                // No inspector, we only search items in their primary language
                $query->where($translatable['single_id_property'], 'IS NOT', null);
            } else if (is_array($inspectors_lang)) {
                // Multiple langs
                $query->where($translatable['lang_property'], 'IN', $inspectors_lang);
            } else  {
                $query->where($translatable['lang_property'],  '=', $inspectors_lang);
            }
            $common_ids = array();
            $keys = array();
        }

        Filter::apply($query, $this->config);

        $count = $query->count();

        // Copied over and adapted from $query->count()
        $select = \Arr::get($model::primary_key(), 0);
        $select = (strpos($select, '.') === false ? $query->alias().'.'.$select : $select);

        // Get the columns
        $columns = \DB::expr('DISTINCT '.\Database_Connection::instance()->quote_identifier($select).' AS group_by_pk');

        // Remove the current select and
        $new_query = call_user_func('DB::select', $columns);

        // Set from table
        $new_query->from(array($model::table(), $query->alias()));



        $tmp   = $query->build_query($new_query, $columns, 'select');
        $new_query = $tmp['query'];
        $objects = $new_query->group_by('group_by_pk')->limit($limit)->offset($offset)->execute($query->connection())->as_array('group_by_pk');

        if (!empty($objects)) {
            $query = $model::find()->where(array($select, 'in', array_keys($objects)));

            Filter::apply($query, $this->config);

            foreach ($query->get() as $object) {
                $item = array();
                foreach ($this->config['dataset'] as $key => $data) {
                    if (is_array($data)) {
                        $data = $data['value'];
                    }
                    if (is_callable($data)) {
                        $item[$key] = $data($object);
                    } else {
                        $item[$key] = $object->{$data};
                    }
                }
                $items[] = $item;
                if ($translatable) {
                    $common_id = $object->{$translatable['common_id_property']};
                    $keys[] = $common_id;
                    $common_ids[$translatable['common_id_property']][] = $common_id;
                }
            }
            if ($translatable) {
                $langs = call_user_func('Cms\Orm_Translatable::orm_notify_class', $model, 'languages', $common_ids);
                foreach ($keys as $key => $common_id) {
                    $items[$key]['lang'] = $langs[$common_id];
                }

                foreach ($items as &$item) {
                    $flags = '';
                    foreach (explode(',', $item['lang']) as $lang) {
                        switch($lang) {
                            case 'en':
                                $lang = 'gb';
                                break;
                        }
                        $flags .= '<img src="static/cms/img/flags/'.$lang.'.png" /> ';
                    }
                    $item['lang'] = $flags;
                }
            }
        }

        $json = array(
            'get' => '',
            'query' =>  '',
            'offset' => $offset,
            'items' => $items,
            'total' => $count,
        );

        if (\Fuel::$env === \Fuel::DEVELOPMENT) {
            $json['get'] = Input::get();
            $json['query'] = (string) $query->get_query();
        }
        if (\Input::get('debug') !== null) {
            \Debug::dump($json);
            exit();
        }

        $response = \Response::forge(\Format::forge()->to_json($json), 200, array(
            'Content-Type' => 'application/json',
        ));
        $response->send(true);
        exit();
    }

	protected function searchtext_condition($menu, $target, $search)
	{
		if ($target) {
			if ($menu['target'] == $target) {
				if (isset($menu['column'])) {
					return array(array($menu['column'], 'like', '%'.$search.'%'));
				} else if (isset($menu['submenu']) && is_array($menu['submenu'])) {
					$wheres = array();
					foreach ($menu['submenu'] as $smenu) {
						$wheres = array_merge($wheres, $this->searchtext_condition($smenu, false, $search));
					}
					return $wheres;
				}
			} else if (isset($menu['submenu']) && is_array($menu['submenu'])) {
				foreach ($menu['submenu'] as $smenu) {
					$where = $this->searchtext_condition($smenu, $target, $search);
					if (count($where)) {
						return $where;
					}
				}
			}
		} else {
			if (isset($menu['column'])) {
				return array(array($menu['column'], 'like', '%'.$search.'%'));
			} else if (isset($menu['submenu']) && is_array($menu['submenu'])) {
				$wheres = array();
				foreach ($menu['submenu'] as $smenu) {
					$wheres = array_merge($wheres, $this->searchtext_condition($smenu, false, $search));
				}
				return $wheres;
			}
		}
		return array();
	}


}

/* End of file list.php */
