<?php


namespace SoulMinato\Paramquery;


class ColumnHelper
{
    public static function isValidColumn($dataIndx)
    {
        return true;
        // Deshabilitado previamente
        if (preg_match('/^[a-z,A-Z_]*$/', $dataIndx))
            return true;

        return false;
    }

    public static function getColumnAndAlias($columns)
    {
        $col = [
            'columns' => [],
            'alias' => []
        ];

        foreach ($columns as $column) {
            if (!$columns instanceof \Illuminate\Database\Query\Expression) {
                $columnAlias = explode('as', $column);
                $col['columns'][] = trim($columnAlias[0]);

                if (sizeof($columnAlias) == 1) {
                    $col['alias'][] = trim($columnAlias[0]);
                } else {
                    $col['alias'][] = trim($columnAlias[1]);
                }
            }
        }
        return $col;


    }
}
