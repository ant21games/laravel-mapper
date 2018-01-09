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
use Mapper\Workers\MapperModel;

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
     * @var string Custom relationshipt name forced by the model for some reason (e.g. multiple FKs)
     */
    protected $customRelName;

    /**
     * @var Fk
     */
    protected $relatedFK;

    public function __construct(
        Column $column,
        MetaTable $referredTable,
        Fk $relatedFK
    ) {
        parent::__construct($column);
        $this->referredTable = $referredTable;
        $this->relatedFK = $relatedFK;
    }

    /**
     * @param string $fullClassName
     * @return VirtualField
     */
    public function setReferredClass(string $fullClassName, ?string $customRelName = null): self
    {
        $this->referredClass = $fullClassName;
        $this->customRelName = $customRelName;
        return $this;
    }

    public function getSetMethodData()
    {
        $setData = parent::getSetMethodData();
        if ($this->customRelName) {
            $setData['target'] = MetaTable::TARGET_AT_RELATIONSHIP_PREFIX . $this->customRelName;
        } else {
            $setData['target'] = MetaTable::TARGET_AT_RELATIONSHIP_PREFIX . $this->referredTable->getTableName();
        }

        return $setData;
    }

    public function getGetMethodData()
    {
        $getData = parent::getGetMethodData();
        if ($this->customRelName) {
            $getData['target'] = MetaTable::TARGET_AT_RELATIONSHIP_PREFIX . $this->customRelName;
        } else {
            $getData['target'] = MetaTable::TARGET_AT_RELATIONSHIP_PREFIX . $this->referredTable->getTableName();
        }
        return $getData;
    }

    public function getReferredClass(): ?string
    {
        return $this->referredClass;
    }

    public function hasReferClass(): bool
    {
        return isset($this->referredClass);
    }

    public abstract static function getType(): string;

    public abstract function getFkCol(): string;

    public function getRelationshipName(): string
    {
        if($this->customRelName) {
            return $this->customRelName;
        } else {
            return $this->getFieldName();
        }
    }


	public function getRelationshipDefinition():?array
	{
	    echo "\n".get_called_class()." -> ".static::getType();
		return [
		  $this->customRelName ?? $this->referredTable->getTableName() => [
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

//	protected static function formatNameToMethod($name)
//	{
//		if($this->hasReferClass()) {
//			if($this->customRelName) {
//				return parent::formatNameToMethod($this->customRelName);
//			} else {
//				/** @var MapperModel $className */
//				$className = $this->getReferredClass();
//				try{
//                    $fieldName = $className::defineRelMethodName($fieldName);
//                }catch (\Exception $e) {
//				    dd($this);
//                }
//
//			}
//		} else
//			return null;
//		return parent::formatNameToMethod($fieldName); // TODO: Change the autogenerated stub
//	}

}