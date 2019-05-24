<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use DB;

class RecuperacionController extends Controller
{
    public function getDatabases()
    {
    	$db = DB::table('schemata')
    	->where('schema_name', '!=', 'information_schema')
    	->where('schema_name', '!=', 'mysql')
    	->where('schema_name', '!=', 'performance_schema')
    	->where('schema_name', '!=', 'sys')
    	->select('schema_name')
    	->get();
    	return response()->json($db, 200);
    }


    public function getTables($table_schema)
    {
        $db = DB::table('tables')
        ->where('table_schema', $table_schema)
        ->select('table_name')
        ->get();
        return response()->json($db, 200);
    }

    public function getColumns($table_schema, $table_name)
    {
        $db = DB::table('columns')
        ->where('table_schema', $table_schema)
        ->where('table_name', $table_name)
        ->select('column_name')
        ->get();
        return response()->json($db, 200);
    }

    public function ejecutarSQL(Request $request)
    {
        $configDb = [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', '3306'),
            'database'    => "$request->database",
            'username'    => env('DB_USERNAME', 'root'),
            'password'    => env('DB_PASSWORD', '1234'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset'     => 'utf8',
            'collation'   => 'utf8_unicode_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
        ];

        Config::set('database.connections.DB_Serverr', $configDb);

        $conexionSQL = DB::connection('DB_Serverr');

        $query = "SELECT ";

        $columnas = $request->columns;
        $count = count($columnas)-1;

        foreach ($columnas as $key => $value) {
            if ($key == $count) {
                $query .= "$value ";
            } else {
                $query .= "$value, ";
            }
        }

        $query .= "FROM $request->table ";

        if ($request->table2 != null && $request->tipo_join != null) {
            $query .= "$request->tipo_join $request->table2 ON $request->table"."."."$request->columns_table1 = $request->table2"."."."$request->columns2 ";
        }

        if ($request->table3 != null && $request->columns3 != null) {
            $query .= "WHERE $request->table3"."."."$request->columns3 $request->operador $request->where";
        }

        $query .= ";";
        $resultado = $conexionSQL->select($query);

        return response()->json($resultado, 200);
    }
}