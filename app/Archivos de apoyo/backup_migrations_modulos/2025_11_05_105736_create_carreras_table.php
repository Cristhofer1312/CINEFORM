<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comun.carreras', function (Blueprint $table) {
            $table->increments('id_carrera');
            $table->string('nombre_carrera', 255);
            $table->string('descripcion', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::table('comun.carreras')->insert([
            ['id_carrera' => 1, 'nombre_carrera' => 'Actor', 'descripcion' => 'Interpreta personajes en teatro, cine, televisión y otras representaciones.'],
            ['id_carrera' => 2, 'nombre_carrera' => 'Administración de Empresas', 'descripcion' => 'Gestión y dirección de empresas'],
            ['id_carrera' => 3, 'nombre_carrera' => 'Administrativo de Empresas', 'descripcion' => 'Gestor y organizador de recursos empresariales.'],
            ['id_carrera' => 4, 'nombre_carrera' => 'Analista de Seguridad de Información', 'descripcion' => 'Protege datos y sistemas empresariales de acceso no autorizado.'],
            ['id_carrera' => 5, 'nombre_carrera' => 'Antropología', 'descripcion' => 'Estudio de las culturas humanas'],
            ['id_carrera' => 6, 'nombre_carrera' => 'Antropólogo Cultural', 'descripcion' => 'Investiga y preserva las manifestaciones culturales y sociales.'],
            ['id_carrera' => 7, 'nombre_carrera' => 'Archivista', 'descripcion' => 'Gestiona y conserva archivos y documentos históricos o artísticos.'],
            ['id_carrera' => 8, 'nombre_carrera' => 'Arquitecto', 'descripcion' => 'Profesional en diseño y construcción de edificaciones.'],
            ['id_carrera' => 9, 'nombre_carrera' => 'Arquitectura', 'descripcion' => 'Diseño y construcción de edificaciones'],
            ['id_carrera' => 10, 'nombre_carrera' => 'Arquitectura Paisajista', 'descripcion' => 'Diseño ambiental de espacios exteriores'],
            ['id_carrera' => 11, 'nombre_carrera' => 'Artes y Diseño', 'descripcion' => 'Disciplinas artísticas y creatividad'],
            ['id_carrera' => 12, 'nombre_carrera' => 'Artista Visual', 'descripcion' => 'Crea obras de arte en diferentes medios como pintura, escultura y medios digitales.'],
            ['id_carrera' => 13, 'nombre_carrera' => 'Bailarín / Coreógrafo', 'descripcion' => 'Crea y ejecuta movimientos para espectáculos dancísticos.'],
            ['id_carrera' => 14, 'nombre_carrera' => 'Biología', 'descripcion' => 'Estudios de organismos vivos y sus ecosistemas'],
            ['id_carrera' => 15, 'nombre_carrera' => 'Ciencias Ambientales', 'descripcion' => 'Estudio del medio ambiente y conservación'],
            ['id_carrera' => 16, 'nombre_carrera' => 'Ciencias de la Computación', 'descripcion' => 'Informática y desarrollo de software'],
            ['id_carrera' => 17, 'nombre_carrera' => 'Ciencias de la Comunicación', 'descripcion' => 'Teoría y práctica de medios y comunicación'],
            ['id_carrera' => 18, 'nombre_carrera' => 'Ciencias de la Información', 'descripcion' => 'Gestión de datos y tecnología de la información'],
            ['id_carrera' => 19, 'nombre_carrera' => 'Ciencias de la Salud', 'descripcion' => 'Ciencias aplicadas a la salud humana'],
            ['id_carrera' => 20, 'nombre_carrera' => 'Ciencias Políticas', 'descripcion' => 'Estudio de sistemas políticos y gobierno'],
            ['id_carrera' => 21, 'nombre_carrera' => 'Ciencias Sociales', 'descripcion' => 'Estudio de la sociedad y relaciones humanas'],
            ['id_carrera' => 22, 'nombre_carrera' => 'Científico de Datos / Analista de Datos', 'descripcion' => 'Experto en análisis y extracción de información valiosa de grandes volúmenes de datos.'],
            ['id_carrera' => 23, 'nombre_carrera' => 'Community Manager', 'descripcion' => 'Gestor de comunidades digitales y redes sociales.'],
            ['id_carrera' => 24, 'nombre_carrera' => 'Community Manager Cultural', 'descripcion' => 'Administra y promueve proyectos culturales en redes y comunidades digitales.'],
            ['id_carrera' => 25, 'nombre_carrera' => 'Comunicación y Periodismo', 'descripcion' => 'Medios y comunicación social'],
            ['id_carrera' => 26, 'nombre_carrera' => 'Conservador de Museos', 'descripcion' => 'Preserva, restaura y exhibe patrimonio cultural y artístico.'],
            ['id_carrera' => 27, 'nombre_carrera' => 'Consultor de Arte', 'descripcion' => 'Asesora en gestión y adquisición de obras de arte y proyectos culturales.'],
            ['id_carrera' => 28, 'nombre_carrera' => 'Consultor en Sostenibilidad', 'descripcion' => 'Diseña estrategias para minimizar impacto ambiental.'],
            ['id_carrera' => 29, 'nombre_carrera' => 'Crítico de Arte', 'descripcion' => 'Analiza y evalúa obras artísticas para medios y publicaciones.'],
            ['id_carrera' => 30, 'nombre_carrera' => 'Curador de Exposiciones', 'descripcion' => 'Organiza y selecciona obras para exhibiciones en museos y galerías.'],
            ['id_carrera' => 31, 'nombre_carrera' => 'Derecho', 'descripcion' => 'Estudios jurídicos y legales'],
            ['id_carrera' => 32, 'nombre_carrera' => 'Derecho Internacional', 'descripcion' => 'Legislación y normas internacionales'],
            ['id_carrera' => 33, 'nombre_carrera' => 'Desarrollador de Software', 'descripcion' => 'Especialista en creación y mantenimiento de aplicaciones y plataformas digitales.'],
            ['id_carrera' => 34, 'nombre_carrera' => 'Director de Arte', 'descripcion' => 'Supervisa la visión estética en proyectos publicitarios, cine y multimedia.'],
            ['id_carrera' => 35, 'nombre_carrera' => 'Diseñador de Interiores', 'descripcion' => 'Transforma espacios con creatividad y funcionalidad.'],
            ['id_carrera' => 36, 'nombre_carrera' => 'Diseñador de Videojuegos', 'descripcion' => 'Desarrolla personajes, escenarios y animaciones para videojuegos.'],
            ['id_carrera' => 37, 'nombre_carrera' => 'Diseñador Gráfico', 'descripcion' => 'Combina arte y tecnología para crear diseños visuales comunicativos.'],
            ['id_carrera' => 38, 'nombre_carrera' => 'Diseño Gráfico', 'descripcion' => 'Comunicación visual y diseño'],
            ['id_carrera' => 39, 'nombre_carrera' => 'Dramaturgo', 'descripcion' => 'Escribe y desarrolla obras para teatro y otros formatos escénicos.'],
            ['id_carrera' => 40, 'nombre_carrera' => 'Economía', 'descripcion' => 'Análisis económico y finanzas'],
            ['id_carrera' => 41, 'nombre_carrera' => 'Editor de Video', 'descripcion' => 'Monta y edita contenidos audiovisuales para producciones creativas.'],
            ['id_carrera' => 42, 'nombre_carrera' => 'Educación', 'descripcion' => 'Formación y pedagogía'],
            ['id_carrera' => 43, 'nombre_carrera' => 'Educación Física', 'descripcion' => 'Ciencias del movimiento y actividad física'],
            ['id_carrera' => 44, 'nombre_carrera' => 'Educador en Línea / E-learning', 'descripcion' => 'Diseña y administra contenidos educativos digitales.'],
            ['id_carrera' => 45, 'nombre_carrera' => 'Enfermería', 'descripcion' => 'Atención de la salud y cuidado de pacientes'],
            ['id_carrera' => 46, 'nombre_carrera' => 'Enfermero/a', 'descripcion' => 'Profesional de la salud encargado del cuidado del paciente.'],
            ['id_carrera' => 47, 'nombre_carrera' => 'Escenógrafo', 'descripcion' => 'Diseña escenarios para producciones teatrales y audiovisuales.'],
            ['id_carrera' => 48, 'nombre_carrera' => 'Escritor / Novelista', 'descripcion' => 'Crea obras literarias originales en diferentes géneros.'],
            ['id_carrera' => 49, 'nombre_carrera' => 'Especialista en Ciberseguridad', 'descripcion' => 'Profesional que protege sistemas y redes ante amenazas y ataques.'],
            ['id_carrera' => 50, 'nombre_carrera' => 'Especialista en Inteligencia Artificial', 'descripcion' => 'Diseña y desarrolla algoritmos para sistemas inteligentes.'],
            ['id_carrera' => 51, 'nombre_carrera' => 'Especialista en Marketing Digital', 'descripcion' => 'Experto en estrategias y herramientas digitales para publicidad y SEO.'],
            ['id_carrera' => 52, 'nombre_carrera' => 'Filosofía', 'descripcion' => 'Estudio de problemas fundamentales y pensamiento crítico'],
            ['id_carrera' => 53, 'nombre_carrera' => 'Física', 'descripcion' => 'Estudio de las propiedades fundamentales de la materia'],
            ['id_carrera' => 54, 'nombre_carrera' => 'Fotógrafo Artístico', 'descripcion' => 'Captura imágenes con propósitos creativos y conceptuales.'],
            ['id_carrera' => 55, 'nombre_carrera' => 'Fotoperiodista', 'descripcion' => 'Fotografía eventos de actualidad con un enfoque documental y artístico.'],
            ['id_carrera' => 56, 'nombre_carrera' => 'Gastronomía', 'descripcion' => 'Arte y técnica culinaria'],
            ['id_carrera' => 57, 'nombre_carrera' => 'Gestor Cultural', 'descripcion' => 'Organiza y administra actividades culturales y eventos artísticos.'],
            ['id_carrera' => 58, 'nombre_carrera' => 'Gestor de Recursos Humanos Digital', 'descripcion' => 'Aplica tecnologías para la gestión del talento humano.'],
            ['id_carrera' => 59, 'nombre_carrera' => 'Guionista', 'descripcion' => 'Crea los guiones para películas, series y videojuegos.'],
            ['id_carrera' => 60, 'nombre_carrera' => 'Historia', 'descripcion' => 'Estudio del pasado'],
            ['id_carrera' => 61, 'nombre_carrera' => 'Ilustrador', 'descripcion' => 'Diseña imágenes para libros, publicidad, multimedia y juegos.'],
            ['id_carrera' => 62, 'nombre_carrera' => 'Ingeniería Ambiental', 'descripcion' => 'Desarrollo sostenible y gestión ambiental'],
            ['id_carrera' => 63, 'nombre_carrera' => 'Ingeniería Bioquímica', 'descripcion' => 'Ingeniería aplicada a procesos bioquímicos'],
            ['id_carrera' => 64, 'nombre_carrera' => 'Ingeniería Civil', 'descripcion' => 'Diseño y construcción de infraestructuras'],
            ['id_carrera' => 65, 'nombre_carrera' => 'Ingeniería de Sistemas', 'descripcion' => 'Desarrollo y mantenimiento de sistemas informáticos'],
            ['id_carrera' => 66, 'nombre_carrera' => 'Ingeniería Eléctrica', 'descripcion' => 'Sistemas eléctricos y electrónicos'],
            ['id_carrera' => 67, 'nombre_carrera' => 'Ingeniería Electrónica', 'descripcion' => 'Diseño y aplicación de circuitos electrónicos'],
            ['id_carrera' => 68, 'nombre_carrera' => 'Ingeniería en Alimentos', 'descripcion' => 'Producción y conservación de alimentos'],
            ['id_carrera' => 69, 'nombre_carrera' => 'Ingeniería en Sistemas de Telecomunicaciones', 'descripcion' => 'Comunicación digital y sistemas de telecomunicación'],
            ['id_carrera' => 70, 'nombre_carrera' => 'Ingeniería en Telecomunicaciones', 'descripcion' => 'Redes y sistemas de comunicación'],
            ['id_carrera' => 71, 'nombre_carrera' => 'Ingeniería Industrial', 'descripcion' => 'Optimización de procesos industriales'],
            ['id_carrera' => 72, 'nombre_carrera' => 'Ingeniería Mecánica', 'descripcion' => 'Diseño y funcionamiento de máquinas y motores'],
            ['id_carrera' => 73, 'nombre_carrera' => 'Ingeniería Química', 'descripcion' => 'Procesos químicos e industriales'],
            ['id_carrera' => 74, 'nombre_carrera' => 'Ingeniero Ambiental', 'descripcion' => 'Profesional en gestión y conservación ambiental.'],
            ['id_carrera' => 75, 'nombre_carrera' => 'Ingeniero en Robótica', 'descripcion' => 'Diseña y desarrolla sistemas robotizados.'],
            ['id_carrera' => 76, 'nombre_carrera' => 'Ingeniero en Telecomunicaciones', 'descripcion' => 'Diseña y mantiene sistemas de comunicación modernos.'],
            ['id_carrera' => 77, 'nombre_carrera' => 'Letras', 'descripcion' => 'Estudios lingüísticos y literarios'],
            ['id_carrera' => 78, 'nombre_carrera' => 'Literatura', 'descripcion' => 'Estudio de obras literarias'],
            ['id_carrera' => 79, 'nombre_carrera' => 'Matemáticas', 'descripcion' => 'Ciencia de los números y estructuras lógicas'],
            ['id_carrera' => 80, 'nombre_carrera' => 'Matemáticas Aplicadas', 'descripcion' => 'Aplicación de matemática en problemas prácticos'],
            ['id_carrera' => 81, 'nombre_carrera' => 'Medicina', 'descripcion' => 'Ciencias de la salud y atención médica'],
            ['id_carrera' => 82, 'nombre_carrera' => 'Médico', 'descripcion' => 'Profesional de salud que diagnostica y trata enfermedades.'],
            ['id_carrera' => 83, 'nombre_carrera' => 'Música y Composición Musical', 'descripcion' => 'Arte musical y composición'],
            ['id_carrera' => 84, 'nombre_carrera' => 'Productor Audiovisual', 'descripcion' => 'Coordina la producción de contenidos para cine, televisión y multimedia.'],
            ['id_carrera' => 85, 'nombre_carrera' => 'Productor Musical', 'descripcion' => 'Supervisa la creación, grabación y producción de música.'],
            ['id_carrera' => 86, 'nombre_carrera' => 'Profesor de Arte', 'descripcion' => 'Enseña técnicas artísticas y teoría del arte en diferentes niveles educativos.'],
            ['id_carrera' => 87, 'nombre_carrera' => 'Psicología', 'descripcion' => 'Estudio del comportamiento y salud mental'],
            ['id_carrera' => 88, 'nombre_carrera' => 'Psicólogo (Organizacional y Clínico)', 'descripcion' => 'Estudia y atiende la salud mental en entornos laborales y personales.'],
            ['id_carrera' => 89, 'nombre_carrera' => 'Psicopedagogía', 'descripcion' => 'Educación y apoyo psicológico en el aprendizaje'],
            ['id_carrera' => 90, 'nombre_carrera' => 'Química', 'descripcion' => 'Ciencia de las sustancias y sus transformaciones'],
            ['id_carrera' => 91, 'nombre_carrera' => 'Relaciones Internacionales', 'descripcion' => 'Diplomacia y relaciones entre países'],
            ['id_carrera' => 92, 'nombre_carrera' => 'Restaurador de Arte', 'descripcion' => 'Restaura y conserva obras de arte dañadas o deterioradas.'],
            ['id_carrera' => 93, 'nombre_carrera' => 'Sociología', 'descripcion' => 'Estudio de la sociedad y sus estructuras'],
            ['id_carrera' => 94, 'nombre_carrera' => 'Técnico de Sonido', 'descripcion' => 'Gestiona la grabación y producción de audio para espectáculos y medios.'],
            ['id_carrera' => 95, 'nombre_carrera' => 'Técnico en Energías Renovables', 'descripcion' => 'Especialista en diseño e instalación de sistemas de energía sustentable.'],
            ['id_carrera' => 96, 'nombre_carrera' => 'Técnico en Iluminación', 'descripcion' => 'Controla la iluminación para escenarios y producciones artísticas.'],
            ['id_carrera' => 97, 'nombre_carrera' => 'Técnico en Logística y Cadena de Suministro', 'descripcion' => 'Optimiza procesos relacionados con la distribución y almacenaje.'],
            ['id_carrera' => 98, 'nombre_carrera' => 'Traductor', 'descripcion' => 'Traduce textos literarios y técnicos, facilitando la comunicación cultural.'],
            ['id_carrera' => 99, 'nombre_carrera' => 'Turismo', 'descripcion' => 'Administración y promoción turística'],
            ['id_carrera' => 100, 'nombre_carrera' => 'Veterinaria', 'descripcion' => 'Medicina y cuidado de animales'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comun.carreras');
    }
};
