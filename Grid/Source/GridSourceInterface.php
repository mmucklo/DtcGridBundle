<?php
namespace Dtc\GridBundle\Grid\Source;

interface GridSourceInterface {
	public function getCount();
	public function getRecords();
	public function getLimit();
	public function getOffset();
	public function getLastModified();
}