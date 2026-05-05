<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comun.municipios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id');

            $table->foreign('state_id')
                ->references('id')
                ->on('comun.estados')
                ->onDelete('cascade');
        });

        DB::table('comun.municipios')->insert([
            // ===================== AMAZONAS (1) - 7 municipios =====================
            ["id" => "1", "name" => "ALTO ORINOCO", "state_id" => "1"],
            ["id" => "2", "name" => "ATABAPO", "state_id" => "1"],
            ["id" => "3", "name" => "ATURES", "state_id" => "1"],
            ["id" => "4", "name" => "AUTANA", "state_id" => "1"],
            ["id" => "5", "name" => "MAROA", "state_id" => "1"],
            ["id" => "6", "name" => "MANAPIARE", "state_id" => "1"],
            ["id" => "7", "name" => "RIO NEGRO", "state_id" => "1"],

            // ===================== ANZOÁTEGUI (2) - 21 municipios =====================
            ["id" => "8", "name" => "ANACO", "state_id" => "2"],
            ["id" => "9", "name" => "ARAGUA", "state_id" => "2"],
            ["id" => "10", "name" => "FERNANDO DE PEÑALVER", "state_id" => "2"],
            ["id" => "11", "name" => "FRANCISCO DEL CARMEN CARVAJAL", "state_id" => "2"],
            ["id" => "12", "name" => "FRANCISCO DE MIRANDA", "state_id" => "2"],
            ["id" => "13", "name" => "GUANTA", "state_id" => "2"],
            ["id" => "14", "name" => "INDEPENDENCIA", "state_id" => "2"],
            ["id" => "15", "name" => "JUAN ANTONIO SOTILLO", "state_id" => "2"],
            ["id" => "16", "name" => "JUAN MANUEL CAJIGAL", "state_id" => "2"],
            ["id" => "17", "name" => "JOSE GREGORIO MONAGAS", "state_id" => "2"],
            ["id" => "18", "name" => "LIBERTAD", "state_id" => "2"],
            ["id" => "19", "name" => "MANUEL EZEQUIEL BRUZUAL", "state_id" => "2"],
            ["id" => "20", "name" => "PEDRO MARIA FREITES", "state_id" => "2"],
            ["id" => "21", "name" => "PIRITU", "state_id" => "2"],
            ["id" => "22", "name" => "SAN JOSE DE GUANIPA", "state_id" => "2"],
            ["id" => "23", "name" => "SAN JUAN DE CAPISTRANO", "state_id" => "2"],
            ["id" => "24", "name" => "SANTA ANA", "state_id" => "2"],
            ["id" => "25", "name" => "SIMON BOLIVAR", "state_id" => "2"],
            ["id" => "26", "name" => "SIMON RODRIGUEZ", "state_id" => "2"],
            ["id" => "27", "name" => "SIR ARTHUR MC GREGOR", "state_id" => "2"],
            ["id" => "28", "name" => "DIEGO BAUTISTA URBANEJA", "state_id" => "2"],

            // ===================== APURE (3) - 7 municipios =====================
            ["id" => "29", "name" => "ACHAGUAS", "state_id" => "3"],
            ["id" => "30", "name" => "BIRUACA", "state_id" => "3"],
            ["id" => "31", "name" => "MUÑOZ", "state_id" => "3"],
            ["id" => "32", "name" => "PAEZ", "state_id" => "3"],
            ["id" => "33", "name" => "PEDRO CAMEJO", "state_id" => "3"],
            ["id" => "34", "name" => "ROMULO GALLEGOS", "state_id" => "3"],
            ["id" => "35", "name" => "SAN FERNANDO", "state_id" => "3"],

            // ===================== ARAGUA (4) - 18 municipios =====================
            ["id" => "36", "name" => "BOLIVAR", "state_id" => "4"],
            ["id" => "37", "name" => "CAMATAGUA", "state_id" => "4"],
            ["id" => "38", "name" => "GIRARDOT", "state_id" => "4"],
            ["id" => "39", "name" => "JOSE ANGEL LAMAS", "state_id" => "4"],
            ["id" => "40", "name" => "JOSE FELIX RIBAS", "state_id" => "4"],
            ["id" => "41", "name" => "JOSE RAFAEL REVENGA", "state_id" => "4"],
            ["id" => "42", "name" => "LIBERTADOR", "state_id" => "4"],
            ["id" => "43", "name" => "MARIO BRICEÑO IRAGORRY", "state_id" => "4"],
            ["id" => "44", "name" => "SAN CASIMIRO", "state_id" => "4"],
            ["id" => "45", "name" => "SAN SEBASTIAN", "state_id" => "4"],
            ["id" => "46", "name" => "SANTIAGO MARIÑO", "state_id" => "4"],
            ["id" => "47", "name" => "SANTOS MICHELENA", "state_id" => "4"],
            ["id" => "48", "name" => "SUCRE", "state_id" => "4"],
            ["id" => "49", "name" => "TOVAR", "state_id" => "4"],
            ["id" => "50", "name" => "URDANETA", "state_id" => "4"],
            ["id" => "51", "name" => "ZAMORA", "state_id" => "4"],
            ["id" => "52", "name" => "FRANCISCO LINARES ALCANTARA", "state_id" => "4"],
            ["id" => "53", "name" => "OCUMARE DE LA COSTA DE ORO", "state_id" => "4"],

            // ===================== BARINAS (5) - 12 municipios =====================
            ["id" => "54", "name" => "ALBERTO ARVELO TORREALBA", "state_id" => "5"],
            ["id" => "55", "name" => "ANTONIO JOSE DE SUCRE", "state_id" => "5"],
            ["id" => "56", "name" => "ARISMENDI", "state_id" => "5"],
            ["id" => "57", "name" => "BARINAS", "state_id" => "5"],
            ["id" => "58", "name" => "BOLIVAR", "state_id" => "5"],
            ["id" => "59", "name" => "CRUZ PAREDES", "state_id" => "5"],
            ["id" => "60", "name" => "EZEQUIEL ZAMORA", "state_id" => "5"],
            ["id" => "61", "name" => "OBISPOS", "state_id" => "5"],
            ["id" => "62", "name" => "PEDRAZA", "state_id" => "5"],
            ["id" => "63", "name" => "ROJAS", "state_id" => "5"],
            ["id" => "64", "name" => "SOSA", "state_id" => "5"],
            ["id" => "65", "name" => "ANDRES ELOY BLANCO", "state_id" => "5"],

            // ===================== BOLÍVAR (6) - 11 municipios =====================
            ["id" => "66", "name" => "CARONI", "state_id" => "6"],
            ["id" => "67", "name" => "CEDEÑO", "state_id" => "6"],
            ["id" => "68", "name" => "EL CALLAO", "state_id" => "6"],
            ["id" => "69", "name" => "GRAN SABANA", "state_id" => "6"],
            ["id" => "70", "name" => "HERES", "state_id" => "6"],
            ["id" => "71", "name" => "PIAR", "state_id" => "6"],
            ["id" => "72", "name" => "RAUL LEONI", "state_id" => "6"],
            ["id" => "73", "name" => "ROSCIO", "state_id" => "6"],
            ["id" => "74", "name" => "SIFONTES", "state_id" => "6"],
            ["id" => "75", "name" => "SUCRE", "state_id" => "6"],
            ["id" => "76", "name" => "PADRE PEDRO CHIEN", "state_id" => "6"],

            // ===================== CARABOBO (7) - 14 municipios =====================
            ["id" => "77", "name" => "BEJUMA", "state_id" => "7"],
            ["id" => "78", "name" => "CARLOS ARVELO", "state_id" => "7"],
            ["id" => "79", "name" => "DIEGO IBARRA", "state_id" => "7"],
            ["id" => "80", "name" => "GUACARA", "state_id" => "7"],
            ["id" => "81", "name" => "JUAN JOSE MORA", "state_id" => "7"],
            ["id" => "82", "name" => "LIBERTADOR", "state_id" => "7"],
            ["id" => "83", "name" => "LOS GUAYOS", "state_id" => "7"],
            ["id" => "84", "name" => "MIRANDA", "state_id" => "7"],
            ["id" => "85", "name" => "MONTALBAN", "state_id" => "7"],
            ["id" => "86", "name" => "NAGUANAGUA", "state_id" => "7"],
            ["id" => "87", "name" => "PUERTO CABELLO", "state_id" => "7"],
            ["id" => "88", "name" => "SAN DIEGO", "state_id" => "7"],
            ["id" => "89", "name" => "SAN JOAQUIN", "state_id" => "7"],
            ["id" => "90", "name" => "VALENCIA", "state_id" => "7"],

            // ===================== COJEDES (8) - 9 municipios =====================
            ["id" => "91", "name" => "ANZOATEGUI", "state_id" => "8"],
            ["id" => "92", "name" => "FALCON", "state_id" => "8"],
            ["id" => "93", "name" => "GIRARDOT", "state_id" => "8"],
            ["id" => "94", "name" => "LIMA BLANCO", "state_id" => "8"],
            ["id" => "95", "name" => "PAO DE SAN JUAN BAUTISTA", "state_id" => "8"],
            ["id" => "96", "name" => "RICAURTE", "state_id" => "8"],
            ["id" => "97", "name" => "ROMULO GALLEGOS", "state_id" => "8"],
            ["id" => "98", "name" => "SAN CARLOS", "state_id" => "8"],
            ["id" => "99", "name" => "TINACO", "state_id" => "8"],

            // ===================== DELTA AMACURO (9) - 4 municipios =====================
            ["id" => "100", "name" => "ANTONIO DIAZ", "state_id" => "9"],
            ["id" => "101", "name" => "CASACOIMA", "state_id" => "9"],
            ["id" => "102", "name" => "PEDERNALES", "state_id" => "9"],
            ["id" => "103", "name" => "TUCUPITA", "state_id" => "9"],

            // ===================== FALCÓN (10) - 25 municipios =====================
            ["id" => "104", "name" => "ACOSTA", "state_id" => "10"],
            ["id" => "105", "name" => "BOLIVAR", "state_id" => "10"],
            ["id" => "106", "name" => "BUCHIVACOA", "state_id" => "10"],
            ["id" => "107", "name" => "CACIQUE MANAURE", "state_id" => "10"],
            ["id" => "108", "name" => "CARIRUBANA", "state_id" => "10"],
            ["id" => "109", "name" => "COLINA", "state_id" => "10"],
            ["id" => "110", "name" => "DABAJURO", "state_id" => "10"],
            ["id" => "111", "name" => "DEMOCRACIA", "state_id" => "10"],
            ["id" => "112", "name" => "FALCON", "state_id" => "10"],
            ["id" => "113", "name" => "FEDERACION", "state_id" => "10"],
            ["id" => "114", "name" => "JACURA", "state_id" => "10"],
            ["id" => "115", "name" => "LOS TAQUES", "state_id" => "10"],
            ["id" => "116", "name" => "MAUROA", "state_id" => "10"],
            ["id" => "117", "name" => "MIRANDA", "state_id" => "10"],
            ["id" => "118", "name" => "MONSEÑOR ITURRIZA", "state_id" => "10"],
            ["id" => "119", "name" => "PALMASOLA", "state_id" => "10"],
            ["id" => "120", "name" => "PETIT", "state_id" => "10"],
            ["id" => "121", "name" => "PIRITU", "state_id" => "10"],
            ["id" => "122", "name" => "SAN FRANCISCO", "state_id" => "10"],
            ["id" => "123", "name" => "SILVA", "state_id" => "10"],
            ["id" => "124", "name" => "SUCRE", "state_id" => "10"],
            ["id" => "125", "name" => "TOCOPERO", "state_id" => "10"],
            ["id" => "126", "name" => "UNION", "state_id" => "10"],
            ["id" => "127", "name" => "URUMACO", "state_id" => "10"],
            ["id" => "128", "name" => "ZAMORA", "state_id" => "10"],

            // ===================== GUÁRICO (11) - 15 municipios =====================
            ["id" => "129", "name" => "CAMAGUAN", "state_id" => "11"],
            ["id" => "130", "name" => "CHAGUARAMAS", "state_id" => "11"],
            ["id" => "131", "name" => "EL SOCORRO", "state_id" => "11"],
            ["id" => "132", "name" => "SAN GERONIMO DE GUAYABAL", "state_id" => "11"],
            ["id" => "133", "name" => "LEONARDO INFANTE", "state_id" => "11"],
            ["id" => "134", "name" => "LAS MERCEDES", "state_id" => "11"],
            ["id" => "135", "name" => "JULIAN MELLADO", "state_id" => "11"],
            ["id" => "136", "name" => "FRANCISCO DE MIRANDA", "state_id" => "11"],
            ["id" => "137", "name" => "JOSE TADEO MONAGAS", "state_id" => "11"],
            ["id" => "138", "name" => "ORTIZ", "state_id" => "11"],
            ["id" => "139", "name" => "JOSE FELIX RIBAS", "state_id" => "11"],
            ["id" => "140", "name" => "JUAN GERMAN ROSCIO", "state_id" => "11"],
            ["id" => "141", "name" => "SAN JOSE DE GUARIBE", "state_id" => "11"],
            ["id" => "142", "name" => "SANTA MARIA DE IPIRE", "state_id" => "11"],
            ["id" => "143", "name" => "PEDRO ZARAZA", "state_id" => "11"],

            // ===================== LARA (12) - 9 municipios =====================
            ["id" => "144", "name" => "ANDRES ELOY BLANCO", "state_id" => "12"],
            ["id" => "145", "name" => "CRESPO", "state_id" => "12"],
            ["id" => "146", "name" => "IRIBARREN", "state_id" => "12"],
            ["id" => "147", "name" => "JIMENEZ", "state_id" => "12"],
            ["id" => "148", "name" => "MORAN", "state_id" => "12"],
            ["id" => "149", "name" => "PALAVECINO", "state_id" => "12"],
            ["id" => "150", "name" => "SIMON PLANAS", "state_id" => "12"],
            ["id" => "151", "name" => "TORRES", "state_id" => "12"],
            ["id" => "152", "name" => "URDANETA", "state_id" => "12"],

            // ===================== MÉRIDA (13) - 23 municipios =====================
            ["id" => "153", "name" => "ALBERTO ADRIANI", "state_id" => "13"],
            ["id" => "154", "name" => "ANDRES BELLO", "state_id" => "13"],
            ["id" => "155", "name" => "ANTONIO PINTO SALINAS", "state_id" => "13"],
            ["id" => "156", "name" => "ARICAGUA", "state_id" => "13"],
            ["id" => "157", "name" => "ARZOBISPO CHACON", "state_id" => "13"],
            ["id" => "158", "name" => "CAMPO ELIAS", "state_id" => "13"],
            ["id" => "159", "name" => "CARACCIOLO PARRA OLMEDO", "state_id" => "13"],
            ["id" => "160", "name" => "CARDENAL QUINTERO", "state_id" => "13"],
            ["id" => "161", "name" => "GUARAQUE", "state_id" => "13"],
            ["id" => "162", "name" => "JULIO CESAR SALAS", "state_id" => "13"],
            ["id" => "163", "name" => "JUSTO BRICEÑO", "state_id" => "13"],
            ["id" => "164", "name" => "LIBERTADOR", "state_id" => "13"],
            ["id" => "165", "name" => "MIRANDA", "state_id" => "13"],
            ["id" => "166", "name" => "OBISPO RAMOS DE LORA", "state_id" => "13"],
            ["id" => "167", "name" => "PADRE NOGUERA", "state_id" => "13"],
            ["id" => "168", "name" => "PUEBLO LLANO", "state_id" => "13"],
            ["id" => "169", "name" => "RANGEL", "state_id" => "13"],
            ["id" => "170", "name" => "RIVAS DAVILA", "state_id" => "13"],
            ["id" => "171", "name" => "SANTOS MARQUINA", "state_id" => "13"],
            ["id" => "172", "name" => "SUCRE", "state_id" => "13"],
            ["id" => "173", "name" => "TOVAR", "state_id" => "13"],
            ["id" => "174", "name" => "TULIO FEBRES CORDERO", "state_id" => "13"],
            ["id" => "175", "name" => "ZEA", "state_id" => "13"],

            // ===================== MIRANDA (14) - 21 municipios =====================
            ["id" => "176", "name" => "ACEVEDO", "state_id" => "14"],
            ["id" => "177", "name" => "ANDRES BELLO", "state_id" => "14"],
            ["id" => "178", "name" => "BARUTA", "state_id" => "14"],
            ["id" => "179", "name" => "BRION", "state_id" => "14"],
            ["id" => "180", "name" => "BUROZ", "state_id" => "14"],
            ["id" => "181", "name" => "CARRIZAL", "state_id" => "14"],
            ["id" => "182", "name" => "CHACAO", "state_id" => "14"],
            ["id" => "183", "name" => "CRISTOBAL ROJAS", "state_id" => "14"],
            ["id" => "184", "name" => "EL HATILLO", "state_id" => "14"],
            ["id" => "185", "name" => "GUAICAIPURO", "state_id" => "14"],
            ["id" => "186", "name" => "INDEPENDENCIA", "state_id" => "14"],
            ["id" => "187", "name" => "LANDER", "state_id" => "14"],
            ["id" => "188", "name" => "LOS SALIAS", "state_id" => "14"],
            ["id" => "189", "name" => "PAEZ", "state_id" => "14"],
            ["id" => "190", "name" => "PAZ CASTILLO", "state_id" => "14"],
            ["id" => "191", "name" => "PEDRO GUAL", "state_id" => "14"],
            ["id" => "192", "name" => "PLAZA", "state_id" => "14"],
            ["id" => "193", "name" => "SIMON BOLIVAR", "state_id" => "14"],
            ["id" => "194", "name" => "SUCRE", "state_id" => "14"],
            ["id" => "195", "name" => "URDANETA", "state_id" => "14"],
            ["id" => "196", "name" => "ZAMORA", "state_id" => "14"],

            // ===================== MONAGAS (15) - 13 municipios =====================
            ["id" => "197", "name" => "ACOSTA", "state_id" => "15"],
            ["id" => "198", "name" => "AGUASAY", "state_id" => "15"],
            ["id" => "199", "name" => "BOLIVAR", "state_id" => "15"],
            ["id" => "200", "name" => "CARIPE", "state_id" => "15"],
            ["id" => "201", "name" => "CEDEÑO", "state_id" => "15"],
            ["id" => "202", "name" => "EZEQUIEL ZAMORA", "state_id" => "15"],
            ["id" => "203", "name" => "LIBERTADOR", "state_id" => "15"],
            ["id" => "204", "name" => "MATURIN", "state_id" => "15"],
            ["id" => "205", "name" => "PIAR", "state_id" => "15"],
            ["id" => "206", "name" => "PUNCERES", "state_id" => "15"],
            ["id" => "207", "name" => "SANTA BARBARA", "state_id" => "15"],
            ["id" => "208", "name" => "SOTILLO", "state_id" => "15"],
            ["id" => "209", "name" => "URACOA", "state_id" => "15"],

            // ===================== NUEVA ESPARTA (16) - 11 municipios =====================
            ["id" => "210", "name" => "ANTOLIN DEL CAMPO", "state_id" => "16"],
            ["id" => "211", "name" => "ARISMENDI", "state_id" => "16"],
            ["id" => "212", "name" => "DIAZ", "state_id" => "16"],
            ["id" => "213", "name" => "GARCIA", "state_id" => "16"],
            ["id" => "214", "name" => "GOMEZ", "state_id" => "16"],
            ["id" => "215", "name" => "MANEIRO", "state_id" => "16"],
            ["id" => "216", "name" => "MARCANO", "state_id" => "16"],
            ["id" => "217", "name" => "MARIÑO", "state_id" => "16"],
            ["id" => "218", "name" => "PENINSULA DE MACANAO", "state_id" => "16"],
            ["id" => "219", "name" => "TUBORES", "state_id" => "16"],
            ["id" => "220", "name" => "VILLALBA", "state_id" => "16"],

            // ===================== PORTUGUESA (17) - 14 municipios =====================
            ["id" => "221", "name" => "AGUA BLANCA", "state_id" => "17"],
            ["id" => "222", "name" => "ARAURE", "state_id" => "17"],
            ["id" => "223", "name" => "ESTELLER", "state_id" => "17"],
            ["id" => "224", "name" => "GUANARE", "state_id" => "17"],
            ["id" => "225", "name" => "GUANARITO", "state_id" => "17"],
            ["id" => "226", "name" => "MONSEÑOR JOSE VICENTE DE UNDA", "state_id" => "17"],
            ["id" => "227", "name" => "OSPINO", "state_id" => "17"],
            ["id" => "228", "name" => "PAEZ", "state_id" => "17"],
            ["id" => "229", "name" => "PAPELON", "state_id" => "17"],
            ["id" => "230", "name" => "SAN GENARO DE BOCONOITO", "state_id" => "17"],
            ["id" => "231", "name" => "SAN RAFAEL DE ONOTO", "state_id" => "17"],
            ["id" => "232", "name" => "SANTA ROSALIA", "state_id" => "17"],
            ["id" => "233", "name" => "SUCRE", "state_id" => "17"],
            ["id" => "234", "name" => "TUREN", "state_id" => "17"],

            // ===================== SUCRE (18) - 15 municipios =====================
            ["id" => "235", "name" => "ANDRES ELOY BLANCO", "state_id" => "18"],
            ["id" => "236", "name" => "ANDRES MATA", "state_id" => "18"],
            ["id" => "237", "name" => "ARISMENDI", "state_id" => "18"],
            ["id" => "238", "name" => "BENITEZ", "state_id" => "18"],
            ["id" => "239", "name" => "BERMUDEZ", "state_id" => "18"],
            ["id" => "240", "name" => "BOLIVAR", "state_id" => "18"],
            ["id" => "241", "name" => "CAJIGAL", "state_id" => "18"],
            ["id" => "242", "name" => "CRUZ SALMERON ACOSTA", "state_id" => "18"],
            ["id" => "243", "name" => "LIBERTADOR", "state_id" => "18"],
            ["id" => "244", "name" => "MARIÑO", "state_id" => "18"],
            ["id" => "245", "name" => "MEJIA", "state_id" => "18"],
            ["id" => "246", "name" => "MONTES", "state_id" => "18"],
            ["id" => "247", "name" => "RIBERO", "state_id" => "18"],
            ["id" => "248", "name" => "SUCRE", "state_id" => "18"],
            ["id" => "249", "name" => "VALDEZ", "state_id" => "18"],

            // ===================== TÁCHIRA (19) - 29 municipios =====================
            ["id" => "250", "name" => "ANDRES BELLO", "state_id" => "19"],
            ["id" => "251", "name" => "ANTONIO ROMULO COSTA", "state_id" => "19"],
            ["id" => "252", "name" => "AYACUCHO", "state_id" => "19"],
            ["id" => "253", "name" => "BOLIVAR", "state_id" => "19"],
            ["id" => "254", "name" => "CARDENAS", "state_id" => "19"],
            ["id" => "255", "name" => "CORDOBA", "state_id" => "19"],
            ["id" => "256", "name" => "FERNANDEZ FEO", "state_id" => "19"],
            ["id" => "257", "name" => "FRANCISCO DE MIRANDA", "state_id" => "19"],
            ["id" => "258", "name" => "GARCIA DE HEVIA", "state_id" => "19"],
            ["id" => "259", "name" => "GUASIMOS", "state_id" => "19"],
            ["id" => "260", "name" => "INDEPENDENCIA", "state_id" => "19"],
            ["id" => "261", "name" => "JAUREGUI", "state_id" => "19"],
            ["id" => "262", "name" => "JOSE MARIA VARGAS", "state_id" => "19"],
            ["id" => "263", "name" => "JUNIN", "state_id" => "19"],
            ["id" => "264", "name" => "LIBERTAD", "state_id" => "19"],
            ["id" => "265", "name" => "LIBERTADOR", "state_id" => "19"],
            ["id" => "266", "name" => "LOBATERA", "state_id" => "19"],
            ["id" => "267", "name" => "MICHELENA", "state_id" => "19"],
            ["id" => "268", "name" => "PANAMERICANO", "state_id" => "19"],
            ["id" => "269", "name" => "PEDRO MARIA UREÑA", "state_id" => "19"],
            ["id" => "270", "name" => "RAFAEL URDANETA", "state_id" => "19"],
            ["id" => "271", "name" => "SAMUEL DARIO MALDONADO", "state_id" => "19"],
            ["id" => "272", "name" => "SAN CRISTOBAL", "state_id" => "19"],
            ["id" => "273", "name" => "SEBORUCO", "state_id" => "19"],
            ["id" => "274", "name" => "SIMON RODRIGUEZ", "state_id" => "19"],
            ["id" => "275", "name" => "SUCRE", "state_id" => "19"],
            ["id" => "276", "name" => "TORBES", "state_id" => "19"],
            ["id" => "277", "name" => "URIBANTE", "state_id" => "19"],
            ["id" => "278", "name" => "SAN JUDAS TADEO", "state_id" => "19"],

            // ===================== TRUJILLO (20) - 20 municipios =====================
            ["id" => "279", "name" => "ANDRES BELLO", "state_id" => "20"],
            ["id" => "280", "name" => "BOCONO", "state_id" => "20"],
            ["id" => "281", "name" => "BOLIVAR", "state_id" => "20"],
            ["id" => "282", "name" => "CANDELARIA", "state_id" => "20"],
            ["id" => "283", "name" => "CARACHE", "state_id" => "20"],
            ["id" => "284", "name" => "ESCUQUE", "state_id" => "20"],
            ["id" => "285", "name" => "JOSE FELIPE MARQUEZ CAÑIZALES", "state_id" => "20"],
            ["id" => "286", "name" => "JUAN VICENTE CAMPO ELIAS", "state_id" => "20"],
            ["id" => "287", "name" => "LA CEIBA", "state_id" => "20"],
            ["id" => "288", "name" => "MIRANDA", "state_id" => "20"],
            ["id" => "289", "name" => "MONTE CARMELO", "state_id" => "20"],
            ["id" => "290", "name" => "MOTATAN", "state_id" => "20"],
            ["id" => "291", "name" => "PAMPAN", "state_id" => "20"],
            ["id" => "292", "name" => "PAMPANITO", "state_id" => "20"],
            ["id" => "293", "name" => "RAFAEL RANGEL", "state_id" => "20"],
            ["id" => "294", "name" => "SAN RAFAEL DE CARVAJAL", "state_id" => "20"],
            ["id" => "295", "name" => "SUCRE", "state_id" => "20"],
            ["id" => "296", "name" => "TRUJILLO", "state_id" => "20"],
            ["id" => "297", "name" => "URDANETA", "state_id" => "20"],
            ["id" => "298", "name" => "VALERA", "state_id" => "20"],

            // ===================== LA GUAIRA (21) - 1 municipio =====================
            ["id" => "299", "name" => "VARGAS", "state_id" => "21"],

            // ===================== YARACUY (22) - 14 municipios =====================
            ["id" => "300", "name" => "ARISTIDES BASTIDAS", "state_id" => "22"],
            ["id" => "301", "name" => "BOLIVAR", "state_id" => "22"],
            ["id" => "302", "name" => "BRUZUAL", "state_id" => "22"],
            ["id" => "303", "name" => "COCOROTE", "state_id" => "22"],
            ["id" => "304", "name" => "INDEPENDENCIA", "state_id" => "22"],
            ["id" => "305", "name" => "JOSE ANTONIO PAEZ", "state_id" => "22"],
            ["id" => "306", "name" => "LA TRINIDAD", "state_id" => "22"],
            ["id" => "307", "name" => "MANUEL MONGE", "state_id" => "22"],
            ["id" => "308", "name" => "NIRGUA", "state_id" => "22"],
            ["id" => "309", "name" => "PEÑA", "state_id" => "22"],
            ["id" => "310", "name" => "SAN FELIPE", "state_id" => "22"],
            ["id" => "311", "name" => "SUCRE", "state_id" => "22"],
            ["id" => "312", "name" => "URACHICHE", "state_id" => "22"],
            ["id" => "313", "name" => "VEROES", "state_id" => "22"],

            // ===================== ZULIA (23) - 21 municipios =====================
            ["id" => "314", "name" => "ALMIRANTE PADILLA", "state_id" => "23"],
            ["id" => "315", "name" => "BARALT", "state_id" => "23"],
            ["id" => "316", "name" => "CABIMAS", "state_id" => "23"],
            ["id" => "317", "name" => "CATATUMBO", "state_id" => "23"],
            ["id" => "318", "name" => "COLON", "state_id" => "23"],
            ["id" => "319", "name" => "FRANCISCO JAVIER PULGAR", "state_id" => "23"],
            ["id" => "320", "name" => "JESUS ENRIQUE LOSSADA", "state_id" => "23"],
            ["id" => "321", "name" => "JESUS MARIA SEMPRUN", "state_id" => "23"],
            ["id" => "322", "name" => "LA CAÑADA DE URDANETA", "state_id" => "23"],
            ["id" => "323", "name" => "LAGUNILLAS", "state_id" => "23"],
            ["id" => "324", "name" => "MACHIQUES DE PERIJA", "state_id" => "23"],
            ["id" => "325", "name" => "MARA", "state_id" => "23"],
            ["id" => "326", "name" => "MARACAIBO", "state_id" => "23"],
            ["id" => "327", "name" => "MIRANDA", "state_id" => "23"],
            ["id" => "328", "name" => "PAEZ", "state_id" => "23"],
            ["id" => "329", "name" => "ROSARIO DE PERIJA", "state_id" => "23"],
            ["id" => "330", "name" => "SAN FRANCISCO", "state_id" => "23"],
            ["id" => "331", "name" => "SANTA RITA", "state_id" => "23"],
            ["id" => "332", "name" => "SIMON BOLIVAR", "state_id" => "23"],
            ["id" => "333", "name" => "SUCRE", "state_id" => "23"],
            ["id" => "334", "name" => "VALMORE RODRIGUEZ", "state_id" => "23"],

            // ===================== DISTRITO CAPITAL (24) - 1 municipio =====================
            ["id" => "335", "name" => "LIBERTADOR", "state_id" => "24"],
        ]);




        if (config('database.default') === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('comun.municipios', 'id'), coalesce(max(id),0) + 1, false) FROM comun.municipios;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comun.municipios');
    }
};
