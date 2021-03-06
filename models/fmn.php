<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Fmns\Models;

use Hubzero\Database\Relational;
use Hubzero\Utility\Str;
use Session;
use Date;
use stdClass;

class Fmn extends Relational implements \Hubzero\Search\Searchable
{
	/**
	 * The table namespace, access to the SQL database
	 *
	 * @var string
	 */
	protected $namespace = 'fmn';

	/**
	 * Default order-by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'notempty'
	);

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string  $type  The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		static $base;

		if (!isset($base))
		{
			$base = 'index.php?option=com_fmns&controller=fmns';
		}

		$link = $base;

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'group':
			$link = DS . 'groups' . DS . $this->get('group_cn');
			break;
			
			case 'edit':
				$link .= '&task=edit&id=' . $this->get('id');
			break;

			case 'delete':
				$link .= '&task=delete&id=' . $this->get('id') . '&' . Session::getFormToken() . '=1';
			break;

			case 'view':
			case 'permalink':
			default:
				$link .= '&task=view&id=' . $this->get('id');
			break;
		}

		return $link;
	}

	/**
	 * Get the fmn's bio
   *
   * This function is for the about text box in the edit, display and site.
   * The different cases are for the different pages raw takes out the format
   * tag and clean takes out the html tags.
	 *
	 * @param  string  $as      Format to return state in [text, number]
	 * @param  integer $shorten Number of characters to shorten text to
	 * @return string
	 */
	public function about($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			// site view
			case 'parsed':
				$content = $this->get('about.parsed', null);

				if ($content === null)
				{
					$about = \Html::content('prepare', (string) $this->get('about', ''));

					$this->set('about.parsed', (string) $about);

					return $this->about($as, $shorten);
				}

				$options['html'] = true;
			break;

			case 'clean':
				$content = strip_tags($this->about('parsed'));
			break;

			// admin view
			case 'raw':
			default:
				$content = $this->get('about');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = Str::truncate($content, $shorten, $options);
		}
		return $content;
	}

	/**
	 * Get the fmn start and stop dates
   *
	 * @param  string  $when    Start or stop date?
	 * @param  string  $as 			Return type
	 * @return date
	 */	
	public function date($when = 'start_date', $as = 'string')
	{
		$when = ($when == 'start_date' ? $when : 'stop_date');

		switch ($as) 
		{
			case 'seconds':
				$content = $this->get($when);
			break;
			
			case 'string':
			default:
				$content = Date::of($this->get($when))->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
			break;
		}
		
		return $content;
	}

	/**
	 * Get the fmn name
   *
	 * @param  integer $shorten Number of characters to shorten text to
	 * @return string
	 */		
	public function name($shorten = 0)
	{
		$content = trim($this->get('name'));
		
		if ($shorten)
		{
			$content = Str::truncate($content, $shorten);
		}
		return $content;
	}

	/**
	 * Deletes the existing/current model
	 *
	 * @return  bool
	 */
	public function destroy()
	{
		return parent::destroy();
	}
	
	/*
	 * Namespace used for solr Search
	 * @return string
	 */
	public static function searchNamespace()
	{
		$searchNamespace = 'fmn';
		return $searchNamespace;
	}
	
	/*
	 * Generate solr search Id
	 * @return string
	 */
	public function searchId()
	{
		$searchId = self::searchNamespace() . '-' . $this->get('id');
		return $searchId;
	}
	
	/**
	 * Get total number of records that will be indexed by Solr.
	 * @return integer
	 */
	public static function searchTotal()
	{
		$total = self::all()->total();
		return $total;
	}
	
	/**
	 * Get records
	 *
	 * @param   integer  $limit
	 * @param   integer  $offset
	 * @return  object
	 */
	public static function searchResults($limit, $offset = 0)
	{
		return self::all()
			->start($offset)
			->limit($limit)
			->whereEquals('state', 1)
			->rows();
	}
	
	/*
	 * Generate search document for Solr
	 * @return array
	 */
	public function searchResult()
	{
		if ($this->get('state') == 0)
		{
			return false;
		}

		$obj = new stdClass;
		$obj->hubtype = self::searchNamespace();
		$obj->id = $this->searchId();
		$obj->title = $this->get('name');

		$description = $this->get('about');
		$description = html_entity_decode($description);
		$description = \Hubzero\Utility\Sanitize::stripAll($description);

		$obj->description   = $description;
		$obj->url = ($this->get('group_cn') ? \Request::root() . 'groups/' . $this->get('group_cn') : \Request::root() . 'community' . DS . 'fmns');

		// No tags
		$obj->tags[] = array(
			'id' => '',
			'title' => ''
		);
		
		// Needed for admin database view
		$obj->access_level = 'public';
		$obj->owner_type = 'group';
		$obj->owner = '';
		
		return $obj;
	}
}
