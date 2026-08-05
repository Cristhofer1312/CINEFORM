<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParroquiasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comun.parroquias')->insert([
            ["name" => "ALTO ORINOCO", "municipality_id" => "1"],
            ["name" => "HUACHAMACARE", "municipality_id" => "1"],
            ["name" => "MARAWAKA", "municipality_id" => "1"],
            ["name" => "MAVACA", "municipality_id" => "1"],
            ["name" => "SIERRA PARIMA", "municipality_id" => "1"],

            ["name" => "UCATA", "municipality_id" => "2"],
            ["name" => "YAPACANA", "municipality_id" => "2"],
            ["name" => "CANAME", "municipality_id" => "2"],

            ["name" => "PUERTO AYACUCHO", "municipality_id" => "3"],
            ["name" => "ALBERTO GOMEZ", "municipality_id" => "3"],
            ["name" => "FERNANDO GIRON", "municipality_id" => "3"],
            ["name" => "LUIS ALBERTO GOMEZ", "municipality_id" => "3"],
            ["name" => "PARHUEÑA", "municipality_id" => "3"],
            ["name" => "PLATANILLAL", "municipality_id" => "3"],

            ["name" => "ISLA RATON", "municipality_id" => "4"],
            ["name" => "GUAYAPO", "municipality_id" => "4"],
            ["name" => "MUNDUAPO", "municipality_id" => "4"],
            ["name" => "SAMARIAPO", "municipality_id" => "4"],
            ["name" => "SIPAPO", "municipality_id" => "4"],

            ["name" => "MAROA", "municipality_id" => "5"],
            ["name" => "VICTORINO", "municipality_id" => "5"],
            ["name" => "COMUNIDAD", "municipality_id" => "5"],

            ["name" => "SAN JUAN DE MANAPIARE", "municipality_id" => "6"],
            ["name" => "ALTO VENTUARI", "municipality_id" => "6"],
            ["name" => "BAJO VENTUARI", "municipality_id" => "6"],
            ["name" => "MEDIO VENTUARI", "municipality_id" => "6"],

            ["name" => "SAN CARLOS DE RIO NEGRO", "municipality_id" => "7"],
            ["name" => "COCUY", "municipality_id" => "7"],
            ["name" => "SAN SIMON DE COCUY", "municipality_id" => "7"],
            ["name" => "SOLANO", "municipality_id" => "7"],

            ["name" => "ANACO", "municipality_id" => "8"],
            ["name" => "SAN JOAQUIN", "municipality_id" => "8"],

            ["name" => "ARAGUA DE BARCELONA", "municipality_id" => "9"],
            ["name" => "CACHIPO", "municipality_id" => "9"],

            ["name" => "PUERTO PIRITU", "municipality_id" => "10"],
            ["name" => "SAN MIGUEL", "municipality_id" => "10"],
            ["name" => "SUCRE", "municipality_id" => "10"],

            ["name" => "VALLE DE GUANAPE", "municipality_id" => "11"],
            ["name" => "SANTA BARBARA", "municipality_id" => "11"],

            ["name" => "PARIAGUAN", "municipality_id" => "12"],
            ["name" => "ATAPIRIRE", "municipality_id" => "12"],
            ["name" => "BOCA DEL PAO", "municipality_id" => "12"],
            ["name" => "EL PAO", "municipality_id" => "12"],

            ["name" => "GUANTA", "municipality_id" => "13"],
            ["name" => "CHORRERON", "municipality_id" => "13"],

            ["name" => "SOLEDAD", "municipality_id" => "14"],
            ["name" => "MAMO", "municipality_id" => "14"],

            ["name" => "PUERTO LA CRUZ", "municipality_id" => "15"],
            ["name" => "POZUELOS", "municipality_id" => "15"],

            ["name" => "ONOTO", "municipality_id" => "16"],
            ["name" => "SAN PABLO", "municipality_id" => "16"],

            ["name" => "MAPIRE", "municipality_id" => "17"],
            ["name" => "PIAR", "municipality_id" => "17"],
            ["name" => "SAN DIEGO DE CABRUTICA", "municipality_id" => "17"],
            ["name" => "SANTA CLARA", "municipality_id" => "17"],
            ["name" => "UVERITO", "municipality_id" => "17"],

            ["name" => "SAN MATEO", "municipality_id" => "18"],
            ["name" => "EL CARITO", "municipality_id" => "18"],
            ["name" => "SANTA INES", "municipality_id" => "18"],

            ["name" => "CLARINES", "municipality_id" => "19"],
            ["name" => "GUANAPE", "municipality_id" => "19"],
            ["name" => "SABANA DE UCHIRE", "municipality_id" => "19"],

            ["name" => "CANTAURA", "municipality_id" => "20"],
            ["name" => "LIBERTADOR", "municipality_id" => "20"],
            ["name" => "SANTA ROSA", "municipality_id" => "20"],

            ["name" => "PIRITU", "municipality_id" => "21"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "21"],

            ["name" => "SAN JOSE DE GUANIPA", "municipality_id" => "22"],

            ["name" => "BOCA DE UCHIRE", "municipality_id" => "23"],
            ["name" => "BOCA DE CHAVEZ", "municipality_id" => "23"],

            ["name" => "SANTA ANA", "municipality_id" => "24"],
            ["name" => "PUEBLO NUEVO", "municipality_id" => "24"],

            ["name" => "BARCELONA", "municipality_id" => "25"],
            ["name" => "EL CARMEN", "municipality_id" => "25"],
            ["name" => "SAN CRISTOBAL", "municipality_id" => "25"],
            ["name" => "NARICUAL", "municipality_id" => "25"],

            ["name" => "EL TIGRE", "municipality_id" => "26"],

            ["name" => "EL CHAPARRO", "municipality_id" => "27"],
            ["name" => "TOMAS ALFARO CALATRAVA", "municipality_id" => "27"],

            ["name" => "LECHERIA", "municipality_id" => "28"],
            ["name" => "EL MORRO", "municipality_id" => "28"],

            ["name" => "ACHAGUAS", "municipality_id" => "29"],
            ["name" => "APURITO", "municipality_id" => "29"],
            ["name" => "EL YAGUAL", "municipality_id" => "29"],
            ["name" => "GUACHARA", "municipality_id" => "29"],
            ["name" => "MUCURITAS", "municipality_id" => "29"],
            ["name" => "QUESERAS DEL MEDIO", "municipality_id" => "29"],

            ["name" => "BIRUACA", "municipality_id" => "30"],

            ["name" => "BRUZUAL", "municipality_id" => "31"],
            ["name" => "MANTECAL", "municipality_id" => "31"],
            ["name" => "QUINTERO", "municipality_id" => "31"],
            ["name" => "RINCON HONDO", "municipality_id" => "31"],
            ["name" => "SAN VICENTE", "municipality_id" => "31"],

            ["name" => "GUASDUALITO", "municipality_id" => "32"],
            ["name" => "ARAMENDI", "municipality_id" => "32"],
            ["name" => "EL AMPARO", "municipality_id" => "32"],
            ["name" => "SAN CAMILO", "municipality_id" => "32"],
            ["name" => "URDANETA", "municipality_id" => "32"],

            ["name" => "SAN JUAN DE PAYARA", "municipality_id" => "33"],
            ["name" => "CODAZZI", "municipality_id" => "33"],
            ["name" => "CUNARUCO", "municipality_id" => "33"],

            ["name" => "ELORZA", "municipality_id" => "34"],
            ["name" => "LA TRINIDAD", "municipality_id" => "34"],

            ["name" => "SAN FERNANDO", "municipality_id" => "35"],
            ["name" => "EL RECREO", "municipality_id" => "35"],
            ["name" => "PEÑALVER", "municipality_id" => "35"],
            ["name" => "SAN RAFAEL DE ATAMAICA", "municipality_id" => "35"],

            ["name" => "SAN MATEO", "municipality_id" => "36"],

            ["name" => "CAMATAGUA", "municipality_id" => "37"],
            ["name" => "CARMEN DE CURA", "municipality_id" => "37"],

            ["name" => "MARACAY", "municipality_id" => "38"],
            ["name" => "CHORONI", "municipality_id" => "38"],
            ["name" => "LAS DELICIAS", "municipality_id" => "38"],
            ["name" => "MADRE MARIA DE SAN JOSE", "municipality_id" => "38"],
            ["name" => "JOAQUIN CRESPO", "municipality_id" => "38"],
            ["name" => "PEDRO JOSE OVALLES", "municipality_id" => "38"],
            ["name" => "JOSE CASANOVA GODOY", "municipality_id" => "38"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "38"],

            ["name" => "SANTA CRUZ", "municipality_id" => "39"],

            ["name" => "LA VICTORIA", "municipality_id" => "40"],
            ["name" => "CASTOR NIEVES RIOS", "municipality_id" => "40"],
            ["name" => "LAS GUACAMAYAS", "municipality_id" => "40"],
            ["name" => "PAO DE ZARATE", "municipality_id" => "40"],
            ["name" => "ZUATA", "municipality_id" => "40"],

            ["name" => "EL CONSEJO", "municipality_id" => "41"],

            ["name" => "PALO NEGRO", "municipality_id" => "42"],
            ["name" => "SAN MARTIN DE PORRES", "municipality_id" => "42"],

            ["name" => "EL LIMON", "municipality_id" => "43"],
            ["name" => "CAÑA DE AZUCAR", "municipality_id" => "43"],

            ["name" => "SAN CASIMIRO", "municipality_id" => "44"],
            ["name" => "GÜIRIPA", "municipality_id" => "44"],
            ["name" => "OLLAS DE CARAMACATE", "municipality_id" => "44"],
            ["name" => "VALLE MORIN", "municipality_id" => "44"],

            ["name" => "SAN SEBASTIAN", "municipality_id" => "45"],

            ["name" => "TURMERO", "municipality_id" => "46"],
            ["name" => "AREVALO APONTE", "municipality_id" => "46"],
            ["name" => "CHUAO", "municipality_id" => "46"],
            ["name" => "SAMAN DE GÜERE", "municipality_id" => "46"],
            ["name" => "ALFREDO PACHECO MIRANDA", "municipality_id" => "46"],

            ["name" => "LAS TEJERIAS", "municipality_id" => "47"],
            ["name" => "TIARA", "municipality_id" => "47"],

            ["name" => "CAGUA", "municipality_id" => "48"],
            ["name" => "BELLA VISTA", "municipality_id" => "48"],

            ["name" => "LA COLONIA TOVAR", "municipality_id" => "49"],

            ["name" => "BARBACOAS", "municipality_id" => "50"],
            ["name" => "SAN FRANCISCO DE ASIS", "municipality_id" => "50"],
            ["name" => "TAGUAY", "municipality_id" => "50"],

            ["name" => "VILLA DE CURA", "municipality_id" => "51"],
            ["name" => "MAGDALENO", "municipality_id" => "51"],
            ["name" => "SAN FRANCISCO DE ASIS", "municipality_id" => "51"],
            ["name" => "VALLES DE TUCUTUNEMO", "municipality_id" => "51"],
            ["name" => "AUGUSTO MIJARES", "municipality_id" => "51"],

            ["name" => "SANTA RITA", "municipality_id" => "52"],
            ["name" => "FRANCISCO DE MIRANDA", "municipality_id" => "52"],

            ["name" => "OCUMARE DE LA COSTA", "municipality_id" => "53"],

            ["name" => "SABANETA", "municipality_id" => "54"],
            ["name" => "JUAN ANTONIO RODRIGUEZ DOMINGUEZ", "municipality_id" => "54"],
            ["name" => "VEGUITAS", "municipality_id" => "54"],

            ["name" => "SOCORRO", "municipality_id" => "55"],
            ["name" => "ANDRES BELLO", "municipality_id" => "55"],
            ["name" => "NICOLAS PULIDO", "municipality_id" => "55"],

            ["name" => "ARISMENDI", "municipality_id" => "56"],
            ["name" => "GUADARRAMA", "municipality_id" => "56"],
            ["name" => "LA UNION", "municipality_id" => "56"],
            ["name" => "SAN ANTONIO", "municipality_id" => "56"],

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

            ["name" => "BARINITAS", "municipality_id" => "58"],
            ["name" => "ALTAMIRA", "municipality_id" => "58"],
            ["name" => "CALDERAS", "municipality_id" => "58"],

            ["name" => "BARRANCAS", "municipality_id" => "59"],
            ["name" => "EL SOCORRO", "municipality_id" => "59"],
            ["name" => "MASPARRO", "municipality_id" => "59"],

            ["name" => "SANTA BARBARA", "municipality_id" => "60"],
            ["name" => "JOSE IGNACIO DEL PUMAR", "municipality_id" => "60"],
            ["name" => "PEDRO BRICEÑO MENDEZ", "municipality_id" => "60"],
            ["name" => "RAMON IGNACIO MENDEZ", "municipality_id" => "60"],

            ["name" => "OBISPOS", "municipality_id" => "61"],
            ["name" => "EL REAL", "municipality_id" => "61"],
            ["name" => "LA LUZ", "municipality_id" => "61"],

            ["name" => "CIUDAD BOLIVIA", "municipality_id" => "62"],
            ["name" => "IGNACIO BRICEÑO", "municipality_id" => "62"],
            ["name" => "PAEZ", "municipality_id" => "62"],
            ["name" => "JOSE FELIX RIBAS", "municipality_id" => "62"],

            ["name" => "LIBERTAD", "municipality_id" => "63"],
            ["name" => "DOLORES", "municipality_id" => "63"],
            ["name" => "PALACIOS FAJARDO", "municipality_id" => "63"],
            ["name" => "SANTA ROSA", "municipality_id" => "63"],

            ["name" => "CIUDAD DE NUTRIAS", "municipality_id" => "64"],
            ["name" => "EL REGALO", "municipality_id" => "64"],
            ["name" => "PUERTO DE NUTRIAS", "municipality_id" => "64"],
            ["name" => "SANTA CATALINA", "municipality_id" => "64"],

            ["name" => "EL CANTON", "municipality_id" => "65"],
            ["name" => "SANTA CRUZ DE GUACAS", "municipality_id" => "65"],
            ["name" => "PUERTO VIVAS", "municipality_id" => "65"],

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

            ["name" => "CAICARA DEL ORINOCO", "municipality_id" => "67"],
            ["name" => "ALTAGRACIA", "municipality_id" => "67"],
            ["name" => "ASCENSION FARRERAS", "municipality_id" => "67"],
            ["name" => "GUANIAMO", "municipality_id" => "67"],
            ["name" => "LA URBANA", "municipality_id" => "67"],
            ["name" => "PIJIGUAOS", "municipality_id" => "67"],

            ["name" => "EL CALLAO", "municipality_id" => "68"],

            ["name" => "SANTA ELENA DE UAIREN", "municipality_id" => "69"],
            ["name" => "IKABARU", "municipality_id" => "69"],

            ["name" => "CATEDRAL", "municipality_id" => "70"],
            ["name" => "ZEA", "municipality_id" => "70"],
            ["name" => "ORINOCO", "municipality_id" => "70"],
            ["name" => "JOSE ANTONIO PAEZ", "municipality_id" => "70"],
            ["name" => "MARHUANTA", "municipality_id" => "70"],
            ["name" => "AGUA SALADA", "municipality_id" => "70"],
            ["name" => "VISTA HERMOSA", "municipality_id" => "70"],
            ["name" => "LA SABANITA", "municipality_id" => "70"],

            ["name" => "UPATA", "municipality_id" => "71"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "71"],

            ["name" => "CIUDAD PIAR", "municipality_id" => "72"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "72"],

            ["name" => "GUASIPATI", "municipality_id" => "73"],
            ["name" => "SALOM", "municipality_id" => "73"],

            ["name" => "TUMEREMO", "municipality_id" => "74"],
            ["name" => "DALLA COSTA", "municipality_id" => "74"],
            ["name" => "SAN ISIDRO", "municipality_id" => "74"],

            ["name" => "MARIPA", "municipality_id" => "75"],
            ["name" => "ARIPAO", "municipality_id" => "75"],
            ["name" => "GUARATARO", "municipality_id" => "75"],
            ["name" => "LAS MAJADAS", "municipality_id" => "75"],

            ["name" => "EL PALMAR", "municipality_id" => "76"],

            ["name" => "BEJUMA", "municipality_id" => "77"],
            ["name" => "CANOABO", "municipality_id" => "77"],
            ["name" => "SIMON BOLIVAR", "municipality_id" => "77"],

            ["name" => "GÜIGÜE", "municipality_id" => "78"],
            ["name" => "BELEN", "municipality_id" => "78"],
            ["name" => "TACARIGUA", "municipality_id" => "78"],

            ["name" => "MARIARA", "municipality_id" => "79"],
            ["name" => "AGUAS CALIENTES", "municipality_id" => "79"],

            ["name" => "GUACARA", "municipality_id" => "80"],
            ["name" => "CIUDAD ALIANZA", "municipality_id" => "80"],
            ["name" => "YAGUA", "municipality_id" => "80"],

            ["name" => "MORON", "municipality_id" => "81"],
            ["name" => "URAMA", "municipality_id" => "81"],

            ["name" => "TOCUYITO", "municipality_id" => "82"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "82"],

            ["name" => "LOS GUAYOS", "municipality_id" => "83"],

            ["name" => "MIRANDA", "municipality_id" => "84"],

            ["name" => "MONTALBAN", "municipality_id" => "85"],

            ["name" => "NAGUANAGUA", "municipality_id" => "86"],

            ["name" => "PUERTO CABELLO", "municipality_id" => "87"],
            ["name" => "BARTOLOME SALOM", "municipality_id" => "87"],
            ["name" => "BORBURATA", "municipality_id" => "87"],
            ["name" => "PATANEMO", "municipality_id" => "87"],

            ["name" => "SAN DIEGO", "municipality_id" => "88"],

            ["name" => "SAN JOAQUIN", "municipality_id" => "89"],

            ["name" => "CANDELARIA", "municipality_id" => "90"],
            ["name" => "CATEDRAL", "municipality_id" => "90"],
            ["name" => "EL SOCORRO", "municipality_id" => "90"],
            ["name" => "MIGUEL PEÑA", "municipality_id" => "90"],
            ["name" => "RAFAEL URDANETA", "municipality_id" => "90"],
            ["name" => "SAN BLAS", "municipality_id" => "90"],
            ["name" => "SAN JOSE", "municipality_id" => "90"],
            ["name" => "SANTA ROSA", "municipality_id" => "90"],
            ["name" => "NEGRO PRIMERO", "municipality_id" => "90"],

            ["name" => "COJEDES", "municipality_id" => "91"],
            ["name" => "JUAN DE MATA SUAREZ", "municipality_id" => "91"],

            ["name" => "TINAQUILLO", "municipality_id" => "92"],

            ["name" => "EL BAUL", "municipality_id" => "93"],
            ["name" => "SUCRE", "municipality_id" => "93"],

            ["name" => "MACAPO", "municipality_id" => "94"],
            ["name" => "LA AGUADITA", "municipality_id" => "94"],

            ["name" => "EL PAO", "municipality_id" => "95"],

            ["name" => "LIBERTAD", "municipality_id" => "96"],
            ["name" => "EL AMPARO", "municipality_id" => "96"],

            ["name" => "ROMULO GALLEGOS", "municipality_id" => "97"],

            ["name" => "SAN CARLOS DE AUSTRIA", "municipality_id" => "98"],
            ["name" => "JUAN ANGEL BRAVO", "municipality_id" => "98"],
            ["name" => "MANUEL MANRIQUE", "municipality_id" => "98"],

            ["name" => "TINACO", "municipality_id" => "99"],

            ["name" => "CURURI", "municipality_id" => "100"],
            ["name" => "MANUEL RENAUD", "municipality_id" => "100"],
            ["name" => "PADRE BARRIAL", "municipality_id" => "100"],
            ["name" => "SANTOS DE ABELGAS", "municipality_id" => "100"],

            ["name" => "IMATACA", "municipality_id" => "101"],
            ["name" => "CINCO DE JULIO", "municipality_id" => "101"],
            ["name" => "JUAN BAUTISTA ARISMENDI", "municipality_id" => "101"],
            ["name" => "MANUEL PIAR", "municipality_id" => "101"],
            ["name" => "ROMULO GALLEGOS", "municipality_id" => "101"],

            ["name" => "PEDERNALES", "municipality_id" => "102"],
            ["name" => "LUIS BELTRAN PRIETO FIGUEROA", "municipality_id" => "102"],

            ["name" => "SAN JOSE", "municipality_id" => "103"],
            ["name" => "JOSE VIDAL MARCANO", "municipality_id" => "103"],
            ["name" => "JUAN MILLAN", "municipality_id" => "103"],
            ["name" => "LEONARDO RUIZ PINEDA", "municipality_id" => "103"],
            ["name" => "MARISCAL ANTONIO JOSE DE SUCRE", "municipality_id" => "103"],
            ["name" => "MONSEÑOR ARGIMIRO GARCIA", "municipality_id" => "103"],
            ["name" => "SAN RAFAEL", "municipality_id" => "103"],
            ["name" => "VIRGEN DEL VALLE", "municipality_id" => "103"],

            ["name" => "SAN JUAN DE LOS CAYOS", "municipality_id" => "104"],
            ["name" => "CAPATARIDA", "municipality_id" => "104"],
            ["name" => "LA PASTORA", "municipality_id" => "104"],
            ["name" => "LIBERTADOR", "municipality_id" => "104"],

            ["name" => "SAN LUIS", "municipality_id" => "105"],
            ["name" => "ARACUA", "municipality_id" => "105"],
            ["name" => "LA PEÑA", "municipality_id" => "105"],

            ["name" => "CAPATARIDA", "municipality_id" => "106"],
            ["name" => "BARIRO", "municipality_id" => "106"],
            ["name" => "BOROJO", "municipality_id" => "106"],
            ["name" => "GUAJIRO", "municipality_id" => "106"],
            ["name" => "SEQUE", "municipality_id" => "106"],
            ["name" => "ZAZARIDA", "municipality_id" => "106"],

            ["name" => "YARACAL", "municipality_id" => "107"],

            ["name" => "PUNTO FIJO", "municipality_id" => "108"],
            ["name" => "NORTE", "municipality_id" => "108"],
            ["name" => "CARIRUBANA", "municipality_id" => "108"],
            ["name" => "SANTA ANA", "municipality_id" => "108"],

            ["name" => "LA VELA DE CORO", "municipality_id" => "109"],
            ["name" => "ACURIGUA", "municipality_id" => "109"],
            ["name" => "GUAIBACOA", "municipality_id" => "109"],
            ["name" => "LAS CALDERAS", "municipality_id" => "109"],
            ["name" => "MACORUCA", "municipality_id" => "109"],

            ["name" => "DABAJURO", "municipality_id" => "110"],

            ["name" => "PEDREGAL", "municipality_id" => "111"],
            ["name" => "AGUA CLARA", "municipality_id" => "111"],
            ["name" => "AVARIA", "municipality_id" => "111"],
            ["name" => "PIEDRA GRANDE", "municipality_id" => "111"],
            ["name" => "PURURECHE", "municipality_id" => "111"],

            ["name" => "PUEBLO NUEVO", "municipality_id" => "112"],
            ["name" => "ADICORA", "municipality_id" => "112"],
            ["name" => "BARAIVED", "municipality_id" => "112"],
            ["name" => "BUENA VISTA", "municipality_id" => "112"],
            ["name" => "JADACAQUIVA", "municipality_id" => "112"],
            ["name" => "MORUY", "municipality_id" => "112"],
            ["name" => "ADAURE", "municipality_id" => "112"],
            ["name" => "EL HATO", "municipality_id" => "112"],
            ["name" => "EL VINCULO", "municipality_id" => "112"],

            ["name" => "CHURUGUARA", "municipality_id" => "113"],
            ["name" => "AGUA LARGA", "municipality_id" => "113"],
            ["name" => "EL PAUJI", "municipality_id" => "113"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "113"],
            ["name" => "MAPARARI", "municipality_id" => "113"],

            ["name" => "JACURA", "municipality_id" => "114"],
            ["name" => "AGUA LINDA", "municipality_id" => "114"],
            ["name" => "ARAURIMA", "municipality_id" => "114"],

            ["name" => "LOS TAQUES", "municipality_id" => "115"],
            ["name" => "JUDIBANA", "municipality_id" => "115"],

            ["name" => "MENE DE MAUROA", "municipality_id" => "116"],
            ["name" => "CASIGUA", "municipality_id" => "116"],
            ["name" => "SAN FELIX", "municipality_id" => "116"],

            ["name" => "SANTA ANA DE CORO", "municipality_id" => "117"],
            ["name" => "GUZMAN GUILLERMO", "municipality_id" => "117"],
            ["name" => "MITARE", "municipality_id" => "117"],
            ["name" => "SAN ANTONIO", "municipality_id" => "117"],
            ["name" => "SAN GABRIEL", "municipality_id" => "117"],

            ["name" => "CHICHIRIVICHE", "municipality_id" => "118"],
            ["name" => "BOCA DE TOCUYO", "municipality_id" => "118"],
            ["name" => "TOCUYO DE LA COSTA", "municipality_id" => "118"],

            ["name" => "PALMASOLA", "municipality_id" => "119"],

            ["name" => "CABURE", "municipality_id" => "120"],
            ["name" => "COLINE", "municipality_id" => "120"],
            ["name" => "CURIMAGUA", "municipality_id" => "120"],

            ["name" => "PIRITU", "municipality_id" => "121"],
            ["name" => "SAN JOSE DE LA COSTA", "municipality_id" => "121"],

            ["name" => "MIRIMIRE", "municipality_id" => "122"],

            ["name" => "TUCACAS", "municipality_id" => "123"],
            ["name" => "BOCA DE AROA", "municipality_id" => "123"],

            ["name" => "LA CRUZ DE TARATARA", "municipality_id" => "124"],
            ["name" => "PECAYA", "municipality_id" => "124"],

            ["name" => "TOCOPERO", "municipality_id" => "125"],

            ["name" => "SANTA CRUZ DE BUCARAL", "municipality_id" => "126"],
            ["name" => "EL CHARAL", "municipality_id" => "126"],
            ["name" => "LAS VEGAS DEL TUY", "municipality_id" => "126"],

            ["name" => "URUMACO", "municipality_id" => "127"],
            ["name" => "BRUZUAL", "municipality_id" => "127"],

            ["name" => "PUERTO CUMAREBO", "municipality_id" => "128"],
            ["name" => "LA CIENAGA", "municipality_id" => "128"],
            ["name" => "LA SOLEDAD", "municipality_id" => "128"],
            ["name" => "PUEBLO CUMAREBO", "municipality_id" => "128"],
            ["name" => "ZAZARIDA", "municipality_id" => "128"],

            ["name" => "CAMAGUAN", "municipality_id" => "129"],
            ["name" => "PUERTO MIRANDA", "municipality_id" => "129"],
            ["name" => "UVERITO", "municipality_id" => "129"],

            ["name" => "CHAGUARAMAS", "municipality_id" => "130"],

            ["name" => "EL SOCORRO", "municipality_id" => "131"],

            ["name" => "GUAYABAL", "municipality_id" => "132"],
            ["name" => "CAZORLA", "municipality_id" => "132"],

            ["name" => "VALLE DE LA PASCUA", "municipality_id" => "133"],
            ["name" => "ESPINO", "municipality_id" => "133"],

            ["name" => "LAS MERCEDES", "municipality_id" => "134"],
            ["name" => "CABRUTA", "municipality_id" => "134"],
            ["name" => "SANTA RITA DE MANAPIRE", "municipality_id" => "134"],

            ["name" => "EL SOMBRERO", "municipality_id" => "135"],
            ["name" => "SOSA", "municipality_id" => "135"],

            ["name" => "CALABOZO", "municipality_id" => "136"],
            ["name" => "EL CALVARIO", "municipality_id" => "136"],
            ["name" => "EL RASTRO", "municipality_id" => "136"],
            ["name" => "GUARDATINAJAS", "municipality_id" => "136"],

            ["name" => "ALTAGRACIA DE ORITUCO", "municipality_id" => "137"],
            ["name" => "LEZAMA", "municipality_id" => "137"],
            ["name" => "LIBERTAD DE ORITUCO", "municipality_id" => "137"],
            ["name" => "SAN FRANCISCO DE MACAIRA", "municipality_id" => "137"],
            ["name" => "SAN RAFAEL DE ORITUCO", "municipality_id" => "137"],

            ["name" => "ORTIZ", "municipality_id" => "138"],
            ["name" => "SAN FRANCISCO DE TIZNADOS", "municipality_id" => "138"],
            ["name" => "SAN JOSE DE TIZNADOS", "municipality_id" => "138"],
            ["name" => "SAN LORENZO DE TIZNADOS", "municipality_id" => "138"],

            ["name" => "TUCUPIDO", "municipality_id" => "139"],
            ["name" => "SAN RAFAEL DE LAYA", "municipality_id" => "139"],

            ["name" => "SAN JUAN DE LOS MORROS", "municipality_id" => "140"],
            ["name" => "PARAPARA", "municipality_id" => "140"],
            ["name" => "CANTAGALLO", "municipality_id" => "140"],

            ["name" => "SAN JOSE DE GUARIBE", "municipality_id" => "141"],

            ["name" => "SANTA MARIA DE IPIRE", "municipality_id" => "142"],
            ["name" => "ALTAMIRA", "municipality_id" => "142"],

            ["name" => "ZARAZA", "municipality_id" => "143"],
            ["name" => "SAN JOSE DE UNARE", "municipality_id" => "143"],

            ["name" => "SANARE", "municipality_id" => "144"],
            ["name" => "YAY", "municipality_id" => "144"],
            ["name" => "LA QUEBRADA", "municipality_id" => "144"],

            ["name" => "DUACA", "municipality_id" => "145"],
            ["name" => "FREITEZ", "municipality_id" => "145"],
            ["name" => "JOSE MARIA BLANCO", "municipality_id" => "145"],

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

            ["name" => "QUIBOR", "municipality_id" => "147"],
            ["name" => "CUARA", "municipality_id" => "147"],
            ["name" => "DIEGO DE LOZADA", "municipality_id" => "147"],
            ["name" => "PARAISO DE SAN JOSE", "municipality_id" => "147"],
            ["name" => "SAN MIGUEL", "municipality_id" => "147"],
            ["name" => "TINTORERO", "municipality_id" => "147"],
            ["name" => "JOSE BERNARDO DORANTE", "municipality_id" => "147"],
            ["name" => "CORONEL MARIANO PERAZA", "municipality_id" => "147"],

            ["name" => "EL TOCUYO", "municipality_id" => "148"],
            ["name" => "ANZOATEGUI", "municipality_id" => "148"],
            ["name" => "BOLIVAR", "municipality_id" => "148"],
            ["name" => "GUARICO", "municipality_id" => "148"],
            ["name" => "HILARIO LUNA Y LUNA", "municipality_id" => "148"],
            ["name" => "HUMOCARO BAJO", "municipality_id" => "148"],
            ["name" => "HUMOCARO ALTO", "municipality_id" => "148"],
            ["name" => "LA CANDELARIA", "municipality_id" => "148"],
            ["name" => "MORAN", "municipality_id" => "148"],

            ["name" => "CABUDARE", "municipality_id" => "149"],
            ["name" => "JOSE GREGORIO BASTIDAS", "municipality_id" => "149"],
            ["name" => "AGUA VIVA", "municipality_id" => "149"],

            ["name" => "SARARE", "municipality_id" => "150"],
            ["name" => "BURIA", "municipality_id" => "150"],
            ["name" => "GUSTAVO VEGAS LEON", "municipality_id" => "150"],

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

            ["name" => "SIQUISIQUE", "municipality_id" => "152"],
            ["name" => "XAGUAZA", "municipality_id" => "152"],
            ["name" => "SAN MIGUEL", "municipality_id" => "152"],
            ["name" => "MOROTURO", "municipality_id" => "152"],

            ["name" => "EL VIGIA", "municipality_id" => "153"],
            ["name" => "HECTOR AMABLE MORA", "municipality_id" => "153"],
            ["name" => "PULIDO MENDEZ", "municipality_id" => "153"],
            ["name" => "PRESIDENTE BETANCOURT", "municipality_id" => "153"],
            ["name" => "PRESIDENTE PAEZ", "municipality_id" => "153"],
            ["name" => "PRESIDENTE ROMULO GALLEGOS", "municipality_id" => "153"],
            ["name" => "GABRIEL PICON GONZALEZ", "municipality_id" => "153"],

            ["name" => "LA AZULITA", "municipality_id" => "154"],

            ["name" => "SANTA CRUZ DE MORA", "municipality_id" => "155"],
            ["name" => "MESA BOLIVAR", "municipality_id" => "155"],
            ["name" => "MESA DE LAS PALMAS", "municipality_id" => "155"],

            ["name" => "ARICAGUA", "municipality_id" => "156"],
            ["name" => "SAN ANTONIO", "municipality_id" => "156"],

            ["name" => "CANAGUA", "municipality_id" => "157"],
            ["name" => "CHACANTA", "municipality_id" => "157"],
            ["name" => "EL MOLINO", "municipality_id" => "157"],
            ["name" => "GUAIMARAL", "municipality_id" => "157"],
            ["name" => "MUCUTUY", "municipality_id" => "157"],
            ["name" => "MUCUCHACHI", "municipality_id" => "157"],

            ["name" => "EJIDO", "municipality_id" => "158"],
            ["name" => "FERNANDEZ PEÑA", "municipality_id" => "158"],
            ["name" => "MATRIZ", "municipality_id" => "158"],
            ["name" => "MONTALBAN", "municipality_id" => "158"],
            ["name" => "JAJI", "municipality_id" => "158"],
            ["name" => "LA MESA", "municipality_id" => "158"],
            ["name" => "SAN JOSE DEL SUR", "municipality_id" => "158"],

            ["name" => "TUCANI", "municipality_id" => "159"],
            ["name" => "FLORENCIO RAMIREZ", "municipality_id" => "159"],

            ["name" => "SANTO DOMINGO", "municipality_id" => "160"],
            ["name" => "LAS PIEDRAS", "municipality_id" => "160"],

            ["name" => "GUARAQUE", "municipality_id" => "161"],
            ["name" => "MESA DE QUINTERO", "municipality_id" => "161"],
            ["name" => "RIO NEGRO", "municipality_id" => "161"],

            ["name" => "ARAPUEY", "municipality_id" => "162"],
            ["name" => "PALMIRA", "municipality_id" => "162"],

            ["name" => "SAN CRISTOBAL DE TORONDOY", "municipality_id" => "163"],
            ["name" => "SAN JOSE DE LAS FLORES", "municipality_id" => "163"],

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

            ["name" => "TIMOTES", "municipality_id" => "165"],
            ["name" => "ANDRES ELOY BLANCO", "municipality_id" => "165"],
            ["name" => "LA VENTA", "municipality_id" => "165"],
            ["name" => "PIÑANGO", "municipality_id" => "165"],

            ["name" => "SANTA ELENA DE ARENALES", "municipality_id" => "166"],
            ["name" => "ELOY PAREDES", "municipality_id" => "166"],
            ["name" => "SAN RAFAEL DE ALCÁZAR", "municipality_id" => "166"],

            ["name" => "SANTA MARIA DE CAPARO", "municipality_id" => "167"],

            ["name" => "PUEBLO LLANO", "municipality_id" => "168"],

            ["name" => "MUCUCHIES", "municipality_id" => "169"],
            ["name" => "MUCURUBA", "municipality_id" => "169"],
            ["name" => "SAN RAFAEL", "municipality_id" => "169"],
            ["name" => "CACUTE", "municipality_id" => "169"],
            ["name" => "LA TOMA", "municipality_id" => "169"],

            ["name" => "BAILADORES", "municipality_id" => "170"],
            ["name" => "GERONIMO MALDONADO", "municipality_id" => "170"],

            ["name" => "TABAY", "municipality_id" => "171"],

            ["name" => "LAGUNILLAS", "municipality_id" => "172"],
            ["name" => "CHIGUARA", "municipality_id" => "172"],
            ["name" => "ESTANQUES", "municipality_id" => "172"],
            ["name" => "LA TRAMPA", "municipality_id" => "172"],
            ["name" => "PUEBLO NUEVO DEL SUR", "municipality_id" => "172"],
            ["name" => "SAN JUAN", "municipality_id" => "172"],

            ["name" => "TOVAR", "municipality_id" => "173"],
            ["name" => "EL AMPARO", "municipality_id" => "173"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "173"],
            ["name" => "SANTA CRUZ DE MORA", "municipality_id" => "173"],

            ["name" => "NUEVA BOLIVIA", "municipality_id" => "174"],
            ["name" => "INDEPENDENCIA", "municipality_id" => "174"],
            ["name" => "MARIA DE LA CONCEPCION PALACIOS BLANCO", "municipality_id" => "174"],
            ["name" => "SANTA APOLONIA", "municipality_id" => "174"],

            ["name" => "ZEA", "municipality_id" => "175"],
            ["name" => "CAÑO EL TIGRE", "municipality_id" => "175"],

            ["name" => "CAUCAGUA", "municipality_id" => "176"],
            ["name" => "ARAGÜITA", "municipality_id" => "176"],
            ["name" => "PANAQUIRE", "municipality_id" => "176"],
            ["name" => "RIO CHICO", "municipality_id" => "176"],
            ["name" => "EL CAFE", "municipality_id" => "176"],
            ["name" => "MARIZAPA", "municipality_id" => "176"],
            ["name" => "SAN JOSE DE RIO CHICO", "municipality_id" => "176"],
            ["name" => "TACARIGUA DE LA LAGUNA", "municipality_id" => "176"],

            ["name" => "SAN JOSE DE BARLOVENTO", "municipality_id" => "177"],
            ["name" => "CUMBO", "municipality_id" => "177"],

            ["name" => "BARUTA", "municipality_id" => "178"],
            ["name" => "EL CAFETAL", "municipality_id" => "178"],
            ["name" => "LAS MINAS DE BARUTA", "municipality_id" => "178"],

            ["name" => "HIGUEROTE", "municipality_id" => "179"],
            ["name" => "CURREIRE", "municipality_id" => "179"],
            ["name" => "TACARIGUA DE BRILLANTE", "municipality_id" => "179"],

            ["name" => "MAMPORAL", "municipality_id" => "180"],

            ["name" => "CARRIZAL", "municipality_id" => "181"],

            ["name" => "CHACAO", "municipality_id" => "182"],

            ["name" => "CHARALLAVE", "municipality_id" => "183"],
            ["name" => "LAS BRISAS", "municipality_id" => "183"],

            ["name" => "EL HATILLO", "municipality_id" => "184"],

            ["name" => "LOS TEQUES", "municipality_id" => "185"],
            ["name" => "ALTAGRACIA DE LA MONTAÑA", "municipality_id" => "185"],
            ["name" => "CECILIO ACOSTA", "municipality_id" => "185"],
            ["name" => "EL JARILLO", "municipality_id" => "185"],
            ["name" => "LAGUNETAS", "municipality_id" => "185"],
            ["name" => "SAN PEDRO DE LOS ALTOS", "municipality_id" => "185"],

            ["name" => "SANTA TERESA DEL TUY", "municipality_id" => "186"],
            ["name" => "EL CARTANAL", "municipality_id" => "186"],

            ["name" => "OCUMARE DEL TUY", "municipality_id" => "187"],
            ["name" => "LA DEMOCRACIA", "municipality_id" => "187"],
            ["name" => "SANTA BARBARA", "municipality_id" => "187"],

            ["name" => "SAN ANTONIO DE LOS ALTOS", "municipality_id" => "188"],

            ["name" => "GUATIRE", "municipality_id" => "189"],
            ["name" => "EL JARILLO", "municipality_id" => "189"],
            ["name" => "SANTA CRUZ DEL VALLE", "municipality_id" => "189"],

            ["name" => "SANTA LUCIA", "municipality_id" => "190"],
            ["name" => "EL ROSARIO", "municipality_id" => "190"],
            ["name" => "SOAPIRE", "municipality_id" => "190"],

            ["name" => "CUPIRA", "municipality_id" => "191"],
            ["name" => "MACHURUCUTO", "municipality_id" => "191"],

            ["name" => "GUARENAS", "municipality_id" => "192"],

            ["name" => "SAN FRANCISCO DE YARE", "municipality_id" => "193"],
            ["name" => "SAN ANTONIO DE YARE", "municipality_id" => "193"],

            ["name" => "PETARE", "municipality_id" => "194"],
            ["name" => "LEONCIO MARTINEZ", "municipality_id" => "194"],
            ["name" => "CAUCAGÜITA", "municipality_id" => "194"],
            ["name" => "FILAS DE MARICHE", "municipality_id" => "194"],
            ["name" => "LA DOLORITA", "municipality_id" => "194"],
            ["name" => "MARICHE", "municipality_id" => "194"],

            ["name" => "CUA", "municipality_id" => "195"],
            ["name" => "NUEVA CUA", "municipality_id" => "195"],

            ["name" => "GUATIRE", "municipality_id" => "196"],
            ["name" => "BOLIVAR", "municipality_id" => "196"],

            ["name" => "SAN ANTONIO", "municipality_id" => "197"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "197"],

            ["name" => "AGUASAY", "municipality_id" => "198"],

            ["name" => "CARIPITO", "municipality_id" => "199"],

            ["name" => "CARIPE", "municipality_id" => "200"],
            ["name" => "TERESEN", "municipality_id" => "200"],
            ["name" => "EL GUACHARO", "municipality_id" => "200"],
            ["name" => "SAN AGUSTIN", "municipality_id" => "200"],
            ["name" => "LA GUANOTA", "municipality_id" => "200"],
            ["name" => "SABANA DE PIEDRA", "municipality_id" => "200"],

            ["name" => "CAICARA DE MATURIN", "municipality_id" => "201"],
            ["name" => "AREO", "municipality_id" => "201"],
            ["name" => "SAN FELIX", "municipality_id" => "201"],
            ["name" => "VIENTO FRESCO", "municipality_id" => "201"],

            ["name" => "PUNTA DE MATA", "municipality_id" => "202"],
            ["name" => "EL TEJERO", "municipality_id" => "202"],

            ["name" => "TEMBLADOR", "municipality_id" => "203"],
            ["name" => "CHAGUARAMAS", "municipality_id" => "203"],
            ["name" => "LAS ALHUACAS", "municipality_id" => "203"],
            ["name" => "TABASCA", "municipality_id" => "203"],

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

            ["name" => "ARAGUA DE MATURIN", "municipality_id" => "205"],
            ["name" => "APARICIO", "municipality_id" => "205"],
            ["name" => "CHAGUARAMAL", "municipality_id" => "205"],
            ["name" => "EL PINTO", "municipality_id" => "205"],
            ["name" => "GUANAGUANA", "municipality_id" => "205"],
            ["name" => "LA TOSCANA", "municipality_id" => "205"],
            ["name" => "TAGUAYA", "municipality_id" => "205"],

            ["name" => "QUIRIQUIRE", "municipality_id" => "206"],
            ["name" => "CACHIPO", "municipality_id" => "206"],

            ["name" => "SANTA BARBARA", "municipality_id" => "207"],

            ["name" => "BARRANCAS", "municipality_id" => "208"],
            ["name" => "LOS BARRANCOS DE FAJARDO", "municipality_id" => "208"],

            ["name" => "URACOA", "municipality_id" => "209"],

            ["name" => "PLAZA PARAGUACHI", "municipality_id" => "210"],

            ["name" => "LA ASUNCION", "municipality_id" => "211"],

            ["name" => "SAN JUAN BAUTISTA", "municipality_id" => "212"],
            ["name" => "ZABALA", "municipality_id" => "212"],

            ["name" => "EL VALLE DEL ESPIRITU SANTO", "municipality_id" => "213"],
            ["name" => "FRANCISCO FAJARDO", "municipality_id" => "213"],

            ["name" => "SANTA ANA", "municipality_id" => "214"],
            ["name" => "GUEVARA", "municipality_id" => "214"],
            ["name" => "MATASIETE", "municipality_id" => "214"],
            ["name" => "BOLIVAR", "municipality_id" => "214"],

            ["name" => "PAMPATAR", "municipality_id" => "215"],
            ["name" => "AGUIRRE", "municipality_id" => "215"],

            ["name" => "JUAN GRIEGO", "municipality_id" => "216"],
            ["name" => "ADRIAN", "municipality_id" => "216"],

            ["name" => "PORLAMAR", "municipality_id" => "217"],

            ["name" => "BOCA DEL RIO", "municipality_id" => "218"],
            ["name" => "SAN FRANCISCO", "municipality_id" => "218"],

            ["name" => "PUNTA DE PIEDRAS", "municipality_id" => "219"],
            ["name" => "LOS BARALES", "municipality_id" => "219"],

            ["name" => "SAN PEDRO DE COCHE", "municipality_id" => "220"],
            ["name" => "VICENTE FUENTES", "municipality_id" => "220"],

            ["name" => "AGUA BLANCA", "municipality_id" => "221"],
            ["name" => "LA ENCRUCIJADA", "municipality_id" => "221"],

            ["name" => "ARAURE", "municipality_id" => "222"],
            ["name" => "RIO ACARIGUA", "municipality_id" => "222"],

            ["name" => "PIRITU", "municipality_id" => "223"],
            ["name" => "UVERAL", "municipality_id" => "223"],

            ["name" => "GUANARE", "municipality_id" => "224"],
            ["name" => "CORDOBA", "municipality_id" => "224"],
            ["name" => "SAN JOSE DE LA MONTAÑA", "municipality_id" => "224"],
            ["name" => "SAN JUAN DE GUANAGUANARE", "municipality_id" => "224"],
            ["name" => "VIRGEN DE COROMOTO", "municipality_id" => "224"],

            ["name" => "GUANARITO", "municipality_id" => "225"],
            ["name" => "TRINIDAD DE LA CAPILLA", "municipality_id" => "225"],
            ["name" => "DIVINA PASTORA", "municipality_id" => "225"],

            ["name" => "CHABASQUEN", "municipality_id" => "226"],
            ["name" => "PEÑA BLANCA", "municipality_id" => "226"],

            ["name" => "OSPINO", "municipality_id" => "227"],
            ["name" => "APARICION", "municipality_id" => "227"],
            ["name" => "LA ESTACION", "municipality_id" => "227"],

            ["name" => "ACARIGUA", "municipality_id" => "228"],
            ["name" => "PAYARA", "municipality_id" => "228"],
            ["name" => "PIMPINELA", "municipality_id" => "228"],
            ["name" => "RAMON PERAZA", "municipality_id" => "228"],

            ["name" => "PAPELON", "municipality_id" => "229"],
            ["name" => "CAÑO DELGADITO", "municipality_id" => "229"],

            ["name" => "BOCONOITO", "municipality_id" => "230"],
            ["name" => "ANTONIA TORRES", "municipality_id" => "230"],

            ["name" => "SAN RAFAEL DE ONOTO", "municipality_id" => "231"],
            ["name" => "SANTA FE", "municipality_id" => "231"],
            ["name" => "EL MOLINO", "municipality_id" => "231"],

            ["name" => "SANTA ROSALIA", "municipality_id" => "232"],
            ["name" => "FLORIDA", "municipality_id" => "232"],

            ["name" => "BISCUCUY", "municipality_id" => "233"],
            ["name" => "BOCONO", "municipality_id" => "233"],
            ["name" => "CAMPO AMOR", "municipality_id" => "233"],
            ["name" => "MASPARRO", "municipality_id" => "233"],
            ["name" => "SAN JOSE DE SAGUAZ", "municipality_id" => "233"],
            ["name" => "SAN RAFAEL DE PALO ALZADO", "municipality_id" => "233"],

            ["name" => "TUREN", "municipality_id" => "234"],
            ["name" => "LA TRINIDAD", "municipality_id" => "234"],
            ["name" => "SAN ANTONIO", "municipality_id" => "234"],
            ["name" => "COLONIA TUREN", "municipality_id" => "234"],

            ["name" => "CASANAY", "municipality_id" => "235"],
            ["name" => "MARIÑO", "municipality_id" => "235"],
            ["name" => "RICAURTE", "municipality_id" => "235"],

            ["name" => "SAN JOSE DE AREOCUAR", "municipality_id" => "236"],
            ["name" => "TAVERA ACOSTA", "municipality_id" => "236"],

            ["name" => "RIO CARIBE", "municipality_id" => "237"],
            ["name" => "ANTONIO JOSE DE SUCRE", "municipality_id" => "237"],
            ["name" => "EL MORRO DE PUERTO SANTO", "municipality_id" => "237"],
            ["name" => "PUERTO SANTO", "municipality_id" => "237"],
            ["name" => "SAN JUAN DE LAS GALDONAS", "municipality_id" => "237"],

            ["name" => "EL PILAR", "municipality_id" => "238"],
            ["name" => "EL RINCON", "municipality_id" => "238"],
            ["name" => "GENERAL FRANCISCO ANTONIO VASQUEZ", "municipality_id" => "238"],
            ["name" => "GUARAUNOS", "municipality_id" => "238"],
            ["name" => "TUNIPUY", "municipality_id" => "238"],
            ["name" => "UNION", "municipality_id" => "238"],

            ["name" => "CARUPANO", "municipality_id" => "239"],
            ["name" => "BOLIVAR", "municipality_id" => "239"],
            ["name" => "SANTA CATALINA", "municipality_id" => "239"],
            ["name" => "SANTA ROSA", "municipality_id" => "239"],
            ["name" => "SANTA TERESA", "municipality_id" => "239"],

            ["name" => "MARIGUITAR", "municipality_id" => "240"],
            ["name" => "ARENAS", "municipality_id" => "240"],
            ["name" => "ARICAGUA", "municipality_id" => "240"],
            ["name" => "COCOLLAR", "municipality_id" => "240"],
            ["name" => "SAN FERNANDO", "municipality_id" => "240"],
            ["name" => "SAN LORENZO", "municipality_id" => "240"],

            ["name" => "YAGUARAPARO", "municipality_id" => "241"],
            ["name" => "EL PAUJIL", "municipality_id" => "241"],
            ["name" => "LIBERTAD", "municipality_id" => "241"],

            ["name" => "ARAYA", "municipality_id" => "242"],
            ["name" => "CHACOPATA", "municipality_id" => "242"],
            ["name" => "MANICUARE", "municipality_id" => "242"],

            ["name" => "TUNIPUY", "municipality_id" => "243"],
            ["name" => "CAMPO ELIAS", "municipality_id" => "243"],
            ["name" => "GÜIRIA", "municipality_id" => "243"],

            ["name" => "IRAPA", "municipality_id" => "244"],
            ["name" => "CAMPO CLARO", "municipality_id" => "244"],
            ["name" => "MARABAL", "municipality_id" => "244"],
            ["name" => "SAN ANTONIO DE IRAPA", "municipality_id" => "244"],
            ["name" => "SORO", "municipality_id" => "244"],

            ["name" => "SAN ANTONIO DEL GOLFO", "municipality_id" => "245"],

            ["name" => "CUMANACOA", "municipality_id" => "246"],
            ["name" => "ARENAS", "municipality_id" => "246"],
            ["name" => "ARICAGUA", "municipality_id" => "246"],
            ["name" => "SAN LORENZO", "municipality_id" => "246"],
            ["name" => "SAN FERNANDO", "municipality_id" => "246"],

            ["name" => "CARIACO", "municipality_id" => "247"],
            ["name" => "CATUARO", "municipality_id" => "247"],
            ["name" => "RENDON", "municipality_id" => "247"],
            ["name" => "SANTA CRUZ", "municipality_id" => "247"],
            ["name" => "SANTA MARIA", "municipality_id" => "247"],

            ["name" => "CUMANA", "municipality_id" => "248"],
            ["name" => "ALTAGRACIA", "municipality_id" => "248"],
            ["name" => "SANTA INES", "municipality_id" => "248"],
            ["name" => "VALENTIN VALIENTE", "municipality_id" => "248"],
            ["name" => "AYACUCHO", "municipality_id" => "248"],
            ["name" => "SAN JUAN", "municipality_id" => "248"],
            ["name" => "RAUL LEONI", "municipality_id" => "248"],
            ["name" => "GRAN MARISCAL", "municipality_id" => "248"],

            ["name" => "GÜIRIA", "municipality_id" => "249"],
            ["name" => "CRISTOBAL COLON", "municipality_id" => "249"],
            ["name" => "PUNTA DE PIEDRA", "municipality_id" => "249"],
            ["name" => "BIDEAU", "municipality_id" => "249"],

            ["name" => "CORDERO", "municipality_id" => "250"],

            ["name" => "LAS MESAS", "municipality_id" => "251"],

            ["name" => "SAN JUAN DE COLON", "municipality_id" => "252"],
            ["name" => "AYACUCHO", "municipality_id" => "252"],
            ["name" => "SAN PEDRO DEL RIO", "municipality_id" => "252"],

            ["name" => "SAN ANTONIO DEL TACHIRA", "municipality_id" => "253"],
            ["name" => "JUAN VICENTE GOMEZ", "municipality_id" => "253"],
            ["name" => "PALOTAL", "municipality_id" => "253"],

            ["name" => "TARIBÁ", "municipality_id" => "254"],
            ["name" => "AMENODORO RANGEL LAMUS", "municipality_id" => "254"],
            ["name" => "LA FLORIDA", "municipality_id" => "254"],

            ["name" => "SANTA ANA DE TACHIRA", "municipality_id" => "255"],

            ["name" => "SAN RAFAEL DEL PINAL", "municipality_id" => "256"],
            ["name" => "SANTO DOMINGO", "municipality_id" => "256"],

            ["name" => "SAN JOSE DE BOLIVAR", "municipality_id" => "257"],

            ["name" => "LA FRIA", "municipality_id" => "258"],
            ["name" => "BOCA DE GRITA", "municipality_id" => "258"],
            ["name" => "JOSE ANTONIO PAEZ", "municipality_id" => "258"],

            ["name" => "PALMIRA", "municipality_id" => "259"],

            ["name" => "CAPACHO NUEVO", "municipality_id" => "260"],
            ["name" => "JUAN GERMAN ROSCIO", "municipality_id" => "260"],
            ["name" => "ROMAN CARDENAS", "municipality_id" => "260"],

            ["name" => "LA GRITA", "municipality_id" => "261"],
            ["name" => "EMILIO CONSTANTINO GUERRERO", "municipality_id" => "261"],
            ["name" => "MONSEÑOR MIGUEL ANTONIO SALAS", "municipality_id" => "261"],

            ["name" => "EL COBRE", "municipality_id" => "262"],

            ["name" => "RUBIO", "municipality_id" => "263"],
            ["name" => "BRAMON", "municipality_id" => "263"],
            ["name" => "LA PETROLEA", "municipality_id" => "263"],
            ["name" => "QUINIMARI", "municipality_id" => "263"],

            ["name" => "CAPACHO VIEJO", "municipality_id" => "264"],
            ["name" => "CIPRIANO CASTRO", "municipality_id" => "264"],
            ["name" => "MANUEL FELIPE RUGELES", "municipality_id" => "264"],

            ["name" => "ABEJALES", "municipality_id" => "265"],
            ["name" => "DORADAS", "municipality_id" => "265"],
            ["name" => "EMETERIO OCHOA", "municipality_id" => "265"],
            ["name" => "SAN JOAQUIN DE NAVAY", "municipality_id" => "265"],

            ["name" => "LOBATERA", "municipality_id" => "266"],
            ["name" => "CONSTITUCION", "municipality_id" => "266"],

            ["name" => "MICHELENA", "municipality_id" => "267"],

            ["name" => "COLONCITO", "municipality_id" => "268"],
            ["name" => "LA PALMITA", "municipality_id" => "268"],

            ["name" => "UREÑA", "municipality_id" => "269"],
            ["name" => "NUEVA ARCADIA", "municipality_id" => "269"],

            ["name" => "DELICIAS", "municipality_id" => "270"],

            ["name" => "LA TENDIDA", "municipality_id" => "271"],
            ["name" => "BOCONO", "municipality_id" => "271"],
            ["name" => "HERNANDEZ", "municipality_id" => "271"],

            ["name" => "LA CONCORDIA", "municipality_id" => "272"],
            ["name" => "PEDRO MARIA MORANTES", "municipality_id" => "272"],
            ["name" => "SAN JUAN BAUTISTA", "municipality_id" => "272"],
            ["name" => "SAN SEBASTIAN", "municipality_id" => "272"],
            ["name" => "DR. FRANCISCO ROMERO LOBO", "municipality_id" => "272"],

            ["name" => "SEBORUCO", "municipality_id" => "273"],

            ["name" => "SAN SIMON", "municipality_id" => "274"],

            ["name" => "QUENIQUEA", "municipality_id" => "275"],
            ["name" => "SAN PABLO", "municipality_id" => "275"],
            ["name" => "SAN JOSECITO", "municipality_id" => "275"],

            ["name" => "SAN JOSECITO", "municipality_id" => "276"],

            ["name" => "PREGONERO", "municipality_id" => "277"],
            ["name" => "CARDENAS", "municipality_id" => "277"],
            ["name" => "JUAN PABLO PEÑALOZA", "municipality_id" => "277"],
            ["name" => "POTOSI", "municipality_id" => "277"],

            ["name" => "UMUQUENA", "municipality_id" => "278"],

            ["name" => "SANTA ISABEL", "municipality_id" => "279"],
            ["name" => "ARAGUANEY", "municipality_id" => "279"],
            ["name" => "EL JAGÜITO", "municipality_id" => "279"],
            ["name" => "LA ESPERANZA", "municipality_id" => "279"],

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

            ["name" => "SABANA GRANDE", "municipality_id" => "281"],
            ["name" => "CHEREGÜE", "municipality_id" => "281"],
            ["name" => "GRANADOS", "municipality_id" => "281"],

            ["name" => "CHEJENDE", "municipality_id" => "282"],
            ["name" => "ARNOLDO GABALDON", "municipality_id" => "282"],
            ["name" => "BOLIVIA", "municipality_id" => "282"],
            ["name" => "CARRILLO", "municipality_id" => "282"],
            ["name" => "CEGARRA", "municipality_id" => "282"],
            ["name" => "MANUEL SALVADOR ULLOA", "municipality_id" => "282"],
            ["name" => "SAN JOSE", "municipality_id" => "282"],

            ["name" => "CARACHE", "municipality_id" => "283"],
            ["name" => "CUICAS", "municipality_id" => "283"],
            ["name" => "LA CONCEPCION", "municipality_id" => "283"],
            ["name" => "PANAMERICANA", "municipality_id" => "283"],
            ["name" => "SANTA CRUZ", "municipality_id" => "283"],

            ["name" => "ESCUQUE", "municipality_id" => "284"],
            ["name" => "LA UNION", "municipality_id" => "284"],
            ["name" => "SABANA LIBRE", "municipality_id" => "284"],
            ["name" => "SANTA RITA", "municipality_id" => "284"],

            ["name" => "EL SOCORRO", "municipality_id" => "285"],
            ["name" => "ANTONIO JOSE DE SUCRE", "municipality_id" => "285"],
            ["name" => "LOS CAPACHOS", "municipality_id" => "285"],

            ["name" => "CAMPO ELIAS", "municipality_id" => "286"],
            ["name" => "ARNOLDO GABALDON", "municipality_id" => "286"],

            ["name" => "SANTA APOLONIA", "municipality_id" => "287"],
            ["name" => "EL PROGRESO", "municipality_id" => "287"],
            ["name" => "LA CEIBA", "municipality_id" => "287"],
            ["name" => "TRES DE FEBRERO", "municipality_id" => "287"],

            ["name" => "EL DIVIDIVE", "municipality_id" => "288"],
            ["name" => "AGUA CALIENTE", "municipality_id" => "288"],
            ["name" => "EL CENIZO", "municipality_id" => "288"],
            ["name" => "AGUA SANTA", "municipality_id" => "288"],
            ["name" => "VALERITA", "municipality_id" => "288"],

            ["name" => "MONTE CARMELO", "municipality_id" => "289"],
            ["name" => "BUENA VISTA", "municipality_id" => "289"],
            ["name" => "SANTA MARIA DEL JUNCAL", "municipality_id" => "289"],

            ["name" => "MOTATAN", "municipality_id" => "290"],
            ["name" => "EL BAÑO", "municipality_id" => "290"],
            ["name" => "JALISCO", "municipality_id" => "290"],

            ["name" => "PAMPAN", "municipality_id" => "291"],
            ["name" => "FLOR DE PATRIA", "municipality_id" => "291"],
            ["name" => "LA PAZ", "municipality_id" => "291"],
            ["name" => "SANTA ANA", "municipality_id" => "291"],

            ["name" => "PAMPANITO", "municipality_id" => "292"],
            ["name" => "LA CONCEPCION", "municipality_id" => "292"],
            ["name" => "PAMPANITO II", "municipality_id" => "292"],

            ["name" => "BETIJOQUE", "municipality_id" => "293"],
            ["name" => "EL CEDRO", "municipality_id" => "293"],
            ["name" => "JOSE GREGORIO HERNANDEZ", "municipality_id" => "293"],
            ["name" => "LA PUEBLITA", "municipality_id" => "293"],

            ["name" => "CARVAJAL", "municipality_id" => "294"],
            ["name" => "ANTONIO NICOLAS BRICEÑO", "municipality_id" => "294"],
            ["name" => "CAMPO ALEGRE", "municipality_id" => "294"],
            ["name" => "JOSE LEONARDO SUAREZ", "municipality_id" => "294"],

            ["name" => "SABANA DE MENDOZA", "municipality_id" => "295"],
            ["name" => "JUNIN", "municipality_id" => "295"],
            ["name" => "VALMORE RODRIGUEZ", "municipality_id" => "295"],
            ["name" => "EL PARAISO", "municipality_id" => "295"],

            ["name" => "TRUJILLO", "municipality_id" => "296"],
            ["name" => "ANDRES LINARES", "municipality_id" => "296"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "296"],
            ["name" => "CRISTOBAL MENDOZA", "municipality_id" => "296"],
            ["name" => "CRUZ CARRILLO", "municipality_id" => "296"],
            ["name" => "MATRIZ", "municipality_id" => "296"],
            ["name" => "MONSEÑOR CARRILLO", "municipality_id" => "296"],
            ["name" => "TRES ESQUINAS", "municipality_id" => "296"],

            ["name" => "LA QUEBRADA", "municipality_id" => "297"],
            ["name" => "CABIMBU", "municipality_id" => "297"],
            ["name" => "JAJO", "municipality_id" => "297"],
            ["name" => "LA MESA DE ESNUJAQUE", "municipality_id" => "297"],
            ["name" => "SANTIAGO", "municipality_id" => "297"],
            ["name" => "TUÑAME", "municipality_id" => "297"],

            ["name" => "VALERA", "municipality_id" => "298"],
            ["name" => "JUAN IGNACIO MONTILLA", "municipality_id" => "298"],
            ["name" => "LA BEATRIZ", "municipality_id" => "298"],
            ["name" => "MERCEDES DIAZ", "municipality_id" => "298"],
            ["name" => "SAN LUIS", "municipality_id" => "298"],
            ["name" => "MENDOZA FRIA", "municipality_id" => "298"],

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

            ["name" => "ARISTIDES BASTIDAS", "municipality_id" => "300"],

            ["name" => "BOLIVAR", "municipality_id" => "301"],

            ["name" => "CHIVACOA", "municipality_id" => "302"],
            ["name" => "CAMPO ELIAS", "municipality_id" => "302"],

            ["name" => "COCOROTE", "municipality_id" => "303"],

            ["name" => "INDEPENDENCIA", "municipality_id" => "304"],

            ["name" => "SABANA DE PARRA", "municipality_id" => "305"],

            ["name" => "LA TRINIDAD", "municipality_id" => "306"],

            ["name" => "MANUEL MONGE", "municipality_id" => "307"],

            ["name" => "NIRGUA", "municipality_id" => "308"],
            ["name" => "SALOM", "municipality_id" => "308"],
            ["name" => "TEMERLA", "municipality_id" => "308"],

            ["name" => "YARITAGUA", "municipality_id" => "309"],
            ["name" => "SAN ANDRES", "municipality_id" => "309"],

            ["name" => "SAN FELIPE", "municipality_id" => "310"],
            ["name" => "ALBARICO", "municipality_id" => "310"],
            ["name" => "SAN JAVIER", "municipality_id" => "310"],

            ["name" => "SUCRE", "municipality_id" => "311"],

            ["name" => "URACHICHE", "municipality_id" => "312"],

            ["name" => "FARRIAR", "municipality_id" => "313"],
            ["name" => "EL GUAYABO", "municipality_id" => "313"],

            ["name" => "ISLA DE TOAS", "municipality_id" => "314"],
            ["name" => "MONAGAS", "municipality_id" => "314"],

            ["name" => "SAN TIMOTEO", "municipality_id" => "315"],
            ["name" => "GENERAL URDANETA", "municipality_id" => "315"],
            ["name" => "LIBERTADOR", "municipality_id" => "315"],
            ["name" => "MARCELINO BRICEÑO", "municipality_id" => "315"],
            ["name" => "PUEBLO NUEVO", "municipality_id" => "315"],
            ["name" => "MANUEL GUANIPA MATOS", "municipality_id" => "315"],

            ["name" => "CABIMAS", "municipality_id" => "316"],
            ["name" => "GERMAN RIOS LINARES", "municipality_id" => "316"],
            ["name" => "JORGE HERNANDEZ", "municipality_id" => "316"],
            ["name" => "LA ROSA", "municipality_id" => "316"],
            ["name" => "PUNTA GORDA", "municipality_id" => "316"],
            ["name" => "CARMEN HERRERA", "municipality_id" => "316"],
            ["name" => "SAN BENITO", "municipality_id" => "316"],
            ["name" => "ROMULO BETANCOURT", "municipality_id" => "316"],
            ["name" => "ARISTIDES CALVANI", "municipality_id" => "316"],

            ["name" => "ENCONTRADOS", "municipality_id" => "317"],
            ["name" => "UDON PEREZ", "municipality_id" => "317"],

            ["name" => "SAN CARLOS DEL ZULIA", "municipality_id" => "318"],
            ["name" => "MORALITO", "municipality_id" => "318"],
            ["name" => "SANTA BARBARA", "municipality_id" => "318"],
            ["name" => "URRIBARRI", "municipality_id" => "318"],

            ["name" => "PUEBLO NUEVO-EL CHIVO", "municipality_id" => "319"],
            ["name" => "AGUAS CALIENTES", "municipality_id" => "319"],
            ["name" => "CARLOS QUEVEDO", "municipality_id" => "319"],
            ["name" => "SIMON RODRIGUEZ", "municipality_id" => "319"],

            ["name" => "LA CONCEPCION", "municipality_id" => "320"],
            ["name" => "JOSE RAMON YEPEZ", "municipality_id" => "320"],
            ["name" => "EL PARAISO", "municipality_id" => "320"],
            ["name" => "SAN JOSE", "municipality_id" => "320"],
            ["name" => "MARIANO PARRA LEON", "municipality_id" => "320"],

            ["name" => "CASIGUA-EL CUBO", "municipality_id" => "321"],
            ["name" => "BARALT", "municipality_id" => "321"],

            ["name" => "CONCEPCION", "municipality_id" => "322"],
            ["name" => "POTRERITOS", "municipality_id" => "322"],
            ["name" => "EL CARMELO", "municipality_id" => "322"],
            ["name" => "CHIQUINQUIRA", "municipality_id" => "322"],
            ["name" => "ANDRES BELLO", "municipality_id" => "322"],

            ["name" => "LAGUNILLAS", "municipality_id" => "323"],
            ["name" => "ALONSO DE OJEDA", "municipality_id" => "323"],
            ["name" => "CAMPO LARA", "municipality_id" => "323"],
            ["name" => "ELEAZAR LOPEZ CONTRERAS", "municipality_id" => "323"],
            ["name" => "EL DIQUE", "municipality_id" => "323"],
            ["name" => "PARAUTE", "municipality_id" => "323"],
            ["name" => "LIBERTAD", "municipality_id" => "323"],
            ["name" => "VENEZUELA", "municipality_id" => "323"],

            ["name" => "MACHIQUES", "municipality_id" => "324"],
            ["name" => "LIBERTAD", "municipality_id" => "324"],
            ["name" => "RIO NEGRO", "municipality_id" => "324"],
            ["name" => "SAN JOSE DE PERIJA", "municipality_id" => "324"],
            ["name" => "BARTOLOME DE LAS CASAS", "municipality_id" => "324"],

            ["name" => "SAN RAFAEL", "municipality_id" => "325"],
            ["name" => "LAS PARCELAS", "municipality_id" => "325"],
            ["name" => "MONSERRATE", "municipality_id" => "325"],
            ["name" => "LA SIERRITA", "municipality_id" => "325"],
            ["name" => "ISLA DE SAN CARLOS", "municipality_id" => "325"],

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

            ["name" => "LOS PUERTOS DE ALTAGRACIA", "municipality_id" => "327"],
            ["name" => "ALTAGRACIA", "municipality_id" => "327"],
            ["name" => "ANA MARIA CAMPOS", "municipality_id" => "327"],
            ["name" => "FARIA", "municipality_id" => "327"],
            ["name" => "SAN ANTONIO", "municipality_id" => "327"],
            ["name" => "SAN JOSE", "municipality_id" => "327"],

            ["name" => "SINAMAICA", "municipality_id" => "328"],
            ["name" => "EL GUAYABO", "municipality_id" => "328"],
            ["name" => "PARAGUAIPOA", "municipality_id" => "328"],

            ["name" => "EL ROSARIO", "municipality_id" => "329"],
            ["name" => "SAN JOSE", "municipality_id" => "329"],
            ["name" => "DONALDO GARCIA", "municipality_id" => "329"],

            ["name" => "SAN FRANCISCO", "municipality_id" => "330"],
            ["name" => "EL BAJO", "municipality_id" => "330"],
            ["name" => "DOMITILA FLORES", "municipality_id" => "330"],
            ["name" => "FRANCISCO OCHOA", "municipality_id" => "330"],
            ["name" => "LOS CORTIJOS", "municipality_id" => "330"],
            ["name" => "MARCIAL HERNANDEZ", "municipality_id" => "330"],
            ["name" => "JOSE DOMINGO RUS", "municipality_id" => "330"],

            ["name" => "SANTA RITA", "municipality_id" => "331"],
            ["name" => "EL MENE", "municipality_id" => "331"],
            ["name" => "PEDRO LUCAS URRIBARRI", "municipality_id" => "331"],
            ["name" => "JOSE CENOVIO URRIBARRI", "municipality_id" => "331"],

            ["name" => "TIA JUANA", "municipality_id" => "332"],
            ["name" => "CIUDAD OJEDA", "municipality_id" => "332"],
            ["name" => "RAFAEL URDANETA", "municipality_id" => "332"],

            ["name" => "BOBURES", "municipality_id" => "333"],
            ["name" => "CASCAJAL", "municipality_id" => "333"],
            ["name" => "EL POZON", "municipality_id" => "333"],
            ["name" => "SAN JOSE", "municipality_id" => "333"],
            ["name" => "SANTA MARIA", "municipality_id" => "333"],
            ["name" => "EL BATAL", "municipality_id" => "333"],
            ["name" => "MONSEÑOR ARTURO CELESTINO ALVAREZ", "municipality_id" => "333"],

            ["name" => "VALMORE RODRIGUEZ", "municipality_id" => "334"],
            ["name" => "RAUL CUENCA", "municipality_id" => "334"],
            ["name" => "LA VICTORIA", "municipality_id" => "334"],

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
    }
}
