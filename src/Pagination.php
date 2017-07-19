<?php

namespace Transitive\Utils;

class Pagination
{
    private $itemCount;
    private $itemPerPage;
    private $currentPage;
    private $currentPageItemCount;

    private $maxPageDisplay = 10;
    private $span = 2;

    public function __construct(int $itemCount, int $itemPerPage = 12, $currentPage = 1)
    {
        $this->itemCount = $itemCount;
        $this->itemPerPage = $itemPerPage;
        $this->setCurrentPage($currentPage);
        $this->setCurrentPageItemCount($itemPerPage);
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
        return $this->itemPerPage * ($this->currentPage - 1);
    }

    public function getPageCount(): int
    {
        if($this->currentPageItemCount < $this->itemPerPage)
            return $this->currentPage;

        return ceil($this->itemCount / $this->itemPerPage);
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

    public function setCurrentPageItemCount(int $itemCount = null)
    {
        $this->currentPageItemCount = intval($itemCount);
    }

    private function _buildUrl(int $page = 1): string
    {
        $params = array_merge($_GET, array('p' => $page));
        if(isset($params['request']))
            unset($params['request']);
        $queryString = http_build_query($params);

        return SELF.'/'.@$_GET['request'].'?'.$queryString;
    }

    private function _pageListGenerator()
    {
        $array = [];
        $pageCount = $this->getPageCount();
        if($pageCount > $this->maxPageDisplay) {
            $array = array_filter(array_unique(array_merge(range($this->currentPage - $this->span, $this->currentPage + $this->span))), function ($v) use ($pageCount) {
                return $v > 1 && $v < $pageCount;
            });

            if($this->currentPage > 1)
                array_unshift($array, 1, 0);
            else
                array_unshift($array, 1);

            if($this->currentPage < $pageCount)
                $array[] = 0;

            $array[] = $this->getPageCount();
        } else
            $array = range(1, $pageCount);

        foreach($array as $i)
            yield $i;
    }

    public function __toString()
    {
        $str = '';
        $pageCount = $this->getPageCount();

        if($pageCount > 1) {
            $str .= '<nav class="pagination"><ul>';

            $str .= '<li'.(($this->currentPage <= 1) ? ' class="inactive"' : '').'><a href="'.$this->_buildUrl($this->currentPage - 1).'">&lsaquo;</a></li>';

            foreach($this->_pageListGenerator() as $i) {
                if($i > $this->currentPage && $this->currentPageItemCount < $this->itemPerPage)
                    break;

                if($i == 0) {
                    $str .= '<li class="spacer">…</li>';
                    continue;
                }

                $str .= '<li';
                if($i == $this->currentPage)
                    $str .= ' class="active"'.$i;

                    $str .= '><a href="'.$this->_buildUrl($i).'">'.$i.'</a>';
                $str .= '</li>';
            }

            $str .= '<li'.(($this->currentPage >= $pageCount) ? ' class="inactive"' : '').'><a href="'.$this->_buildUrl($this->currentPage + 1).'">&rsaquo;</a></li>';

            $str .= '</ul></nav>';
        }

        return $str;
    }
}
