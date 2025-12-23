@if (isset($paginator) && $paginator->lastPage() > 1)
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
        
        $query='';$requests='';

        if (Request::all()) {
            foreach (Request::all() as  $key => $request) {
                if (strpos($key,'page') ===false )
                    $query .= '&'.$key . '=' . $request . '&';
            }

            $query = substr($query,0, strlen($query)-1);
       
        }
        ?>
        
        <!-- first/previous -->
        @if($paginator->currentPage() > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url(1).$query }}" aria-label="First">
                    <span aria-hidden="true">First</span>
                </a>
            </li>

            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($paginator->currentPage() - 1).$query }}" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
            </li>
        @endif
        
        <!-- links -->
        @for($i = $from; $i <= $to; $i++)
            <?php 
            $isCurrentPage = $paginator->currentPage() == $i;
            ?>
            <li class="page-item {{ $isCurrentPage ? 'active' : '' }}">
                <a class="page-link" href="{{ !$isCurrentPage ? $paginator->url($i).$query : '#' }}">
                    {{ $i }}
                </a>
            </li>
        @endfor
        
        <!-- next/last -->
        @if($paginator->currentPage() < $paginator->lastPage())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($paginator->currentPage() + 1).$query }}" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </li>

            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($paginator->lastpage()).$query }}" aria-label="Last">
                    <span aria-hidden="true">Last</span>
                </a>
            </li>
        @endif
        
    </ul>
</nav>
@endif