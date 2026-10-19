<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sincroniza las secuencias de autoincremento con el MAX real de la
     * columna primaria de cada tabla.
     *
     * En PostgreSQL la secuencia solo avanza cuando se usa nextval, es decir
     * al insertar sin indicar la clave primaria. Un seeder que inserta la
     * clave de forma explicita la deja intacta: MunicipiosSeeder mete los 335
     * municipios con id fijo y por eso comun.municipios_id_seq se quedó en 12
     * con la tabla llena hasta 335. El siguiente insertGetId() pedia el 13 y
     * reventaba con "duplicate key value violates unique constraint".
     *
     * Solo se mueve hacia adelante. Bajar una secuencia provocaria choques
     * con filas existentes en lugar de evitarlos.
     */
    public function up(): void
    {
        foreach ($this->secuenciasDeColumnas() as $secuencia) {
            $maximo = DB::table($secuencia->tabla)->max($secuencia->columna);

            if ($maximo === null) {
                // Tabla vacia: la secuencia ya esta donde debe.
                continue;
            }

            $actual = (int) DB::selectOne('SELECT last_value FROM ' . $secuencia->secuencia)->last_value;

            if ((int) $maximo > $actual) {
                // El nombre va como literal: sin comillas, un nombre con
                // esquema ("comun.municipios_id_seq") se interpreta como alias.
                DB::statement(sprintf(
                    "SELECT setval('%s', %s, true)",
                    $secuencia->secuencia,
                    (int) $maximo
                ));
            }
        }
    }

    public function down(): void
    {
        // No hay vuelta atras: el valor correcto de una secuencia es el MAX de
        // su tabla, y esta migracion solo lo lleva ahi.
    }

    /**
     * Devuelve cada secuencia de autoincremento con la tabla y la columna a
     * las que pertenece.
     */
    private function secuenciasDeColumnas(): array
    {
        return DB::select(<<<'SQL'
            SELECT
                sn.nspname || '.' || sc.relname AS secuencia,
                tn.nspname || '.' || tc.relname AS tabla,
                a.attname AS columna
            FROM pg_class sc
            JOIN pg_namespace sn ON sn.oid = sc.relnamespace
            JOIN pg_depend d ON d.objid = sc.oid AND d.deptype = 'a'
            JOIN pg_class tc ON tc.oid = d.refobjid
            JOIN pg_namespace tn ON tn.oid = tc.relnamespace
            JOIN pg_attribute a ON a.attrelid = d.refobjid AND a.attnum = d.refobjsubid
            WHERE sc.relkind = 'S'
              AND tn.nspname NOT IN ('pg_catalog', 'information_schema')
            ORDER BY 1
        SQL);
    }
};
