<?php

namespace SoulMinato\Paramquery;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Paramquery
{

    private $_query;

    private $_columnsRaw = [];

    private $_columns = [];

    private $_columnsAlias = [];

    private $_filterTable;

    private $sortersColumn = [];

    private $_paginationLimit = [];

    public function __construct($_query)
    {
        $this->_query = $_query;
        $this->_filterTable = new \stdClass();
        $this->_setColumns($this->_query);
//        dd($this->_columnsRaw);
        $this->_configureColumnsAndAlias(ColumnHelper::getColumnAndAlias($this->_columnsRaw));
    }

    private function _setColumns($query)
    {
        if(isset($query->columns))
        {
            $this->_columnsRaw = $query->columns;
        }
        else{
            $this->_columnsRaw= $query->getQuery()->columns;
        }
    }

    public function _configureColumnsAndAlias($columnsAndAlias)
    {
        $this->_columns = $columnsAndAlias['columns'];
        $this->_columnsAlias = $columnsAndAlias['alias'];
    }

    private function _sort()
    {
        if (Request::has('pq_sort')) {
            $pq_sort = Request::input('pq_sort');
            $this->sortersColumn = SortHelper::deSerializeSort($pq_sort);

            foreach ($this->sortersColumn as $sort) {
                $this->_query->orderBy($sort['column'], $sort['dir']);
            }

        }
    }

    private function _getQueryStatement()
    {
        return $this->_query;
    }

    private function _filter()
    {
        if (Request::has('pq_filter')) {
            $this->_filterTable = FilterHelper::deSerializeFilter(Request::input('pq_filter'), $this->_columns, $this->_columnsAlias);
        }
    }

    /**
     * Function to allow paginate the query for table PQGrid
     */
    private function _pagination()
    {

        if (Request::has('pq_curpage') && Request::has('pq_rpp')) {
            $columnsTemp = $this->_columnsRaw;
            $pq_curPage = Request::input('pq_curpage');
            $pq_rPP = Request::input('pq_rpp');
            $queryTemp = new \stdClass();
            $queryTemp = $this->_getQueryStatement();
            $queryTemp->columns = null;

            // Verifica si existe algun filtro para aplicarlo al COUNT y determinar cuantos registros hay en la consulta
            if (property_exists($this->_filterTable, 'query')) {
                $queryTemp->whereRaw($this->_filterTable->query, $this->_filterTable->param);
            }

            $totalRecordQuery = $queryTemp->count();
            $this->_query->columns = $columnsTemp;

            $skip = ($pq_rPP * ($pq_curPage - 1));

            if ($skip >= $totalRecordQuery) {
                $pq_curPage = ceil($totalRecordQuery / $pq_rPP);
                $skip = ($pq_rPP * ($pq_curPage - 1));
            }

            // Make limit to query
            $this->_query->offset($skip)
                ->limit($pq_rPP);

            $this->_paginationLimit = [
                'totalRecords' => $totalRecordQuery,
                'curPage' => $pq_curPage,
            ];
        }
    }

    public function make()
    {
        $this->_filter();
        $this->_pagination();
        $this->_sort();

        return response()->json(array_merge([
                'data' => $this->_query->get(),
            ], $this->_paginationLimit)
        );
    }

    public function makeSimple()
    {
        return response()->json([
            'data' => $this->_query->get()
        ]);
    }

}
