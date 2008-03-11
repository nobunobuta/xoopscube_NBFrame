<?php
class NBFramePageNav
{
    /**#@+
     * @access  private
     */
    var $mTotal;
    var $mPerpage;
    var $mCurrent;
    var $mName;
    var $mBaseUrl;
    /**#@-*/

    /**
     * Constructor
     *
     * @param   int     $total_items    Total number of items
     * @param   int     $items_perpage  Number of items per page
     * @param   int     $current_start  First item on the current page
     * @param   string  $start_name     Name for "start" or "offset"
     * @param   string  $extra_arg      Additional arguments to pass in the URL
     **/
    function NBFramePageNav($total_items, $items_perpage, $current_start, $start_name="start", $baseUrl='')
    {
        $this->mTotal = intval($total_items);
        $this->mPerpage = intval($items_perpage);
        $this->mCurrent = intval($current_start);
        $this->mName = $start_name;
        $this->mBaseUrl = $baseUrl;
    }

    /**
     * Create text navigation
     *
     * @param   integer $offset
     * @return  string
     **/
    function renderNav($offset = 4)
    {
        $ret = '';
        if ( $this->mTotal <= $this->mPerpage ) {
            return $ret;
        }
        $total_pages = ceil($this->mTotal / $this->mPerpage);
        if ( $total_pages > 1 ) {
            $prev = $this->mCurrent - $this->mPerpage;
            if ( $prev >= 0 ) {
                $ret .= '<a href="'.$this->_Url($prev).'"><u>&laquo;</u></a> ';
            }
            $counter = 1;
            $current_page = intval(floor(($this->mCurrent + $this->mPerpage) / $this->mPerpage));
            while ( $counter <= $total_pages ) {
                if ( $counter == $current_page ) {
                    $ret .= '<b>('.$counter.')</b> ';
                } elseif ( ($counter > $current_page-$offset && $counter < $current_page + $offset ) || $counter == 1 || $counter == $total_pages ) {
                    if ( $counter == $total_pages && $current_page < $total_pages - $offset ) {
                        $ret .= '... ';
                    }
                    $ret .= '<a href="'.$this->_Url((($counter - 1) * $this->mPerpage)).'">'.$counter.'</a> ';
                    if ( $counter == 1 && $current_page > 1 + $offset ) {
                        $ret .= '... ';
                    }
                }
                $counter++;
            }
            $next = $this->mCurrent + $this->mPerpage;
            if ( $this->mTotal > $next ) {
                $ret .= '<a href="'.$this->_Url($next).'"><u>&raquo;</u></a> ';
            }
        }
        return $ret;
    }
    function _Url($start) {
        if ($start) {
            return NBFrame::addQueryArgs($this->mBaseUrl, array($this->mName=>$start));
        } else {
            return $this->mBaseUrl;
        }
    }

}

?>