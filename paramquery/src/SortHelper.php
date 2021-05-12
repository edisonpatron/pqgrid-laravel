<?php


namespace SoulMinato\Paramquery;


class SortHelper
{

    public static function deSerializeSort($pq_sort)
    {
        $sorters = json_decode($pq_sort);
        $orderByListColumns = [];

        foreach ($sorters as $sorter)
        {
            $dataIndx = $sorter->dataIndx;
            $dir = $sorter->dir;

            if($dir == 'up')
                $dir = "asc";
            else
                $dir = "desc";

            if(ColumnHelper::isValidColumn($dataIndx))
            {
                $orderByListColumns[] = ['column' => $dataIndx, 'dir' => $dir];
            }
            else
                throw new \Exception("Columna invalida". $dataIndx);
        }

        return $orderByListColumns;

    }

}
