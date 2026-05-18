<?php

namespace Transitive\Utils;

class Pagination
{
	private int $currentPage = 1;
	private int $currentPageItemCount = 0;

	private int $maxPageDisplay = 10;
	private int $span = 2;

	public function __construct(
		private int $itemCount = 0,
		private int $itemPerPage = 12,
		?int $currentPage = 1,
		private array $ignoredParameters = ['request']
	) {
		$this->setItemCount($itemCount);
		$this->itemPerPage = max(1, $this->itemPerPage);

		$this->setCurrentPage($currentPage ?? 1);
		$this->setCurrentPageItemCount($this->itemPerPage);
	}

	public function getItemCount(): int
	{
		return $this->itemCount;
	}
	public function setItemCount(int $count): void
	{
		$this->itemCount = max(0, $count);
		$this->setCurrentPage($this->currentPage);
	}

	public function getItemPerPage(): int
	{
		return $this->itemPerPage;
	}

	public function getOffset(): int
	{
		if(!$this->itemCount)
			return 0;

		return $this->itemPerPage * ($this->currentPage - 1);
	}

	public function getPageCount(): int
	{
		return max(1, (int)ceil($this->itemCount / $this->itemPerPage));
	}

	public function getCurrentPage(): int
	{
		return $this->currentPage;
	}

	public function setCurrentPage(int $currentPage = 1): void
	{
		$this->currentPage = max(1, $currentPage);
		$this->currentPage = min($this->currentPage, $this->getPageCount());
	}

	public function setCurrentPageItemCount(int $itemCount = 0): void
	{
		$this->currentPageItemCount = max(0, $itemCount);
	}

	private function _buildUrl(
		int $pageNumber = 1,
		array $URLParameters = [],
		?string $url = null,
		array $ignoredParameters = []
	): string
	{
		$url ??= $_SERVER['REQUEST_URI'] ?? '';
		$parts = parse_url($url);
		$query = [];

		if(!empty($parts['query']))
			parse_str($parts['query'], $query);

		$query = array_merge($query, $URLParameters);
		foreach($ignoredParameters as $parameter)
			unset($query[$parameter]);

		$query['p'] = max(1, $pageNumber);
		$queryString = http_build_query($query);

		return (
			$parts['path']
			?? ''
		).(
			$queryString
			? '?'.$queryString
			: ''
		).(
			isset($parts['fragment'])
			? '#'.$parts['fragment']
			: ''
		);
	}

	private function _escape(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function _pageListGenerator(): \Generator
	{
		$pageCount = $this->getPageCount();
		if($pageCount <= $this->maxPageDisplay) {
			foreach(range(1, $pageCount) as $i)
				yield $i;

			return;
		}

		$array = array_filter(
			array_unique(range($this->currentPage - $this->span, $this->currentPage + $this->span)),
			fn($v) => $v > 1 && $v < $pageCount
		);

		if($this->currentPage > 2 + $this->span) {
			if($array[array_key_first($array)] > 1 + $this->span)
				array_unshift($array, 1, 0);
			else
				array_unshift($array, 1, 2);
		} else
			array_unshift($array, 1);

		if($this->currentPage < $pageCount - $this->span - 2)
			$array[] = 0;

		$array[] = $pageCount;
		foreach($array as $i)
			yield $i;
	}

	public function getPageSwitcher(array $URLParameters = []): string
	{
		$pageCount = $this->getPageCount();
		if($pageCount <= 1)
			return '';

		$previousPage = max(1, $this->currentPage - 1);
		$nextPage = min($pageCount, $this->currentPage + 1);

		$str = '<nav class="pagination"><ul>';
		$str .= '<li'.(($this->currentPage <= 1) ? ' class="inactive"' : '').'>';
		$str .= '<a href="'.$this->_escape($this->_buildUrl($previousPage, $URLParameters, ignoredParameters: $this->ignoredParameters)).'"><span>Page précédente</span></a>';
		$str .= '</li>';

		foreach($this->_pageListGenerator() as $i) {
			if($i > $this->currentPage && $this->currentPageItemCount < $this->itemPerPage)
				break;

			if($i === 0) {
				$str .= '<li class="spacer"></li>';
				continue;
			}

			$str .= '<li'.(($i === $this->currentPage) ? ' class="active"' : '').'>';
			$str .= '<a href="'.$this->_escape($this->_buildUrl($i, $URLParameters, ignoredParameters: $this->ignoredParameters)).'">'.$i.'</a>';
			$str .= '</li>';
		}

		$str .= '<li'.(($this->currentPage >= $pageCount) ? ' class="inactive"' : '').'>';
		$str .= '<a href="'.$this->_escape($this->_buildUrl($nextPage, $URLParameters, ignoredParameters: $this->ignoredParameters)).'"><span>Page suivante</span></a>';
		$str .= '</li>';
		$str .= '</ul></nav>';

		return $str;
	}

	public function __toString(): string
	{
		return $this->getPageSwitcher();
	}
}
