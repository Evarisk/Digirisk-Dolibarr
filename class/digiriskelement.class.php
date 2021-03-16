<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/digiriskelement.class.php
 * \ingroup     digiriskdolibarr
 * \brief       This file is a CRUD class file for DigiriskElement (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
dol_include_once('/digiriskdolibarr/class/risk.class.php');
dol_include_once('/digiriskdolibarr/class/digiriskevaluation.class.php');
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for DigiriskElement
 */
class DigiriskElement extends CommonObject
{
	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'digiriskelement';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'digiriskdolibarr_digiriskelement';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for digiriskelement. Must be the part after the 'object_' into object_digiriskelement.png
	 */
	public $picto = 'digiriskelement@digiriskdolibarr';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth200', 'help'=>"Help text", 'showoncombobox'=>'1',),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>0, 'visible'=>1, 'index'=>1,),
		'element_type' => array('type'=>'varchar(50)', 'label'=>'ElementType', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>1,),
		'fk_parent' => array('type'=>'integer', 'label'=>'ParentElement', 'enabled'=>'1', 'position'=>1020, 'notnull'=>1, 'visible'=>1,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1030, 'notnull'=>-1, 'visible'=>0,),
		'last_main_doc' => array('type'=>'varchar(50)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>1050, 'notnull'=>0, 'visible'=>-1,),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>550, 'notnull'=>0, 'visible'=>-1,),
	);
	public $rowid;
	public $ref;
	public $label;
	public $description;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	public $element_type;
	public $fk_parent;
	public $model_pdf;
	public $last_main_doc;
	public $entity;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'digiriskdolibarr_digiriskelementline';

	/**
	 * @var int    Field with ID of parent key if this object has a parent
	 */
	//public $fk_element = 'fk_digiriskelement';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'DigiriskElementline';

	/**
	 * @var array	List of child tables. To test if we can delete object.
	 */
	//protected $childtables = array();

	/**
	 * @var array    List of child tables. To know object to delete on cascade.
	 *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	 *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	 */
	//protected $childtablesoncascade = array('digiriskdolibarr_digiriskelementdet');

	/**
	 * @var DigiriskElementLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->digiriskdolibarr->digiriskelement->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$this->element = $this->element_type . '@digiriskdolibarr';
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);


		// Clear fields
		$object->ref = empty($this->fields['ref']['default']) ? "copy_of_".$object->ref : $this->fields['ref']['default'];
		$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		$object->status = self::STATUS_DRAFT;
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				}
				else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->digiriskelement->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->digiriskelement->digiriskelement_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		}
		else
		{
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."',";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('DIGIRISKELEMENT_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'digiriskelement/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'digiriskelement/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->digiriskdolibarr->dir_output.'/digiriskelement/'.$oldref;
				$dirdest = $conf->digiriskdolibarr->dir_output.'/digiriskelement/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->digiriskdolibarr->dir_output.'/digiriskelement/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->digiriskdolibarr_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'DIGIRISKELEMENT_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->digiriskdolibarr_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'DIGIRISKELEMENT_CLOSE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->digiriskdolibarr->digiriskdolibarr_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'DIGIRISKELEMENT_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = '<u>'.$langs->trans("DigiriskElement").'</u>';
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;
		if (isset($this->status)) {
			$label .= '<br><b>'.$langs->trans("Status").":</b> ".$this->getLibStatut(5);
		}

		$url = dol_buildpath('/digiriskdolibarr/digiriskelement_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowDigiriskElement");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		}
		else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					}
					else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				}
				else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->label;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('digiriskelementdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("digiriskdolibarr@digiriskdolibarr");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new DigiriskElementLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_digiriskelement = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		}
		else
		{
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("digiriskdolibarr@digiriskdolibarr");

		if (empty($conf->global->DIGIRISKDOLIBARR_DIGIRISKELEMENT_ADDON)) {
			$conf->global->DIGIRISKDOLIBARR_DIGIRISKELEMENT_ADDON = 'mod_digiriskelement_standard';
		}

		if (!empty($conf->global->DIGIRISKDOLIBARR_DIGIRISKELEMENT_ADDON))
		{
			$mybool = false;

			$file = $conf->global->DIGIRISKDOLIBARR_DIGIRISKELEMENT_ADDON.".php";
			$classname = $conf->global->DIGIRISKDOLIBARR_DIGIRISKELEMENT_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/digiriskdolibarr/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				}
				else
				{
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		}
		else
		{
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs, $user;
		$result = 0;
		$includedocgeneration = 1;
		$langs->load("digiriskdolibarr@digiriskdolibarr");

		if (!dol_strlen($modele)) {
			$modele = 'standard_digiriskelement';

			if ($this->modelpdf) {
				$modele = $this->modelpdf;
			} elseif (!empty($conf->global->DIGIRISKELEMENT_ADDON_PDF)) {
				$modele = $conf->global->DIGIRISKELEMENT_ADDON_PDF;
			}
		}
		$modelpath = "core/modules/digiriskdolibarr/doc/";

		$template = preg_replace('/_odt/', '.odt', $modele );

		if ( preg_match( '/listing_risks_photos/', $template ) ) {
			$path = DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/documents/doctemplates/listingrisksphoto/';
		} elseif ( preg_match( '/listing_risks_actions/', $template ) ) {
			$path = DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/documents/doctemplates/listingrisksaction/';
		} elseif ( preg_match( '/groupment/', $template ) || preg_match( '/workunit/', $template ) ) {
			$path = DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/documents/doctemplates/'. $this->element_type . '/';
		} elseif ( preg_match( '/legaldisplay/', $template ) ) {

			$path = DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/documents/doctemplates/legaldisplay/';
		}

		$modele = $modele.":". $path . "template_" . $template;
		if ($includedocgeneration) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		if ( preg_match( '/listing_risks_photos/', $template ) ) {
			$this->call_trigger('LISTING_RISKS_PHOTOS_GENERATE', $user);
		} elseif ( preg_match( '/listing_risks_actions/', $template ) ) {
			$this->call_trigger('LISTING_RISKS_ACTIONS_GENERATE', $user);
		} elseif ( preg_match( '/groupment/', $template ) ) {
			$this->call_trigger('GROUPMENT_GENERATE', $user);
		} elseif ( preg_match( '/workunit/', $template ) ) {
			$this->call_trigger('WORKUNIT_GENERATE', $user);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	/**
	 *	Show HTML header HTML + BODY + Top menu + left menu + DIV
	 *
	 * @param 	string 	$head				Optionnal head lines
	 * @param 	string 	$title				HTML title
	 * @param	string	$help_url			Url links to help page
	 * 		                            	Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
	 *                                  	For other external page: http://server/url
	 * @param	string	$target				Target to use on links
	 * @param 	int    	$disablejs			More content into html header
	 * @param 	int    	$disablehead		More content into html header
	 * @param 	array  	$arrayofjs			Array of complementary js files
	 * @param 	array  	$arrayofcss			Array of complementary css files
	 * @param	string	$morequerystring	Query string to add to the link "print" to get same parameters (use only if autodetect fails)
	 * @param   string  $morecssonbody      More CSS on body tag.
	 * @param	string	$replacemainareaby	Replace call to main_area() by a print of this string
	 * @return	void
	 */
	public function digiriskHeader($head = '', $title = '', $help_url = '', $target = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $morequerystring = '', $morecssonbody = '', $replacemainareaby = '')
	{
		global $conf, $langs;

		// html header
		top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);

		$tmpcsstouse = 'sidebar-collapse'.($morecssonbody ? ' '.$morecssonbody : '');
		// If theme MD and classic layer, we open the menulayer by default.
		if ($conf->theme == 'md' && !in_array($conf->browser->layout, array('phone', 'tablet')) && empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
		{
			global $mainmenu;
			if ($mainmenu != 'website') $tmpcsstouse = $morecssonbody; // We do not use sidebar-collpase by default to have menuhider open by default.
		}

		if (!empty($conf->global->MAIN_OPTIMIZEFORCOLORBLIND)) {
			$tmpcsstouse .= ' colorblind-'.strip_tags($conf->global->MAIN_OPTIMIZEFORCOLORBLIND);
		}

		print '<body id="mainbody" class="'.$tmpcsstouse.'">'."\n";
		llxHeader('');//Body navigation digirisk
		$object  = new DigiriskElement($this->db);
		$objects = $object->fetchAll('', '', 0,0,array('entity' => $conf->entity));
		$results  = $this->recurse_tree(0,0,$objects); ?>
		<div id="id-container" class="id-container">
			<div class="side-nav" style="width: 500px; display: block">
				<div id="id-left" style="width: 500px">
					<div class="digirisk-wrap wpeo-wrap">
						<div class="navigation-container">
							<div class="society-header">
								<a class="linkElement" href="digiriskelement_card.php">
									<span class="icon fas fa-building fa-fw"></span>
										<div class="title">
										<?php echo $conf->global->MAIN_INFO_SOCIETE_NOM ?>
										</div>
									<div class="add-container">
										<a id="newGroupment" href="digiriskelement_card.php?action=create&element_type=groupment&fk_parent=0">
											<div class="wpeo-button button-square-50 wpeo-tooltip-event" data-direction="bottom" data-color="light" aria-label="<?php echo $langs->trans('NewGroupment'); ?>"><span class="button-icon fas fa-home"></span><span class="button-add animated fas fa-plus-circle"></span></div>
										</a>
										<a id="newWorkunit" href="digiriskelement_card.php?action=create&element_type=workunit&fk_parent=0">
											<div class="wpeo-button button-square-50 wpeo-tooltip-event" data-direction="bottom" data-color="light" aria-label="<?php echo $langs->trans('NewWorkunit'); ?>"><span class="button-icon fas fa-home"></span><span class="button-add animated fas fa-plus-circle"></span></div>
										</a>
									</div>
									<div class="mobile-add-container wpeo-dropdown dropdown-right option">
										<div class="dropdown-toggle"><i class="action fas fa-ellipsis-v"></i></div>
										<ul class="dropdown-content">
											<li class="dropdown-item" data-type="Group_Class"><i class="icon dashicons dashicons-admin-multisite"></i><?php //echo //esc_attr( 'Ajouter groupement', 'digirisk' ); ?></li>
											<li class="dropdown-item" data-type="Workunit_Class"><i class="icon dashicons dashicons-admin-home"></i><?php //echo //esc_attr( 'Ajouter unité', 'digirisk' ); ?></li>
										</ul>
									</div>
									<div class="close-popup"><i class="icon fas fa-times"></i></div>
								</a>
							</div>
							<div class="toolbar">
								<div class="toggle-plus tooltip hover" aria-label="<?php echo $langs->trans('UnwrapAll'); ?>"><span class="icon fas fa-plus-square"></span></div>
								<div class="toggle-minus tooltip hover" aria-label="<?php echo $langs->trans('WrapAll'); ?>"><span class="icon fas fa-minus-square"></span></div>
							</div>

							<ul class="workunit-list">
									<?php $this->display_recurse_tree($results) ?>

									<script>
									// Get previous menu to display it
									var MENU = localStorage.menu
									if (MENU == null || MENU == '') {
										MENU = new Set()
									} else {
										MENU = JSON.parse(MENU)
										MENU = new Set(MENU)
									}

									MENU.forEach((id) =>  {
										jQuery( '#menu'+id).removeClass( 'fa-chevron-right').addClass( 'fa-chevron-down' );
										jQuery( '#unit'+id ).addClass( 'toggled' );
									})

									// Set active unit active
									jQuery( '.digirisk-wrap .navigation-container .unit.active' ).removeClass( 'active' );

									var params = new window.URLSearchParams(window.location.search);
									var id = params.get('id')

									jQuery( '#unit'  + id ).addClass( 'active' );
									jQuery( '#unit'  +id  ).closest( '.unit' ).attr( 'value', id );

									</script>
								</ul>
						</div>
					</div>
				</div>
			</div>
		<?php


		// main area
		if ($replacemainareaby)
		{
			print $replacemainareaby;
			return;
		}
		main_area($title);
	}

	function recurse_tree($parent, $niveau, $array) {
		$result = array();
		foreach ($array as $noeud) {
			if ($parent == $noeud->fk_parent) {
				$result[$noeud->id] = array(
					'id'       => $noeud->id,
					'object'   => $noeud,
					'children' => $this->recurse_tree($noeud->id, ($niveau + 1), $array),
				);
			}
		}
		return $result;
	}

	function display_recurse_tree($results) {
		global $conf, $langs;

		if ( !empty( $results ) ) {
			foreach ($results as $element) { ?>
				<li class="unit" id="unit<?php  echo $element['object']->id; ?>">
					<div class="unit-container">
						<?php if ($element['object']->element_type == 'groupment' && count($element['children'])) { ?>
							<div class="toggle-unit">
								<i class="toggle-icon fas fa-chevron-right" id="menu<?php echo $element['object']->id;?>"></i>
							</div>
						<?php } else { ?>
							<div class="spacer"></div>
						<?php } ?>
						<?php $filearray = dol_dir_list($conf->digiriskdolibarr->multidir_output[$conf->entity].'/'.$element['object']->element_type.'/'.$element['object']->ref.'/', "files", 0, '', '(\.odt|_preview.*\.png)$', 'position_name', 'asc', 1);
						if (count($filearray)) {
							print '<span class="floatleft inline-block valignmiddle divphotoref">'.$element['object']->digirisk_show_photos('digiriskdolibarr', $conf->digiriskdolibarr->multidir_output[$conf->entity].'/'.$element['object']->element_type, 'small', 1, 0, 0, 0, 50, 0, 0, 0, 0, $element['object']->element_type).'</span>';
						} else {
							$nophoto = '/public/theme/common/nophoto.png'; ?>
							<span class="floatleft inline-block valignmiddle divphotoref"><img class="photodigiriskdolibarr" alt="No photo" src="<?php echo DOL_URL_ROOT.$nophoto ?>"></span>
						<?php } ?>
						<div class="title" id="scores" value="<?php echo $element['object']->id ?>" >
								<a id="slider" class="linkElement id<?php echo $element['object']->id;?>" href="digiriskelement_risk.php?id=<?php echo $element['object']->id; ?>">
									<span class="title-container">
										<span class="ref"><?php echo $element['object']->ref; ?></span>
										<span class="name"><?php echo $element['object']->label; ?></span>
									</span>
								</a>
						</div>
						<?php if ($element['object']->element_type == 'groupment') { ?>
							<div class="add-container">
								<a id="newGroupment" href="digiriskelement_card.php?action=create&element_type=groupment&fk_parent=<?php echo $element['object']->id; ?>">
									<div
										class="wpeo-button button-square-50 wpeo-tooltip-event"
										data-direction="bottom" data-color="light"
										aria-label="<?php echo $langs->trans('NewGroupment'); ?>">
										<span class="button-icon fas fa-home"></span>
										<span class="button-add animated fas fa-plus-circle"></span>
									</div>
								</a>
								<a id="newWorkunit" href="digiriskelement_card.php?action=create&element_type=workunit&fk_parent=<?php echo $element['object']->id; ?>">
									<div
										class="wpeo-button button-square-50 wpeo-tooltip-event"
										data-direction="bottom" data-color="light"
										aria-label="<?php echo $langs->trans('NewWorkunit'); ?>">
										<span class="button-icon fas fa-home"></span>
										<span class="button-add animated fas fa-plus-circle"></span>
									</div>
								</a>
							</div>
							<div class="mobile-add-container wpeo-dropdown dropdown-right option">
								<div class="dropdown-toggle"><i class="action fas fa-ellipsis-v"></i></div>
								<ul class="dropdown-content">
									<li class="dropdown-item" data-type="Group_Class"><i class="icon dashicons dashicons-admin-multisite"></i><?php echo $langs->trans('NewGroupment'); ?></li>
									<li class="dropdown-item" data-type="Workunit_Class"><i class="icon dashicons dashicons-admin-home"></i><?php echo $langs->trans('NewWorkunit'); ?></li>
								</ul>
							</div>
						<?php } ?>
					</div>
					<ul class="sub-list"><?php $this->display_recurse_tree($element['children']) ?></ul>
				</li>
			<?php }
		}
	}

	  // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Show photos of an object (nbmax maximum), into several columns
	 *
	 *  @param		string	$modulepart		'product', 'ticket', ...
	 *  @param      string	$sdir        	Directory to scan (full absolute path)
	 *  @param      int		$size        	0=original size, 1='small' use thumbnail if possible
	 *  @param      int		$nbmax       	Nombre maximum de photos (0=pas de max)
	 *  @param      int		$nbbyrow     	Number of image per line or -1 to use div. Used only if size=1.
	 * 	@param		int		$showfilename	1=Show filename
	 * 	@param		int		$showaction		1=Show icon with action links (resize, delete)
	 * 	@param		int		$maxHeight		Max height of original image when size='small' (so we can use original even if small requested). If 0, always use 'small' thumb image.
	 * 	@param		int		$maxWidth		Max width of original image when size='small'
	 *  @param      int     $nolink         Do not add a href link to view enlarged imaged into a new tab
	 *  @param      int     $notitle        Do not add title tag on image
	 *  @param		int		$usesharelink	Use the public shared link of image (if not available, the 'nophoto' image will be shown instead)
	 *  @return     string					Html code to show photo. Number of photos shown is saved in this->nbphoto
	 */
	public function digirisk_show_photos($modulepart, $sdir, $size = 0, $nbmax = 0, $nbbyrow = 5, $showfilename = 0, $showaction = 0, $maxHeight = 120, $maxWidth = 160, $nolink = 0, $notitle = 0, $usesharelink = 0, $subdir = '')
	{
        // phpcs:enable
		global $conf, $user, $langs;

		include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

		$sortfield = 'position_name';
		$sortorder = 'asc';

		$dir = $sdir.'/';

		$pdir = '/' . $subdir . '/';


		$dir .= get_exdir(0, 0, 0, 0, $this, $modulepart).$this->ref.'/';
		$pdir .= get_exdir(0, 0, 0, 0, $this, $modulepart).$this->ref.'/';

		// For backward compatibility
		if ($modulepart == 'product' && !empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
		{
			$dir = $sdir.'/'.get_exdir($this->id, 2, 0, 0, $this, $modulepart).$this->id."/photos/";
			$pdir = '/'.get_exdir($this->id, 2, 0, 0, $this, $modulepart).$this->id."/photos/";
		}

		// Defined relative dir to DOL_DATA_ROOT
		$relativedir = '';
		if ($dir)
		{
			$relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT, '/').'/', '', $dir);
			$relativedir = preg_replace('/^[\\/]/', '', $relativedir);
			$relativedir = preg_replace('/[\\/]$/', '', $relativedir);
		}

		$dirthumb = $dir.'thumbs/';
		$pdirthumb = $pdir.'thumbs/';

		$return = '<!-- Photo -->'."\n";
		$nbphoto = 0;

		$filearray = dol_dir_list($dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);
		/*if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))    // For backward compatiblity, we scan also old dirs
		 {
		 $filearrayold=dol_dir_list($dirold,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
		 $filearray=array_merge($filearray, $filearrayold);
		 }*/

		completeFileArrayWithDatabaseInfo($filearray, $relativedir);

		if (count($filearray))
		{
			if ($sortfield && $sortorder)
			{
				$filearray = dol_sort_array($filearray, $sortfield, $sortorder);
			}

			foreach ($filearray as $key => $val)
			{
				$photo = '';
				$file = $val['name'];

				//if (! utf8_check($file)) $file=utf8_encode($file);	// To be sure file is stored in UTF8 in memory

				//if (dol_is_file($dir.$file) && image_format_supported($file) >= 0)
				if (image_format_supported($file) >= 0)
				{
					$nbphoto++;
					$photo = $file;
					$viewfilename = $file;

					if ($size == 1 || $size == 'small') {   // Format vignette
						// Find name of thumb file
						$photo_vignette = basename(getImageFileNameForSize($dir.$file, '_small'));
						if (!dol_is_file($dirthumb.$photo_vignette)) $photo_vignette = '';

						// Get filesize of original file
						$imgarray = dol_getImageSize($dir.$photo);

						if ($nbbyrow > 0)
						{
							if ($nbphoto == 1) $return .= '<table class="valigntop center centpercent" style="border: 0; padding: 2px; border-spacing: 2px; border-collapse: separate;">';

							if ($nbphoto % $nbbyrow == 1) $return .= '<tr class="center valignmiddle" style="border: 1px">';
							$return .= '<td style="width: '.ceil(100 / $nbbyrow).'%" class="photo">';
						}
						elseif ($nbbyrow < 0) $return .= '<div class="inline-block">';

						$return .= "\n";

						$relativefile = preg_replace('/^\//', '', $pdir.$photo);
						if (empty($nolink))
						{
							$urladvanced = getAdvancedPreviewUrl($modulepart, $relativefile, 0, 'entity='.$this->entity);
							if ($urladvanced) $return .= '<a href="'.$urladvanced.'">';
							else $return .= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" class="aphoto" target="_blank">';
						}

						// Show image (width height=$maxHeight)
						// Si fichier vignette disponible et image source trop grande, on utilise la vignette, sinon on utilise photo origine
						$alt = $langs->transnoentitiesnoconv('File').': '.$relativefile;
						$alt .= ' - '.$langs->transnoentitiesnoconv('Size').': '.$imgarray['width'].'x'.$imgarray['height'];
						if ($notitle) $alt = '';

						if ($usesharelink)
						{
							if ($val['share'])
							{
								if (empty($maxHeight) || $photo_vignette && $imgarray['height'] > $maxHeight)
								{
									$return .= '<!-- Show original file (thumb not yet available with shared links) -->';
									$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?hashp='.urlencode($val['share']).'" title="'.dol_escape_htmltag($alt).'">';
								}
								else {
									$return .= '<!-- Show original file -->';
									$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?hashp='.urlencode($val['share']).'" title="'.dol_escape_htmltag($alt).'">';
								}
							}
							else
							{
								$return .= '<!-- Show nophoto file (because file is not shared) -->';
								$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/public/theme/common/nophoto.png" title="'.dol_escape_htmltag($alt).'">';
							}
						}
						else
						{
							if (empty($maxHeight) || $photo_vignette && $imgarray['height'] > $maxHeight)
							{
								$return .= '<!-- Show thumb -->';
								$return .= '<img class="photo photowithmargin"  height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$this->entity.'&file='.urlencode($pdirthumb.$photo_vignette).'" title="'.dol_escape_htmltag($alt).'">';
							}
							else {
								$return .= '<!-- Show original file -->';
								$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" title="'.dol_escape_htmltag($alt).'">';
							}
						}

						if (empty($nolink)) $return .= '</a>';
						$return .= "\n";

						if ($showfilename) $return .= '<br>'.$viewfilename;
						if ($showaction)
						{
							$return .= '<br>';
							// On propose la generation de la vignette si elle n'existe pas et si la taille est superieure aux limites
							if ($photo_vignette && (image_format_supported($photo) > 0) && ($this->imgWidth > $maxWidth || $this->imgHeight > $maxHeight))
							{
								$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=addthumb&amp;file='.urlencode($pdir.$viewfilename).'">'.img_picto($langs->trans('GenerateThumb'), 'refresh').'&nbsp;&nbsp;</a>';
							}
							// Special cas for product
							if ($modulepart == 'product' && ($user->rights->produit->creer || $user->rights->service->creer))
							{
								// Link to resize
								$return .= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$this->id.'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"), 'resize', '').'</a> &nbsp; ';

								// Link to delete
								$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
								$return .= img_delete().'</a>';
							}
						}
						$return .= "\n";

						if ($nbbyrow > 0)
						{
							$return .= '</td>';
							if (($nbphoto % $nbbyrow) == 0) $return .= '</tr>';
						}
						elseif ($nbbyrow < 0) $return .= '</div>';
					}

					if (empty($size)) {     // Format origine
						$return .= '<img class="photo photowithmargin" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'">';

						if ($showfilename) $return .= '<br>'.$viewfilename;
						if ($showaction)
						{
							// Special case for product
							if ($modulepart == 'product' && ($user->rights->produit->creer || $user->rights->service->creer))
							{
								// Link to resize
								$return .= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$this->id.'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"), 'resize', '').'</a> &nbsp; ';

								// Link to delete
								$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
								$return .= img_delete().'</a>';
							}
						}
					}

					// On continue ou on arrete de boucler ?
					if ($nbmax && $nbphoto >= $nbmax) break;
				}
			}

			if ($size == 1 || $size == 'small')
			{
				if ($nbbyrow > 0)
				{
					// Ferme tableau
					while ($nbphoto % $nbbyrow)
					{
						$return .= '<td style="width: '.ceil(100 / $nbbyrow).'%">&nbsp;</td>';
						$nbphoto++;
					}

					if ($nbphoto) $return .= '</table>';
				}
			}
		}

		$this->nbphoto = $nbphoto;

		return $return;
	}
		/**
	 *  Show tab footer of a card.
	 *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->showrefnav.
	 *
	 *  @param	Object	$object			Object to show
	 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
	 *  @param	string	$morehtml  		More html content to output just before the nav bar
	 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
	 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field). Use 'none' for no prev/next search.
	 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
	 *  @param	string	$morehtmlref  	More html to show after ref
	 *  @param	string	$moreparam  	More param to add in nav link url.
	 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
	 *	@param	string	$morehtmlleft	More html code to show before ref
	 *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
	 *  @param  int     $onlybanner     Put this to 1, if the card will contains only a banner (this add css 'arearefnobottom' on div)
	 *	@param	string	$morehtmlright	More html code to show before navigation arrows
	 *  @return	void
	 */
	function digirisk_banner_tab($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $onlybanner = 0, $morehtmlright = '')
	{
		global $conf, $form, $user, $langs;


		print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
		print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
		print '</div>';
		print '<div class="underrefbanner clearboth"></div>';
	}

	public function DigiriskElementDocuments($object, $element_type) {
		global $langs, $conf;

		$risk = new Risk($this->db);
		$data = array();

		if ( ! empty( $object ) ) {
			$risks = $risk->fetchFromParent($object->id);
				if ($risks !== -1) {
					foreach ($risks as $key => $risk) {
						$evaluation = new DigiriskEvaluation($this->db);
						$lastEvaluation = $evaluation->fetchFromParent($risk->id,1);
						$lastEvaluation = array_shift($lastEvaluation);

						$data['risk']['nomDanger']         = $risk->get_danger_name($risk);
						$data['risk']['identifiantRisque'] = $risk->ref . ' - ' . $lastEvaluation->ref;
						$data['risk']['quotationRisque']   = $lastEvaluation->cotation;
						$data['risk']['commentaireRisque'] = $lastEvaluation->comment;
					}
				}

				$data[$element_type]['reference']   = $object->ref;
				$data[$element_type]['nom']         = $object->label;
				$data[$element_type]['description'] = $object->description;

			return $data;
		}
		else
		{
			return -1;
		}
	}
		/**
	 *		Set last model used by doc generator
	 *
	 *		@param		User	$user		User object that make change
	 *		@param		string	$modelpdf	Modele name
	 *		@return		int					<0 if KO, >0 if OK
	 */
	public function setDocModel($user, $modelpdf)
	{
		if (!$this->table_element)
		{
			dol_syslog(get_class($this)."::setDocModel was called on objet with property table_element not defined", LOG_ERR);
			return -1;
		}
		if ($this->id > 0) {
			$newmodelpdf = dol_trunc($modelpdf, 255);

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " SET model_pdf = '".$this->db->escape($newmodelpdf)."'";
		$sql .= " WHERE rowid = ".$this->id;
		// if ($this->element == 'facture') $sql.= " AND fk_statut < 2";
		// if ($this->element == 'propal')  $sql.= " AND fk_statut = 0";

		dol_syslog(get_class($this)."::setDocModel", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$this->modelpdf = $modelpdf;
			return 1;
		}
		else
		{
			dol_print_error($this->db);
			return 0;
		}
		}

	}

}
