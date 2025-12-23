@if ($paginator->hasPages())
<nav aria-label="Page navigation">
    <ul class="pagination">
        
        <?php
        $interval = isset($interval) ? abs(intval($interval)) : 3 ;
        $from = $paginator->currentPage() - $interval;
        if($from < 1){
            $from = 1;
        }
        
        if (!empty($paginator->lastPage())) {
            $to = $paginator->currentPage() + $interval;
            if($to > $paginator->lastPage()){
                $to = $paginator->lastPage();
            }
        }
        
        $query='';

        if (Request::all()) {
            foreach (Request::all() as  $key => $request) {
                if (strpos($key,'page') ===false) 
                    if (!is_array($request))
                        $query .= '&'.$key . '=' . $request . '&';
            }

            $query = substr($query,0, strlen($query)-1);
            $query = str_replace('==','=',$query);
            if (strpos($query,'&&_=') !== false)
                $query = substr($query,0,strpos($query,'&&_='));

        }
        ?>
        
        
        @if ($paginator->onFirstPage())
        <li class="page-link disabled">
            <span aria-hidden="true">&lsaquo;&nbsp;Previous</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link previouspage" href="{{ $paginator->url($paginator->currentPage() - 1).$query }}" aria-label="Previous">
                <span aria-hidden="true">&lsaquo;&nbsp;Previous</span>
            </a>
        </li>
        @endif
    
        <!-- links -->
        <div class="numbered_pages">
        @for($i = $from; $i <= $to; $i++)
            <?php 
            $isCurrentPage = $paginator->currentPage() == $i;
            ?>
            <li class="page-item {{ $isCurrentPage ? 'active' : '' }}">
                <?php 
                    $url = $paginator->url($i);
                    $filteredURL3 = preg_replace('~(\?|&)'.'query'.'=[^&]*~', '$1', $url);
                ?>
                
                <a class="page-link" href="{{ !$isCurrentPage ? $paginator->url($i).$query : '#' }}">
                    {{ $i }}
                </a>
            </li>
        @endfor
        </div>
        
        <!-- next/last -->
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link nextpage" href="{{ $paginator->url($paginator->currentPage() + 1).$query }}" aria-label="Next">
                    <span aria-hidden="true">Next&nbsp;&rsaquo;</span>
                </a>
            </li>
        @else
            <li class="page-link disabled">
                <span aria-hidden="true">Next&nbsp;&rsaquo;</span>
            </li>
        @endif
        
    </ul>
</nav>
@endif