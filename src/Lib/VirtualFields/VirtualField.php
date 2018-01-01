<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2018-01-01
 * Time: 3:26 PM
 */

namespace Mapper\Lib\VirtualFiedls;

use Mapper\Lib\MetaField;
use Mapper\Lib\MetaTable;
use Doctrine\DBAL\Schema\ForeignKeyConstraint as Fk;
use Doctrine\DBAL\Schema\Column;

abstract class VirtualField extends  MetaField
{
	/**
	 * @var MetaTable
	 */
	protected $referredTable;

	/**
	 * @var string Class name if the there is a model defined to the referredTable
	 */
	protected $referredClass;

	/**
	 * @var Fk
	 */
	protected $relatedFK;

	public function __construct(Column $column, MetaTable $referredTable, Fk $relatedFK
	) {
		parent::__construct($column);
		$this->referredTable = $referredTable;
		$this->relatedFK = $relatedFK;
	}

	/**
	 * @param string $fullClassName
	 * @return VirtualField
	 */
	public function setReferredClass(string $fullClassName) :self
	{
		$this->referredClass = $fullClassName;
		return $this;
	}

	public function getReferredClass():?string
	{
		return $this->referredClass;
	}

	public function hasReferClass():bool
	{
		return isset($this->referredClass);
	}

	public abstract static function getType():string;

	public abstract function getFkCol():string;

	public function getRelationshipDefinition():?array
	{
		return [
		  $this->referredTable->getTableName() => [
			'rel' => static::getType(),
			'local_col' => $this->getFieldName(),
			'foreign_col' => $this->getFkCol()
		  ]
		];
	}


	/**
	 * @return string
	 */
	protected function getReferredClassName()
	{
		return substr($this->referredClass, strrpos($this->referredClass, '\\') + 1);
	}

	public function getClassDependencies() :?string
	{
		if($this->hasReferClass()) {
			return $this->getReferredClass();
		} else
			return null;
	}

}