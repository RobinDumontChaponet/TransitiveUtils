<?php

namespace Transitive\Utils;

class Pagination {

	private $itemCount;
	private $itemPerPage;
	private $currentPage;

	function __construct(int $itemCount, int $itemPerPage = 12, $currentPage = 1)
	{
		$this->itemCount = $itemCount;
		$this->itemPerPage = $itemPerPage;
		$this->setCurrentPage($currentPage);
	}

	public function getItemCount(): int
	{
		return $this->itemCount;
	}
	public function getItemPerPage(): int
	{
		return $this->itemPerPage;
	}
	public function getOffset(): int
	{
		return $this->itemPerPage*($this->currentPage-1);
	}

	public function getPageCount()
	{
		return ceil($this->itemCount/$this->itemPerPage);
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}
	public function setCurrentPage($currentPage = 1)
	{
		$this->currentPage = intval($currentPage);

		if($this->currentPage <= 0 || $this->currentPage > $this->getPageCount())
			$this->currentPage = 1;
	}

	private function _buildUrl(int $page = 1): string
	{
		$params = array_merge($_GET, array('p' => $page));
		if(isset($params['request']))
			unset($params['request']);
		$queryString = http_build_query($params);

		return SELF. '/'. @$_GET['request']. '?'. $queryString;
	}

	public function __toString()
	{
		$str = '';
		$pageCount = $this->getPageCount();

		if($pageCount > 1) {
			$str.= '<nav class="pagination"><ul>';

			$str.= '<li'. (($this->currentPage <= 1)?' class="inactive"':'') .'><a href="'.$this->_buildUrl($this->currentPage-1).'">&lsaquo;</a></li>';

			for($i=1; $i<=$pageCount; $i++) {


				$str.= '<li';
				if($i==$this->currentPage)
					$str.= ' class="active"'. $i;

					$str.= '><a href="'. $this->_buildUrl($i) .'">'.$i.'</a>';
				$str.= '</li>';
			}

			$str.= '<li'. (($this->currentPage >= $pageCount)?' class="inactive"':'') .'><a href="'.$this->_buildUrl($this->currentPage+1).'">&rsaquo;</a></li>';

			$str.= '</ul></nav>';
		}

		return $str;
	}
}