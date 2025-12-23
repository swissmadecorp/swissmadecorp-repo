<?php
 
namespace App\Models;
 
trait FullTextSearch
{
    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);
 
        //\Log::debug("+".str_replace([" ","-"]," +",$term));
        $words = explode(' ', $term);
        if (count($words) == 1)
            return $term."*";
        else return " +".str_replace([" ","-"]," +",$term);
        
        $words = explode(' ', $term);
 
        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            
            if(strlen($word) >= 3) {
                if (strpos($word,'.') !==false )
                    $words[$key] = '"' . $word . '"';
                elseif (strpos($word,'-') !== false) {
                    // \Log::debug(\Request::route()->getName());
                    $words[$key] = '+"' . $word . '"*';
                } elseif (strpos($word,' ') !== false) {
                    //\Log::debug(\Request::route()->getName());
                    $words[$key] = "+ $word";
                } else
                    $words[$key] = '+' . $word . '*';
            }
        }
        
        $searchTerm = implode( ' ', $words);
        return $searchTerm;
    }
 
    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchAll($query, $term)
    {
        return($query);
        $columns = implode(',',$this->searchable);
        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)" , $this->fullTextWildcards($term));
 
        return $query;
    }
}