<?php
namespace Dtc\GridBundle\Grid\Source;

interface GridSourceInterface {
	public function setId($id);
	public function getId();

	public function getCount();
	public function getRecords();
	public function getLimit();
	public function getOffset();
	public function getLastModified();
}