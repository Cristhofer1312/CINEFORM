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
        Schema::create('comun.parroquias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('municipality_id');

            $table->foreign('municipality_id')
                ->references('id')
                ->on('comun.municipios')
                ->onDelete('cascade');
        });

        DB::table('comun.parroquias')->insert([
            // ===================== AMAZONAS (1) =====================
            // --- ALTO ORINOCO (1) ---
            ["name" => "ALTO ORINOCO", "municipality_id" => "1"],
            ["name" => "HUACHAMACARE", "municipality_id" => "1"],
            ["name" => "MARAWAKA", "municipality_id" => "1"],
            ["name" => "MAVACA", "municipality_id" => "1"],
            ["name" => "SIERRA PARIMA", "municipality_id" => "1"],

            // --- ATABAPO (2) ---
            ["name" => "UCATA", "municipality_id" => "2"],
            ["name" => "YAPACANA", "municipality_id" => "2"],
            ["name" => "CANAME", "municipality_id" => "2"],

            // --- ATURES (3) ---
            ["name" => "PUERTO AYACUCHO", "municipality_id" => "3"],
            ["name" => "ALBERTO GOMEZ", "municipality_id" => "3"],
            ["name" => "FERNANDO GIRON", "municipality_id" => "3"],
            ["name" => "LUIS ALBERTO GOMEZ", "municipality_id" => "3"],
            ["name" => "PARHUEÑA", "municipality_id" => "3"],
            ["name" => "PLATANILLAL", "municipality_id" => "3"],

            // --- AUTANA (4) ---
            ["name" => "ISLA RATON", "municipality_id" => "4"],
            ["name" => "GUAYAPO", "municipality_id" => "4"],
            ["name" => "MUNDUAPO", "municipality_id" => "4"],
            ["name" => "SAMARIAPO", "municipality_id" => "4"],
            ["name" => "SIPAPO", "municipality_id" => "4"],

            // --- MAROA (5) ---
            ["name" => "MAROA", "municipality_id" => "5"],
            ["name" => "VICTORINO", "municipality_id" => "5"],
            ["name" => "COMUNIDAD", "municipality_id" => "5"],

            // --- MANAPIARE (6) ---
            ["name" => "SAN JUAN DE MANAPIARE", "municipality_id" => "6"],
            ["name" => "ALTO VENTUARI", "municipality_id" => "6"],
            ["name" => "BAJO VENTUARI", "municipality_id" => "6"],
            ["name" => "MEDIO VENTUARI", "municipality_id" => "6"],

            // --- RIO NEGRO (7) ---
            ["name" => "SAN CARLOS DE RIO NEGRO", "municipality_id" => "7"],
            ["name" => "COCUY", "municipality_id" => "7"],
            ["name" => "SAN SIMON DE COCUY", "municipality_id" => "7"],
            ["name" => "SOLANO", "municipality_id" => "7"],

            // ===================== ANZOÁTEGUI (2) =====================
            // --- ANACO (8) ---
            ["name" => "ANACO", "municipality_id" => "8"],
            ["name" => "SAN JOAQUIN", "municipality_id" => "8"],

            // --- ARAGUA (9) ---
            ["name" => "ARAGUA DE BARCELONA", "municipality_id" => "9"],
            ["name" => "CACHIPO", "municipality_id" => "9"],

            // --- FERNANDO DE PEÑALVER (10) ---
            ["name" => "PUERTO PIRITU", "municipality_id" => "10"],
            ["name" => "SAN MIGUEL", "municipality_id" => "10"],
            ["name" => "SUCRE", "municipality_id" => "10"],

            // --- FRANCISCO DEL CARMEN CARVAJAL (11) ---
            ["name" => "VALLE DE GUANAPE", "municipality_id" => "11"],
            ["name" => "SANTA BARBARA", "municipality_id" => "11"],

            // --- FRANCISCO DE MIRANDA (12) ---
            ["name" => "PARIAGUAN", "municipality_id" => "12"],
            ["name" => "ATAPIRIRE", "municipality_id" => "12"],
            ["name" => "BOCA DEL PAO", "municipality_id" => "12"],
            ["name" => "EL PAO", "municipality_id" => "12"],

            // --- GUANTA (13) ---
            ["name" => "GUANTA", "municipality_id" => "13"],
            ["name" => "CHORRERON", "municipality_id" => "13"],

            // --- INDEPENDENCIA (14) ---
            ["name" => "SOLEDAD", "municipality_id" => "14"],
            ["name" => "MAMO", "municipality_id" => "14"],

            // --- JUAN ANTONIO SOTILLO (15) ---
            ["name" => "PUERTO LA CRUZ", "municipality_id" => "15"],
            ["name" => "POZUELOS", "municipality_id" => "15"],

            // --- JUAN MANUEL CAJIGAL (16) ---
            ["name" => "ONOTO", "municipality_id" => "16"],
            ["name" => "SAN PABLO", "municipality_id" => "16"],

            // --- JOSE GREGORIO MONAGAS (17) ---
            ["name" => "MAPIRE", "municipality_id" => "17"],
            ["name" => "PIAR", "municipality_id" => "17"],
            ["name" => "SAN DIEGO DE CABRUTICA", "municipality_id" => "17"],
            ["name" => "SANTA CLARA", "municipality_id" => "17"],
            ["name" => "UVERITO", "municipality_id" => "17"],

            // --- LIBERTAD (18) ---
            ["name" => "SAN MATEO", "municipality_id" => "18"],
            ["name" => "EL CARITO", "municipality_id" => "18"],
            ["name" => "SANTA INES", "municipality_id" => "18"],

            // --- MANUEL EZEQUIEL BRUZUAL (19) ---
            ["name" => "CLARINES", "municipality_id" => "19"],
            ["name" => "GUANAPE", "municipality_id" => "19"],
            ["name" => "SABANA DE UCHIRE", "municipality_id" => "19"],

            // --- PEDRO MARIA FREITES (20) ---
            ["name" => "CANTAURA", "municipality_id" => "20"],
            ["name" => "LIBERTADOR", "municipality_id" => "20"],
            ["name" => "SANTA ROSA", "municipality_id" => "20"],

            // --- PIRITU (21) ---
            ["name" => "PIRITU", "municipality_id" => "21"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "21"],

            // --- SAN JOSE DE GUANIPA (22) ---
            ["name" => "SAN JOSE DE GUANIPA", "municipality_id" => "22"],

            // --- SAN JUAN DE CAPISTRANO (23) ---
            ["name" => "BOCA DE UCHIRE", "municipality_id" => "23"],
            ["name" => "BOCA DE CHAVEZ", "municipality_id" => "23"],

            // --- SANTA ANA (24) ---
            ["name" => "SANTA ANA", "municipality_id" => "24"],
            ["name" => "PUEBLO NUEVO", "municipality_id" => "24"],

            // --- SIMON BOLIVAR (25) ---
            ["name" => "BARCELONA", "municipality_id" => "25"],
            ["name" => "EL CARMEN", "municipality_id" => "25"],
            ["name" => "SAN CRISTOBAL", "municipality_id" => "25"],
            ["name" => "NARICUAL", "municipality_id" => "25"],

            // --- SIMON RODRIGUEZ (26) ---
            ["name" => "EL TIGRE", "municipality_id" => "26"],

            // --- SIR ARTHUR MC GREGOR (27) ---
            ["name" => "EL CHAPARRO", "municipality_id" => "27"],
            ["name" => "TOMAS ALFARO CALATRAVA", "municipality_id" => "27"],

            // --- DIEGO BAUTISTA URBANEJA (28) ---
            ["name" => "LECHERIA", "municipality_id" => "28"],
            ["name" => "EL MORRO", "municipality_id" => "28"],

            // ===================== APURE (3) =====================
            // --- ACHAGUAS (29) ---
            ["name" => "ACHAGUAS", "municipality_id" => "29"],
            ["name" => "APURITO", "municipality_id" => "29"],
            ["name" => "EL YAGUAL", "municipality_id" => "29"],
            ["name" => "GUACHARA", "municipality_id" => "29"],
            ["name" => "MUCURITAS", "municipality_id" => "29"],
            ["name" => "QUESERAS DEL MEDIO", "municipality_id" => "29"],

            // --- BIRUACA (30) ---
            ["name" => "BIRUACA", "municipality_id" => "30"],

            // --- MUÑOZ (31) ---
            ["name" => "BRUZUAL", "municipality_id" => "31"],
            ["name" => "MANTECAL", "municipality_id" => "31"],
            ["name" => "QUINTERO", "municipality_id" => "31"],
            ["name" => "RINCON HONDO", "municipality_id" => "31"],
            ["name" => "SAN VICENTE", "municipality_id" => "31"],

            // --- PAEZ (32) ---
            ["name" => "GUASDUALITO", "municipality_id" => "32"],
            ["name" => "ARAMENDI", "municipality_id" => "32"],
            ["name" => "EL AMPARO", "municipality_id" => "32"],
            ["name" => "SAN CAMILO", "municipality_id" => "32"],
            ["name" => "URDANETA", "municipality_id" => "32"],

            // --- PEDRO CAMEJO (33) ---
            ["name" => "SAN JUAN DE PAYARA", "municipality_id" => "33"],
            ["name" => "CODAZZI", "municipality_id" => "33"],
            ["name" => "CUNARUCO", "municipality_id" => "33"],

            // --- ROMULO GALLEGOS (34) ---
            ["name" => "ELORZA", "municipality_id" => "34"],
            ["name" => "LA TRINIDAD", "municipality_id" => "34"],

            // --- SAN FERNANDO (35) ---
            ["name" => "SAN FERNANDO", "municipality_id" => "35"],
            ["name" => "EL RECREO", "municipality_id" => "35"],
            ["name" => "PEÑALVER", "municipality_id" => "35"],
            ["name" => "SAN RAFAEL DE ATAMAICA", "municipality_id" => "35"],

            // ===================== ARAGUA (4) =====================
            // --- BOLIVAR (36) ---
            ["name" => "SAN MATEO", "municipality_id" => "36"],

            // --- CAMATAGUA (37) ---
            ["name" => "CAMATAGUA", "municipality_id" => "37"],
            ["name" => "CARMEN DE CURA", "municipality_id" => "37"],

            // --- GIRARDOT (38) ---
            ["name" => "MARACAY", "municipality_id" => "38"],
            ["name" => "CHORONI", "municipality_id" => "38"],
            ["name" => "LAS DELICIAS", "municipality_id" => "38"],
            ["name" => "MADRE MARIA DE SAN JOSE", "municipality_id" => "38"],
            ["name" => "JOAQUIN CRESPO", "municipality_id" => "38"],
            ["name" => "PEDRO JOSE OVALLES", "municipality_id" => "38"],
            ["name" => "JOSE CASANOVA GODOY", "municipality_id" => "38"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "38"],

            // --- JOSE ANGEL LAMAS (39) ---
            ["name" => "SANTA CRUZ", "municipality_id" => "39"],

            // --- JOSE FELIX RIBAS (40) ---
            ["name" => "LA VICTORIA", "municipality_id" => "40"],
            ["name" => "CASTOR NIEVES RIOS", "municipality_id" => "40"],
            ["name" => "LAS GUACAMAYAS", "municipality_id" => "40"],
            ["name" => "PAO DE ZARATE", "municipality_id" => "40"],
            ["name" => "ZUATA", "municipality_id" => "40"],

            // --- JOSE RAFAEL REVENGA (41) ---
            ["name" => "EL CONSEJO", "municipality_id" => "41"],

            // --- LIBERTADOR (42) ---
            ["name" => "PALO NEGRO", "municipality_id" => "42"],
            ["name" => "SAN MARTIN DE PORRES", "municipality_id" => "42"],

            // --- MARIO BRICEÑO IRAGORRY (43) ---
            ["name" => "EL LIMON", "municipality_id" => "43"],
            ["name" => "CAÑA DE AZUCAR", "municipality_id" => "43"],

            // --- SAN CASIMIRO (44) ---
            ["name" => "SAN CASIMIRO", "municipality_id" => "44"],
            ["name" => "GÜIRIPA", "municipality_id" => "44"],
            ["name" => "OLLAS DE CARAMACATE", "municipality_id" => "44"],
            ["name" => "VALLE MORIN", "municipality_id" => "44"],

            // --- SAN SEBASTIAN (45) ---
            ["name" => "SAN SEBASTIAN", "municipality_id" => "45"],

            // --- SANTIAGO MARIÑO (46) ---
            ["name" => "TURMERO", "municipality_id" => "46"],
            ["name" => "AREVALO APONTE", "municipality_id" => "46"],
            ["name" => "CHUAO", "municipality_id" => "46"],
            ["name" => "SAMAN DE GÜERE", "municipality_id" => "46"],
            ["name" => "ALFREDO PACHECO MIRANDA", "municipality_id" => "46"],

            // --- SANTOS MICHELENA (47) ---
            ["name" => "LAS TEJERIAS", "municipality_id" => "47"],
            ["name" => "TIARA", "municipality_id" => "47"],

            // --- SUCRE (48) ---
            ["name" => "CAGUA", "municipality_id" => "48"],
            ["name" => "BELLA VISTA", "municipality_id" => "48"],

            // --- TOVAR (49) ---
            ["name" => "LA COLONIA TOVAR", "municipality_id" => "49"],

            // --- URDANETA (50) ---
            ["name" => "BARBACOAS", "municipality_id" => "50"],
            ["name" => "SAN FRANCISCO DE ASIS", "municipality_id" => "50"],
            ["name" => "TAGUAY", "municipality_id" => "50"],

            // --- ZAMORA (51) ---
            ["name" => "VILLA DE CURA", "municipality_id" => "51"],
            ["name" => "MAGDALENO", "municipality_id" => "51"],
            ["name" => "SAN FRANCISCO DE ASIS", "municipality_id" => "51"],
            ["name" => "VALLES DE TUCUTUNEMO", "municipality_id" => "51"],
            ["name" => "AUGUSTO MIJARES", "municipality_id" => "51"],

            // --- FRANCISCO LINARES ALCANTARA (52) ---
            ["name" => "SANTA RITA", "municipality_id" => "52"],
            ["name" => "FRANCISCO DE MIRANDA", "municipality_id" => "52"],

            // --- OCUMARE DE LA COSTA DE ORO (53) ---
            ["name" => "OCUMARE DE LA COSTA", "municipality_id" => "53"],

            // ===================== BARINAS (5) =====================
            // --- ALBERTO ARVELO TORREALBA (54) ---
            ["name" => "SABANETA", "municipality_id" => "54"],
            ["name" => "JUAN ANTONIO RODRIGUEZ DOMINGUEZ", "municipality_id" => "54"],
            ["name" => "VEGUITAS", "municipality_id" => "54"],

            // --- ANTONIO JOSE DE SUCRE (55) ---
            ["name" => "SOCORRO", "municipality_id" => "55"],
            ["name" => "ANDRES BELLO", "municipality_id" => "55"],
            ["name" => "NICOLAS PULIDO", "municipality_id" => "55"],

            // --- ARISMENDI (56) ---
            ["name" => "ARISMENDI", "municipality_id" => "56"],
            ["name" => "GUADARRAMA", "municipality_id" => "56"],
            ["name" => "LA UNION", "municipality_id" => "56"],
            ["name" => "SAN ANTONIO", "municipality_id" => "56"],

            // --- BARINAS (57) ---
            ["name" => "BARINAS", "municipality_id" => "57"],
            ["name" => "ALFREDO ARVELO LARRIVA", "municipality_id" => "57"],
            ["name" => "SAN SILVESTRE", "municipality_id" => "57"],
            ["name" => "SANTA INES", "municipality_id" => "57"],
            ["name" => "SANTA LUCIA", "municipality_id" => "57"],
            ["name" => "TORUNOS", "municipality_id" => "57"],
            ["name" => "EL CARMEN", "municipality_id" => "57"],
            ["name" => "ROMULO BETANCOURT", "municipality_id" => "57"],
            ["name" => "CORAZON DE JESUS", "municipality_id" => "57"],
            ["name" => "RAMON IGNACIO MENDEZ", "municipality_id" => "57"],
            ["name" => "ALTO BARINAS", "municipality_id" => "57"],
            ["name" => "MANUEL PALACIO FAJARDO", "municipality_id" => "57"],
            ["name" => "DOMINGA ORTIZ DE PAEZ", "municipality_id" => "57"],

            // --- BOLIVAR (58) ---
            ["name" => "BARINITAS", "municipality_id" => "58"],
            ["name" => "ALTAMIRA", "municipality_id" => "58"],
            ["name" => "CALDERAS", "municipality_id" => "58"],

            // --- CRUZ PAREDES (59) ---
            ["name" => "BARRANCAS", "municipality_id" => "59"],
            ["name" => "EL SOCORRO", "municipality_id" => "59"],
            ["name" => "MASPARRO", "municipality_id" => "59"],

            // --- EZEQUIEL ZAMORA (60) ---
            ["name" => "SANTA BARBARA", "municipality_id" => "60"],
            ["name" => "JOSE IGNACIO DEL PUMAR", "municipality_id" => "60"],
            ["name" => "PEDRO BRICEÑO MENDEZ", "municipality_id" => "60"],
            ["name" => "RAMON IGNACIO MENDEZ", "municipality_id" => "60"],

            // --- OBISPOS (61) ---
            ["name" => "OBISPOS", "municipality_id" => "61"],
            ["name" => "EL REAL", "municipality_id" => "61"],
            ["name" => "LA LUZ", "municipality_id" => "61"],

            // --- PEDRAZA (62) ---
            ["name" => "CIUDAD BOLIVIA", "municipality_id" => "62"],
            ["name" => "IGNACIO BRICEÑO", "municipality_id" => "62"],
            ["name" => "PAEZ", "municipality_id" => "62"],
            ["name" => "JOSE FELIX RIBAS", "municipality_id" => "62"],

            // --- ROJAS (63) ---
            ["name" => "LIBERTAD", "municipality_id" => "63"],
            ["name" => "DOLORES", "municipality_id" => "63"],
            ["name" => "PALACIOS FAJARDO", "municipality_id" => "63"],
            ["name" => "SANTA ROSA", "municipality_id" => "63"],

            // --- SOSA (64) ---
            ["name" => "CIUDAD DE NUTRIAS", "municipality_id" => "64"],
            ["name" => "EL REGALO", "municipality_id" => "64"],
            ["name" => "PUERTO DE NUTRIAS", "municipality_id" => "64"],
            ["name" => "SANTA CATALINA", "municipality_id" => "64"],

            // --- ANDRES ELOY BLANCO (65) ---
            ["name" => "EL CANTON", "municipality_id" => "65"],
            ["name" => "SANTA CRUZ DE GUACAS", "municipality_id" => "65"],
            ["name" => "PUERTO VIVAS", "municipality_id" => "65"],

            // ===================== BOLÍVAR (6) =====================
            // --- CARONI (66) ---
            ["name" => "CACHAMAY", "municipality_id" => "66"],
            ["name" => "CHIRICA", "municipality_id" => "66"],
            ["name" => "DALLA COSTA", "municipality_id" => "66"],
            ["name" => "ONCE DE ABRIL", "municipality_id" => "66"],
            ["name" => "SIMON BOLIVAR", "municipality_id" => "66"],
            ["name" => "UNARE", "municipality_id" => "66"],
            ["name" => "UNIVERSIDAD", "municipality_id" => "66"],
            ["name" => "VISTA AL SOL", "municipality_id" => "66"],
            ["name" => "POZO VERDE", "municipality_id" => "66"],
            ["name" => "YOCOIMA", "municipality_id" => "66"],
            ["name" => "ONCE DE ABRIL", "municipality_id" => "66"],

            // --- CEDEÑO (67) ---
            ["name" => "CAICARA DEL ORINOCO", "municipality_id" => "67"],
            ["name" => "ALTAGRACIA", "municipality_id" => "67"],
            ["name" => "ASCENSION FARRERAS", "municipality_id" => "67"],
            ["name" => "GUANIAMO", "municipality_id" => "67"],
            ["name" => "LA URBANA", "municipality_id" => "67"],
            ["name" => "PIJIGUAOS", "municipality_id" => "67"],

            // --- EL CALLAO (68) ---
            ["name" => "EL CALLAO", "municipality_id" => "68"],

            // --- GRAN SABANA (69) ---
            ["name" => "SANTA ELENA DE UAIREN", "municipality_id" => "69"],
            ["name" => "IKABARU", "municipality_id" => "69"],

            // --- HERES (70) ---
            ["name" => "CATEDRAL", "municipality_id" => "70"],
            ["name" => "ZEA", "municipality_id" => "70"],
            ["name" => "ORINOCO", "municipality_id" => "70"],
            ["name" => "JOSE ANTONIO PAEZ", "municipality_id" => "70"],
            ["name" => "MARHUANTA", "municipality_id" => "70"],
            ["name" => "AGUA SALADA", "municipality_id" => "70"],
            ["name" => "VISTA HERMOSA", "municipality_id" => "70"],
            ["name" => "LA SABANITA", "municipality_id" => "70"],

            // --- PIAR (71) ---
            ["name" => "UPATA", "municipality_id" => "71"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "71"],

            // --- RAUL LEONI (72) ---
            ["name" => "CIUDAD PIAR", "municipality_id" => "72"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "72"],

            // --- ROSCIO (73) ---
            ["name" => "GUASIPATI", "municipality_id" => "73"],
            ["name" => "SALOM", "municipality_id" => "73"],

            // --- SIFONTES (74) ---
            ["name" => "TUMEREMO", "municipality_id" => "74"],
            ["name" => "DALLA COSTA", "municipality_id" => "74"],
            ["name" => "SAN ISIDRO", "municipality_id" => "74"],

            // --- SUCRE (75) ---
            ["name" => "MARIPA", "municipality_id" => "75"],
            ["name" => "ARIPAO", "municipality_id" => "75"],
            ["name" => "GUARATARO", "municipality_id" => "75"],
            ["name" => "LAS MAJADAS", "municipality_id" => "75"],

            // --- PADRE PEDRO CHIEN (76) ---
            ["name" => "EL PALMAR", "municipality_id" => "76"],

            // ===================== CARABOBO (7) =====================
            // --- BEJUMA (77) ---
            ["name" => "BEJUMA", "municipality_id" => "77"],
            ["name" => "CANOABO", "municipality_id" => "77"],
            ["name" => "SIMON BOLIVAR", "municipality_id" => "77"],

            // --- CARLOS ARVELO (78) ---
            ["name" => "GÜIGÜE", "municipality_id" => "78"],
            ["name" => "BELEN", "municipality_id" => "78"],
            ["name" => "TACARIGUA", "municipality_id" => "78"],

            // --- DIEGO IBARRA (79) ---
            ["name" => "MARIARA", "municipality_id" => "79"],
            ["name" => "AGUAS CALIENTES", "municipality_id" => "79"],

            // --- GUACARA (80) ---
            ["name" => "GUACARA", "municipality_id" => "80"],
            ["name" => "CIUDAD ALIANZA", "municipality_id" => "80"],
            ["name" => "YAGUA", "municipality_id" => "80"],

            // --- JUAN JOSE MORA (81) ---
            ["name" => "MORON", "municipality_id" => "81"],
            ["name" => "URAMA", "municipality_id" => "81"],

            // --- LIBERTADOR (82) ---
            ["name" => "TOCUYITO", "municipality_id" => "82"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "82"],

            // --- LOS GUAYOS (83) ---
            ["name" => "LOS GUAYOS", "municipality_id" => "83"],

            // --- MIRANDA (84) ---
            ["name" => "MIRANDA", "municipality_id" => "84"],

            // --- MONTALBAN (85) ---
            ["name" => "MONTALBAN", "municipality_id" => "85"],

            // --- NAGUANAGUA (86) ---
            ["name" => "NAGUANAGUA", "municipality_id" => "86"],

            // --- PUERTO CABELLO (87) ---
            ["name" => "PUERTO CABELLO", "municipality_id" => "87"],
            ["name" => "BARTOLOME SALOM", "municipality_id" => "87"],
            ["name" => "BORBURATA", "municipality_id" => "87"],
            ["name" => "PATANEMO", "municipality_id" => "87"],

            // --- SAN DIEGO (88) ---
            ["name" => "SAN DIEGO", "municipality_id" => "88"],

            // --- SAN JOAQUIN (89) ---
            ["name" => "SAN JOAQUIN", "municipality_id" => "89"],

            // --- VALENCIA (90) ---
            ["name" => "CANDELARIA", "municipality_id" => "90"],
            ["name" => "CATEDRAL", "municipality_id" => "90"],
            ["name" => "EL SOCORRO", "municipality_id" => "90"],
            ["name" => "MIGUEL PEÑA", "municipality_id" => "90"],
            ["name" => "RAFAEL URDANETA", "municipality_id" => "90"],
            ["name" => "SAN BLAS", "municipality_id" => "90"],
            ["name" => "SAN JOSE", "municipality_id" => "90"],
            ["name" => "SANTA ROSA", "municipality_id" => "90"],
            ["name" => "NEGRO PRIMERO", "municipality_id" => "90"],

            // ===================== COJEDES (8) =====================
            // --- ANZOATEGUI (91) ---
            ["name" => "COJEDES", "municipality_id" => "91"],
            ["name" => "JUAN DE MATA SUAREZ", "municipality_id" => "91"],

            // --- FALCON (92) ---
            ["name" => "TINAQUILLO", "municipality_id" => "92"],

            // --- GIRARDOT (93) ---
            ["name" => "EL BAUL", "municipality_id" => "93"],
            ["name" => "SUCRE", "municipality_id" => "93"],

            // --- LIMA BLANCO (94) ---
            ["name" => "MACAPO", "municipality_id" => "94"],
            ["name" => "LA AGUADITA", "municipality_id" => "94"],

            // --- PAO DE SAN JUAN BAUTISTA (95) ---
            ["name" => "EL PAO", "municipality_id" => "95"],

            // --- RICAURTE (96) ---
            ["name" => "LIBERTAD", "municipality_id" => "96"],
            ["name" => "EL AMPARO", "municipality_id" => "96"],

            // --- ROMULO GALLEGOS (97) ---
            ["name" => "ROMULO GALLEGOS", "municipality_id" => "97"],

            // --- SAN CARLOS (98) ---
            ["name" => "SAN CARLOS DE AUSTRIA", "municipality_id" => "98"],
            ["name" => "JUAN ANGEL BRAVO", "municipality_id" => "98"],
            ["name" => "MANUEL MANRIQUE", "municipality_id" => "98"],

            // --- TINACO (99) ---
            ["name" => "TINACO", "municipality_id" => "99"],

            // ===================== DELTA AMACURO (9) =====================
            // --- ANTONIO DIAZ (100) ---
            ["name" => "CURURI", "municipality_id" => "100"],
            ["name" => "MANUEL RENAUD", "municipality_id" => "100"],
            ["name" => "PADRE BARRIAL", "municipality_id" => "100"],
            ["name" => "SANTOS DE ABELGAS", "municipality_id" => "100"],

            // --- CASACOIMA (101) ---
            ["name" => "IMATACA", "municipality_id" => "101"],
            ["name" => "CINCO DE JULIO", "municipality_id" => "101"],
            ["name" => "JUAN BAUTISTA ARISMENDI", "municipality_id" => "101"],
            ["name" => "MANUEL PIAR", "municipality_id" => "101"],
            ["name" => "ROMULO GALLEGOS", "municipality_id" => "101"],

            // --- PEDERNALES (102) ---
            ["name" => "PEDERNALES", "municipality_id" => "102"],
            ["name" => "LUIS BELTRAN PRIETO FIGUEROA", "municipality_id" => "102"],

            // --- TUCUPITA (103) ---
            ["name" => "SAN JOSE", "municipality_id" => "103"],
            ["name" => "JOSE VIDAL MARCANO", "municipality_id" => "103"],
            ["name" => "JUAN MILLAN", "municipality_id" => "103"],
            ["name" => "LEONARDO RUIZ PINEDA", "municipality_id" => "103"],
            ["name" => "MARISCAL ANTONIO JOSE DE SUCRE", "municipality_id" => "103"],
            ["name" => "MONSEÑOR ARGIMIRO GARCIA", "municipality_id" => "103"],
            ["name" => "SAN RAFAEL", "municipality_id" => "103"],
            ["name" => "VIRGEN DEL VALLE", "municipality_id" => "103"],

            // ===================== FALCÓN (10) =====================
            // --- ACOSTA (104) ---
            ["name" => "SAN JUAN DE LOS CAYOS", "municipality_id" => "104"],
            ["name" => "CAPATARIDA", "municipality_id" => "104"],
            ["name" => "LA PASTORA", "municipality_id" => "104"],
            ["name" => "LIBERTADOR", "municipality_id" => "104"],

            // --- BOLIVAR (105) ---
            ["name" => "SAN LUIS", "municipality_id" => "105"],
            ["name" => "ARACUA", "municipality_id" => "105"],
            ["name" => "LA PEÑA", "municipality_id" => "105"],

            // --- BUCHIVACOA (106) ---
            ["name" => "CAPATARIDA", "municipality_id" => "106"],
            ["name" => "BARIRO", "municipality_id" => "106"],
            ["name" => "BOROJO", "municipality_id" => "106"],
            ["name" => "GUAJIRO", "municipality_id" => "106"],
            ["name" => "SEQUE", "municipality_id" => "106"],
            ["name" => "ZAZARIDA", "municipality_id" => "106"],

            // --- CACIQUE MANAURE (107) ---
            ["name" => "YARACAL", "municipality_id" => "107"],

            // --- CARIRUBANA (108) ---
            ["name" => "PUNTO FIJO", "municipality_id" => "108"],
            ["name" => "NORTE", "municipality_id" => "108"],
            ["name" => "CARIRUBANA", "municipality_id" => "108"],
            ["name" => "SANTA ANA", "municipality_id" => "108"],

            // --- COLINA (109) ---
            ["name" => "LA VELA DE CORO", "municipality_id" => "109"],
            ["name" => "ACURIGUA", "municipality_id" => "109"],
            ["name" => "GUAIBACOA", "municipality_id" => "109"],
            ["name" => "LAS CALDERAS", "municipality_id" => "109"],
            ["name" => "MACORUCA", "municipality_id" => "109"],

            // --- DABAJURO (110) ---
            ["name" => "DABAJURO", "municipality_id" => "110"],

            // --- DEMOCRACIA (111) ---
            ["name" => "PEDREGAL", "municipality_id" => "111"],
            ["name" => "AGUA CLARA", "municipality_id" => "111"],
            ["name" => "AVARIA", "municipality_id" => "111"],
            ["name" => "PIEDRA GRANDE", "municipality_id" => "111"],
            ["name" => "PURURECHE", "municipality_id" => "111"],

            // --- FALCON (112) ---
            ["name" => "PUEBLO NUEVO", "municipality_id" => "112"],
            ["name" => "ADICORA", "municipality_id" => "112"],
            ["name" => "BARAIVED", "municipality_id" => "112"],
            ["name" => "BUENA VISTA", "municipality_id" => "112"],
            ["name" => "JADACAQUIVA", "municipality_id" => "112"],
            ["name" => "MORUY", "municipality_id" => "112"],
            ["name" => "ADAURE", "municipality_id" => "112"],
            ["name" => "EL HATO", "municipality_id" => "112"],
            ["name" => "EL VINCULO", "municipality_id" => "112"],

            // --- FEDERACION (113) ---
            ["name" => "CHURUGUARA", "municipality_id" => "113"],
            ["name" => "AGUA LARGA", "municipality_id" => "113"],
            ["name" => "EL PAUJI", "municipality_id" => "113"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "113"],
            ["name" => "MAPARARI", "municipality_id" => "113"],

            // --- JACURA (114) ---
            ["name" => "JACURA", "municipality_id" => "114"],
            ["name" => "AGUA LINDA", "municipality_id" => "114"],
            ["name" => "ARAURIMA", "municipality_id" => "114"],

            // --- LOS TAQUES (115) ---
            ["name" => "LOS TAQUES", "municipality_id" => "115"],
            ["name" => "JUDIBANA", "municipality_id" => "115"],

            // --- MAUROA (116) ---
            ["name" => "MENE DE MAUROA", "municipality_id" => "116"],
            ["name" => "CASIGUA", "municipality_id" => "116"],
            ["name" => "SAN FELIX", "municipality_id" => "116"],

            // --- MIRANDA (117) ---
            ["name" => "SANTA ANA DE CORO", "municipality_id" => "117"],
            ["name" => "GUZMAN GUILLERMO", "municipality_id" => "117"],
            ["name" => "MITARE", "municipality_id" => "117"],
            ["name" => "SAN ANTONIO", "municipality_id" => "117"],
            ["name" => "SAN GABRIEL", "municipality_id" => "117"],

            // --- MONSEÑOR ITURRIZA (118) ---
            ["name" => "CHICHIRIVICHE", "municipality_id" => "118"],
            ["name" => "BOCA DE TOCUYO", "municipality_id" => "118"],
            ["name" => "TOCUYO DE LA COSTA", "municipality_id" => "118"],

            // --- PALMASOLA (119) ---
            ["name" => "PALMASOLA", "municipality_id" => "119"],

            // --- PETIT (120) ---
            ["name" => "CABURE", "municipality_id" => "120"],
            ["name" => "COLINE", "municipality_id" => "120"],
            ["name" => "CURIMAGUA", "municipality_id" => "120"],

            // --- PIRITU (121) ---
            ["name" => "PIRITU", "municipality_id" => "121"],
            ["name" => "SAN JOSE DE LA COSTA", "municipality_id" => "121"],

            // --- SAN FRANCISCO (122) ---
            ["name" => "MIRIMIRE", "municipality_id" => "122"],

            // --- SILVA (123) ---
            ["name" => "TUCACAS", "municipality_id" => "123"],
            ["name" => "BOCA DE AROA", "municipality_id" => "123"],

            // --- SUCRE (124) ---
            ["name" => "LA CRUZ DE TARATARA", "municipality_id" => "124"],
            ["name" => "PECAYA", "municipality_id" => "124"],

            // --- TOCOPERO (125) ---
            ["name" => "TOCOPERO", "municipality_id" => "125"],

            // --- UNION (126) ---
            ["name" => "SANTA CRUZ DE BUCARAL", "municipality_id" => "126"],
            ["name" => "EL CHARAL", "municipality_id" => "126"],
            ["name" => "LAS VEGAS DEL TUY", "municipality_id" => "126"],

            // --- URUMACO (127) ---
            ["name" => "URUMACO", "municipality_id" => "127"],
            ["name" => "BRUZUAL", "municipality_id" => "127"],

            // --- ZAMORA (128) ---
            ["name" => "PUERTO CUMAREBO", "municipality_id" => "128"],
            ["name" => "LA CIENAGA", "municipality_id" => "128"],
            ["name" => "LA SOLEDAD", "municipality_id" => "128"],
            ["name" => "PUEBLO CUMAREBO", "municipality_id" => "128"],
            ["name" => "ZAZARIDA", "municipality_id" => "128"],

            // ===================== GUÁRICO (11) =====================
            // --- CAMAGUAN (129) ---
            ["name" => "CAMAGUAN", "municipality_id" => "129"],
            ["name" => "PUERTO MIRANDA", "municipality_id" => "129"],
            ["name" => "UVERITO", "municipality_id" => "129"],

            // --- CHAGUARAMAS (130) ---
            ["name" => "CHAGUARAMAS", "municipality_id" => "130"],

            // --- EL SOCORRO (131) ---
            ["name" => "EL SOCORRO", "municipality_id" => "131"],

            // --- SAN GERONIMO DE GUAYABAL (132) ---
            ["name" => "GUAYABAL", "municipality_id" => "132"],
            ["name" => "CAZORLA", "municipality_id" => "132"],

            // --- LEONARDO INFANTE (133) ---
            ["name" => "VALLE DE LA PASCUA", "municipality_id" => "133"],
            ["name" => "ESPINO", "municipality_id" => "133"],

            // --- LAS MERCEDES (134) ---
            ["name" => "LAS MERCEDES", "municipality_id" => "134"],
            ["name" => "CABRUTA", "municipality_id" => "134"],
            ["name" => "SANTA RITA DE MANAPIRE", "municipality_id" => "134"],

            // --- JULIAN MELLADO (135) ---
            ["name" => "EL SOMBRERO", "municipality_id" => "135"],
            ["name" => "SOSA", "municipality_id" => "135"],

            // --- FRANCISCO DE MIRANDA (136) ---
            ["name" => "CALABOZO", "municipality_id" => "136"],
            ["name" => "EL CALVARIO", "municipality_id" => "136"],
            ["name" => "EL RASTRO", "municipality_id" => "136"],
            ["name" => "GUARDATINAJAS", "municipality_id" => "136"],

            // --- JOSE TADEO MONAGAS (137) ---
            ["name" => "ALTAGRACIA DE ORITUCO", "municipality_id" => "137"],
            ["name" => "LEZAMA", "municipality_id" => "137"],
            ["name" => "LIBERTAD DE ORITUCO", "municipality_id" => "137"],
            ["name" => "SAN FRANCISCO DE MACAIRA", "municipality_id" => "137"],
            ["name" => "SAN RAFAEL DE ORITUCO", "municipality_id" => "137"],

            // --- ORTIZ (138) ---
            ["name" => "ORTIZ", "municipality_id" => "138"],
            ["name" => "SAN FRANCISCO DE TIZNADOS", "municipality_id" => "138"],
            ["name" => "SAN JOSE DE TIZNADOS", "municipality_id" => "138"],
            ["name" => "SAN LORENZO DE TIZNADOS", "municipality_id" => "138"],

            // --- JOSE FELIX RIBAS (139) ---
            ["name" => "TUCUPIDO", "municipality_id" => "139"],
            ["name" => "SAN RAFAEL DE LAYA", "municipality_id" => "139"],

            // --- JUAN GERMAN ROSCIO (140) ---
            ["name" => "SAN JUAN DE LOS MORROS", "municipality_id" => "140"],
            ["name" => "PARAPARA", "municipality_id" => "140"],
            ["name" => "CANTAGALLO", "municipality_id" => "140"],

            // --- SAN JOSE DE GUARIBE (141) ---
            ["name" => "SAN JOSE DE GUARIBE", "municipality_id" => "141"],

            // --- SANTA MARIA DE IPIRE (142) ---
            ["name" => "SANTA MARIA DE IPIRE", "municipality_id" => "142"],
            ["name" => "ALTAMIRA", "municipality_id" => "142"],

            // --- PEDRO ZARAZA (143) ---
            ["name" => "ZARAZA", "municipality_id" => "143"],
            ["name" => "SAN JOSE DE UNARE", "municipality_id" => "143"],

            // ===================== LARA (12) =====================
            // --- ANDRES ELOY BLANCO (144) ---
            ["name" => "SANARE", "municipality_id" => "144"],
            ["name" => "YAY", "municipality_id" => "144"],
            ["name" => "LA QUEBRADA", "municipality_id" => "144"],

            // --- CRESPO (145) ---
            ["name" => "DUACA", "municipality_id" => "145"],
            ["name" => "FREITEZ", "municipality_id" => "145"],
            ["name" => "JOSE MARIA BLANCO", "municipality_id" => "145"],

            // --- IRIBARREN (146) ---
            ["name" => "CATEDRAL", "municipality_id" => "146"],
            ["name" => "CONCEPCION", "municipality_id" => "146"],
            ["name" => "EL CUJI", "municipality_id" => "146"],
            ["name" => "JUAN DE VILLEGAS", "municipality_id" => "146"],
            ["name" => "SANTA ROSA", "municipality_id" => "146"],
            ["name" => "TAMACA", "municipality_id" => "146"],
            ["name" => "UNION", "municipality_id" => "146"],
            ["name" => "AGUEDO FELIPE ALVARADO", "municipality_id" => "146"],
            ["name" => "BUENA VISTA", "municipality_id" => "146"],
            ["name" => "JUAREZ", "municipality_id" => "146"],

            // --- JIMENEZ (147) ---
            ["name" => "QUIBOR", "municipality_id" => "147"],
            ["name" => "CUARA", "municipality_id" => "147"],
            ["name" => "DIEGO DE LOZADA", "municipality_id" => "147"],
            ["name" => "PARAISO DE SAN JOSE", "municipality_id" => "147"],
            ["name" => "SAN MIGUEL", "municipality_id" => "147"],
            ["name" => "TINTORERO", "municipality_id" => "147"],
            ["name" => "JOSE BERNARDO DORANTE", "municipality_id" => "147"],
            ["name" => "CORONEL MARIANO PERAZA", "municipality_id" => "147"],

            // --- MORAN (148) ---
            ["name" => "EL TOCUYO", "municipality_id" => "148"],
            ["name" => "ANZOATEGUI", "municipality_id" => "148"],
            ["name" => "BOLIVAR", "municipality_id" => "148"],
            ["name" => "GUARICO", "municipality_id" => "148"],
            ["name" => "HILARIO LUNA Y LUNA", "municipality_id" => "148"],
            ["name" => "HUMOCARO BAJO", "municipality_id" => "148"],
            ["name" => "HUMOCARO ALTO", "municipality_id" => "148"],
            ["name" => "LA CANDELARIA", "municipality_id" => "148"],
            ["name" => "MORAN", "municipality_id" => "148"],

            // --- PALAVECINO (149) ---
            ["name" => "CABUDARE", "municipality_id" => "149"],
            ["name" => "JOSE GREGORIO BASTIDAS", "municipality_id" => "149"],
            ["name" => "AGUA VIVA", "municipality_id" => "149"],

            // --- SIMON PLANAS (150) ---
            ["name" => "SARARE", "municipality_id" => "150"],
            ["name" => "BURIA", "municipality_id" => "150"],
            ["name" => "GUSTAVO VEGAS LEON", "municipality_id" => "150"],

            // --- TORRES (151) ---
            ["name" => "CARORA", "municipality_id" => "151"],
            ["name" => "ALTAGRACIA", "municipality_id" => "151"],
            ["name" => "ANTONIO DIAZ", "municipality_id" => "151"],
            ["name" => "CAMACARO", "municipality_id" => "151"],
            ["name" => "CASTAÑEDA", "municipality_id" => "151"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "151"],
            ["name" => "EL BLANCO", "municipality_id" => "151"],
            ["name" => "ESPINOZA DE LOS MONTEROS", "municipality_id" => "151"],
            ["name" => "LARA", "municipality_id" => "151"],
            ["name" => "LAS MERCEDES", "municipality_id" => "151"],
            ["name" => "MANUEL MORILLO", "municipality_id" => "151"],
            ["name" => "MONTAÑA VERDE", "municipality_id" => "151"],
            ["name" => "MONTES DE OCA", "municipality_id" => "151"],
            ["name" => "TORRES", "municipality_id" => "151"],
            ["name" => "HERIBERTO ARROYO", "municipality_id" => "151"],
            ["name" => "REYES VARGAS", "municipality_id" => "151"],
            ["name" => "TRINIDAD SAMUEL", "municipality_id" => "151"],

            // --- URDANETA (152) ---
            ["name" => "SIQUISIQUE", "municipality_id" => "152"],
            ["name" => "XAGUAZA", "municipality_id" => "152"],
            ["name" => "SAN MIGUEL", "municipality_id" => "152"],
            ["name" => "MOROTURO", "municipality_id" => "152"],

            // ===================== MÉRIDA (13) =====================
            // --- ALBERTO ADRIANI (153) ---
            ["name" => "EL VIGIA", "municipality_id" => "153"],
            ["name" => "HECTOR AMABLE MORA", "municipality_id" => "153"],
            ["name" => "PULIDO MENDEZ", "municipality_id" => "153"],
            ["name" => "PRESIDENTE BETANCOURT", "municipality_id" => "153"],
            ["name" => "PRESIDENTE PAEZ", "municipality_id" => "153"],
            ["name" => "PRESIDENTE ROMULO GALLEGOS", "municipality_id" => "153"],
            ["name" => "GABRIEL PICON GONZALEZ", "municipality_id" => "153"],

            // --- ANDRES BELLO (154) ---
            ["name" => "LA AZULITA", "municipality_id" => "154"],

            // --- ANTONIO PINTO SALINAS (155) ---
            ["name" => "SANTA CRUZ DE MORA", "municipality_id" => "155"],
            ["name" => "MESA BOLIVAR", "municipality_id" => "155"],
            ["name" => "MESA DE LAS PALMAS", "municipality_id" => "155"],

            // --- ARICAGUA (156) ---
            ["name" => "ARICAGUA", "municipality_id" => "156"],
            ["name" => "SAN ANTONIO", "municipality_id" => "156"],

            // --- ARZOBISPO CHACON (157) ---
            ["name" => "CANAGUA", "municipality_id" => "157"],
            ["name" => "CHACANTA", "municipality_id" => "157"],
            ["name" => "EL MOLINO", "municipality_id" => "157"],
            ["name" => "GUAIMARAL", "municipality_id" => "157"],
            ["name" => "MUCUTUY", "municipality_id" => "157"],
            ["name" => "MUCUCHACHI", "municipality_id" => "157"],

            // --- CAMPO ELIAS (158) ---
            ["name" => "EJIDO", "municipality_id" => "158"],
            ["name" => "FERNANDEZ PEÑA", "municipality_id" => "158"],
            ["name" => "MATRIZ", "municipality_id" => "158"],
            ["name" => "MONTALBAN", "municipality_id" => "158"],
            ["name" => "JAJI", "municipality_id" => "158"],
            ["name" => "LA MESA", "municipality_id" => "158"],
            ["name" => "SAN JOSE DEL SUR", "municipality_id" => "158"],

            // --- CARACCIOLO PARRA OLMEDO (159) ---
            ["name" => "TUCANI", "municipality_id" => "159"],
            ["name" => "FLORENCIO RAMIREZ", "municipality_id" => "159"],

            // --- CARDENAL QUINTERO (160) ---
            ["name" => "SANTO DOMINGO", "municipality_id" => "160"],
            ["name" => "LAS PIEDRAS", "municipality_id" => "160"],

            // --- GUARAQUE (161) ---
            ["name" => "GUARAQUE", "municipality_id" => "161"],
            ["name" => "MESA DE QUINTERO", "municipality_id" => "161"],
            ["name" => "RIO NEGRO", "municipality_id" => "161"],

            // --- JULIO CESAR SALAS (162) ---
            ["name" => "ARAPUEY", "municipality_id" => "162"],
            ["name" => "PALMIRA", "municipality_id" => "162"],

            // --- JUSTO BRICEÑO (163) ---
            ["name" => "SAN CRISTOBAL DE TORONDOY", "municipality_id" => "163"],
            ["name" => "SAN JOSE DE LAS FLORES", "municipality_id" => "163"],

            // --- LIBERTADOR (164) ---
            ["name" => "MERIDA", "municipality_id" => "164"],
            ["name" => "ANTONIO SPINETTI DINI", "municipality_id" => "164"],
            ["name" => "ARIAS", "municipality_id" => "164"],
            ["name" => "CARACCIOLO PARRA PEREZ", "municipality_id" => "164"],
            ["name" => "DOMINGO PEÑA", "municipality_id" => "164"],
            ["name" => "EL LLANO", "municipality_id" => "164"],
            ["name" => "GONZALO PICON FEBRES", "municipality_id" => "164"],
            ["name" => "JACINTO PLAZA", "municipality_id" => "164"],
            ["name" => "JUAN RODRIGUEZ SUAREZ", "municipality_id" => "164"],
            ["name" => "LASSO DE LA VEGA", "municipality_id" => "164"],
            ["name" => "MARIANO PICON SALAS", "municipality_id" => "164"],
            ["name" => "MILLA", "municipality_id" => "164"],
            ["name" => "OSUNA RODRIGUEZ", "municipality_id" => "164"],
            ["name" => "SAGRARIO", "municipality_id" => "164"],
            ["name" => "EL MORRO", "municipality_id" => "164"],
            ["name" => "LOS NEVADOS", "municipality_id" => "164"],

            // --- MIRANDA (165) ---
            ["name" => "TIMOTES", "municipality_id" => "165"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "165"],
            ["name" => "LA VENTA", "municipality_id" => "165"],
            ["name" => "PIÑANGO", "municipality_id" => "165"],

            // --- OBISPO RAMOS DE LORA (166) ---
            ["name" => "SANTA ELENA DE ARENALES", "municipality_id" => "166"],
            ["name" => "ELOY PAREDES", "municipality_id" => "166"],
            ["name" => "SAN RAFAEL DE ALCÁZAR", "municipality_id" => "166"],

            // --- PADRE NOGUERA (167) ---
            ["name" => "SANTA MARIA DE CAPARO", "municipality_id" => "167"],

            // --- PUEBLO LLANO (168) ---
            ["name" => "PUEBLO LLANO", "municipality_id" => "168"],

            // --- RANGEL (169) ---
            ["name" => "MUCUCHIES", "municipality_id" => "169"],
            ["name" => "MUCURUBA", "municipality_id" => "169"],
            ["name" => "SAN RAFAEL", "municipality_id" => "169"],
            ["name" => "CACUTE", "municipality_id" => "169"],
            ["name" => "LA TOMA", "municipality_id" => "169"],

            // --- RIVAS DAVILA (170) ---
            ["name" => "BAILADORES", "municipality_id" => "170"],
            ["name" => "GERONIMO MALDONADO", "municipality_id" => "170"],

            // --- SANTOS MARQUINA (171) ---
            ["name" => "TABAY", "municipality_id" => "171"],

            // --- SUCRE (172) ---
            ["name" => "LAGUNILLAS", "municipality_id" => "172"],
            ["name" => "CHIGUARA", "municipality_id" => "172"],
            ["name" => "ESTANQUES", "municipality_id" => "172"],
            ["name" => "LA TRAMPA", "municipality_id" => "172"],
            ["name" => "PUEBLO NUEVO DEL SUR", "municipality_id" => "172"],
            ["name" => "SAN JUAN", "municipality_id" => "172"],

            // --- TOVAR (173) ---
            ["name" => "TOVAR", "municipality_id" => "173"],
            ["name" => "EL AMPARO", "municipality_id" => "173"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "173"],
            ["name" => "SANTA CRUZ DE MORA", "municipality_id" => "173"],

            // --- TULIO FEBRES CORDERO (174) ---
            ["name" => "NUEVA BOLIVIA", "municipality_id" => "174"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "174"],
            ["name" => "MARIA DE LA CONCEPCION PALACIOS BLANCO", "municipality_id" => "174"],
            ["name" => "SANTA APOLONIA", "municipality_id" => "174"],

            // --- ZEA (175) ---
            ["name" => "ZEA", "municipality_id" => "175"],
            ["name" => "CAÑO EL TIGRE", "municipality_id" => "175"],

            // ===================== MIRANDA (14) =====================
            // --- ACEVEDO (176) ---
            ["name" => "CAUCAGUA", "municipality_id" => "176"],
            ["name" => "ARAGÜITA", "municipality_id" => "176"],
            ["name" => "PANAQUIRE", "municipality_id" => "176"],
            ["name" => "RIO CHICO", "municipality_id" => "176"],
            ["name" => "EL CAFE", "municipality_id" => "176"],
            ["name" => "MARIZAPA", "municipality_id" => "176"],
            ["name" => "SAN JOSE DE RIO CHICO", "municipality_id" => "176"],
            ["name" => "TACARIGUA DE LA LAGUNA", "municipality_id" => "176"],

            // --- ANDRES BELLO (177) ---
            ["name" => "SAN JOSE DE BARLOVENTO", "municipality_id" => "177"],
            ["name" => "CUMBO", "municipality_id" => "177"],

            // --- BARUTA (178) ---
            ["name" => "BARUTA", "municipality_id" => "178"],
            ["name" => "EL CAFETAL", "municipality_id" => "178"],
            ["name" => "LAS MINAS DE BARUTA", "municipality_id" => "178"],

            // --- BRION (179) ---
            ["name" => "HIGUEROTE", "municipality_id" => "179"],
            ["name" => "CURREIRE", "municipality_id" => "179"],
            ["name" => "TACARIGUA DE BRILLANTE", "municipality_id" => "179"],

            // --- BUROZ (180) ---
            ["name" => "MAMPORAL", "municipality_id" => "180"],

            // --- CARRIZAL (181) ---
            ["name" => "CARRIZAL", "municipality_id" => "181"],

            // --- CHACAO (182) ---
            ["name" => "CHACAO", "municipality_id" => "182"],

            // --- CRISTOBAL ROJAS (183) ---
            ["name" => "CHARALLAVE", "municipality_id" => "183"],
            ["name" => "LAS BRISAS", "municipality_id" => "183"],

            // --- EL HATILLO (184) ---
            ["name" => "EL HATILLO", "municipality_id" => "184"],

            // --- GUAICAIPURO (185) ---
            ["name" => "LOS TEQUES", "municipality_id" => "185"],
            ["name" => "ALTAGRACIA DE LA MONTAÑA", "municipality_id" => "185"],
            ["name" => "CECILIO ACOSTA", "municipality_id" => "185"],
            ["name" => "EL JARILLO", "municipality_id" => "185"],
            ["name" => "LAGUNETAS", "municipality_id" => "185"],
            ["name" => "SAN PEDRO DE LOS ALTOS", "municipality_id" => "185"],

            // --- INDEPENDENCIA (186) ---
            ["name" => "SANTA TERESA DEL TUY", "municipality_id" => "186"],
            ["name" => "EL CARTANAL", "municipality_id" => "186"],

            // --- LANDER (187) ---
            ["name" => "OCUMARE DEL TUY", "municipality_id" => "187"],
            ["name" => "LA DEMOCRACIA", "municipality_id" => "187"],
            ["name" => "SANTA BARBARA", "municipality_id" => "187"],

            // --- LOS SALIAS (188) ---
            ["name" => "SAN ANTONIO DE LOS ALTOS", "municipality_id" => "188"],

            // --- PAEZ (189) ---
            ["name" => "GUATIRE", "municipality_id" => "189"],
            ["name" => "EL JARILLO", "municipality_id" => "189"],
            ["name" => "SANTA CRUZ DEL VALLE", "municipality_id" => "189"],

            // --- PAZ CASTILLO (190) ---
            ["name" => "SANTA LUCIA", "municipality_id" => "190"],
            ["name" => "EL ROSARIO", "municipality_id" => "190"],
            ["name" => "SOAPIRE", "municipality_id" => "190"],

            // --- PEDRO GUAL (191) ---
            ["name" => "CUPIRA", "municipality_id" => "191"],
            ["name" => "MACHURUCUTO", "municipality_id" => "191"],

            // --- PLAZA (192) ---
            ["name" => "GUARENAS", "municipality_id" => "192"],

            // --- SIMON BOLIVAR (193) ---
            ["name" => "SAN FRANCISCO DE YARE", "municipality_id" => "193"],
            ["name" => "SAN ANTONIO DE YARE", "municipality_id" => "193"],

            // --- SUCRE (194) ---
            ["name" => "PETARE", "municipality_id" => "194"],
            ["name" => "LEONCIO MARTINEZ", "municipality_id" => "194"],
            ["name" => "CAUCAGÜITA", "municipality_id" => "194"],
            ["name" => "FILAS DE MARICHE", "municipality_id" => "194"],
            ["name" => "LA DOLORITA", "municipality_id" => "194"],
            ["name" => "MARICHE", "municipality_id" => "194"],

            // --- URDANETA (195) ---
            ["name" => "CUA", "municipality_id" => "195"],
            ["name" => "NUEVA CUA", "municipality_id" => "195"],

            // --- ZAMORA (196) ---
            ["name" => "GUATIRE", "municipality_id" => "196"],
            ["name" => "BOLIVAR", "municipality_id" => "196"],

            // ===================== MONAGAS (15) =====================
            // --- ACOSTA (197) ---
            ["name" => "SAN ANTONIO", "municipality_id" => "197"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "197"],

            // --- AGUASAY (198) ---
            ["name" => "AGUASAY", "municipality_id" => "198"],

            // --- BOLIVAR (199) ---
            ["name" => "CARIPITO", "municipality_id" => "199"],

            // --- CARIPE (200) ---
            ["name" => "CARIPE", "municipality_id" => "200"],
            ["name" => "TERESEN", "municipality_id" => "200"],
            ["name" => "EL GUACHARO", "municipality_id" => "200"],
            ["name" => "SAN AGUSTIN", "municipality_id" => "200"],
            ["name" => "LA GUANOTA", "municipality_id" => "200"],
            ["name" => "SABANA DE PIEDRA", "municipality_id" => "200"],

            // --- CEDEÑO (201) ---
            ["name" => "CAICARA DE MATURIN", "municipality_id" => "201"],
            ["name" => "AREO", "municipality_id" => "201"],
            ["name" => "SAN FELIX", "municipality_id" => "201"],
            ["name" => "VIENTO FRESCO", "municipality_id" => "201"],

            // --- EZEQUIEL ZAMORA (202) ---
            ["name" => "PUNTA DE MATA", "municipality_id" => "202"],
            ["name" => "EL TEJERO", "municipality_id" => "202"],

            // --- LIBERTADOR (203) ---
            ["name" => "TEMBLADOR", "municipality_id" => "203"],
            ["name" => "CHAGUARAMAS", "municipality_id" => "203"],
            ["name" => "LAS ALHUACAS", "municipality_id" => "203"],
            ["name" => "TABASCA", "municipality_id" => "203"],

            // --- MATURIN (204) ---
            ["name" => "MATURIN", "municipality_id" => "204"],
            ["name" => "ALTO DE LOS GODOS", "municipality_id" => "204"],
            ["name" => "BOQUERON", "municipality_id" => "204"],
            ["name" => "LAS COCUIZAS", "municipality_id" => "204"],
            ["name" => "SAN SIMON", "municipality_id" => "204"],
            ["name" => "SANTA CRUZ", "municipality_id" => "204"],
            ["name" => "EL COROZO", "municipality_id" => "204"],
            ["name" => "EL FURRIAL", "municipality_id" => "204"],
            ["name" => "JUSEPIN", "municipality_id" => "204"],
            ["name" => "LA PICA", "municipality_id" => "204"],
            ["name" => "SAN VICENTE", "municipality_id" => "204"],

            // --- PIAR (205) ---
            ["name" => "ARAGUA DE MATURIN", "municipality_id" => "205"],
            ["name" => "APARICIO", "municipality_id" => "205"],
            ["name" => "CHAGUARAMAL", "municipality_id" => "205"],
            ["name" => "EL PINTO", "municipality_id" => "205"],
            ["name" => "GUANAGUANA", "municipality_id" => "205"],
            ["name" => "LA TOSCANA", "municipality_id" => "205"],
            ["name" => "TAGUAYA", "municipality_id" => "205"],

            // --- PUNCERES (206) ---
            ["name" => "QUIRIQUIRE", "municipality_id" => "206"],
            ["name" => "CACHIPO", "municipality_id" => "206"],

            // --- SANTA BARBARA (207) ---
            ["name" => "SANTA BARBARA", "municipality_id" => "207"],

            // --- SOTILLO (208) ---
            ["name" => "BARRANCAS", "municipality_id" => "208"],
            ["name" => "LOS BARRANCOS DE FAJARDO", "municipality_id" => "208"],

            // --- URACOA (209) ---
            ["name" => "URACOA", "municipality_id" => "209"],

            // ===================== NUEVA ESPARTA (16) =====================
            // --- ANTOLIN DEL CAMPO (210) ---
            ["name" => "PLAZA PARAGUACHI", "municipality_id" => "210"],

            // --- ARISMENDI (211) ---
            ["name" => "LA ASUNCION", "municipality_id" => "211"],

            // --- DIAZ (212) ---
            ["name" => "SAN JUAN BAUTISTA", "municipality_id" => "212"],
            ["name" => "ZABALA", "municipality_id" => "212"],

            // --- GARCIA (213) ---
            ["name" => "EL VALLE DEL ESPIRITU SANTO", "municipality_id" => "213"],
            ["name" => "FRANCISCO FAJARDO", "municipality_id" => "213"],

            // --- GOMEZ (214) ---
            ["name" => "SANTA ANA", "municipality_id" => "214"],
            ["name" => "GUEVARA", "municipality_id" => "214"],
            ["name" => "MATASIETE", "municipality_id" => "214"],
            ["name" => "BOLIVAR", "municipality_id" => "214"],

            // --- MANEIRO (215) ---
            ["name" => "PAMPATAR", "municipality_id" => "215"],
            ["name" => "AGUIRRE", "municipality_id" => "215"],

            // --- MARCANO (216) ---
            ["name" => "JUAN GRIEGO", "municipality_id" => "216"],
            ["name" => "ADRIAN", "municipality_id" => "216"],

            // --- MARIÑO (217) ---
            ["name" => "PORLAMAR", "municipality_id" => "217"],

            // --- PENINSULA DE MACANAO (218) ---
            ["name" => "BOCA DEL RIO", "municipality_id" => "218"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "218"],

            // --- TUBORES (219) ---
            ["name" => "PUNTA DE PIEDRAS", "municipality_id" => "219"],
            ["name" => "LOS BARALES", "municipality_id" => "219"],

            // --- VILLALBA (220) ---
            ["name" => "SAN PEDRO DE COCHE", "municipality_id" => "220"],
            ["name" => "VICENTE FUENTES", "municipality_id" => "220"],

            // ===================== PORTUGUESA (17) =====================
            // --- AGUA BLANCA (221) ---
            ["name" => "AGUA BLANCA", "municipality_id" => "221"],
            ["name" => "LA ENCRUCIJADA", "municipality_id" => "221"],

            // --- ARAURE (222) ---
            ["name" => "ARAURE", "municipality_id" => "222"],
            ["name" => "RIO ACARIGUA", "municipality_id" => "222"],

            // --- ESTELLER (223) ---
            ["name" => "PIRITU", "municipality_id" => "223"],
            ["name" => "UVERAL", "municipality_id" => "223"],

            // --- GUANARE (224) ---
            ["name" => "GUANARE", "municipality_id" => "224"],
            ["name" => "CORDOBA", "municipality_id" => "224"],
            ["name" => "SAN JOSE DE LA MONTAÑA", "municipality_id" => "224"],
            ["name" => "SAN JUAN DE GUANAGUANARE", "municipality_id" => "224"],
            ["name" => "VIRGEN DE COROMOTO", "municipality_id" => "224"],

            // --- GUANARITO (225) ---
            ["name" => "GUANARITO", "municipality_id" => "225"],
            ["name" => "TRINIDAD DE LA CAPILLA", "municipality_id" => "225"],
            ["name" => "DIVINA PASTORA", "municipality_id" => "225"],

            // --- MONSEÑOR JOSE VICENTE DE UNDA (226) ---
            ["name" => "CHABASQUEN", "municipality_id" => "226"],
            ["name" => "PEÑA BLANCA", "municipality_id" => "226"],

            // --- OSPINO (227) ---
            ["name" => "OSPINO", "municipality_id" => "227"],
            ["name" => "APARICION", "municipality_id" => "227"],
            ["name" => "LA ESTACION", "municipality_id" => "227"],

            // --- PAEZ (228) ---
            ["name" => "ACARIGUA", "municipality_id" => "228"],
            ["name" => "PAYARA", "municipality_id" => "228"],
            ["name" => "PIMPINELA", "municipality_id" => "228"],
            ["name" => "RAMON PERAZA", "municipality_id" => "228"],

            // --- PAPELON (229) ---
            ["name" => "PAPELON", "municipality_id" => "229"],
            ["name" => "CAÑO DELGADITO", "municipality_id" => "229"],

            // --- SAN GENARO DE BOCONOITO (230) ---
            ["name" => "BOCONOITO", "municipality_id" => "230"],
            ["name" => "ANTONIA TORRES", "municipality_id" => "230"],

            // --- SAN RAFAEL DE ONOTO (231) ---
            ["name" => "SAN RAFAEL DE ONOTO", "municipality_id" => "231"],
            ["name" => "SANTA FE", "municipality_id" => "231"],
            ["name" => "EL MOLINO", "municipality_id" => "231"],

            // --- SANTA ROSALIA (232) ---
            ["name" => "SANTA ROSALIA", "municipality_id" => "232"],
            ["name" => "FLORIDA", "municipality_id" => "232"],

            // --- SUCRE (233) ---
            ["name" => "BISCUCUY", "municipality_id" => "233"],
            ["name" => "BOCONO", "municipality_id" => "233"],
            ["name" => "CAMPO AMOR", "municipality_id" => "233"],
            ["name" => "MASPARRO", "municipality_id" => "233"],
            ["name" => "SAN JOSE DE SAGUAZ", "municipality_id" => "233"],
            ["name" => "SAN RAFAEL DE PALO ALZADO", "municipality_id" => "233"],

            // --- TUREN (234) ---
            ["name" => "TUREN", "municipality_id" => "234"],
            ["name" => "LA TRINIDAD", "municipality_id" => "234"],
            ["name" => "SAN ANTONIO", "municipality_id" => "234"],
            ["name" => "COLONIA TUREN", "municipality_id" => "234"],

            // ===================== SUCRE (18) =====================
            // --- ANDRES ELOY BLANCO (235) ---
            ["name" => "CASANAY", "municipality_id" => "235"],
            ["name" => "MARIÑO", "municipality_id" => "235"],
            ["name" => "RICAURTE", "municipality_id" => "235"],

            // --- ANDRES MATA (236) ---
            ["name" => "SAN JOSE DE AREOCUAR", "municipality_id" => "236"],
            ["name" => "TAVERA ACOSTA", "municipality_id" => "236"],

            // --- ARISMENDI (237) ---
            ["name" => "RIO CARIBE", "municipality_id" => "237"],
            ["name" => "ANTONIO JOSE DE SUCRE", "municipality_id" => "237"],
            ["name" => "EL MORRO DE PUERTO SANTO", "municipality_id" => "237"],
            ["name" => "PUERTO SANTO", "municipality_id" => "237"],
            ["name" => "SAN JUAN DE LAS GALDONAS", "municipality_id" => "237"],

            // --- BENITEZ (238) ---
            ["name" => "EL PILAR", "municipality_id" => "238"],
            ["name" => "EL RINCON", "municipality_id" => "238"],
            ["name" => "GENERAL FRANCISCO ANTONIO VASQUEZ", "municipality_id" => "238"],
            ["name" => "GUARAUNOS", "municipality_id" => "238"],
            ["name" => "TUNIPUY", "municipality_id" => "238"],
            ["name" => "UNION", "municipality_id" => "238"],

            // --- BERMUDEZ (239) ---
            ["name" => "CARUPANO", "municipality_id" => "239"],
            ["name" => "BOLIVAR", "municipality_id" => "239"],
            ["name" => "SANTA CATALINA", "municipality_id" => "239"],
            ["name" => "SANTA ROSA", "municipality_id" => "239"],
            ["name" => "SANTA TERESA", "municipality_id" => "239"],

            // --- BOLIVAR (240) ---
            ["name" => "MARIGUITAR", "municipality_id" => "240"],
            ["name" => "ARENAS", "municipality_id" => "240"],
            ["name" => "ARICAGUA", "municipality_id" => "240"],
            ["name" => "COCOLLAR", "municipality_id" => "240"],
            ["name" => "SAN FERNANDO", "municipality_id" => "240"],
            ["name" => "SAN LORENZO", "municipality_id" => "240"],

            // --- CAJIGAL (241) ---
            ["name" => "YAGUARAPARO", "municipality_id" => "241"],
            ["name" => "EL PAUJIL", "municipality_id" => "241"],
            ["name" => "LIBERTAD", "municipality_id" => "241"],

            // --- CRUZ SALMERON ACOSTA (242) ---
            ["name" => "ARAYA", "municipality_id" => "242"],
            ["name" => "CHACOPATA", "municipality_id" => "242"],
            ["name" => "MANICUARE", "municipality_id" => "242"],

            // --- LIBERTADOR (243) ---
            ["name" => "TUNIPUY", "municipality_id" => "243"],
            ["name" => "CAMPO ELIAS", "municipality_id" => "243"],
            ["name" => "GÜIRIA", "municipality_id" => "243"],

            // --- MARIÑO (244) ---
            ["name" => "IRAPA", "municipality_id" => "244"],
            ["name" => "CAMPO CLARO", "municipality_id" => "244"],
            ["name" => "MARABAL", "municipality_id" => "244"],
            ["name" => "SAN ANTONIO DE IRAPA", "municipality_id" => "244"],
            ["name" => "SORO", "municipality_id" => "244"],

            // --- MEJIA (245) ---
            ["name" => "SAN ANTONIO DEL GOLFO", "municipality_id" => "245"],

            // --- MONTES (246) ---
            ["name" => "CUMANACOA", "municipality_id" => "246"],
            ["name" => "ARENAS", "municipality_id" => "246"],
            ["name" => "ARICAGUA", "municipality_id" => "246"],
            ["name" => "SAN LORENZO", "municipality_id" => "246"],
            ["name" => "SAN FERNANDO", "municipality_id" => "246"],

            // --- RIBERO (247) ---
            ["name" => "CARIACO", "municipality_id" => "247"],
            ["name" => "CATUARO", "municipality_id" => "247"],
            ["name" => "RENDON", "municipality_id" => "247"],
            ["name" => "SANTA CRUZ", "municipality_id" => "247"],
            ["name" => "SANTA MARIA", "municipality_id" => "247"],

            // --- SUCRE (248) ---
            ["name" => "CUMANA", "municipality_id" => "248"],
            ["name" => "ALTAGRACIA", "municipality_id" => "248"],
            ["name" => "SANTA INES", "municipality_id" => "248"],
            ["name" => "VALENTIN VALIENTE", "municipality_id" => "248"],
            ["name" => "AYACUCHO", "municipality_id" => "248"],
            ["name" => "SAN JUAN", "municipality_id" => "248"],
            ["name" => "RAUL LEONI", "municipality_id" => "248"],
            ["name" => "GRAN MARISCAL", "municipality_id" => "248"],

            // --- VALDEZ (249) ---
            ["name" => "GÜIRIA", "municipality_id" => "249"],
            ["name" => "CRISTOBAL COLON", "municipality_id" => "249"],
            ["name" => "PUNTA DE PIEDRA", "municipality_id" => "249"],
            ["name" => "BIDEAU", "municipality_id" => "249"],
            // ===================== TÁCHIRA (19) =====================
            // --- ANDRES BELLO (250) ---
            ["name" => "CORDERO", "municipality_id" => "250"],

            // --- ANTONIO ROMULO COSTA (251) ---
            ["name" => "LAS MESAS", "municipality_id" => "251"],

            // --- AYACUCHO (252) ---
            ["name" => "SAN JUAN DE COLON", "municipality_id" => "252"],
            ["name" => "AYACUCHO", "municipality_id" => "252"],
            ["name" => "SAN PEDRO DEL RIO", "municipality_id" => "252"],

            // --- BOLIVAR (253) ---
            ["name" => "SAN ANTONIO DEL TACHIRA", "municipality_id" => "253"],
            ["name" => "JUAN VICENTE GOMEZ", "municipality_id" => "253"],
            ["name" => "PALOTAL", "municipality_id" => "253"],

            // --- CARDENAS (254) ---
            ["name" => "TARIBÁ", "municipality_id" => "254"],
            ["name" => "AMENODORO RANGEL LAMUS", "municipality_id" => "254"],
            ["name" => "LA FLORIDA", "municipality_id" => "254"],

            // --- CORDOBA (255) ---
            ["name" => "SANTA ANA DE TACHIRA", "municipality_id" => "255"],

            // --- FERNANDEZ FEO (256) ---
            ["name" => "SAN RAFAEL DEL PINAL", "municipality_id" => "256"],
            ["name" => "SANTO DOMINGO", "municipality_id" => "256"],

            // --- FRANCISCO DE MIRANDA (257) ---
            ["name" => "SAN JOSE DE BOLIVAR", "municipality_id" => "257"],

            // --- GARCIA DE HEVIA (258) ---
            ["name" => "LA FRIA", "municipality_id" => "258"],
            ["name" => "BOCA DE GRITA", "municipality_id" => "258"],
            ["name" => "JOSE ANTONIO PAEZ", "municipality_id" => "258"],

            // --- GUASIMOS (259) ---
            ["name" => "PALMIRA", "municipality_id" => "259"],

            // --- INDEPENDENCIA (260) ---
            ["name" => "CAPACHO NUEVO", "municipality_id" => "260"],
            ["name" => "JUAN GERMAN ROSCIO", "municipality_id" => "260"],
            ["name" => "ROMAN CARDENAS", "municipality_id" => "260"],

            // --- JAUREGUI (261) ---
            ["name" => "LA GRITA", "municipality_id" => "261"],
            ["name" => "EMILIO CONSTANTINO GUERRERO", "municipality_id" => "261"],
            ["name" => "MONSEÑOR MIGUEL ANTONIO SALAS", "municipality_id" => "261"],

            // --- JOSE MARIA VARGAS (262) ---
            ["name" => "EL COBRE", "municipality_id" => "262"],

            // --- JUNIN (263) ---
            ["name" => "RUBIO", "municipality_id" => "263"],
            ["name" => "BRAMON", "municipality_id" => "263"],
            ["name" => "LA PETROLEA", "municipality_id" => "263"],
            ["name" => "QUINIMARI", "municipality_id" => "263"],

            // --- LIBERTAD (264) ---
            ["name" => "CAPACHO VIEJO", "municipality_id" => "264"],
            ["name" => "CIPRIANO CASTRO", "municipality_id" => "264"],
            ["name" => "MANUEL FELIPE RUGELES", "municipality_id" => "264"],

            // --- LIBERTADOR (265) ---
            ["name" => "ABEJALES", "municipality_id" => "265"],
            ["name" => "DORADAS", "municipality_id" => "265"],
            ["name" => "EMETERIO OCHOA", "municipality_id" => "265"],
            ["name" => "SAN JOAQUIN DE NAVAY", "municipality_id" => "265"],

            // --- LOBATERA (266) ---
            ["name" => "LOBATERA", "municipality_id" => "266"],
            ["name" => "CONSTITUCION", "municipality_id" => "266"],

            // --- MICHELENA (267) ---
            ["name" => "MICHELENA", "municipality_id" => "267"],

            // --- PANAMERICANO (268) ---
            ["name" => "COLONCITO", "municipality_id" => "268"],
            ["name" => "LA PALMITA", "municipality_id" => "268"],

            // --- PEDRO MARIA UREÑA (269) ---
            ["name" => "UREÑA", "municipality_id" => "269"],
            ["name" => "NUEVA ARCADIA", "municipality_id" => "269"],

            // --- RAFAEL URDANETA (270) ---
            ["name" => "DELICIAS", "municipality_id" => "270"],

            // --- SAMUEL DARIO MALDONADO (271) ---
            ["name" => "LA TENDIDA", "municipality_id" => "271"],
            ["name" => "BOCONO", "municipality_id" => "271"],
            ["name" => "HERNANDEZ", "municipality_id" => "271"],

            // --- SAN CRISTOBAL (272) ---
            ["name" => "LA CONCORDIA", "municipality_id" => "272"],
            ["name" => "PEDRO MARIA MORANTES", "municipality_id" => "272"],
            ["name" => "SAN JUAN BAUTISTA", "municipality_id" => "272"],
            ["name" => "SAN SEBASTIAN", "municipality_id" => "272"],
            ["name" => "DR. FRANCISCO ROMERO LOBO", "municipality_id" => "272"],

            // --- SEBORUCO (273) ---
            ["name" => "SEBORUCO", "municipality_id" => "273"],

            // --- SIMON RODRIGUEZ (274) ---
            ["name" => "SAN SIMON", "municipality_id" => "274"],

            // --- SUCRE (275) ---
            ["name" => "QUENIQUEA", "municipality_id" => "275"],
            ["name" => "SAN PABLO", "municipality_id" => "275"],
            ["name" => "SAN JOSECITO", "municipality_id" => "275"],

            // --- TORBES (276) ---
            ["name" => "SAN JOSECITO", "municipality_id" => "276"],

            // --- URIBANTE (277) ---
            ["name" => "PREGONERO", "municipality_id" => "277"],
            ["name" => "CARDENAS", "municipality_id" => "277"],
            ["name" => "JUAN PABLO PEÑALOZA", "municipality_id" => "277"],
            ["name" => "POTOSI", "municipality_id" => "277"],

            // --- SAN JUDAS TADEO (278) ---
            ["name" => "UMUQUENA", "municipality_id" => "278"],

            // ===================== TRUJILLO (20) =====================
            // --- ANDRES BELLO (279) ---
            ["name" => "SANTA ISABEL", "municipality_id" => "279"],
            ["name" => "ARAGUANEY", "municipality_id" => "279"],
            ["name" => "EL JAGÜITO", "municipality_id" => "279"],
            ["name" => "LA ESPERANZA", "municipality_id" => "279"],

            // --- BOCONO (280) ---
            ["name" => "BOCONO", "municipality_id" => "280"],
            ["name" => "EL CARMEN", "municipality_id" => "280"],
            ["name" => "MOSQUEY", "municipality_id" => "280"],
            ["name" => "AYACUCHO", "municipality_id" => "280"],
            ["name" => "BURBUSAY", "municipality_id" => "280"],
            ["name" => "GENERAL RIVAS", "municipality_id" => "280"],
            ["name" => "MONSEÑOR JAUREGUI", "municipality_id" => "280"],
            ["name" => "RAFAEL RANGEL", "municipality_id" => "280"],
            ["name" => "SAN JOSE", "municipality_id" => "280"],
            ["name" => "SAN MIGUEL", "municipality_id" => "280"],
            ["name" => "GUARAMACAL", "municipality_id" => "280"],
            ["name" => "SAN RAFAEL", "municipality_id" => "280"],
            ["name" => "RIO FRIO", "municipality_id" => "280"],

            // --- BOLIVAR (281) ---
            ["name" => "SABANA GRANDE", "municipality_id" => "281"],
            ["name" => "CHEREGÜE", "municipality_id" => "281"],
            ["name" => "GRANADOS", "municipality_id" => "281"],

            // --- CANDELARIA (282) ---
            ["name" => "CHEJENDE", "municipality_id" => "282"],
            ["name" => "ARNOLDO GABALDON", "municipality_id" => "282"],
            ["name" => "BOLIVIA", "municipality_id" => "282"],
            ["name" => "CARRILLO", "municipality_id" => "282"],
            ["name" => "CEGARRA", "municipality_id" => "282"],
            ["name" => "MANUEL SALVADOR ULLOA", "municipality_id" => "282"],
            ["name" => "SAN JOSE", "municipality_id" => "282"],

            // --- CARACHE (283) ---
            ["name" => "CARACHE", "municipality_id" => "283"],
            ["name" => "CUICAS", "municipality_id" => "283"],
            ["name" => "LA CONCEPCION", "municipality_id" => "283"],
            ["name" => "PANAMERICANA", "municipality_id" => "283"],
            ["name" => "SANTA CRUZ", "municipality_id" => "283"],

            // --- ESCUQUE (284) ---
            ["name" => "ESCUQUE", "municipality_id" => "284"],
            ["name" => "LA UNION", "municipality_id" => "284"],
            ["name" => "SABANA LIBRE", "municipality_id" => "284"],
            ["name" => "SANTA RITA", "municipality_id" => "284"],

            // --- JOSE FELIPE MARQUEZ CAÑIZALES (285) ---
            ["name" => "EL SOCORRO", "municipality_id" => "285"],
            ["name" => "ANTONIO JOSE DE SUCRE", "municipality_id" => "285"],
            ["name" => "LOS CAPACHOS", "municipality_id" => "285"],

            // --- JUAN VICENTE CAMPO ELIAS (286) ---
            ["name" => "CAMPO ELIAS", "municipality_id" => "286"],
            ["name" => "ARNOLDO GABALDON", "municipality_id" => "286"],

            // --- LA CEIBA (287) ---
            ["name" => "SANTA APOLONIA", "municipality_id" => "287"],
            ["name" => "EL PROGRESO", "municipality_id" => "287"],
            ["name" => "LA CEIBA", "municipality_id" => "287"],
            ["name" => "TRES DE FEBRERO", "municipality_id" => "287"],

            // --- MIRANDA (288) ---
            ["name" => "EL DIVIDIVE", "municipality_id" => "288"],
            ["name" => "AGUA CALIENTE", "municipality_id" => "288"],
            ["name" => "EL CENIZO", "municipality_id" => "288"],
            ["name" => "AGUA SANTA", "municipality_id" => "288"],
            ["name" => "VALERITA", "municipality_id" => "288"],

            // --- MONTE CARMELO (289) ---
            ["name" => "MONTE CARMELO", "municipality_id" => "289"],
            ["name" => "BUENA VISTA", "municipality_id" => "289"],
            ["name" => "SANTA MARIA DEL JUNCAL", "municipality_id" => "289"],

            // --- MOTATAN (290) ---
            ["name" => "MOTATAN", "municipality_id" => "290"],
            ["name" => "EL BAÑO", "municipality_id" => "290"],
            ["name" => "JALISCO", "municipality_id" => "290"],

            // --- PAMPAN (291) ---
            ["name" => "PAMPAN", "municipality_id" => "291"],
            ["name" => "FLOR DE PATRIA", "municipality_id" => "291"],
            ["name" => "LA PAZ", "municipality_id" => "291"],
            ["name" => "SANTA ANA", "municipality_id" => "291"],

            // --- PAMPANITO (292) ---
            ["name" => "PAMPANITO", "municipality_id" => "292"],
            ["name" => "LA CONCEPCION", "municipality_id" => "292"],
            ["name" => "PAMPANITO II", "municipality_id" => "292"],

            // --- RAFAEL RANGEL (293) ---
            ["name" => "BETIJOQUE", "municipality_id" => "293"],
            ["name" => "EL CEDRO", "municipality_id" => "293"],
            ["name" => "JOSE GREGORIO HERNANDEZ", "municipality_id" => "293"],
            ["name" => "LA PUEBLITA", "municipality_id" => "293"],

            // --- SAN RAFAEL DE CARVAJAL (294) ---
            ["name" => "CARVAJAL", "municipality_id" => "294"],
            ["name" => "ANTONIO NICOLAS BRICEÑO", "municipality_id" => "294"],
            ["name" => "CAMPO ALEGRE", "municipality_id" => "294"],
            ["name" => "JOSE LEONARDO SUAREZ", "municipality_id" => "294"],

            // --- SUCRE (295) ---
            ["name" => "SABANA DE MENDOZA", "municipality_id" => "295"],
            ["name" => "JUNIN", "municipality_id" => "295"],
            ["name" => "VALMORE RODRIGUEZ", "municipality_id" => "295"],
            ["name" => "EL PARAISO", "municipality_id" => "295"],

            // --- TRUJILLO (296) ---
            ["name" => "TRUJILLO", "municipality_id" => "296"],
            ["name" => "ANDRES LINARES", "municipality_id" => "296"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "296"],
            ["name" => "CRISTOBAL MENDOZA", "municipality_id" => "296"],
            ["name" => "CRUZ CARRILLO", "municipality_id" => "296"],
            ["name" => "MATRIZ", "municipality_id" => "296"],
            ["name" => "MONSEÑOR CARRILLO", "municipality_id" => "296"],
            ["name" => "TRES ESQUINAS", "municipality_id" => "296"],

            // --- URDANETA (297) ---
            ["name" => "LA QUEBRADA", "municipality_id" => "297"],
            ["name" => "CABIMBU", "municipality_id" => "297"],
            ["name" => "JAJO", "municipality_id" => "297"],
            ["name" => "LA MESA DE ESNUJAQUE", "municipality_id" => "297"],
            ["name" => "SANTIAGO", "municipality_id" => "297"],
            ["name" => "TUÑAME", "municipality_id" => "297"],

            // --- VALERA (298) ---
            ["name" => "VALERA", "municipality_id" => "298"],
            ["name" => "JUAN IGNACIO MONTILLA", "municipality_id" => "298"],
            ["name" => "LA BEATRIZ", "municipality_id" => "298"],
            ["name" => "MERCEDES DIAZ", "municipality_id" => "298"],
            ["name" => "SAN LUIS", "municipality_id" => "298"],
            ["name" => "MENDOZA FRIA", "municipality_id" => "298"],

            // ===================== LA GUAIRA (21) =====================
            // --- VARGAS (299) ---
            ["name" => "CARUAO", "municipality_id" => "299"],
            ["name" => "CATIA LA MAR", "municipality_id" => "299"],
            ["name" => "CARAYACA", "municipality_id" => "299"],
            ["name" => "CARLOS SOUBLETTE", "municipality_id" => "299"],
            ["name" => "EL JUNKO", "municipality_id" => "299"],
            ["name" => "LA GUAIRA", "municipality_id" => "299"],
            ["name" => "MACUTO", "municipality_id" => "299"],
            ["name" => "MAIQUETIA", "municipality_id" => "299"],
            ["name" => "NAIGUATA", "municipality_id" => "299"],
            ["name" => "RAUL LEONI", "municipality_id" => "299"],
            ["name" => "URIMARE", "municipality_id" => "299"],

            // ===================== YARACUY (22) =====================
            // --- ARISTIDES BASTIDAS (300) ---
            ["name" => "ARISTIDES BASTIDAS", "municipality_id" => "300"],

            // --- BOLIVAR (301) ---
            ["name" => "BOLIVAR", "municipality_id" => "301"],

            // --- BRUZUAL (302) ---
            ["name" => "CHIVACOA", "municipality_id" => "302"],
            ["name" => "CAMPO ELIAS", "municipality_id" => "302"],

            // --- COCOROTE (303) ---
            ["name" => "COCOROTE", "municipality_id" => "303"],

            // --- INDEPENDENCIA (304) ---
            ["name" => "INDEPENDENCIA", "municipality_id" => "304"],

            // --- JOSE ANTONIO PAEZ (305) ---
            ["name" => "SABANA DE PARRA", "municipality_id" => "305"],

            // --- LA TRINIDAD (306) ---
            ["name" => "LA TRINIDAD", "municipality_id" => "306"],

            // --- MANUEL MONGE (307) ---
            ["name" => "MANUEL MONGE", "municipality_id" => "307"],

            // --- NIRGUA (308) ---
            ["name" => "NIRGUA", "municipality_id" => "308"],
            ["name" => "SALOM", "municipality_id" => "308"],
            ["name" => "TEMERLA", "municipality_id" => "308"],

            // --- PEÑA (309) ---
            ["name" => "YARITAGUA", "municipality_id" => "309"],
            ["name" => "SAN ANDRES", "municipality_id" => "309"],

            // --- SAN FELIPE (310) ---
            ["name" => "SAN FELIPE", "municipality_id" => "310"],
            ["name" => "ALBARICO", "municipality_id" => "310"],
            ["name" => "SAN JAVIER", "municipality_id" => "310"],

            // --- SUCRE (311) ---
            ["name" => "SUCRE", "municipality_id" => "311"],

            // --- URACHICHE (312) ---
            ["name" => "URACHICHE", "municipality_id" => "312"],

            // --- VEROES (313) ---
            ["name" => "FARRIAR", "municipality_id" => "313"],
            ["name" => "EL GUAYABO", "municipality_id" => "313"],

            // ===================== ZULIA (23) =====================
            // --- ALMIRANTE PADILLA (314) ---
            ["name" => "ISLA DE TOAS", "municipality_id" => "314"],
            ["name" => "MONAGAS", "municipality_id" => "314"],

            // --- BARALT (315) ---
            ["name" => "SAN TIMOTEO", "municipality_id" => "315"],
            ["name" => "GENERAL URDANETA", "municipality_id" => "315"],
            ["name" => "LIBERTADOR", "municipality_id" => "315"],
            ["name" => "MARCELINO BRICEÑO", "municipality_id" => "315"],
            ["name" => "PUEBLO NUEVO", "municipality_id" => "315"],
            ["name" => "MANUEL GUANIPA MATOS", "municipality_id" => "315"],

            // --- CABIMAS (316) ---
            ["name" => "CABIMAS", "municipality_id" => "316"],
            ["name" => "GERMAN RIOS LINARES", "municipality_id" => "316"],
            ["name" => "JORGE HERNANDEZ", "municipality_id" => "316"],
            ["name" => "LA ROSA", "municipality_id" => "316"],
            ["name" => "PUNTA GORDA", "municipality_id" => "316"],
            ["name" => "CARMEN HERRERA", "municipality_id" => "316"],
            ["name" => "SAN BENITO", "municipality_id" => "316"],
            ["name" => "ROMULO BETANCOURT", "municipality_id" => "316"],
            ["name" => "ARISTIDES CALVANI", "municipality_id" => "316"],

            // --- CATATUMBO (317) ---
            ["name" => "ENCONTRADOS", "municipality_id" => "317"],
            ["name" => "UDON PEREZ", "municipality_id" => "317"],

            // --- COLON (318) ---
            ["name" => "SAN CARLOS DEL ZULIA", "municipality_id" => "318"],
            ["name" => "MORALITO", "municipality_id" => "318"],
            ["name" => "SANTA BARBARA", "municipality_id" => "318"],
            ["name" => "URRIBARRI", "municipality_id" => "318"],

            // --- FRANCISCO JAVIER PULGAR (319) ---
            ["name" => "PUEBLO NUEVO-EL CHIVO", "municipality_id" => "319"],
            ["name" => "AGUAS CALIENTES", "municipality_id" => "319"],
            ["name" => "CARLOS QUEVEDO", "municipality_id" => "319"],
            ["name" => "SIMON RODRIGUEZ", "municipality_id" => "319"],

            // --- JESUS ENRIQUE LOSSADA (320) ---
            ["name" => "LA CONCEPCION", "municipality_id" => "320"],
            ["name" => "JOSE RAMON YEPEZ", "municipality_id" => "320"],
            ["name" => "EL PARAISO", "municipality_id" => "320"],
            ["name" => "SAN JOSE", "municipality_id" => "320"],
            ["name" => "MARIANO PARRA LEON", "municipality_id" => "320"],

            // --- JESUS MARIA SEMPRUN (321) ---
            ["name" => "CASIGUA-EL CUBO", "municipality_id" => "321"],
            ["name" => "BARALT", "municipality_id" => "321"],

            // --- LA CAÑADA DE URDANETA (322) ---
            ["name" => "CONCEPCION", "municipality_id" => "322"],
            ["name" => "POTRERITOS", "municipality_id" => "322"],
            ["name" => "EL CARMELO", "municipality_id" => "322"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "322"],
            ["name" => "ANDRES BELLO", "municipality_id" => "322"],

            // --- LAGUNILLAS (323) ---
            ["name" => "LAGUNILLAS", "municipality_id" => "323"],
            ["name" => "ALONSO DE OJEDA", "municipality_id" => "323"],
            ["name" => "CAMPO LARA", "municipality_id" => "323"],
            ["name" => "ELEAZAR LOPEZ CONTRERAS", "municipality_id" => "323"],
            ["name" => "EL DIQUE", "municipality_id" => "323"],
            ["name" => "PARAUTE", "municipality_id" => "323"],
            ["name" => "LIBERTAD", "municipality_id" => "323"],
            ["name" => "VENEZUELA", "municipality_id" => "323"],

            // --- MACHIQUES DE PERIJA (324) ---
            ["name" => "MACHIQUES", "municipality_id" => "324"],
            ["name" => "LIBERTAD", "municipality_id" => "324"],
            ["name" => "RIO NEGRO", "municipality_id" => "324"],
            ["name" => "SAN JOSE DE PERIJA", "municipality_id" => "324"],
            ["name" => "BARTOLOME DE LAS CASAS", "municipality_id" => "324"],

            // --- MARA (325) ---
            ["name" => "SAN RAFAEL", "municipality_id" => "325"],
            ["name" => "LAS PARCELAS", "municipality_id" => "325"],
            ["name" => "MONSERRATE", "municipality_id" => "325"],
            ["name" => "LA SIERRITA", "municipality_id" => "325"],
            ["name" => "ISLA DE SAN CARLOS", "municipality_id" => "325"],

            // --- MARACAIBO (326) ---
            ["name" => "ANTONIO BORJAS ROMERO", "municipality_id" => "326"],
            ["name" => "BOLIVAR", "municipality_id" => "326"],
            ["name" => "CACIQUE MARA", "municipality_id" => "326"],
            ["name" => "CARACCIOLO PARRA PEREZ", "municipality_id" => "326"],
            ["name" => "CECILIO ACOSTA", "municipality_id" => "326"],
            ["name" => "CRISTO DE ARANZA", "municipality_id" => "326"],
            ["name" => "COQUIVACOA", "municipality_id" => "326"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "326"],
            ["name" => "FRANCISCO EUGENIO BUSTAMANTE", "municipality_id" => "326"],
            ["name" => "IDELFONSO VASQUEZ", "municipality_id" => "326"],
            ["name" => "JUANA DE AVILA", "municipality_id" => "326"],
            ["name" => "LUIS HURTADO HIGUERA", "municipality_id" => "326"],
            ["name" => "MANUEL DAGNINO", "municipality_id" => "326"],
            ["name" => "OLEGARIO VILLALOBOS", "municipality_id" => "326"],
            ["name" => "RAUL LEONI", "municipality_id" => "326"],
            ["name" => "SANTA LUCIA", "municipality_id" => "326"],
            ["name" => "VENANCIO PULGAR", "municipality_id" => "326"],
            ["name" => "SAN ISIDRO", "municipality_id" => "326"],

            // --- MIRANDA (327) ---
            ["name" => "LOS PUERTOS DE ALTAGRACIA", "municipality_id" => "327"],
            ["name" => "ALTAGRACIA", "municipality_id" => "327"],
            ["name" => "ANA MARIA CAMPOS", "municipality_id" => "327"],
            ["name" => "FARIA", "municipality_id" => "327"],
            ["name" => "SAN ANTONIO", "municipality_id" => "327"],
            ["name" => "SAN JOSE", "municipality_id" => "327"],

            // --- PAEZ (328) ---
            ["name" => "SINAMAICA", "municipality_id" => "328"],
            ["name" => "EL GUAYABO", "municipality_id" => "328"],
            ["name" => "PARAGUAIPOA", "municipality_id" => "328"],

            // --- ROSARIO DE PERIJA (329) ---
            ["name" => "EL ROSARIO", "municipality_id" => "329"],
            ["name" => "SAN JOSE", "municipality_id" => "329"],
            ["name" => "DONALDO GARCIA", "municipality_id" => "329"],

            // --- SAN FRANCISCO (330) ---
            ["name" => "SAN FRANCISCO", "municipality_id" => "330"],
            ["name" => "EL BAJO", "municipality_id" => "330"],
            ["name" => "DOMITILA FLORES", "municipality_id" => "330"],
            ["name" => "FRANCISCO OCHOA", "municipality_id" => "330"],
            ["name" => "LOS CORTIJOS", "municipality_id" => "330"],
            ["name" => "MARCIAL HERNANDEZ", "municipality_id" => "330"],
            ["name" => "JOSE DOMINGO RUS", "municipality_id" => "330"],

            // --- SANTA RITA (331) ---
            ["name" => "SANTA RITA", "municipality_id" => "331"],
            ["name" => "EL MENE", "municipality_id" => "331"],
            ["name" => "PEDRO LUCAS URRIBARRI", "municipality_id" => "331"],
            ["name" => "JOSE CENOVIO URRIBARRI", "municipality_id" => "331"],

            // --- SIMON BOLIVAR (332) ---
            ["name" => "TIA JUANA", "municipality_id" => "332"],
            ["name" => "CIUDAD OJEDA", "municipality_id" => "332"],
            ["name" => "RAFAEL URDANETA", "municipality_id" => "332"],

            // --- SUCRE (333) ---
            ["name" => "BOBURES", "municipality_id" => "333"],
            ["name" => "CASCAJAL", "municipality_id" => "333"],
            ["name" => "EL POZON", "municipality_id" => "333"],
            ["name" => "SAN JOSE", "municipality_id" => "333"],
            ["name" => "SANTA MARIA", "municipality_id" => "333"],
            ["name" => "EL BATAL", "municipality_id" => "333"],
            ["name" => "MONSEÑOR ARTURO CELESTINO ALVAREZ", "municipality_id" => "333"],

            // --- VALMORE RODRIGUEZ (334) ---
            ["name" => "VALMORE RODRIGUEZ", "municipality_id" => "334"],
            ["name" => "RAUL CUENCA", "municipality_id" => "334"],
            ["name" => "LA VICTORIA", "municipality_id" => "334"],

            // ===================== DISTRITO CAPITAL (24) =====================
            // --- LIBERTADOR (335) ---
            ["name" => "ALTAGRACIA", "municipality_id" => "335"],
            ["name" => "ANTIMANO", "municipality_id" => "335"],
            ["name" => "CANDELARIA", "municipality_id" => "335"],
            ["name" => "CATEDRAL", "municipality_id" => "335"],
            ["name" => "LA PASTORA", "municipality_id" => "335"],
            ["name" => "SAN AGUSTIN", "municipality_id" => "335"],
            ["name" => "SAN JOSE", "municipality_id" => "335"],
            ["name" => "SAN JUAN", "municipality_id" => "335"],
            ["name" => "SANTA ROSALIA", "municipality_id" => "335"],
            ["name" => "SANTA TERESA", "municipality_id" => "335"],
            ["name" => "SUCRE", "municipality_id" => "335"],
            ["name" => "23 DE ENERO", "municipality_id" => "335"],
            ["name" => "EL RECREO", "municipality_id" => "335"],
            ["name" => "EL VALLE", "municipality_id" => "335"],
            ["name" => "LA VEGA", "municipality_id" => "335"],
            ["name" => "MACARAO", "municipality_id" => "335"],
            ["name" => "CARICUAO", "municipality_id" => "335"],
            ["name" => "EL JUNQUITO", "municipality_id" => "335"],
            ["name" => "EL PARAISO", "municipality_id" => "335"],
            ["name" => "SAN BERNARDINO", "municipality_id" => "335"],
            ["name" => "SAN PEDRO", "municipality_id" => "335"],
            ["name" => "SANTA ROSA DE LIMA", "municipality_id" => "335"],

        ]);

        if (config('database.default') === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('comun.parroquias', 'id'), coalesce(max(id),0) + 1, false) FROM comun.parroquias;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comun.parroquias');
    }
};
