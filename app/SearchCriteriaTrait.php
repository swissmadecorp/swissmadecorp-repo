<?php

namespace App;

trait SearchCriteriaTrait
{
    /**
     * Generate a search term query string based on the input and columns
     *
     * @param string $search The search term
     * @param array $columns The columns to search within
     * @return string The generated search term query string
     */
    public function generateSearchQuery(string $search, array $columns): string
    {
        // Check if $columns is empty
        if (empty($columns)) {
            throw new Exception("Error: No columns specified for search.");
        }
        
        $words = explode(' ', $search);
        $searchTerm = "";

        if ($search) {
            foreach ($words as $word) {
                $searchWords = "(";
                foreach ($columns as $column) {
                    $searchWords .= $column . ' LIKE "%' . $word . '%" OR ';
                }
                $searchWords = substr($searchWords, 0, -4) . ") AND ";
                $searchTerm .= $searchWords;
            }
        }

        // Remove the trailing " AND "
        return substr($searchTerm, 0, -5);
    }
}
